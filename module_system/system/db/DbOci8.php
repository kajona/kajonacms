<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Db;

use Kajona\System\System\Database;
use Kajona\System\System\DbConnectionParams;
use Kajona\System\System\DbDatatypes;
use Kajona\System\System\Exception;
use Kajona\System\System\Logger;
use Kajona\System\System\StringUtil;


/**
 * db-driver for oracle using the ovi8-interface
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.4.1
 */
class DbOci8 extends DbBase
{

    private $linkDB; //DB-Link
    /** @var DbConnectionParams  */
    private $objCfg = null;

    private $strDumpBin = "exp"; // Binary to dump db (if not in path, add the path here)
    // /usr/lib/oracle/xe/app/oracle/product/10.2.0/server/bin/
    private $strRestoreBin = "imp"; //Binary to restore db (if not in path, add the path here)

    private $bitTxOpen = false;

    private $objErrorStmt = null;

    /**
     * Flag whether the sring comparison method (case sensitive / insensitive) should be reset back to default after the current query
     *
     * @var bool
     */
    private $bitResetOrder = false;

    /**
     * @inheritdoc
     */
    public function dbconnect(DbConnectionParams $objParams)
    {
        if ($objParams->getIntPort() == "" || $objParams->getIntPort() == 0) {
            $objParams->setIntPort(1521);
        }
        $this->objCfg = $objParams;
                //try to set the NLS_LANG env attribute
        putenv("NLS_LANG=American_America.UTF8");

        $this->linkDB = @oci_connect($this->objCfg->getStrUsername(), $this->objCfg->getStrPass(), $this->objCfg->getStrHost().":".$this->objCfg->getIntPort()."/".$this->objCfg->getStrDbName(), "AL32UTF8");


        if ($this->linkDB !== false) {
            @oci_set_client_info($this->linkDB, "Kajona CMS");
            $this->_pQuery("ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '.,'", array());
            return true;
        } else {
            throw new Exception("Error connecting to database", Exception::$level_FATALERROR);
        }
    }

    /**
     * Closes the connection to the database
     *
     * @return void
     */
    public function dbclose()
    {
        @oci_close($this->linkDB);
    }

    /**
     * Creates a single query in order to insert multiple rows at one time.
     * For most databases, this will create s.th. like
     * INSERT INTO $strTable ($arrColumns) VALUES (?, ?), (?, ?)...
     *
     * Please note that this method is used to create the query itself, based on the Kajona-internal syntax.
     * The query is fired to the database by Database
     *
     * @param string $strTable
     * @param string[] $arrColumns
     * @param array $arrValueSets
     * @param Database $objDb
     *
     * @return bool
     */
    public function triggerMultiInsert($strTable, $arrColumns, $arrValueSets, Database $objDb)
    {

        $bitReturn = true;

        $arrPlaceholder = array();
        $arrSafeColumns = array();

        foreach ($arrColumns as $strOneColumn) {
            $arrSafeColumns[] = $this->encloseColumnName($strOneColumn);
            $arrPlaceholder[] = "?";
        }
        $strPlaceholder = " (".implode(",", $arrPlaceholder).") ";
        $strColumnNames = " (".implode(",", $arrSafeColumns).") ";

        $arrParams = array();

        $strQuery = "INSERT ALL ";
        foreach ($arrValueSets as $arrOneSet) {
            $arrParams = array_merge($arrParams, array_values($arrOneSet));

            $strQuery .= " INTO ".$this->encloseTableName($strTable)." ".$strColumnNames." VALUES ".$strPlaceholder." ";
        }
        $strQuery .= " SELECT * FROM dual";

        $bitReturn = $objDb->_pQuery($strQuery, $arrParams) && $bitReturn;

        return $bitReturn;
    }


    /**
     * Sends a prepared statement to the database. All params must be represented by the ? char.
     * The params themself are stored using the second params using the matching order.
     *
     * @param string $strQuery
     * @param array $arrParams
     *
     * @return bool
     * @since 3.4
     */
    public function _pQuery($strQuery, $arrParams)
    {
        $strQuery = $this->processQuery($strQuery);
        $objStatement = $this->getParsedStatement($strQuery);
        if ($objStatement === false) {
            return false;
        }

        foreach ($arrParams as $intPos => $strValue) {
            oci_bind_by_name($objStatement, ":".($intPos + 1), $arrParams[$intPos]);
        }

        $bitAddon = OCI_COMMIT_ON_SUCCESS;
        if ($this->bitTxOpen) {
            $bitAddon = OCI_NO_AUTO_COMMIT;
        }
        $bitResult = @oci_execute($objStatement, $bitAddon);

        if (!$bitResult) {
            $this->objErrorStmt = $objStatement;
            return false;
        }

        $this->intAffectedRows = @oci_num_rows($objStatement);

        @oci_free_statement($objStatement);
        return $bitResult;
    }


    /**
     * @inheritDoc
     */
    public function insertOrUpdate($strTable, $arrColumns, $arrValues, $arrPrimaryColumns)
    {

        //return parent::insertOrUpdate($strTable, $arrColumns, $arrValues, $arrPrimaryColumns);

        $arrPlaceholder = array();
        $arrMappedColumns = array();
        $arrKeyValuePairs = array();


        $arrParams = array();
        $arrPrimaryCompares = array();

        foreach ($arrColumns as $intKey => $strOneCol) {
            $arrPlaceholder[] = "?";
            $arrMappedColumns[] = $this->encloseColumnName($strOneCol);

            if (in_array($strOneCol, $arrPrimaryColumns)) {
                $arrPrimaryCompares[] = $strOneCol." = ? ";
                $arrParams[] = $arrValues[$intKey];
            }
        }

        $arrParams = array_merge($arrParams, $arrValues);


        foreach ($arrColumns as $intKey => $strOneCol) {
            if (!in_array($strOneCol, $arrPrimaryColumns)) {
                $arrKeyValuePairs[] = $this->encloseColumnName($strOneCol)." = ?";
                $arrParams[] = $arrValues[$intKey];
            }
        }


        $strQuery = "MERGE INTO ".$this->encloseTableName(_dbprefix_.$strTable)." using dual on (".implode(" AND ", $arrPrimaryCompares).") 
                       WHEN NOT MATCHED THEN INSERT (".implode(", ", $arrMappedColumns).") values (".implode(", ", $arrPlaceholder).")
                       WHEN MATCHED then update set ".implode(", ", $arrKeyValuePairs)."";

        return $this->_pQuery($strQuery, $arrParams);
    }


    /**
     * This method is used to retrieve an array of resultsets from the database using
     * a prepared statement
     *
     * @param string $strQuery
     * @param array $arrParams
     *
     * @since 3.4
     * @return array
     */
    public function getPArray($strQuery, $arrParams)
    {
        $arrReturn = array();
        $intCounter = 0;

        $strQuery = $this->processQuery($strQuery);
        $objStatement = $this->getParsedStatement($strQuery);

        if ($objStatement === false) {
            return false;
        }

        foreach ($arrParams as $intPos => $strValue) {
            oci_bind_by_name($objStatement, ":".($intPos + 1), $arrParams[$intPos]);
        }

        $bitAddon = OCI_COMMIT_ON_SUCCESS;
        if ($this->bitTxOpen) {
            $bitAddon = OCI_NO_AUTO_COMMIT;
        }
        $resultSet = @oci_execute($objStatement, $bitAddon);

        if (!$resultSet) {
            $this->objErrorStmt = $objStatement;
            return false;
        }

        while ($arrRow = oci_fetch_array($objStatement, OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS)) {
            $arrRow = $this->parseResultRow($arrRow);
            $arrReturn[$intCounter++] = $arrRow;
        }
        @oci_free_statement($objStatement);

        if ($this->bitResetOrder) {
            $this->setCaseSensitiveSort();
            $this->bitResetOrder = false;
        }

        return $arrReturn;
    }

    /**
     * Returns the last error reported by the database.
     * Is being called after unsuccessful queries
     *
     * @return string
     */
    public function getError()
    {
        $strError = oci_error($this->objErrorStmt != null ? $this->objErrorStmt : $this->linkDB);
        $this->objErrorStmt = null;
        return print_r($strError, true);
    }

    /**
     * Returns ALL tables in the database currently connected to
     *
     * @return mixed
     */
    public function getTables()
    {
        $arrTemp = $this->getPArray("SELECT table_name AS name FROM ALL_TABLES", array());

        foreach ($arrTemp as $intKey => $strValue) {
            $arrTemp[$intKey]["name"] = StringUtil::toLowerCase($strValue["name"]);
        }
        return $arrTemp;
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
    public function getColumnsOfTable($strTableName)
    {
        $arrReturn = array();
        $arrTemp = $this->getPArray("select column_name, data_type from user_tab_columns where table_name=?", array(strtoupper($strTableName)));

        if (empty($arrTemp)) {
            return array();
        }

        foreach ($arrTemp as $arrOneColumn) {
            $arrReturn[] = array(
                "columnName" => strtolower($arrOneColumn["column_name"]),
                "columnType" => ($arrOneColumn["data_type"] == "integer" ? "int" : strtolower($arrOneColumn["data_type"])),
            );

        }

        return $arrReturn;
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
    public function getDatatype($strType)
    {
        $strReturn = "";

        if ($strType == DbDatatypes::STR_TYPE_INT) {
            $strReturn .= " NUMBER(19,0) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_LONG) {
            $strReturn .= " NUMBER(19, 0) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_DOUBLE) {
            $strReturn .= " FLOAT (24) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR10) {
            $strReturn .= " VARCHAR2( 10 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR20) {
            $strReturn .= " VARCHAR2( 20 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR100) {
            $strReturn .= " VARCHAR2( 100 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR254) {
            $strReturn .= " VARCHAR2( 280 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR500) {
            $strReturn .= " VARCHAR2( 500 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_TEXT) {
            $strReturn .= " VARCHAR2( 4000 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_LONGTEXT) {
            $strReturn .= " CLOB ";
        } else {
            $strReturn .= " VARCHAR( 254 ) ";
        }

        return $strReturn;
    }

    /**
     * Renames a single column of the table
     *
     * @param $strTable
     * @param $strOldColumnName
     * @param $strNewColumnName
     * @param $strNewDatatype
     *
     * @return bool
     * @since 4.6
     */
    public function changeColumn($strTable, $strOldColumnName, $strNewColumnName, $strNewDatatype)
    {
        $bitReturn = $this->_pQuery("ALTER TABLE ".($this->encloseTableName($strTable))." RENAME COLUMN ".($this->encloseColumnName($strOldColumnName)." TO ".$this->encloseColumnName($strNewColumnName)), array());
        return $bitReturn && $this->_pQuery("ALTER TABLE ".$this->encloseTableName($strTable)." MODIFY ( ".$this->encloseColumnName($strNewColumnName)." ".$this->getDatatype($strNewDatatype)." )", array());
    }

    /**
     * Adds a column to a table
     *
     * @param $strTable
     * @param $strColumn
     * @param $strDatatype
     *
     * @return bool
     * @since 4.6
     */
    public function addColumn($strTable, $strColumn, $strDatatype, $bitNull = null, $strDefault = null)
    {
        $strQuery = "ALTER TABLE ".($this->encloseTableName($strTable))." ADD ".($this->encloseColumnName($strColumn)." ".$this->getDatatype($strDatatype));

        if ($strDefault !== null) {
            $strQuery .= " DEFAULT ".$strDefault;
        }

        if ($bitNull !== null) {
            $strQuery .= $bitNull ? " NULL" : " NOT NULL";
        }

        return $this->_pQuery($strQuery, array());
    }

    /**
     * Used to send a create table statement to the database
     * By passing the query through this method, the driver can
     * add db-specific commands.
     * The array of fields should have the following structure
     * $array[string columnName] = array(string datatype, boolean isNull [, default (only if not null)])
     * whereas datatype is one of the following:
     *         int
     *         long
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
    public function createTable($strName, $arrFields, $arrKeys, $arrIndices = array(), $bitTxSafe = true)
    {
        $strQuery = "";

        //loop over existing tables to check, if the table already exists
        $arrTables = $this->getTables();
        foreach ($arrTables as $arrOneTable) {
            if ($arrOneTable["name"] == $strName) {
                return true;
            }
        }

        //build the oracle code
        $strQuery .= "CREATE TABLE ".$strName." ( \n";

        //loop the fields
        foreach ($arrFields as $strFieldName => $arrColumnSettings) {
            $strQuery .= " ".$strFieldName." ";

            $strQuery .= $this->getDatatype($arrColumnSettings[0]);

            //any default?
            if (isset($arrColumnSettings[2])) {
                $strQuery .= "DEFAULT ".$arrColumnSettings[2]." ";
            }

            //nullable?
            if ($arrColumnSettings[1] === true) {
                $strQuery .= " NULL ";
            } else {
                $strQuery .= " NOT NULL ";
            }

            $strQuery .= " , \n";

        }

        //primary keys
        $strQuery .= " CONSTRAINT pk_".generateSystemid()." primary key ( ".implode(" , ", $arrKeys)." ) \n";
        $strQuery .= ") ";

        $bitCreate = $this->_pQuery($strQuery, array());

        if ($bitCreate && count($arrIndices) > 0) {
            foreach ($arrIndices as $strOneIndex) {
                if (is_array($strOneIndex)) {
                    $strQuery = "CREATE INDEX ix_".generateSystemid()." ON ".$strName." ( ".implode(", ", $strOneIndex).") ";
                } else {
                    $strQuery = "CREATE INDEX ix_".generateSystemid()." ON ".$strName." ( ".$strOneIndex.") ";
                }
                $bitCreate = $bitCreate && $this->_pQuery($strQuery, array());
            }
        }

        return $bitCreate;
    }

    /**
     * Starts a transaction
     *
     * @return void
     */
    public function transactionBegin()
    {
        $this->bitTxOpen = true;
    }

    /**
     * Ends a successful operation by committing the transaction
     *
     * @return void
     */
    public function transactionCommit()
    {
        oci_commit($this->linkDB);
        $this->bitTxOpen = false;
    }

    /**
     * Ends a non-successful transaction by using a rollback
     *
     * @return void
     */
    public function transactionRollback()
    {
        oci_rollback($this->linkDB);
        $this->bitTxOpen = false;
    }

    /**
     * @return array|mixed
     */
    public function getDbInfo()
    {
        $arrReturn = array();
        $arrReturn["dbserver"] = oci_server_version($this->linkDB);
        $arrReturn["dbclient"] = function_exists("oci_client_version") ? oci_client_version() : "";
        $arrReturn["nls_sort"] = $this->getPArray("select sys_context ('userenv', 'nls_sort') val1 from sys.dual", array())[0]["val1"];
        $arrReturn["nls_comp"] = $this->getPArray("select sys_context ('userenv', 'nls_comp') val1 from sys.dual", array())[0]["val1"];
        return $arrReturn;
    }


    //--- DUMP & RESTORE ------------------------------------------------------------------------------------

    /**
     * Dumps the current db
     *
     * @param string $strFilename
     * @param array $arrTables
     *
     * @return bool
     */
    public function dbExport($strFilename, $arrTables)
    {

        $strFilename = _realpath_.$strFilename;
        $strTables = implode(",", $arrTables);

        $strCommand = $this->strDumpBin." ".$this->objCfg->getStrUsername()."/".$this->objCfg->getStrPass()." CONSISTENT=n TABLES=".$strTables." FILE='".$strFilename."'";
        Logger::getInstance(Logger::DBLOG)->addLogRow("dump command: ".$strCommand, Logger::$levelInfo);
        //Now do a systemfork
        $intTemp = "";
        system($strCommand, $intTemp);
        Logger::getInstance(Logger::DBLOG)->addLogRow($this->strDumpBin." exited with code ".$intTemp, Logger::$levelInfo);
        return $intTemp == 0;
    }

    /**
     * Imports the given db-dump to the database
     *
     * @param string $strFilename
     *
     * @return bool
     */
    public function dbImport($strFilename)
    {

        $strFilename = _realpath_.$strFilename;
        $strCommand = $this->strRestoreBin." ".$this->objCfg->getStrUsername()."/".$this->objCfg->getStrPass()." FILE='".$strFilename."'";
        $intTemp = "";
        system($strCommand, $intTemp);
        Logger::getInstance(Logger::DBLOG)->addLogRow($this->strRestoreBin." exited with code ".$intTemp, Logger::$levelInfo);
        return $intTemp == 0;
    }

    /**
     * Transforms the prepared statement into a valid oracle syntax.
     * This is done by replying the ?-chars by :x entries.
     *
     * @param string $strQuery
     *
     * @return string
     */
    private function processQuery($strQuery)
    {
        $intCount = 1;
        while (StringUtil::indexOf($strQuery, "?") !== false) {
            $intPos = StringUtil::indexOf($strQuery, "?");
            $strQuery = substr($strQuery, 0, $intPos).":".$intCount++.substr($strQuery, $intPos + 1);
        }

        if (StringUtil::indexOf($strQuery, " like ", false) !== false) {
            $this->setCaseInsensitiveSort();
            $this->bitResetOrder = true;
        }

        return $strQuery;
    }

    /**
     * Does as cache-lookup for prepared statements.
     * Reduces the number of recompiles at the db-side.
     *
     * @param string $strQuery
     *
     * @return resource
     * @since 3.4
     */
    private function getParsedStatement($strQuery)
    {

        if (StringUtil::indexOf($strQuery, "select", false) !== false) {
            $strQuery = StringUtil::replace(array(" as ", " AS "), array(" ", " "), $strQuery);
        }

        $objStatement = oci_parse($this->linkDB, $strQuery);
        return $objStatement;
    }

    /**
     * converts a result-row. changes all keys to lower-case keys again
     *
     * @param array $arrRow
     * @return array
     */
    private function parseResultRow(array $arrRow)
    {
        $arrRow = array_change_key_case($arrRow, CASE_LOWER);
        if (isset($arrRow["count(*)"])) {
            $arrRow["COUNT(*)"] = $arrRow["count(*)"];
        }

        return $arrRow;
    }

    /**
     * A method triggered in special cases in order to
     * have even the caches stored at the db-driver being flushed.
     * This could get important in case of schema updates since pre-compiled queries may get invalid due
     * to updated table definitions.
     *
     * @return void
     */
    public function flushQueryCache()
    {
    }

    /**
     * @inheritdoc
     */
    public function appendLimitExpression($strQuery, $intStart, $intEnd)
    {
        $intStart++;
        $intEnd++;

        return "SELECT * FROM (
                     SELECT a.*, ROWNUM rnum FROM
                        ( ".$strQuery.") a
                     WHERE ROWNUM <= ".$intEnd."
                )
                WHERE rnum >= ".$intStart;
    }

    /**
     * Sets the sorting and comparison of strings to case insensitive
     */
    private function setCaseInsensitiveSort()
    {
        $this->_pQuery("alter session set nls_sort=binary_ci", array());
        $this->_pQuery("alter session set nls_comp=LINGUISTIC", array());
    }

    /**
     * Sets the sorting and comparison of strings to case sensitive
     */
    private function setCaseSensitiveSort()
    {
        $this->_pQuery("alter session set nls_sort=binary", array());
        $this->_pQuery("alter session set nls_comp=ANSI", array());
    }
}

