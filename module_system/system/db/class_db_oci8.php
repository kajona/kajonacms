<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * db-driver for oracle using the ovi8-interface
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.4.1
 */
class class_db_oci8 extends class_db_base {

    private $linkDB; //DB-Link
    private $strHost = "";
    private $strUsername = "";
    private $strPass = "";
    private $strDbName = "";
    private $intPort = "";

    private $strDumpBin = "exp"; // Binary to dump db (if not in path, add the path here)
    // /usr/lib/oracle/xe/app/oracle/product/10.2.0/server/bin/
    private $strRestoreBin = "imp"; //Binary to restore db (if not in path, add the path here)

    private $bitTxOpen = false;

    /**
     * This method makes sure to connect to the database properly
     *
     * @param string $strHost
     * @param string $strUsername
     * @param string $strPass
     * @param string $strDbName
     * @param int $intPort
     *
     * @return bool
     * @throws class_exception
     */
    public function dbconnect($strHost, $strUsername, $strPass, $strDbName, $intPort) {
        if($intPort == "")
            $intPort = "1521";

        //save connection-details
        $this->strHost = $strHost;
        $this->strUsername = $strUsername;
        $this->strPass = $strPass;
        $this->strDbName = $strDbName;
        $this->intPort = $intPort;

        //try to set the NLS_LANG env attribute
        putenv("NLS_LANG=American_America.UTF8");

        $this->linkDB = @oci_connect($strUsername, $strPass, $strHost.":".$intPort."/".$strDbName, "AL32UTF8");


        if($this->linkDB !== false) {
            @oci_set_client_info($this->linkDB, "Kajona CMS");
            return true;
        }
        else {
            throw new class_exception("Error connecting to database", class_exception::$level_FATALERROR);
        }
    }

    /**
     * Closes the connection to the database
     * @return void
     */
    public function dbclose() {
        @oci_close($this->linkDB);
    }

    /**
     * Creates a single query in order to insert multiple rows at one time.
     * For most databases, this will create s.th. like
     * INSERT INTO $strTable ($arrColumns) VALUES (?, ?), (?, ?)...
     *
     * Please note that this method is used to create the query itself, based on the Kajona-internal syntax.
     * The query is fired to the database by class_db
     *
     * @param string $strTable
     * @param string[] $arrColumns
     * @param array $arrValueSets
     * @param class_db $objDb
     *
     * @return bool
     */
    public function triggerMultiInsert($strTable, $arrColumns, $arrValueSets, class_db $objDb) {

        $bitReturn = true;

        //ugly hack for oracle: it only supports 1000 params per query as maximum, so split into several parts
        //calc the number of max rows per insert. to be sure split it down to 970
        $intSetsPerInsert = floor(970 / count($arrColumns));
        foreach(array_chunk($arrValueSets, $intSetsPerInsert) as $arrSingleValueSet) {

            $arrPlaceholder = array();
            $arrSafeColumns = array();

            foreach($arrColumns as $strOneColumn) {
                $arrSafeColumns[] = $this->encloseColumnName($strOneColumn);
                $arrPlaceholder[] = "?";
            }
            $strPlaceholder = " (".implode(",", $arrPlaceholder).") ";
            $strColumnNames = " (".implode(",", $arrSafeColumns).") ";

            $arrParams = array();

            $strQuery = "INSERT ALL ";
            foreach($arrSingleValueSet as $arrOneSet) {
                $arrParams = array_merge($arrParams, $arrOneSet);

                $strQuery .= " INTO ".$this->encloseTableName($strTable)." ".$strColumnNames." VALUES ".$strPlaceholder." ";
            }
            $strQuery .= " SELECT * FROM dual";

            $bitReturn = $objDb->_pQuery($strQuery, $arrParams) && $bitReturn;
        }

        return $bitReturn;
    }

    /**
     * Sends a query (e.g. an update) to the database
     *
     * @param string $strQuery
     *
     * @return bool
     */
    public function _query($strQuery) {
        $objStatement = $this->getParsedStatement($strQuery);

        $bitAddon = OCI_COMMIT_ON_SUCCESS;
        if($this->bitTxOpen)
            $bitAddon = OCI_NO_AUTO_COMMIT;
        $bitResult = oci_execute($objStatement, $bitAddon);
        @oci_free_statement($objStatement);
        return $bitResult;
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
    public function _pQuery($strQuery, $arrParams) {
        $strQuery = $this->processQuery($strQuery);
        $objStatement = $this->getParsedStatement($strQuery);
        if($objStatement === false)
            return false;

        foreach($arrParams as $intPos => $strValue)
            oci_bind_by_name($objStatement, ":".($intPos + 1), $arrParams[$intPos]);

        $bitAddon = OCI_COMMIT_ON_SUCCESS;
        if($this->bitTxOpen)
            $bitAddon = OCI_NO_AUTO_COMMIT;
        $bitResult = oci_execute($objStatement, $bitAddon);
        @oci_free_statement($objStatement);
        return $bitResult;
    }

    /**
     * This method is used to retrieve an array of resultsets from the database
     *
     * @param string $strQuery
     *
     * @return mixed
     */
    public function getArray($strQuery) {
        $arrReturn = array();
        $intCounter = 0;
        $objStatement = $this->getParsedStatement($strQuery);

        $bitAddon = OCI_COMMIT_ON_SUCCESS;
        if($this->bitTxOpen)
            $bitAddon = OCI_NO_AUTO_COMMIT;
        $resultSet = oci_execute($objStatement, $bitAddon);

        if(!$resultSet)
            return false;

        while($arrRow = oci_fetch_array($objStatement, OCI_BOTH + OCI_RETURN_NULLS)) {
            $arrRow = $this->parseResultRow($arrRow);
            $arrReturn[$intCounter++] = $arrRow;
        }

        @oci_free_statement($objStatement);
        return $arrReturn;
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
    public function getPArray($strQuery, $arrParams) {
        $arrReturn = array();
        $intCounter = 0;

        $strQuery = $this->processQuery($strQuery);
        $objStatement = $this->getParsedStatement($strQuery);

        if($objStatement === false)
            return false;

        foreach($arrParams as $intPos => $strValue)
            oci_bind_by_name($objStatement, ":".($intPos + 1), $arrParams[$intPos]);

        $bitAddon = OCI_COMMIT_ON_SUCCESS;
        if($this->bitTxOpen)
            $bitAddon = OCI_NO_AUTO_COMMIT;
        $resultSet = oci_execute($objStatement, $bitAddon);

        if(!$resultSet)
            return false;

        while($arrRow = oci_fetch_array($objStatement, OCI_BOTH + OCI_RETURN_NULLS)) {
            $arrRow = $this->parseResultRow($arrRow);
            $arrReturn[$intCounter++] = $arrRow;
        }
        @oci_free_statement($objStatement);
        return $arrReturn;
    }

    /**
     * Returns just a part of a recodset, defined by the start- and the end-rows,
     * defined by the params
     *
     * @param string $strQuery
     * @param int $intStart
     * @param int $intEnd
     *
     * @return array
     */
    public function getArraySection($strQuery, $intStart, $intEnd) {
        //array-counters to real-counters
        $intStart++;
        $intEnd++;

        //modify the query
        $strQuery = "SELECT * FROM (
             SELECT a.*, ROWNUM rnum FROM
                ( ".$strQuery.") a
             WHERE ROWNUM <= ".$intEnd."
        )
        WHERE rnum >= ".$intStart;

        //and load the array
        return $this->getArray($strQuery);
    }

    /**
     * Returns just a part of a recodset, defined by the start- and the end-rows,
     * defined by the params. Makes use of prepared statements.
     * <b>Note:</b> Use array-like counters, so the first row is startRow 0 whereas
     * the n-th row is the (n-1)th key!!!
     *
     * @param string $strQuery
     * @param array $arrParams
     * @param int $intStart
     * @param int $intEnd
     *
     * @return array
     * @since 3.4
     */
    public function getPArraySection($strQuery, $arrParams, $intStart, $intEnd) {
        //calculate the end-value:
        //array-counters to real-counters
        $intStart++;
        $intEnd++;

        //modify the query
        $strQuery = "SELECT * FROM (
             SELECT a.*, ROWNUM rnum FROM
                ( ".$strQuery.") a
             WHERE ROWNUM <= ".$intEnd."
        )
        WHERE rnum >= ".$intStart;

        //and load the array
        return $this->getPArray($strQuery, $arrParams);
    }

    /**
     * Returns the last error reported by the database.
     * Is being called after unsuccessful queries
     *
     * @return string
     */
    public function getError() {
        $strError = oci_error($this->linkDB);
        return $strError;
    }

    /**
     * Returns ALL tables in the database currently connected to
     *
     * @return mixed
     */
    public function getTables() {
        $arrTemp = $this->getArray("SELECT table_name AS name FROM ALL_TABLES");

        foreach($arrTemp as $intKey => $strValue)
            $arrTemp[$intKey]["name"] = uniStrtolower($strValue["name"]);
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
    public function getColumnsOfTable($strTableName) {
        $arrReturn = array();
        $arrTemp = $this->getPArray("select column_name, data_type from user_tab_columns where table_name=?", array(strtoupper($strTableName)));

        foreach($arrTemp as $arrOneColumn) {
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
    public function getDatatype($strType) {
        $strReturn = "";

        if($strType == class_db_datatypes::STR_TYPE_INT)
            $strReturn .= " NUMBER(19,0) ";
        elseif($strType == class_db_datatypes::STR_TYPE_LONG)
            $strReturn .= " NUMBER(19, 0) ";
        elseif($strType == class_db_datatypes::STR_TYPE_DOUBLE)
            $strReturn .= " FLOAT (24) ";
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR10)
            $strReturn .= " VARCHAR2( 10 ) ";
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR20)
            $strReturn .= " VARCHAR2( 20 ) ";
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR100)
            $strReturn .= " VARCHAR2( 100 ) ";
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR254)
            $strReturn .= " VARCHAR2( 280 ) ";
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR500)
            $strReturn .= " VARCHAR2( 500 ) ";
        elseif($strType == class_db_datatypes::STR_TYPE_TEXT)
            $strReturn .= " VARCHAR2( 4000 ) ";
        elseif($strType == class_db_datatypes::STR_TYPE_LONGTEXT)
            $strReturn .= " CLOB ";
        else
            $strReturn .= " VARCHAR( 254 ) ";

        return $strReturn;
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
    public function createTable($strName, $arrFields, $arrKeys, $arrIndices = array(), $bitTxSafe = true) {
        $strQuery = "";

        //loop over existing tables to check, if the table already exists
        $arrTables = $this->getTables();
        foreach($arrTables as $arrOneTable) {
            if($arrOneTable["name"] == $strName)
                return true;
        }

        //build the oracle code
        $strQuery .= "CREATE TABLE ".$strName." ( \n";

        //loop the fields
        foreach($arrFields as $strFieldName => $arrColumnSettings) {
            $strQuery .= " ".$strFieldName." ";

            $strQuery .= $this->getDatatype($arrColumnSettings[0]);

            //any default?
            if(isset($arrColumnSettings[2]))
                $strQuery .= "DEFAULT ".$arrColumnSettings[2]." ";

            //nullable?
            if($arrColumnSettings[1] === true) {
                $strQuery .= " NULL ";
            }
            else {
                $strQuery .= " NOT NULL ";
            }

            $strQuery .= " , \n";

        }

        //primary keys
        $strQuery .= " CONSTRAINT pk_".generateSystemid()." primary key ( ".implode(" , ", $arrKeys)." ) \n";
        $strQuery .= ") ";

        $bitCreate = $this->_query($strQuery);

        if($bitCreate && count($arrIndices) > 0) {
            $strQuery = "CREATE INDEX ix_".generateSystemid()." ON ".$strName." ( ".implode(", ", $arrIndices).") ";
            $bitCreate = $bitCreate && $this->_query($strQuery);
        }

        return $bitCreate;
    }

    /**
     * Starts a transaction
     *
     * @return void
     */
    public function transactionBegin() {
        $this->bitTxOpen = true;
    }

    /**
     * Ends a successful operation by committing the transaction
     * @return void
     */
    public function transactionCommit() {
        oci_commit($this->linkDB);
        $this->bitTxOpen = false;
    }

    /**
     * Ends a non-successful transaction by using a rollback
     * @return void
     */
    public function transactionRollback() {
        oci_rollback($this->linkDB);
        $this->bitTxOpen = false;
    }

    /**
     * @return array|mixed
     */
    public function getDbInfo() {
        $arrReturn = array();
        $arrReturn["dbdriver"] = "oci8-oracle-extension";
        $arrReturn["dbserver"] = oci_server_version($this->linkDB);
        $arrReturn["dbclient"] = function_exists("oci_client_version") ? oci_client_version($this->linkDB) : "";
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
    public function dbExport($strFilename, $arrTables) {

        $strFilename = _realpath_.$strFilename;
        $strTables = implode(",", $arrTables);

        /*
        if ($this->strPass != "") {
        	$strParamPass = " -p".$this->strPass;
        }
        */

        $strCommand = $this->strDumpBin." ".$this->strUsername."/".$this->strPass." CONSISTENT=n TABLES=".$strTables." FILE='".$strFilename."'";
        class_logger::getInstance(class_logger::DBLOG)->addLogRow("dump command: ".$strCommand, class_logger::$levelInfo);
        //Now do a systemfork
        $intTemp = "";
        system($strCommand, $intTemp);
        class_logger::getInstance(class_logger::DBLOG)->addLogRow($this->strDumpBin." exited with code ".$intTemp, class_logger::$levelInfo);
        return $intTemp == 0;
    }

    /**
     * Imports the given db-dump to the database
     *
     * @param string $strFilename
     *
     * @return bool
     */
    public function dbImport($strFilename) {

        $strFilename = _realpath_.$strFilename;
        $strCommand = $this->strRestoreBin." ".$this->strUsername."/".$this->strPass." FILE='".$strFilename."'";
        $intTemp = "";
        system($strCommand, $intTemp);
        class_logger::getInstance(class_logger::DBLOG)->addLogRow($this->strRestoreBin." exited with code ".$intTemp, class_logger::$levelInfo);
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
    private function processQuery($strQuery) {
        $intCount = 1;
        while(uniStrpos($strQuery, "?") !== false) {
            $intPos = uniStrpos($strQuery, "?");
            $strQuery = substr($strQuery, 0, $intPos).":".$intCount++.substr($strQuery, $intPos + 1);
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
    private function getParsedStatement($strQuery) {

        if(uniStripos($strQuery, "select") !== false) {
            $strQuery = uniStrReplace(array(" as ", " AS "), array(" ", " "), $strQuery);
        }

        $objStatement = oci_parse($this->linkDB, $strQuery);
        return $objStatement;
    }

    /**
     * convertes a result-row. changes all keys to lower-case keys again
     *
     * @param array $arrRow
     *
     * @return array
     */
    private function parseResultRow(array $arrRow) {
        $arrRow = array_change_key_case($arrRow, CASE_LOWER);
        if(isset($arrRow["count(*)"]))
            $arrRow["COUNT(*)"] = $arrRow["count(*)"];

        foreach($arrRow as $intKey => $mixedValue) {
            if(is_object($mixedValue)) {
                $arrRow[$intKey] = $mixedValue->load();
                $mixedValue->free();
            }
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
    public function flushQueryCache() {
    }

}

