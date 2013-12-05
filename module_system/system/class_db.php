<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

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
class class_db {
    private $objConfig = null; //Config-Objekt
    private $arrQueryCache = array(); //Array to cache queries
    private $arrTablesCache = array();
    private $intNumber = 0; //Number of queries send to database
    private $intNumberCache = 0; //Number of queries returned from cache

    /**
     * Instance of the db-driver defined in the configs
     *
     * @var interface_db_driver
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
    private function __construct() {

        $this->objConfig = class_config::getInstance();

        //Load the defined db-driver
        $strDriver = $this->objConfig->getConfig("dbdriver");
        if($strDriver != "%%defaultdriver%%") {
            //build a class-name & include the driver
            $strClassname = "class_db_".$strDriver;
            if(class_exists($strClassname))
                $this->objDbDriver = new $strClassname();
            else
                throw new class_exception("db-driver ".$strClassname." could not be loaded", class_exception::$level_FATALERROR);

        }
        else {
            //Do not throw any exception here, otherwise an endless loop will exit with an overloaded stack frame
            //throw new class_exception("No db-driver defined!", class_exception::$level_FATALERROR);
        }

    }

    /**
     * Destructor.
     * Handles the closing of remaining tx and closes the db-connection
     */
    public function __destruct() {
        if($this->intNumberOfOpenTransactions != 0) {
            //something bad happened. rollback, plz
            $this->objDbDriver->transactionRollback();
            class_logger::getInstance(class_logger::DBLOG)->addLogRow("Rolled back open transactions on deletion of current instance of class_db!", class_logger::$levelWarning);
        }


        if($this->objDbDriver !== null && $this->bitConnected) {
            class_logger::getInstance(class_logger::DBLOG)->addLogRow("closing database-connection", class_logger::$levelInfo);
            $this->objDbDriver->dbclose();
        }

    }

    /**
     * Method to get an instance of the db-class
     *
     * @return class_db
     */
    public static function getInstance() {
        if(self::$objDB == null) {
            self::$objDB = new class_db();
        }

        return self::$objDB;
    }


    /**
     * This method connects with the databse
     */
    public function dbconnect() {
        if($this->objDbDriver !== null) {
            try {
                class_logger::getInstance(class_logger::DBLOG)->addLogRow("creating database-connection using driver ".get_class($this->objDbDriver), class_logger::$levelInfo);
                $this->objDbDriver->dbconnect(
                    $this->objConfig->getConfig("dbhost"),
                    $this->objConfig->getConfig("dbusername"),
                    $this->objConfig->getConfig("dbpassword"),
                    $this->objConfig->getConfig("dbname"),
                    $this->objConfig->getConfig("dbport")
                );
            }
            catch(class_exception $objException) {
                $objException->processException();
            }

            $this->bitConnected = true;
        }
    }

    /**
     * Sending a query to the database
     *
     * @param string $strQuery
     *
     * @return bool
     */
    public function _query($strQuery) {
        if(!$this->bitConnected)
            $this->dbconnect();

        $bitReturn = false;

        $strQuery = $this->processQuery($strQuery);

        if(_dblog_)
            class_logger::getInstance(class_logger::QUERIES)->addLogRow("\r\n".$strQuery, class_logger::$levelInfo, true);

        //Increasing the counter
        $this->intNumber++;

        if($this->objDbDriver != null) {
            $bitReturn = $this->objDbDriver->_query($strQuery);
        }

        if(!$bitReturn)
            $this->getError($strQuery);

        return $bitReturn;
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
    public function _pQuery($strQuery, $arrParams, $arrEscapes = array()) {
        if(!$this->bitConnected)
            $this->dbconnect();

        $bitReturn = false;

        $strQuery = $this->processQuery($strQuery);

        if(_dblog_)
            class_logger::getInstance(class_logger::QUERIES)->addLogRow("\r\n".$strQuery."\r\n params: ".implode(", ", $arrParams), class_logger::$levelInfo, true);

        //Increasing the counter
        $this->intNumber++;

        if($this->objDbDriver != null) {
            $bitReturn = $this->objDbDriver->_pQuery($strQuery, $this->dbsafeParams($arrParams, $arrEscapes));
        }

        if(!$bitReturn)
            $this->getError($strQuery."\r\n params: ".implode(", ", $arrParams));

        return $bitReturn;
    }


    /**
     * Returns one row from a resultset
     *
     * @param string $strQuery
     * @param int $intNr
     * @param bool $bitCache
     *
     * @return array
     * @deprecated use getPRow() instead
     */
    public function getRow($strQuery, $intNr = 0, $bitCache = true) {
        $arrTemp = $this->getArray($strQuery, $bitCache);
        if(count($arrTemp) > 0)
            return $arrTemp[$intNr];
        else
            return array();
    }


    /**
     * Returns one row from a resultset.
     * Makes use of preprared statements.
     *
     * @param string $strQuery
     * @param array $arrParams
     * @param int $intNr
     * @param bool $bitCache
     *
     * @return array
     */
    public function getPRow($strQuery, $arrParams, $intNr = 0, $bitCache = true) {
        $arrTemp = $this->getPArray($strQuery, $arrParams, null, null, $bitCache);
        if(count($arrTemp) > 0)
            return $arrTemp[$intNr];
        else
            return array();
    }


    /**
     * Method to get an array of rows for a given query from the database
     *
     * @param string $strQuery
     * @param bool $bitCache
     *
     * @return array
     * @deprecated use getPArraySection() instead
     */
    public function getArray($strQuery, $bitCache = true) {
        if(!$this->bitConnected)
            $this->dbconnect();

        $strQuery = $this->processQuery($strQuery);
        //Increasing global counter
        $this->intNumber++;

        if(defined("_system_use_dbcache_")) {
            if(_system_use_dbcache_ == "false") {
                $bitCache = false;
            }
        }

        $strQueryMd5 = md5($strQuery);
        if($bitCache) {
            if(isset($this->arrQueryCache[$strQueryMd5])) {
                //Increasing Cache counter
                $this->intNumberCache++;
                return $this->arrQueryCache[$strQueryMd5];
            }
        }

        $arrReturn = array();

        if(_dblog_)
            class_logger::getInstance(class_logger::QUERIES)->addLogRow("\r\n".$strQuery, class_logger::$levelInfo, true);

        class_logger::getInstance(class_logger::DBLOG)->addLogRow("deprecated getArray call: ".$strQuery, class_logger::$levelWarning);

        if($this->objDbDriver != null) {
            $arrReturn = $this->objDbDriver->getArray($strQuery);
            if($arrReturn === false) {
                $this->getError($strQuery);
                return array();
            }
            if($bitCache)
                $this->arrQueryCache[$strQueryMd5] = $arrReturn;
        }
        return $arrReturn;
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
    public function getPArray($strQuery, $arrParams, $intStart = null, $intEnd = null, $bitCache = true) {
        if(!$this->bitConnected)
            $this->dbconnect();

        //param validation
        if((int)$intStart < 0)
            $intStart = null;

        if((int)$intEnd < 0)
            $intEnd = null;


        $strQuery = $this->processQuery($strQuery);
        //Increasing global counter
        $this->intNumber++;

        if(defined("_system_use_dbcache_")) {
            if(_system_use_dbcache_ == "false") {
                $bitCache = false;
            }
        }

        $strQueryMd5 = null;
        if($bitCache) {
            $strQueryMd5 = md5($strQuery.implode(",", $arrParams).$intStart.$intEnd);
            if(isset($this->arrQueryCache[$strQueryMd5])) {
                //Increasing Cache counter
                $this->intNumberCache++;
                return $this->arrQueryCache[$strQueryMd5];
            }
        }

        $arrReturn = array();

        if(_dblog_)
            class_logger::getInstance(class_logger::QUERIES)->addLogRow("\r\n".$strQuery."\r\n params: ".implode(", ", $arrParams), class_logger::$levelInfo, true);

        if($this->objDbDriver != null) {
            if($intStart !== null && $intEnd !== null && $intStart !== false && $intEnd !== false)
                $arrReturn = $this->objDbDriver->getPArraySection($strQuery, $this->dbsafeParams($arrParams), $intStart, $intEnd);
            else
                $arrReturn = $this->objDbDriver->getPArray($strQuery, $this->dbsafeParams($arrParams));

            if($arrReturn === false) {
                $this->getError($strQuery."\n params: ".implode(", ", $arrParams));
                return array();
            }
            if($bitCache)
                $this->arrQueryCache[$strQueryMd5] = $arrReturn;
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
     * @deprecated use getPArraySection() instead
     */
    public function getArraySection($strQuery, $intStart, $intEnd, $bitCache = true) {
        if(!$this->bitConnected)
            $this->dbconnect();

        $arrReturn = array();
        //param validation
        if((int)$intStart < 0)
            $intStart = 0;

        if((int)$intEnd < 0)
            $intEnd = 0;
        //process query
        $strQuery = $this->processQuery($strQuery);

        //Increasing global counter
        $this->intNumber++;

        if(defined("_system_use_dbcache_")) {
            if(_system_use_dbcache_ == "false") {
                $bitCache = false;
            }
        }

        //generate a hash-value
        $strQueryMd5 = md5($strQuery.$intStart."-".$intEnd);
        if($bitCache) {
            if(isset($this->arrQueryCache[$strQueryMd5])) {
                //Increasing Cache counter
                $this->intNumberCache++;
                return $this->arrQueryCache[$strQueryMd5];
            }
        }

        if(_dblog_)
            class_logger::getInstance(class_logger::QUERIES)->addLogRow("\r\n".$strQuery, class_logger::$levelInfo, true);

        class_logger::getInstance(class_logger::DBLOG)->addLogRow("deprecated getArraySection call: ".$strQuery, class_logger::$levelWarning);

        if($this->objDbDriver != null) {
            $arrReturn = $this->objDbDriver->getArraySection($strQuery, $intStart, $intEnd);
            if($arrReturn === false) {
                $this->getError($strQuery);
                return array();
            }
            if($bitCache)
                $this->arrQueryCache[$strQueryMd5] = $arrReturn;
        }

        return $arrReturn;
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
    public function getPArraySection($strQuery, $arrParams, $intStart, $intEnd, $bitCache = true) {
        class_logger::getInstance(class_logger::DBLOG)->addLogRow("deprecated getPArraySection call: ".$strQuery, class_logger::$levelWarning);
        return $this->getPArray($strQuery, $arrParams, $intStart, $intEnd, $bitCache);
    }

    /**
     * Writes the last DB-Error to the screen
     *
     * @param string $strQuery
     *
     * @throws class_exception
     */
    private function getError($strQuery) {
        if(!$this->bitConnected)
            $this->dbconnect();

        $strError = "";
        if($this->objDbDriver != null) {
            $strError = $this->objDbDriver->getError();
        }
        if($this->objConfig->getDebug("debuglevel") > 0) {

            //reprocess query
            $strQuery = str_ireplace(
                array(" from ", " where ", " and ", " group by ", " order by "),
                array("\nFROM ", "\nWHERE ", "\n\tAND ", "\nGROUP BY ", "\nORDER BY "),
                $strQuery
            );

            $strErrorCode = "";
            $strErrorCode .= "Error in query\n\n";
            $strErrorCode .= "Error:\n";
            $strErrorCode .= $strError."\n\n";
            $strErrorCode .= "Query:\n";
            $strErrorCode .= $strQuery."\n";
            $strErrorCode .= "\n";
            $strErrorCode .= "Callstack:\n";
            if(function_exists("debug_backtrace")) {
                $arrStack = debug_backtrace();

                foreach($arrStack as $intPos => $arrValue) {
                    $strErrorCode .= (isset($arrValue["file"]) ? $arrValue["file"] : "n.a.")."\n\t Row ".(isset($arrValue["line"]) ? $arrValue["line"] : "n.a.").", function ".$arrStack[$intPos]["function"]."\n";
                }
            }
            class_logger::getInstance(class_logger::DBLOG)->addLogRow("Error in Query: ".$strQuery, class_logger::$levelWarning);
            throw new class_exception($strErrorCode, class_exception::$level_ERROR);
        }
        else {
            //send a warning to the logger
            class_logger::getInstance(class_logger::DBLOG)->addLogRow("Error in Query: ".$strQuery, class_logger::$levelWarning);
        }

    }


    /**
     * Starts a trancaction

     */
    public function transactionBegin() {
        if(!$this->bitConnected)
            $this->dbconnect();

        if($this->objDbDriver != null) {
            //just start a new tx, if no other tx is open
            if($this->intNumberOfOpenTransactions == 0)
                $this->objDbDriver->transactionBegin();

            //increase tx-counter
            $this->intNumberOfOpenTransactions++;

        }
    }

    /**
     * Ends a tx successfully

     */
    public function transactionCommit() {
        if(!$this->bitConnected)
            $this->dbconnect();

        if($this->objDbDriver != null) {

            //check, if the current tx is allowed to be commited
            if($this->intNumberOfOpenTransactions == 1) {
                //so, this is the last remaining tx. Commit or rollback?
                if(!$this->bitCurrentTxIsDirty) {
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

     */
    public function transactionRollback() {
        if(!$this->bitConnected)
            $this->dbconnect();

        if($this->objDbDriver != null) {

            if($this->intNumberOfOpenTransactions == 1) {
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
     * @param bool $bitAll just the name or with additional informations?
     *
     * @return array
     */
    public function getTables($bitAll = false) {
        if(!$this->bitConnected)
            $this->dbconnect();

        $arrReturn = array();
        if($this->objDbDriver != null) {

            if($bitAll && isset($this->arrTablesCache["all"]))
                return $this->arrTablesCache["all"];
            else if(isset($this->arrTablesCache["filtered"]))
                return $this->arrTablesCache["filtered"];

            //increase global counter
            $this->intNumber++;

            $strFakeQuery = "SELECT ALL TABLES /// KAJONA INTERNAL QUERY";
            $strQueryMd5 = md5($strFakeQuery);

            $bitCache = true;
            if(defined("_system_use_dbcache_") && _system_use_dbcache_ == "false")
                $bitCache = false;

            $arrTemp = array();
            if($bitCache) {
                if(isset($this->arrQueryCache[$strQueryMd5])) {
                    //Increasing Cache counter
                    $this->intNumberCache++;
                    $arrTemp = $this->arrQueryCache[$strQueryMd5];
                }
                else {
                    $arrTemp = $this->objDbDriver->getTables();
                    if(_dblog_)
                        class_logger::getInstance(class_logger::QUERIES)->addLogRow("\r\n".$strFakeQuery, class_logger::$levelInfo, true);
                }
            }
            else {
                $arrTemp = $this->objDbDriver->getTables();
                if(_dblog_)
                    class_logger::getInstance(class_logger::QUERIES)->addLogRow("\r\n".$strFakeQuery, class_logger::$levelInfo, true);
            }

            if($bitCache)
                $this->arrQueryCache[$strQueryMd5] = $arrTemp;


            //Filtering tables not used by this project, if dbprefix was given
            if(_dbprefix_ != "") {
                foreach($arrTemp as $arrTable) {
                    $intPos = uniStripos($arrTable["name"], _dbprefix_);
                    if($intPos !== false && $intPos == 0) {
                        if($bitAll)
                            $arrReturn[] = $arrTable;
                        else
                            $arrReturn[] = $arrTable["name"];
                    }
                }
            }
            else {
                foreach($arrTemp as $arrTable) {
                    if($bitAll)
                        $arrReturn[] = $arrTable;
                    else
                        $arrReturn[] = $arrTable["name"];
                }
            }

            if($bitAll)
                $this->arrTablesCache["all"] = $arrReturn;
            else
                $this->arrTablesCache["filtered"] = $arrReturn;
        }


        return $arrReturn;
    }

    /**
     * Looks up the columns of the given table.
     * Should return an array for each row consting of:
     * array ("columnName", "columnType")
     *
     * @param string $strTableName
     *
     * @return array
     */
    public function getColumnsOfTable($strTableName) {
        if(!$this->bitConnected)
            $this->dbconnect();

        return $this->objDbDriver->getColumnsOfTable($strTableName);
    }

    /**
     * Returns the db-specific datatype for the kajona internal datatype.
     * Currently, this are
     *      int
     *      long
     *      double
     *      char10
     *      char20
     *      char100
     *      char254
     *      char500
     *      text
     *      longtext
     *
     * @param string $strType
     *
     * @return string
     */
    public function getDatatype($strType) {
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
     * @return bool
     */
    public function createTable($strName, $arrFields, $arrKeys, $arrIndices = array(), $bitTxSafe = true) {
        if(!$this->bitConnected)
            $this->dbconnect();

        $bitReturn = $this->objDbDriver->createTable($strName, $arrFields, $arrKeys, $arrIndices, $bitTxSafe);
        if(!$bitReturn)
            $this->getError("");

        return $bitReturn;
    }

    /**
     * Dumps the current db
     * Takes care of holding just the defined number of dumps in the filesystem, defined by _system_dbdump_amount_
     *
     * @param array $arrTablesToExclude specify a set of tables not to be included in the dump
     *
     * @return bool
     */
    public function dumpDb($arrTablesToExclude = array()) {
        if(!$this->bitConnected)
            $this->dbconnect();

        // Check, how many dumps to keep
        $objFilesystem = new class_filesystem();
        $arrFiles = $objFilesystem->getFilelist(_projectpath_."/dbdumps/", array(".sql", ".gz"));

        while(count($arrFiles) >= _system_dbdump_amount_) {
            $strFile = array_shift($arrFiles);
            if(!$objFilesystem->fileDelete(_projectpath_."/dbdumps/".$strFile)) {
                class_logger::getInstance(class_logger::DBLOG)->addLogRow("Error deleting old db-dumps", class_logger::$levelWarning);
                return false;
            }
            $arrFiles = $objFilesystem->getFilelist(_projectpath_."/dbdumps/", array(".sql", ".gz"));
        }

        $strTargetFilename = _projectpath_."/dbdumps/dbdump_".time().".sql";

        $arrTables = $this->getTables();
        $arrTablesFinal = array();

        if(count($arrTablesToExclude) > 0) {
            foreach($arrTables as $strOneTable) {
                if(!in_array(uniStrReplace(_dbprefix_, "", $strOneTable), $arrTablesToExclude))
                    $arrTablesFinal[] = $strOneTable;
            }
        }
        else
            $arrTablesFinal = $arrTables;

        $bitDump = $this->objDbDriver->dbExport($strTargetFilename, $arrTablesFinal);
        if($bitDump == true) {
            $objGzip = new class_gzip();
            try {
                if(!$objGzip->compressFile($strTargetFilename, true))
                    class_logger::getInstance(class_logger::DBLOG)->addLogRow("Failed to compress (gzip) the file ".basename($strTargetFilename)."", class_logger::$levelWarning);
            }
            catch(class_exception $objExc) {
                $objExc->processException();
            }
        }
        if($bitDump)
            class_logger::getInstance(class_logger::DBLOG)->addLogRow("DB-Dump ".basename($strTargetFilename)." created", class_logger::$levelInfo);
        else
            class_logger::getInstance(class_logger::DBLOG)->addLogRow("Error creating ".basename($strTargetFilename), class_logger::$levelError);
        return $bitDump;
    }

    /**
     * Imports the given dump
     *
     * @param string $strFilename
     *
     * @return bool
     */
    public function importDb($strFilename) {
        if(!$this->bitConnected)
            $this->dbconnect();

        //gz file?
        $bitGzip = false;
        if(substr($strFilename, -3) == ".gz") {
            $bitGzip = true;
            //try to decompress
            $objGzip = new class_gzip();
            try {
                if($objGzip->decompressFile(_projectpath_."/dbdumps/".$strFilename))
                    $strFilename = substr($strFilename, 0, strlen($strFilename) - 3);
                else {
                    class_logger::getInstance(class_logger::DBLOG)->addLogRow("Failed to decompress (gzip) the file ".basename($strFilename)."", class_logger::$levelWarning);
                    return false;
                }
            }
            catch(class_exception $objExc) {
                $objExc->processException();
                return false;
            }
        }

        $bitImport = $this->objDbDriver->dbImport(_projectpath_."/dbdumps/".$strFilename);
        //Delete source unzipped file?
        if($bitGzip) {
            $objFilesystem = new class_filesystem();
            $objFilesystem->fileDelete(_projectpath_."/dbdumps/".$strFilename);
        }
        if($bitImport)
            class_logger::getInstance(class_logger::DBLOG)->addLogRow("DB-DUMP ".$strFilename." was restored", class_logger::$levelWarning);
        else
            class_logger::getInstance(class_logger::DBLOG)->addLogRow("Error restoring DB-DUMP ".$strFilename, class_logger::$levelError);
        return $bitImport;
    }

    /**
     * Parses a query to eliminate unnecessary characters such as whitespaces
     *
     * @param string $strQuery
     *
     * @return string
     */
    private function processQuery($strQuery) {

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

    public function getDbInfo() {
        if(!$this->bitConnected)
            $this->dbconnect();

        if($this->objDbDriver != null) {
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
    public function getNumber() {
        return $this->intNumber;
    }

    /**
     * Returns the number of queries solved by the cache
     *
     * @return int
     */
    public function getNumberCache() {
        return $this->intNumberCache;
    }

    /**
     * Returns the number of items currently in the query-cache
     *
     * @return  int
     */
    public function getCacheSize() {
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
     * @see class_db::dbsafeString($strString, $bitHtmlSpecialChars = true)
     */
    private function dbsafeParams($arrParams, $arrEscapes = array()) {
        foreach($arrParams as $intKey => &$strParam) {
            if(isset($arrEscapes[$intKey]))
                $strParam = $this->dbsafeString($strParam, $arrEscapes[$intKey], false);
            else
                $strParam = $this->dbsafeString($strParam, true, false);
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
    public function dbsafeString($strString, $bitHtmlSpecialChars = true, $bitAddSlashes = true) {

        if($strString === null)
            return null;

        //escape special chars
        if($bitHtmlSpecialChars) {
            $strString = html_entity_decode($strString, ENT_COMPAT, "UTF-8");
            $strString = htmlspecialchars($strString, ENT_COMPAT, "UTF-8");
        }

        //already escaped by php?
        if(get_magic_quotes_gpc() == 1) {
            $strString = stripslashes($strString);
        }

        if($bitAddSlashes)
            $strString = addslashes($strString);

        return $strString;
    }

    /**
     * Method to flush the query-cache
     */
    public function flushQueryCache() {
        //class_logger::getInstance(class_logger::DBLOG)->addLogRow("Flushing query cache", class_logger::$levelInfo);
        $this->arrQueryCache = array();
        $this->arrTablesCache = array();
        class_objectfactory::getInstance()->flushCache();
    }

    /**
     * Helper to flush the precompiled queries stored at the db-driver.
     * Use this method with great care!
     */
    public function flushPreparedStatementsCache() {
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
    public function encloseColumnName($strColumn) {
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
    public function encloseTableName($strTable) {
        return $this->objDbDriver->encloseTableName($strTable);
    }


    /**
     * Tries to validate the passed connection data.
     * May be used by other classes in order to test some credentials,
     * e.g. the installer.
     * The connection established will be closed directly and is not usable by other modules.
     *
     * @param $strDriver
     * @param $strDbHost
     * @param $strDbUser
     * @param $strDbPass
     * @param $strDbName
     * @param $intDbPort
     *
     * @return bool
     */
    public function validateDbCxData($strDriver, $strDbHost, $strDbUser, $strDbPass, $strDbName, $intDbPort) {

        /** @var $objDbDriver interface_db_driver */
        $objDbDriver = null;

        $strClassname = "class_db_".$strDriver;
        if(class_exists($strClassname))
            $objDbDriver = new $strClassname();
        else
            return false;

        try {
            if($objDbDriver->dbconnect($strDbHost, $strDbUser, $strDbPass, $strDbName, $intDbPort)) {
                $objDbDriver->dbclose();
                return true;
            }
        }
        catch(class_exception $objEx) {
            return false;
        }

        return false;
    }
}
