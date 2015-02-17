<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * db-driver for MySQL using the php-mysqli-interface
 *
 * @package module_system
 */
class class_db_mysqli extends class_db_base {

    /**
     * @var mysqli
     */
    private $linkDB; //DB-Link
    private $strHost = "";
    private $strUsername = "";
    private $strPass = "";
    private $strDbName = "";
    private $intPort = "";
    private $strDumpBin = "mysqldump"; //Binary to dump db (if not in path, add the path here)
    private $strRestoreBin = "mysql"; //Binary to dump db (if not in path, add the path here)

    private $strErrorMessage = "";

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
        if($intPort == "") {
            $intPort = "3306";
        }

        //save connection-details
        $this->strHost = $strHost;
        $this->strUsername = $strUsername;
        $this->strPass = $strPass;
        $this->strDbName = $strDbName;
        $this->intPort = $intPort;

        $this->linkDB = @new mysqli($strHost, $strUsername, $strPass, $strDbName, $intPort);
        if($this->linkDB !== false) {
            if(@$this->linkDB->select_db($strDbName)) {
                //erst ab mysql-client-bib > 4
                //mysqli_set_charset($this->linkDB, "utf8");
                $this->_pQuery("SET NAMES 'utf8'", array());
                $this->_pQuery("SET CHARACTER SET utf8", array());
                $this->_pQuery("SET character_set_connection ='utf8'", array());
                $this->_pQuery("SET character_set_database ='utf8'", array());
                $this->_pQuery("SET character_set_server ='utf8'", array());
                return true;
            }
            else {
                throw new class_exception("Error selecting database", class_exception::$level_FATALERROR);
            }
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
        $this->linkDB->close();
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
        $objStatement = $this->getPreparedStatement($strQuery);
        $bitReturn = false;
        if($objStatement !== false) {
            $strTypes = "";
            foreach($arrParams as $strOneParam) {
                $strTypes .= "s";
            }

            if(count($arrParams) > 0) {
                $arrParams = array_merge(array($strTypes), $arrParams);
                call_user_func_array(array($objStatement, 'bind_param'), $this->refValues($arrParams));
            }

            $bitReturn = $objStatement->execute();
        }

        return $bitReturn;
    }

    /**
     * This method is used to retrieve an array of resultsets from the database using
     * a prepared statement.
     *
     * @param string $strQuery
     * @param array $arrParams
     *
     * @since 3.4
     * @return array
     */
    public function getPArray($strQuery, $arrParams) {
        $objStatement = $this->getPreparedStatement($strQuery);
        $arrReturn = array();
        if($objStatement !== false) {
            $strTypes = "";
            foreach($arrParams as $strOneParam) {
                $strTypes .= "s";
            }

            if(count($arrParams) > 0) {
                $arrParams = array_merge(array($strTypes), $arrParams);
                call_user_func_array(array($objStatement, 'bind_param'), $this->refValues($arrParams));
            }

            if(!$objStatement->execute()) {
                return false;
            }

            //should remain here due to the bug http://bugs.php.net/bug.php?id=47928
            $objStatement->store_result();

            $objMetadata = $objStatement->result_metadata();
            $arrParams = array();
            $arrRow = array();
            while($objField = $objMetadata->fetch_field()) {
                $arrParams[] = &$arrRow[$objField->name];
            }

            call_user_func_array(array($objStatement, 'bind_result'), $arrParams);

            while($objStatement->fetch()) {
                $arrSingleRow = array();
                foreach($arrRow as $key => $val) {
                    $arrSingleRow[$key] = $val;
                }
                $arrReturn[] = $arrSingleRow;
            }

        }
        else {
            return false;
        }

        return $arrReturn;
    }


    /**
     * Returns the last error reported by the database.
     * Is being called after unsuccessful queries
     *
     * @return string
     */
    public function getError() {
        $strError = $this->strErrorMessage . " " . $this->linkDB->error;
        $this->strErrorMessage = "";

        return $strError;
    }

    /**
     * Returns ALL tables in the database currently connected to
     *
     * @return mixed
     */
    public function getTables() {
        $arrTemp = $this->getPArray("SHOW TABLE STATUS", array());
        foreach($arrTemp as $intKey => $arrOneTemp) {
            $arrTemp[$intKey]["name"] = $arrTemp[$intKey]["Name"];
        }
        return $arrTemp;
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
    public function getColumnsOfTable($strTableName) {
        $arrReturn = array();
        $arrTemp = $this->getPArray("SHOW COLUMNS FROM ".$this->encloseTableName(class_db::getInstance()->dbsafeString($strTableName)), array());
        foreach($arrTemp as $arrOneColumn) {
            $arrReturn[] = array(
                "columnName" => $arrOneColumn["Field"],
                "columnType" => $arrOneColumn["Type"],
            );
        }
        return $arrReturn;
    }

    /**
     * Returns the db-specific datatype for the kajona internal datatype.
     *
     * @param string $strType
     *
     * @return string
     */
    public function getDatatype($strType) {
        $strReturn = "";

        if($strType == class_db_datatypes::STR_TYPE_INT) {
            $strReturn .= " INT ";
        }
        elseif($strType == class_db_datatypes::STR_TYPE_LONG) {
            $strReturn .= " BIGINT ";
        }
        elseif($strType == class_db_datatypes::STR_TYPE_DOUBLE) {
            $strReturn .= " DOUBLE ";
        }
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR10) {
            $strReturn .= " VARCHAR( 10 ) ";
        }
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR20) {
            $strReturn .= " VARCHAR( 20 ) ";
        }
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR100) {
            $strReturn .= " VARCHAR( 100 ) ";
        }
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR254) {
            $strReturn .= " VARCHAR( 254 ) ";
        }
        elseif($strType == class_db_datatypes::STR_TYPE_CHAR500) {
            $strReturn .= " VARCHAR( 500 ) ";
        }
        elseif($strType == class_db_datatypes::STR_TYPE_TEXT) {
            $strReturn .= " TEXT ";
        }
        elseif($strType == class_db_datatypes::STR_TYPE_LONGTEXT) {
            $strReturn .= " LONGTEXT ";
        }
        else {
            $strReturn .= " VARCHAR( 254 ) ";
        }

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
        $strQuery = "";

        //build the mysql code
        $strQuery .= "CREATE TABLE IF NOT EXISTS `" .$strName . "` ( \n";

        //loop the fields
        foreach($arrFields as $strFieldName => $arrColumnSettings) {
            $strQuery .= " `" . $strFieldName . "` ";

            $strQuery .= $this->getDatatype($arrColumnSettings[0]);

            //any default?
            if(isset($arrColumnSettings[2])) {
                $strQuery .= "DEFAULT " . $arrColumnSettings[2] . " ";
            }

            //nullable?
            if($arrColumnSettings[1] === true) {
                $strQuery .= " NULL , \n";
            }
            else {
                $strQuery .= " NOT NULL , \n";
            }

        }

        //primary keys
        $strQuery .= " PRIMARY KEY ( `" . implode("` , `", $arrKeys) . "` ) \n";

        if(count($arrIndices) > 0) {
            foreach($arrIndices as $strOneIndex) {
                $strQuery .= ", INDEX ( `" . $strOneIndex . "` ) \n ";
            }
        }


        $strQuery .= ") ";

        if(!$bitTxSafe) {
            $strQuery .= " ENGINE = myisam CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        }
        else {
            $strQuery .= " ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        }

        return $this->_pQuery($strQuery, array());
    }

    /**
     * Starts a transaction
     * @return void
     */
    public function transactionBegin() {
        //Autocommit 0 setzten
        $strQuery = "SET AUTOCOMMIT = 0";
        $strQuery2 = "BEGIN";
        $this->_pQuery($strQuery, array());
        $this->_pQuery($strQuery2, array());
    }

    /**
     * Ends a successful operation by Commiting the transaction
     * @return void
     */
    public function transactionCommit() {
        $str_pQuery = "COMMIT";
        $str_pQuery2 = "SET AUTOCOMMIT = 1";
        $this->_pQuery($str_pQuery, array());
        $this->_pQuery($str_pQuery2, array());
    }

    /**
     * Ends a non-successful transaction by using a rollback
     * @return void
     */
    public function transactionRollback() {
        $strQuery = "ROLLBACK";
        $strQuery2 = "SET AUTOCOMMIT = 1";
        $this->_pQuery($strQuery, array());
        $this->_pQuery($strQuery2, array());
    }

    /**
     * @return array|mixed
     */
    public function getDbInfo() {
        $arrReturn = array();
        $arrReturn["dbdriver"] = "mysqli-extension";
        $arrReturn["dbserver"] = "MySQL " . $this->linkDB->server_info;
        $arrReturn["dbclient"] =  $this->linkDB->client_info;
        $arrReturn["dbconnection"] = $this->linkDB->host_info;
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
        return "`" . $strColumn . "`";
    }

    /**
     * Allows the db-driver to add database-specific surrounding to table-names.
     *
     * @param string $strTable
     *
     * @return string
     */
    public function encloseTableName($strTable) {
        return "`" . $strTable . "`";
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
        $strFilename = _realpath_ . $strFilename;
        $strTables = implode(" ", $arrTables);
        $strParamPass = "";

        if($this->strPass != "") {
            $strParamPass = " -p\"" . $this->strPass . "\"";
        }

        $strCommand = $this->strDumpBin . " -h" . $this->strHost . " -u" . $this->strUsername . $strParamPass . " -P" . $this->intPort . " " . $this->strDbName . " " . $strTables . " > \"" . $strFilename . "\"";
        //Now do a systemfork
        $intTemp = "";
        system($strCommand, $intTemp);
        if($intTemp == 0)
            class_logger::getInstance(class_logger::DBLOG)->addLogRow($this->strDumpBin . " exited with code " . $intTemp, class_logger::$levelInfo);
        else
            class_logger::getInstance(class_logger::DBLOG)->addLogRow($this->strDumpBin . " exited with code " . $intTemp, class_logger::$levelWarning);

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
        $strFilename = _realpath_ . $strFilename;
        $strParamPass = "";

        if($this->strPass != "") {
            $strParamPass = " -p\"" . $this->strPass . "\"";
        }

        $strCommand = $this->strRestoreBin . " -h" . $this->strHost . " -u" . $this->strUsername . $strParamPass . " -P" . $this->intPort . " " . $this->strDbName . " < \"" . $strFilename . "\"";
        $intTemp = "";
        system($strCommand, $intTemp);
        if($intTemp == 0)
            class_logger::getInstance(class_logger::DBLOG)->addLogRow($this->strDumpBin . " exited with code " . $intTemp, class_logger::$levelInfo);
        else
            class_logger::getInstance(class_logger::DBLOG)->addLogRow($this->strDumpBin . " exited with code " . $intTemp, class_logger::$levelWarning);
        return $intTemp == 0;
    }

    /**
     * Converts a simple array into a an array of references.
     * Required for PHP > 5.3
     *
     * @param array $arrValues
     *
     * @return array
     */
    private function refValues($arrValues) {
        if(strnatcmp(phpversion(), '5.3') >= 0) { //Reference is required for PHP 5.3+
            $refs = array();
            foreach($arrValues as $key => $value) {
                $refs[$key] = &$arrValues[$key];
            }
            return $refs;
        }
        return $arrValues;
    }

    /**
     * Prepares a statement or uses an instance from the cache
     *
     * @param string $strQuery
     *
     * @return mysqli_stmt
     */
    private function getPreparedStatement($strQuery) {

        $strName = md5($strQuery);

        if(isset($this->arrStatementsCache[$strName])) {
            return $this->arrStatementsCache[$strName];
        }

        $objStatement = $this->linkDB->stmt_init();
        if(!$objStatement->prepare($strQuery)) {
            $this->strErrorMessage = $objStatement->error;
            return false;
        }

        $this->arrStatementsCache[$strName] = $objStatement;

        return $objStatement;
    }


}

