<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\System\Db\DbDriverInterface;


/**
 * This class handles all traffic from and to the database and takes care of a correct tx-handling
 * CHANGE WITH CARE!
 * Since version 3.4, prepared statments are supported. As a parameter-escaping, only the ? char is allowed,
 * named params are not supported at the moment.
 * Old plain queries are still allows, but will be discontinued around kajona 3.5 / 4.0. Up from kajona > 3.4.0
 * a warning will be generated when using the old apis.
 * When using prepared statements, all escaping is done by the database layer.
 * When using the old, plain queries, you have to escape all embedded arguments yourself by using dbsafeString()
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class Database
{
    private $objConfig = null; //Config-Objekt
    private $arrQueryCache = array(); //Array to cache queries
    private $arrTablesCache = array();
    private $intNumber = 0; //Number of queries send to database
    private $intNumberCache = 0; //Number of queries returned from cache

    /**
     * Instance of the db-driver defined in the configs
     *
     * @var DbDriverInterface
     */
    private $objDbDriver = null; //An object of the db-driver defined in the configs
    private static $objDB = null; //An object of this class

    /**
     * The number of transactions currently opened
     *
     * @var int
     */
    private $intNumberOfOpenTransactions = 0; //The number of transactions opened

    /**
     * Set to true, if a rollback is requested, but there are still open tx.
     * In this case, the tx is rolled back, when the enclosing tx is finished
     *
     * @var bool
     */
    private $bitCurrentTxIsDirty = false;

    /**
     * Flag indicating if the internal connection was setup.
     * Needed to have a proper lazy-connection initialization.
     *
     * @var bool
     */
    private $bitConnected = false;


    /**
     * Constructor
     */
    private function __construct()
    {
        $this->objConfig = Config::getInstance();

        //Load the defined db-driver
        $strDriver = $this->objConfig->getConfig("dbdriver");
        if ($strDriver != "%%defaultdriver%%") {

            //build a class-name & include the driver
            $strPath = Resourceloader::getInstance()->getPathForFile("/system/db/Db".ucfirst($strDriver).".php");
            $objDriver = Classloader::getInstance()->getInstanceFromFilename($strPath);
            if ($objDriver !== null) {
                $this->objDbDriver = $objDriver;
            }
            else {
                throw new Exception("db-driver Db".ucfirst($strDriver)." could not be loaded", Exception::$level_FATALERROR);
            }

        }
        else {
            //Do not throw any exception here, otherwise an endless loop will exit with an overloaded stack frame
            //throw new Exception("No db-driver defined!", Exception::$level_FATALERROR);
        }
    }

    /**
     * Destructor.
     * Handles the closing of remaining tx and closes the db-connection
     */
    public function __destruct()
    {
        if ($this->intNumberOfOpenTransactions != 0) {
            //something bad happened. rollback, plz
            $this->objDbDriver->transactionRollback();
            Logger::getInstance(Logger::DBLOG)->addLogRow("Rolled back open transactions on deletion of current instance of Db!", Logger::$levelWarning);
        }


        if ($this->objDbDriver !== null && $this->bitConnected) {
            Logger::getInstance(Logger::DBLOG)->addLogRow("closing database-connection", Logger::$levelInfo);
            $this->objDbDriver->dbclose();
        }

    }

    /**
     * Method to get an instance of the db-class
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$objDB == null) {
            self::$objDB = new Database();
        }

        return self::$objDB;
    }


    /**
     * This method connects with the database
     *
     * @return void
     */
    private function dbconnect()
    {
        if ($this->objDbDriver !== null) {
            try {
                Logger::getInstance(Logger::DBLOG)->addLogRow("creating database-connection using driver ".get_class($this->objDbDriver), Logger::$levelInfo);
                $this->objDbDriver->dbconnect(
                    $this->objConfig->getConfig("dbhost"),
                    $this->objConfig->getConfig("dbusername"),
                    $this->objConfig->getConfig("dbpassword"),
                    $this->objConfig->getConfig("dbname"),
                    $this->objConfig->getConfig("dbport")
                );
            }
            catch (Exception $objException) {
                $objException->processException();
            }

            $this->bitConnected = true;
        }
    }

    /**
     * Creates a single query in order to insert multiple rows at one time.
     * For most databases, this will create s.th. like
     * INSERT INTO $strTable ($arrColumns) VALUES (?, ?), (?, ?)...
     *
     * @param string $strTable
     * @param string[] $arrColumns
     * @param array $arrValueSets
     *
     * @return bool
     */
    public function multiInsert($strTable, $arrColumns, $arrValueSets)
    {
        if (count($arrValueSets) == 0) {
            return true;
        }

        //chunk columns down to less then 1000 params, could lead to errors on oracle and sqlite otherwise
        $bitReturn = true;
        $intSetsPerInsert = floor(970 / count($arrColumns));

        foreach (array_chunk($arrValueSets, $intSetsPerInsert) as $arrSingleValueSet) {
            $bitReturn = $bitReturn && $this->objDbDriver->triggerMultiInsert(_dbprefix_.$strTable, $arrColumns, $arrSingleValueSet, $this);
        }

        return $bitReturn;
    }

    /**
     * Sending a query to the database
     *
     * @param string $strQuery
     *
     * @return bool
     * @deprecated
     */
    public function _query($strQuery)
    {
        return $this->_pQuery($strQuery, array());
    }

    /**
     * Sending a prepared statement to the database
     *
     * @param string $strQuery
     * @param array $arrParams
     * @param array $arrEscapes An array of booleans for each param, used to block the escaping of html-special chars.
     *                          If not passed, all params will be cleaned.
     *
     * @return bool
     * @since 3.4
     */
    public function _pQuery($strQuery, $arrParams, $arrEscapes = array())
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        $bitReturn = false;

        $strQuery = $this->processQuery($strQuery);

        if (_dblog_) {
            Logger::getInstance(Logger::QUERIES)->addLogRow("\r\n".$strQuery."\r\n params: ".implode(", ", $arrParams), Logger::$levelInfo, true);
        }

        //Increasing the counter
        $this->intNumber++;

        if ($this->objDbDriver != null) {
            $bitReturn = $this->objDbDriver->_pQuery($strQuery, $this->dbsafeParams($arrParams, $arrEscapes));
        }

        if (!$bitReturn) {
            $this->getError($strQuery, $arrParams);
        }

        return $bitReturn;
    }


    /**
     * Returns one row from a result-set
     *
     * @param string $strQuery
     * @param int $intNr
     * @param bool $bitCache
     *
     * @return array
     * @deprecated use getPRow() instead
     */
    public function getRow($strQuery, $intNr = 0, $bitCache = true)
    {
        return $this->getPRow($strQuery, array(), $intNr, $bitCache);
    }


    /**
     * Returns one row from a result-set.
     * Makes use of prepared statements.
     *
     * @param string $strQuery
     * @param array $arrParams
     * @param int $intNr
     * @param bool $bitCache
     *
     * @return array
     */
    public function getPRow($strQuery, $arrParams, $intNr = 0, $bitCache = true)
    {
        $arrTemp = $this->getPArray($strQuery, $arrParams, $intNr, $intNr + 1, $bitCache);
        if (count($arrTemp) > 0) {
            return $arrTemp[$intNr];
        }
        else {
            return array();
        }
    }


    /**
     * Method to get an array of rows for a given query from the database
     *
     * @param string $strQuery
     * @param bool $bitCache
     *
     * @return array
     * @deprecated use getPArray() instead
     */
    public function getArray($strQuery, $bitCache = true)
    {
        Logger::getInstance(Logger::DBLOG)->addLogRow("deprecated getArray call: ".$strQuery, Logger::$levelWarning);
        return $this->getPArray($strQuery, array(), null, null, $bitCache);
    }


    /**
     * Method to get an array of rows for a given query from the database.
     * Makes use of prepared statements.
     *
     * @param string $strQuery
     * @param array $arrParams
     * @param int|null $intStart
     * @param int|null $intEnd
     * @param bool $bitCache
     *
     * @return array
     * @since 3.4
     */
    public function getPArray($strQuery, $arrParams, $intStart = null, $intEnd = null, $bitCache = true)
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        //param validation
        if ((int)$intStart < 0) {
            $intStart = null;
        }

        if ((int)$intEnd < 0) {
            $intEnd = null;
        }


        $strQuery = $this->processQuery($strQuery);
        //Increasing global counter
        $this->intNumber++;

        $strQueryMd5 = null;
        if ($bitCache) {
            $strQueryMd5 = md5($strQuery.implode(",", $arrParams).$intStart.$intEnd);
            if (isset($this->arrQueryCache[$strQueryMd5])) {
                //Increasing Cache counter
                $this->intNumberCache++;
                return $this->arrQueryCache[$strQueryMd5];
            }
        }

        $arrReturn = array();

        if (_dblog_) {
            Logger::getInstance(Logger::QUERIES)->addLogRow("\r\n".$strQuery."\r\n params: ".implode(", ", $arrParams), Logger::$levelInfo, true);
        }

        if ($this->objDbDriver != null) {
            if ($intStart !== null && $intEnd !== null && $intStart !== false && $intEnd !== false) {
                $arrReturn = $this->objDbDriver->getPArraySection($strQuery, $this->dbsafeParams($arrParams), $intStart, $intEnd);
            }
            else {
                $arrReturn = $this->objDbDriver->getPArray($strQuery, $this->dbsafeParams($arrParams));
            }

            if ($arrReturn === false) {
                $this->getError($strQuery, $arrParams);
                return array();
            }
            if ($bitCache) {
                $this->arrQueryCache[$strQueryMd5] = $arrReturn;
            }
        }
        return $arrReturn;
    }

    /**
     * Returns just a part of a recordset, defined by the start- and the end-rows,
     * defined by the params.
     * <b>Note:</b> Use array-like counters, so the first row is startRow 0 whereas
     * the n-th row is the (n-1)th key!!!
     *
     * @param string $strQuery
     * @param int $intStart
     * @param int $intEnd
     * @param bool $bitCache
     *
     * @return array
     * @deprecated use getPArray() instead
     */
    public function getArraySection($strQuery, $intStart, $intEnd, $bitCache = true)
    {
        Logger::getInstance(Logger::DBLOG)->addLogRow("deprecated getArraySection call: ".$strQuery, Logger::$levelWarning);
        return $this->getPArray($strQuery, array(), $intStart, $intEnd, $bitCache);
    }


    /**
     * Returns just a part of a recordset, defined by the start- and the end-rows,
     * defined by the params. Makes use of prepared statements
     * <b>Note:</b> Use array-like counters, so the first row is startRow 0 whereas
     * the n-th row is the (n-1)th key!!!
     *
     * @param string $strQuery
     * @param array $arrParams
     * @param int $intStart
     * @param int $intEnd
     * @param bool $bitCache
     *
     * @return array
     * @deprecated use getPArray() instead
     */
    public function getPArraySection($strQuery, $arrParams, $intStart, $intEnd, $bitCache = true)
    {
        Logger::getInstance(Logger::DBLOG)->addLogRow("deprecated getPArraySection call: ".$strQuery, Logger::$levelWarning);
        return $this->getPArray($strQuery, $arrParams, $intStart, $intEnd, $bitCache);
    }

    /**
     * Writes the last DB-Error to the screen
     *
     * @param string $strQuery
     *
     * @throws Exception
     * @return void
     */
    private function getError($strQuery, $arrParams)
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        $strError = "";
        if ($this->objDbDriver != null) {
            $strError = $this->objDbDriver->getError();
        }

        //reprocess query
        $strQuery = str_ireplace(
            array(" from ", " where ", " and ", " group by ", " order by "),
            array("\nFROM ", "\nWHERE ", "\n\tAND ", "\nGROUP BY ", "\nORDER BY "),
            $strQuery
        );

        //$strQuery = $this->prettifyQuery($strQuery, $arrParams);

        $strErrorCode = "";
        $strErrorCode .= "Error in query\n\n";
        $strErrorCode .= "Error:\n";
        $strErrorCode .= $strError."\n\n";
        $strErrorCode .= "Query:\n";
        $strErrorCode .= $strQuery."\n";
        $strErrorCode .= "\n\n";
        $strErrorCode .= "Params: ".implode(", ", $arrParams)."\n";
        $strErrorCode .= "Callstack:\n";
        if (function_exists("debug_backtrace")) {
            $arrStack = debug_backtrace();

            foreach ($arrStack as $intPos => $arrValue) {
                $strErrorCode .= (isset($arrValue["file"]) ? $arrValue["file"] : "n.a.")."\n\t Row ".(isset($arrValue["line"]) ? $arrValue["line"] : "n.a.").", function ".$arrStack[$intPos]["function"]."\n";
            }
        }
        //send a warning to the logger
        Logger::getInstance(Logger::DBLOG)->addLogRow($strErrorCode, Logger::$levelWarning);

        if ($this->objConfig->getDebug("debuglevel") > 0) {
            throw new Exception($strErrorCode, Exception::$level_ERROR);
        }

    }


    /**
     * Starts a transaction
     *
     * @return void
     */
    public function transactionBegin()
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        if ($this->objDbDriver != null) {
            //just start a new tx, if no other tx is open
            if ($this->intNumberOfOpenTransactions == 0) {
                $this->objDbDriver->transactionBegin();
            }

            //increase tx-counter
            $this->intNumberOfOpenTransactions++;

        }
    }

    /**
     * Ends a tx successfully
     *
     * @return void
     */
    public function transactionCommit()
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        if ($this->objDbDriver != null) {

            //check, if the current tx is allowed to be commited
            if ($this->intNumberOfOpenTransactions == 1) {
                //so, this is the last remaining tx. Commit or rollback?
                if (!$this->bitCurrentTxIsDirty) {
                    $this->objDbDriver->transactionCommit();
                }
                else {
                    $this->objDbDriver->transactionRollback();
                    $this->bitCurrentTxIsDirty = false;
                }

                //decrement counter
                $this->intNumberOfOpenTransactions--;
            }
            else {
                $this->intNumberOfOpenTransactions--;
            }

        }
    }

    /**
     * Rollback of the current tx
     *
     * @return void
     */
    public function transactionRollback()
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        if ($this->objDbDriver != null) {

            if ($this->intNumberOfOpenTransactions == 1) {
                //so, this is the last remaining tx. rollback anyway
                $this->objDbDriver->transactionRollback();
                $this->bitCurrentTxIsDirty = false;
                //decrement counter
                $this->intNumberOfOpenTransactions--;
            }
            else {
                //mark the current tx session a dirty
                $this->bitCurrentTxIsDirty = true;
                //decrement the number of open tx
                $this->intNumberOfOpenTransactions--;
            }

        }
    }


    /**
     * Returns all tables used by the project
     *
     * @param bool $bitAll just the name or with additional information
     *
     * @return array
     */
    public function getTables($bitAll = false)
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        $arrReturn = array();
        if ($this->objDbDriver != null) {

            if ($bitAll && isset($this->arrTablesCache["all"])) {
                return $this->arrTablesCache["all"];
            }
            elseif (isset($this->arrTablesCache["filtered"])) {
                return $this->arrTablesCache["filtered"];
            }

            //increase global counter
            $this->intNumber++;
            $arrTemp = $this->objDbDriver->getTables();

            //Filtering tables not used by this project, if dbprefix was given
            if (_dbprefix_ != "") {
                foreach ($arrTemp as $arrTable) {
                    $intPos = uniStripos($arrTable["name"], _dbprefix_);
                    if ($intPos !== false && $intPos == 0) {
                        if ($bitAll) {
                            $arrReturn[] = $arrTable;
                        }
                        else {
                            $arrReturn[] = $arrTable["name"];
                        }
                    }
                }
            }
            else {
                foreach ($arrTemp as $arrTable) {
                    if ($bitAll) {
                        $arrReturn[] = $arrTable;
                    }
                    else {
                        $arrReturn[] = $arrTable["name"];
                    }
                }
            }

            if ($bitAll) {
                $this->arrTablesCache["all"] = $arrReturn;
            }
            else {
                $this->arrTablesCache["filtered"] = $arrReturn;
            }
        }


        return $arrReturn;
    }

    /**
     * Looks up the columns of the given table.
     * Should return an array for each row consisting of:
     * array ("columnName", "columnType")
     *
     * @param string $strTableName
     *
     * @return array
     */
    public function getColumnsOfTable($strTableName)
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        return $this->objDbDriver->getColumnsOfTable($strTableName);
    }

    /**
     * Returns the db-specific datatype for the kajona internal datatype.
     * Currently, this are
     *
     * @param string $strType
     *
     * @see Db_datatypes
     *
     * @return string
     */
    public function getDatatype($strType)
    {
        return $this->objDbDriver->getDatatype($strType);
    }

    /**
     * Used to send a create table statement to the database
     * By passing the query through this method, the driver can
     * add db-specific commands.
     * The array of fields should have the following structure
     * $array[string columnName] = array(string data-type, boolean isNull [, default (only if not null)])
     * whereas data-type is one of the following:
     *         int
     *      long
     *         double
     *         char10
     *         char20
     *         char100
     *         char254
     *      char500
     *         text
     *      longtext
     *
     * @param string $strName
     * @param array $arrFields array of fields / columns
     * @param array $arrKeys array of primary keys
     * @param array $arrIndices array of additional indices
     * @param bool $bitTxSafe Should the table support transactions?
     *
     * @see Db_datatypes
     *
     * @return bool
     */
    public function createTable($strName, $arrFields, $arrKeys, $arrIndices = array(), $bitTxSafe = true)
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        $bitReturn = $this->objDbDriver->createTable(_dbprefix_.$strName, $arrFields, $arrKeys, $arrIndices, $bitTxSafe);
        if (!$bitReturn) {
            $this->getError("", array());
        }

        return $bitReturn;
    }

    /**
     * Renames a table
     *
     * @param $strOldName
     * @param $strNewName
     *
     * @return bool
     */
    public function renameTable($strOldName, $strNewName)
    {
        return $this->objDbDriver->renameTable(_dbprefix_.$strOldName, _dbprefix_.$strNewName);
    }

    /**
     * Changes a single column, e.g. the datatype
     *
     * @param $strTable
     * @param $strOldColumnName
     * @param $strNewColumnName
     * @param $strNewDatatype
     *
     * @return bool
     */
    public function changeColumn($strTable, $strOldColumnName, $strNewColumnName, $strNewDatatype)
    {
        return $this->objDbDriver->changeColumn(_dbprefix_.$strTable, $strOldColumnName, $strNewColumnName, $strNewDatatype);
    }

    /**
     * Adds a column to a table
     *
     * @param $strTable
     * @param $strColumn
     * @param $strDatatype
     *
     * @return bool
     */
    public function addColumn($strTable, $strColumn, $strDatatype, $bitNull = null, $strDefault = null)
    {
        return $this->objDbDriver->addColumn(_dbprefix_.$strTable, $strColumn, $strDatatype, $bitNull, $strDefault);
    }

    /**
     * Removes a column from a table
     *
     * @param $strTable
     * @param $strColumn
     *
     * @return bool
     */
    public function removeColumn($strTable, $strColumn)
    {
        return $this->objDbDriver->removeColumn(_dbprefix_.$strTable, $strColumn);
    }

    /**
     * Dumps the current db
     * Takes care of holding just the defined number of dumps in the filesystem, defined by _system_dbdump_amount_
     *
     * @param array $arrTablesToExclude specify a set of tables not to be included in the dump
     *
     * @return bool
     */
    public function dumpDb($arrTablesToExclude = array())
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        // Check, how many dumps to keep
        $objFilesystem = new Filesystem();
        $arrFiles = $objFilesystem->getFilelist(_projectpath_."/dbdumps/", array(".sql", ".gz"));

        while (count($arrFiles) >= SystemSetting::getConfigValue("_system_dbdump_amount_")) {
            $strFile = array_shift($arrFiles);
            if (!$objFilesystem->fileDelete(_projectpath_."/dbdumps/".$strFile)) {
                Logger::getInstance(Logger::DBLOG)->addLogRow("Error deleting old db-dumps", Logger::$levelWarning);
                return false;
            }
            $arrFiles = $objFilesystem->getFilelist(_projectpath_."/dbdumps/", array(".sql", ".gz"));
        }

        $strTargetFilename = _projectpath_."/dbdumps/dbdump_".time().".sql";

        $arrTables = $this->getTables();
        $arrTablesFinal = array();

        if (count($arrTablesToExclude) > 0) {
            foreach ($arrTables as $strOneTable) {
                if (!in_array(uniStrReplace(_dbprefix_, "", $strOneTable), $arrTablesToExclude)) {
                    $arrTablesFinal[] = $strOneTable;
                }
            }
        }
        else {
            $arrTablesFinal = $arrTables;
        }

        $bitDump = $this->objDbDriver->dbExport($strTargetFilename, $arrTablesFinal);
        if ($bitDump == true) {
            $objGzip = new Gzip();
            try {
                if (!$objGzip->compressFile($strTargetFilename, true)) {
                    Logger::getInstance(Logger::DBLOG)->addLogRow("Failed to compress (gzip) the file ".basename($strTargetFilename)."", Logger::$levelWarning);
                }
            }
            catch (Exception $objExc) {
                $objExc->processException();
            }
        }
        if ($bitDump) {
            Logger::getInstance(Logger::DBLOG)->addLogRow("DB-Dump ".basename($strTargetFilename)." created", Logger::$levelInfo);
        }
        else {
            Logger::getInstance(Logger::DBLOG)->addLogRow("Error creating ".basename($strTargetFilename), Logger::$levelError);
        }
        return $bitDump;
    }

    /**
     * Imports the given dump
     *
     * @param string $strFilename
     *
     * @return bool
     */
    public function importDb($strFilename)
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        //gz file?
        $bitGzip = false;
        if (substr($strFilename, -3) == ".gz") {
            $bitGzip = true;
            //try to decompress
            $objGzip = new Gzip();
            try {
                if ($objGzip->decompressFile(_projectpath_."/dbdumps/".$strFilename)) {
                    $strFilename = substr($strFilename, 0, strlen($strFilename) - 3);
                }
                else {
                    Logger::getInstance(Logger::DBLOG)->addLogRow("Failed to decompress (gzip) the file ".basename($strFilename)."", Logger::$levelWarning);
                    return false;
                }
            }
            catch (Exception $objExc) {
                $objExc->processException();
                return false;
            }
        }

        $bitImport = $this->objDbDriver->dbImport(_projectpath_."/dbdumps/".$strFilename);
        //Delete source unzipped file?
        if ($bitGzip) {
            $objFilesystem = new Filesystem();
            $objFilesystem->fileDelete(_projectpath_."/dbdumps/".$strFilename);
        }
        if ($bitImport) {
            Logger::getInstance(Logger::DBLOG)->addLogRow("DB-DUMP ".$strFilename." was restored", Logger::$levelWarning);
        }
        else {
            Logger::getInstance(Logger::DBLOG)->addLogRow("Error restoring DB-DUMP ".$strFilename, Logger::$levelError);
        }
        return $bitImport;
    }

    /**
     * Parses a query to eliminate unnecessary characters such as whitespaces
     *
     * @param string $strQuery
     *
     * @return string
     */
    private function processQuery($strQuery)
    {

        $strQuery = trim($strQuery);
        $arrSearch = array(
            "\r\n",
            "\n",
            "\r",
            "\t",
            "    ",
            "   ",
            "  "
        );
        $arrReplace = array(
            "",
            "",
            "",
            " ",
            " ",
            " ",
            " "
        );

        $strQuery = str_replace($arrSearch, $arrReplace, $strQuery);

        return $strQuery;
    }

    /**
     * Queries the current db-driver about common information
     *
     * @return mixed|string
     */
    public function getDbInfo()
    {
        if (!$this->bitConnected) {
            $this->dbconnect();
        }

        if ($this->objDbDriver != null) {
            return $this->objDbDriver->getDbInfo();
        }

        return "";
    }


    /**
     * Returns the number of queries sent to the database
     * including those solved by the cache
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->intNumber;
    }

    /**
     * Returns the number of queries solved by the cache
     *
     * @return int
     */
    public function getNumberCache()
    {
        return $this->intNumberCache;
    }

    /**
     * Returns the number of items currently in the query-cache
     *
     * @return  int
     */
    public function getCacheSize()
    {
        return count($this->arrQueryCache);
    }

    /**
     * Internal wrapper to dbsafeString, used to process a complete array of parameters
     * as used by prepared statements.
     *
     * @param array $arrParams
     * @param array $arrEscapes An array of boolean for each param, used to block the escaping of html-special chars.
     *                          If not passed, all params will be cleaned.
     *
     * @return array
     * @since 3.4
     * @see Db::dbsafeString($strString, $bitHtmlSpecialChars = true)
     */
    private function dbsafeParams($arrParams, $arrEscapes = array())
    {
        foreach ($arrParams as $intKey => &$strParam) {
            if (isset($arrEscapes[$intKey])) {
                $strParam = $this->dbsafeString($strParam, $arrEscapes[$intKey], false);
            }
            else {
                $strParam = $this->dbsafeString($strParam, true, false);
            }
        }
        return $arrParams;
    }

    /**
     * Makes a string db-safe
     *
     * @param string $strString
     * @param bool $bitHtmlSpecialChars
     * @param bool $bitAddSlashes
     *
     * @return string
     */
    public function dbsafeString($strString, $bitHtmlSpecialChars = true, $bitAddSlashes = true)
    {

        if ($strString === null) {
            return null;
        }

        //escape special chars
        if ($bitHtmlSpecialChars) {
            $strString = html_entity_decode($strString, ENT_COMPAT, "UTF-8");
            $strString = htmlspecialchars($strString, ENT_COMPAT, "UTF-8");
        }

        //already escaped by php?
        if (get_magic_quotes_gpc() == 1) {
            $strString = stripslashes($strString);
        }

        if ($bitAddSlashes) {
            $strString = addslashes($strString);
        }

        return $strString;
    }

    /**
     * Method to flush the query-cache
     *
     * @return void
     */
    public function flushQueryCache()
    {
        //Logger::getInstance(Logger::DBLOG)->addLogRow("Flushing query cache", Logger::$levelInfo);
        $this->arrQueryCache = array();
        Objectfactory::getInstance()->flushCache();
    }

    /**
     * Method to flush the table-cache.
     * Since the tables won't change during regular operations,
     * flushing the tables cache is only required during package updates / installations
     *
     * @return void
     */
    public function flushTablesCache()
    {
        $this->arrTablesCache = array();
    }

    /**
     * Helper to flush the precompiled queries stored at the db-driver.
     * Use this method with great care!
     *
     * @return void
     */
    public function flushPreparedStatementsCache()
    {
        $this->objDbDriver->flushQueryCache();
    }

    /**
     * Allows the db-driver to add database-specific surroundings to column-names.
     * E.g. needed by the mysql-drivers
     *
     * @param string $strColumn
     *
     * @return string
     */
    public function encloseColumnName($strColumn)
    {
        return $this->objDbDriver->encloseColumnName($strColumn);
    }

    /**
     * Allows the db-driver to add database-specific surroundings to table-names.
     * E.g. needed by the mysql-drivers
     *
     * @param string $strTable
     *
     * @return string
     */
    public function encloseTableName($strTable)
    {
        return $this->objDbDriver->encloseTableName($strTable);
    }


    /**
     * Tries to validate the passed connection data.
     * May be used by other classes in order to test some credentials,
     * e.g. the installer.
     * The connection established will be closed directly and is not usable by other modules.
     *
     * @param string $strDriver
     * @param string $strDbHost
     * @param string $strDbUser
     * @param string $strDbPass
     * @param string $strDbName
     * @param int $intDbPort
     *
     * @return bool
     */
    public function validateDbCxData($strDriver, $strDbHost, $strDbUser, $strDbPass, $strDbName, $intDbPort)
    {

        /** @var $objDbDriver DbDriverInterface */
        $objDbDriver = null;

        $strPath = Resourceloader::getInstance()->getPathForFile("/system/db/Db".ucfirst($strDriver).".php");
        if ($strPath != null) {
            $objDbDriver = Classloader::getInstance()->getInstanceFromFilename($strPath);
        }
        else {
            return false;
        }

        try {
            if ($objDbDriver != null && $objDbDriver->dbconnect($strDbHost, $strDbUser, $strDbPass, $strDbName, $intDbPort)) {
                $objDbDriver->dbclose();
                return true;
            }
        }
        catch (Exception $objEx) {
            return false;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function getBitConnected()
    {
        return $this->bitConnected;
    }

    /**
     * For some database vendors we need to escape the backslash character even if we are using prepared statements. This
     * method unifies the behaviour. In order to select a column which contains a backslash you need to escape the value
     * with this method
     *
     * @param string $strValue
     *
     * @return mixed
     */
    public function escape($strValue)
    {
        return $this->objDbDriver->escape($strValue);
    }

    /**
     * Helper to replace all param-placeholder with the matching value, only to be used
     * to render a debuggable-statement.
     * 
     * @param $strQuery
     * @param $arrParams
     *
     * @return string
     */
    public function prettifyQuery($strQuery, $arrParams)
    {
        foreach($arrParams as $strOneParam) {

            if (!is_numeric($strOneParam)) {
                $strOneParam = "'{$strOneParam}'";
            }

            $intPos = uniStrpos($strQuery, '?');
            if ($intPos !== false) {
                $strQuery = substr_replace($strQuery, $strOneParam, $intPos, 1);
            }
        }

        return $strQuery;
    }

}
