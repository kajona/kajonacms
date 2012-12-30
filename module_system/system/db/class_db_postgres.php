<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

/**
 * db-driver for postgres using the php-postgres-interface
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_db_postgres implements interface_db_driver {

    private $linkDB; //DB-Link
    private $strHost = "";
    private $strUsername = "";
    private $strPass = "";
    private $strDbName = "";
    private $intPort = "";
    private $strDumpBin = "pg_dump"; //Binary to dump db (if not in path, add the path here)
    private $strRestoreBin = "psql"; //Binary to restore db (if not in path, add the path here)

    private $arrStatementsCache = array();

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
            $intPort = "5432";

        //save connection-details
        $this->strHost = $strHost;
        $this->strUsername = $strUsername;
        $this->strPass = $strPass;
        $this->strDbName = $strDbName;
        $this->intPort = $intPort;

        $this->linkDB = @pg_connect("host='".$strHost."' port='".$intPort."' dbname='".$strDbName."' user='".$strUsername."' password='".$strPass."'");

        if($this->linkDB !== false) {
            $this->_query("SET client_encoding='UTF8'");
            return true;
        }
        else {
            throw new class_exception("Error connecting to database", class_exception::$level_FATALERROR);
        }
    }

    /*
     * Closes the connection to the database
     */
    public function dbclose() {
        @pg_close($this->linkDB);
    }

    /**
     * Sends a query (e.g. an update) to the database
     *
     * @param string $strQuery
     *
     * @return bool
     */
    public function _query($strQuery) {
        $bitReturn = @pg_query($this->linkDB, $strQuery);
        return $bitReturn !== false;
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
        $strName = $this->getPreparedStatementName($strQuery);
        if($strName === false)
            return false;

        return @pg_execute($this->linkDB, $strName, $arrParams) !== false;
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
        $resultSet = @pg_query($this->linkDB, $strQuery);
        if(!$resultSet)
            return false;
        while($arrRow = @pg_fetch_array($resultSet)) {
            //conversions to remain compatible:
            //   count --> COUNT(*)
            if(isset($arrRow["count"]))
                $arrRow["COUNT(*)"] = $arrRow["count"];

            $arrReturn[$intCounter++] = $arrRow;
        }
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
        $strName = $this->getPreparedStatementName($strQuery);
        if($strName === false)
            return false;

        $resultSet = @pg_execute($this->linkDB, $strName, $arrParams);

        while($arrRow = @pg_fetch_array($resultSet)) {
            //conversions to remain compatible:
            //   count --> COUNT(*)
            if(isset($arrRow["count"]))
                $arrRow["COUNT(*)"] = $arrRow["count"];

            $arrReturn[$intCounter++] = $arrRow;
        }
        return $arrReturn;
    }

    /**
     * Returns just a part of a recordset, defined by the start- and the end-rows,
     * defined by the params
     *
     * @param string $strQuery
     * @param int $intStart
     * @param int $intEnd
     *
     * @return array
     */
    public function getArraySection($strQuery, $intStart, $intEnd) {
        //calculate the end-value:
        $intEnd = $intEnd - $intStart + 1;
        //add the limits to the query
        $strQuery .= " LIMIT  ".$intEnd." OFFSET ".$intStart;
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
        $intEnd = $intEnd - $intStart + 1;
        //add the limits to the query
        $strQuery .= " LIMIT  ".$intEnd." OFFSET ".$intStart;
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
        $strError = @pg_last_error($this->linkDB);
        return $strError;
    }

    /**
     * Returns ALL tables in the database currently connected to
     *
     * @return mixed
     */
    public function getTables() {
        $arrTemp = $this->getArray("SELECT *, table_name as name FROM information_schema.tables");

        $arrReturn = array();
        foreach($arrTemp as $arrOneRow) {
            if(uniStrpos($arrOneRow["name"], _dbprefix_) !== false)
                $arrReturn[] = $arrOneRow;
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
    public function getColumnsOfTable($strTableName) {
        $arrReturn = array();
        $arrTemp = $this->getArray(
            "SELECT *
            FROM information_schema.columns
            WHERE table_name = '".dbsafeString($strTableName)."'"
        );

        foreach($arrTemp as $arrOneColumn) {
            $arrReturn[] = array(
                "columnName" => $arrOneColumn["column_name"],
                "columnType" => ($arrOneColumn["data_type"] == "integer" ? "int" : $arrOneColumn["data_type"]),
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

        if($strType == "int")
            $strReturn .= " INT ";
        elseif($strType == "long")
            $strReturn .= " BIGINT ";
        elseif($strType == "double")
            $strReturn .= " NUMERIC ";
        elseif($strType == "char10")
            $strReturn .= " VARCHAR( 10 ) ";
        elseif($strType == "char20")
            $strReturn .= " VARCHAR( 20 ) ";
        elseif($strType == "char100")
            $strReturn .= " VARCHAR( 100 ) ";
        elseif($strType == "char254")
            $strReturn .= " VARCHAR( 254 ) ";
        elseif($strType == "char500")
            $strReturn .= " VARCHAR( 500 ) ";
        elseif($strType == "text")
            $strReturn .= " TEXT ";
        elseif($strType == "longtext")
            $strReturn .= " TEXT ";
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
            if($arrOneTable["name"] == _dbprefix_.$strName)
                return true;
        }

        //build the mysql code
        $strQuery .= "CREATE TABLE "._dbprefix_.$strName." ( \n";

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
        $strQuery .= " PRIMARY KEY ( ".implode(" , ", $arrKeys)." ) \n";


        $strQuery .= ") ";
        $bitCreate = $this->_query($strQuery);

        if($bitCreate && count($arrIndices) > 0) {
            $strQuery = "CREATE INDEX ix_".generateSystemid()." ON "._dbprefix_.$strName." ( ".implode(", ", $arrIndices).") ";
            $bitCreate = $bitCreate && $this->_query($strQuery);
        }

        return $bitCreate;
    }

    /**
     * Starts a transaction

     */
    public function transactionBegin() {
        //Autocommit 0 setzten
        $strQuery = "BEGIN";
        $this->_query($strQuery);
    }

    /**
     * Ends a successfull operation by Commiting the transaction

     */
    public function transactionCommit() {
        $str_query = "COMMIT";
        $this->_query($str_query);
    }

    /**
     * Ends a non-successfull transaction by using a rollback

     */
    public function transactionRollback() {
        $strQuery = "ROLLBACK";
        $this->_query($strQuery);
    }

    public function getDbInfo() {
        $arrInfo = @pg_version($this->linkDB);
        $arrReturn = array();
        $arrReturn["dbdriver"] = "postgres-extension";
        $arrReturn["dbserver"] = "postgres ".$arrInfo["server"];
        $arrReturn["dbclient"] = $arrInfo["client"];
        $arrReturn["dbconnection"] = $arrInfo["protocol"];
        return $arrReturn;
    }

    /**
     * Allows the db-driver to add database-specific surrounding to column-names.
     * E.g. needed by the mysql-drivers
     *
     * @param string $strColumn
     *
     * @return string
     */
    public function encloseColumnName($strColumn) {
        return $strColumn;
    }

    /**
     * Allows the db-driver to add database-specific surrounding to table-names.
     *
     * @param string $strTable
     *
     * @return string
     */
    public function encloseTableName($strTable) {
        return $strTable;
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
        $strTables = "-t ".implode(" -t ", $arrTables);
        $strParamPass = "";

        /*
        if ($this->strPass != "") {
        	$strParamPass = " -p".$this->strPass;
        }
        */

        $strCommand = $this->strDumpBin." --clean -h".$this->strHost." -U".$this->strUsername.$strParamPass." -p".$this->intPort." ".$strTables." ".$this->strDbName." > \"".$strFilename."\"";
        //Now do a systemfork
        $intTemp = "";
        $strResult = system($strCommand, $intTemp);
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
        $strParamPass = "";
        /*
        if ($this->strPass != "") {
            $strParamPass = " -p".$this->strPass;
        }
        */
        $strCommand = $this->strRestoreBin." -q -h".$this->strHost." -U".$this->strUsername.$strParamPass." -p".$this->intPort." ".$this->strDbName." < \"".$strFilename."\"";
        $intTemp = "";
        $strResult = system($strCommand, $intTemp);
        class_logger::getInstance(class_logger::DBLOG)->addLogRow($this->strRestoreBin." exited with code ".$intTemp, class_logger::$levelInfo);
        return $intTemp == 0;
    }

    /**
     * Transforms the query into a valid postgres-syntax
     *
     * @param string $strQuery
     *
     * @return string
     */
    private function processQuery($strQuery) {
        $intCount = 1;
        while(uniStrpos($strQuery, "?") !== false) {
            $intPos = uniStrpos($strQuery, "?");
            $strQuery = substr($strQuery, 0, $intPos)."$".$intCount++.substr($strQuery, $intPos + 1);
        }
        return $strQuery;
    }

    /**
     * Does as cache-lookup for prepared statements.
     * Reduces the number of recompiles at the db-side.
     *
     * @param string $strQuery
     *
     * @return ressource
     * @since 3.4
     */
    private function getPreparedStatementName($strQuery) {
        $strSum = md5($strQuery);
        if(in_array($strSum, $this->arrStatementsCache))
            return $strSum;

        if(@pg_prepare($this->linkDB, $strSum, $strQuery))
            $this->arrStatementsCache[] = $strSum;
        else
            return false;

        return $strSum;
    }

    /**
     * A method triggered in special cases in order to
     * have even the caches stored at the db-driver being flushed.
     * This could get important in case of schema updates since precompiled queries may get invalid due
     * to updated table definitions.
     *
     * @return void
     */
    public function flushQueryCache() {
        $this->arrStatementsCache = array();
    }

}

