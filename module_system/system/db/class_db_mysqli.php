<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/

/**
 * db-driver for MySQL using the php-mysqli-interface
 *
 * @package module_system
 */
class class_db_mysqli implements interface_db_driver {

    private $linkDB;						//DB-Link
    private $strHost = "";
    private $strUsername = "";
    private $strPass = "";
    private $strDbName = "";
    private $intPort = "";
    private $strDumpBin = "mysqldump";              //Binary to dump db (if not in path, add the path here)
    private $strRestoreBin = "mysql";               //Binary to dump db (if not in path, add the path here)
    private $arrStatementsCache = array();

    private $strErrorMessage = "";

   /**
     * This method makes sure to connect to the database properly
     *
     * @param string $strHost
     * @param string $strUsername
     * @param string $strPass
     * @param string $strDbName
     * @param int $intPort
     * @return bool
     * @throws class_exception
     */
    public function dbconnect($strHost, $strUsername, $strPass, $strDbName, $intPort) {
        if($intPort == "")
			$intPort = "3306";

		//save connection-details
		$this->strHost = $strHost;
		$this->strUsername = $strUsername;
		$this->strPass = $strPass;
		$this->strDbName = $strDbName;
		$this->intPort = $intPort;

		$this->linkDB = @mysqli_connect($strHost, $strUsername, $strPass, $strDbName, $intPort);
		if($this->linkDB !== false) {
			if(@mysqli_select_db($this->linkDB, $strDbName)) {
			    //erst ab mysql-client-bib > 4
			    //mysqli_set_charset($this->linkDB, "utf8");
                $this->_query("SET NAMES 'utf8'");
                $this->_query("SET CHARACTER SET utf8");
                $this->_query("SET character_set_connection ='utf8'");
                $this->_query("SET character_set_database ='utf8'");
                $this->_query("SET character_set_server ='utf8'");
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
     */
    public function dbclose() {
        @mysqli_close($this->linkDB);
    }

    /**
     * Sends a query (e.g. an update) to the database
     *
     * @param string $strQuery
     * @return bool
     */
    public function _query($strQuery) {
		$bitReturn = @mysqli_query($this->linkDB, $strQuery);
        return $bitReturn;
    }

    /**
     * Sends a prepared statement to the database. All params must be represented by the ? char.
     * The params themself are stored using the second params using the matching order.
     *
     * @param string $strQuery
     * @param array $arrParams
     * @return bool
     * @since 3.4
     */
    public function _pQuery($strQuery, $arrParams) {
        $objStatement = $this->getPreparedStatement($strQuery);
        $bitReturn = false;
        if($objStatement !== false) {
            $strTypes = "";
            foreach($arrParams as $strOneParam) {
                $strType = "s";
//                if(is_numeric($strOneParam))
//                    $strType = "i";
//                if(is_float($strOneParam) || is_long($strOneParam))
//                    $strType = "d";

                $strTypes .= $strType;
            }

            if(count($arrParams) > 0) {
                $arrParams = array_merge(array($strTypes), $arrParams);
                call_user_func_array(array($objStatement, 'bind_param'), $this->refValues($arrParams));
            }

            $bitReturn = @mysqli_stmt_execute($objStatement);
            //mysqli_stmt_close($objStatement);
        }

        return $bitReturn;
    }

    /**
     * This method is used to retrieve an array of resultsets from the database
     *
     * @param string $strQuery
     * @return mixed
     */
    public function getArray($strQuery) {
        $arrReturn = array();
        $intCounter = 0;
		$resultSet = @mysqli_query($this->linkDB, $strQuery);
		if(!$resultSet)
			return false;
		while($arrRow = @mysqli_fetch_array($resultSet)) {
			$arrReturn[$intCounter++] = $arrRow;
		}
		return $arrReturn;
    }

    /**
     * This method is used to retrieve an array of resultsets from the database using
     * a prepared statement.
     *
     * @param string $strQuery
     * @param array $arrParams
     * @since 3.4
     * @return array
     */
    public function getPArray($strQuery, $arrParams) {
        $objStatement = $this->getPreparedStatement($strQuery);
        $arrReturn = array();
        if($objStatement !== false) {
            $strTypes = "";
            foreach($arrParams as $strOneParam) {
                $strType = "s";
//                if(is_numeric($strOneParam))
//                    $strType = "i";
//                if(is_float($strOneParam) || is_long($strOneParam))
//                    $strType = "d";


                $strTypes .= $strType;
            }

            if(count($arrParams) > 0) {
                $arrParams = array_merge(array($strTypes), $arrParams);
                call_user_func_array(array($objStatement, 'bind_param'), $this->refValues($arrParams));
            }

            if(!mysqli_stmt_execute($objStatement))
                return false;

            //should remain here due to the bug http://bugs.php.net/bug.php?id=47928
            mysqli_stmt_store_result($objStatement);

            $objMetadata = mysqli_stmt_result_metadata($objStatement);
            $arrParams = array();
            $arrRow = array();
            while ($objField = $objMetadata->fetch_field()) {
                $arrParams[] = &$arrRow[$objField->name];
            }

            call_user_func_array(array($objStatement, 'bind_result'), $arrParams);

            while ($objStatement->fetch()) {
                $arrSingleRow = array();
                foreach($arrRow as $key => $val)  {
                    $arrSingleRow[$key] = $val;
                }
                $arrReturn[] = $arrSingleRow;
            }

            //mysqli_stmt_close($objStatement);
        }
        else
            return false;

        return $arrReturn;
    }

    /**
     * Returns just a part of a recodset, defined by the start- and the end-rows,
     * defined by the params
     *
     * @param string $strQuery
     * @param int $intStart
     * @param int $intEnd
     * @return array
     */
    public function getArraySection($strQuery, $intStart, $intEnd) {
        //calculate the end-value: mysql limit: start, nr of records, so:
        $intEnd = $intEnd - $intStart +1;
        //add the limits to the query
        $strQuery .= " LIMIT ".$intStart.", ".$intEnd;
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
     * @return array
     * @since 3.4
     */
    public function getPArraySection($strQuery, $arrParams, $intStart, $intEnd) {
        //calculate the end-value: mysql limit: start, nr of records, so:
        $intEnd = $intEnd - $intStart +1;
        //add the limits to the query
        $strQuery .= " LIMIT ".$intStart.", ".$intEnd;
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
		$strError = $this->strErrorMessage. " ".@mysqli_error($this->linkDB);
        $this->strErrorMessage = "";

		return $strError;
    }

    /**
     * Returns ALL tables in the database currently connected to
     *
     * @return mixed
     */
    public function getTables() {
		$arrTemp = $this->getArray("SHOW TABLE STATUS");
    	foreach($arrTemp as $intKey => $arrOneTemp) {
			$arrTemp[$intKey]["name"] = $arrTemp[$intKey]["Name"];
		}
		return $arrTemp;
    }

    /**
     * Looks up the columns of the given table.
     * Should return an array for each row consting of:
     * array ("columnName", "columnType")
     *
     * @param string $strTableName
     * @return array
     */
    public function getColumnsOfTable($strTableName) {
        $arrReturn = array();
        $arrTemp = $this->getArray("SHOW COLUMNS FROM ".dbsafeString($strTableName));
        foreach ($arrTemp as $arrOneColumn) {
            $arrReturn[] = array(
                        "columnName" => $arrOneColumn["Field"],
                        "columnType" => $arrOneColumn["Type"],
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
     * @return string
     */
    public function getDatatype($strType) {
    	$strReturn = "";

        if($strType == "int")
            $strReturn .= " INT ";
        elseif($strType == "long")
            $strReturn .= " BIGINT ";
        elseif($strType == "double")
            $strReturn .= " DOUBLE ";
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
            $strReturn .= " LONGTEXT ";
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
     * 		int
     *      long
     * 		double
     * 		char10
     * 		char20
     * 		char100
     * 		char254
     *      char500
     * 		text
     *      longtext
     *
     * @param string $strName
     * @param array $arrFields array of fields / columns
     * @param array $arrKeys array of primary keys
     * @param array $arrIndices array of additional indices
     * @param bool $bitTxSafe Should the table support transactions?
     * @return bool
     */
    public function createTable($strName, $arrFields, $arrKeys, $arrIndices = array(), $bitTxSafe = true) {
    	$strQuery = "";

    	//build the mysql code
    	$strQuery .= "CREATE TABLE IF NOT EXISTS `"._dbprefix_.$strName."` ( \n";

    	//loop the fields
    	foreach($arrFields as $strFieldName => $arrColumnSettings) {
    		$strQuery .= " `".$strFieldName."` ";

    		$strQuery .= $this->getDatatype($arrColumnSettings[0]);

    		//any default?
    		if(isset($arrColumnSettings[2]))
    				$strQuery .= "DEFAULT ".$arrColumnSettings[2]." ";

    		//nullable?
    		if($arrColumnSettings[1] === true) {
    			$strQuery .= " NULL , \n";
    		}
    		else {
    			$strQuery .= " NOT NULL , \n";
    		}

    	}

    	//primary keys
    	$strQuery .= " PRIMARY KEY ( `".implode("` , `", $arrKeys)."` ) \n";

    	if(count($arrIndices) > 0) {
            foreach($arrIndices as $strOneIndex) {
                $strQuery .= ", INDEX ( `".$strOneIndex."` ) \n ";
            }
        }


    	$strQuery .= ") ";

        if(!$bitTxSafe)
            $strQuery .= " ENGINE = myisam CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        else
            $strQuery .= " ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

        return $this->_query($strQuery);
    }

    /**
     * Starts a transaction
     *
     */
    public function transactionBegin() {
        //Autocommit 0 setzten
		$strQuery = "SET AUTOCOMMIT = 0";
		$strQuery2 = "BEGIN";
		$this->_query($strQuery);
		$this->_query($strQuery2);
    }

    /**
     * Ends a successfull operation by Commiting the transaction
     *
     */
    public function transactionCommit() {
        $str_query = "COMMIT";
		$str_query2 = "SET AUTOCOMMIT = 1";
		$this->_query($str_query);
		$this->_query($str_query2);
    }

    /**
     * Ends a non-successfull transaction by using a rollback
     *
     */
    public function transactionRollback() {
        $strQuery = "ROLLBACK";
		$strQuery2 = "SET AUTOCOMMIT = 1";
		$this->_query($strQuery);
		$this->_query($strQuery2);
    }

    public function getDbInfo() {
        $arrReturn = array();
        $arrReturn["dbdriver"] = "mysqli-extension";
        $arrReturn["dbserver"] = "MySQL ".mysqli_get_server_info($this->linkDB);
        $arrReturn["dbclient"] = mysqli_get_client_info($this->linkDB);
        $arrReturn["dbconnection"] = mysqli_get_host_info($this->linkDB);
        return $arrReturn;
    }

	/**
     * Allows the db-driver to add database-specific surrounding to column-names.
     * E.g. needed by the mysql-drivers
     *
     * @param string $strColumn
     * @return string
     */
    public function encloseColumnName($strColumn) {
    	return "`".$strColumn."`";
    }

    /**
     * Allows the db-driver to add database-specific surrounding to table-names.
     *
     * @param string $strTable
     * @return string
     */
    public function encloseTableName($strTable) {
        return "`".$strTable."`";
    }


//--- DUMP & RESTORE ------------------------------------------------------------------------------------

    /**
     * Dumps the current db
     *
     * @param string $strFilename
     * @param array $arrTables
     */
    public function dbExport($strFilename, $arrTables) {
        $strFilename = _realpath_.$strFilename;
        $strTables = implode(" ", $arrTables);
        $strParamPass = "";

        if ($this->strPass != "") {
        	$strParamPass = " -p\"".$this->strPass."\"";
        }

        $strCommand = $this->strDumpBin." -h".$this->strHost." -u".$this->strUsername.$strParamPass." -P".$this->intPort." ".$this->strDbName." ".$strTables." > \"".$strFilename."\"";
		//Now do a systemfork
		$intTemp = "";
		$strResult = system($strCommand, $intTemp);
        class_logger::getInstance()->addLogRow($this->strDumpBin." exited with code ".$intTemp, class_logger::$levelInfo);
        return $intTemp == 0;
    }

    /**
     * Imports the given db-dump to the database
     *
     * @param string $strFilename
     * @return bool
     */
    public function dbImport($strFilename) {
        $strFilename = _realpath_.$strFilename;
        $strParamPass = "";

        if ($this->strPass != "") {
            $strParamPass = " -p\"".$this->strPass."\"";
        }

        $strCommand = $this->strRestoreBin." -h".$this->strHost." -u".$this->strUsername.$strParamPass." -P".$this->intPort." ".$this->strDbName." < \"".$strFilename."\"";
        $intTemp = "";
        $strResult = system($strCommand, $intTemp);
        class_logger::getInstance()->addLogRow($this->strRestoreBin." exited with code ".$intTemp, class_logger::$levelInfo);
	    return $intTemp == 0;
    }

    /**
     * Converts a simple array into a an array of references.
     * Required fpr PHP > 5.3
     * @param array $arrValues
     * @return array
     */
    private function refValues($arrValues){
        if (strnatcmp(phpversion(),'5.3') >= 0) { //Reference is required for PHP 5.3+
            $refs = array();
            foreach($arrValues as $key => $value)
                $refs[$key] = &$arrValues[$key];
            return $refs;
        }
        return $arrValues;
    }

    /**
     * Prepares a statement or uses an instance from the cache
     *
     * @param string $strQuery
     * @return mysqli_stmt
     */
    private function getPreparedStatement($strQuery) {

        $strName = md5($strQuery);

        if(isset($this->arrStatementsCache[$strName]))
            return $this->arrStatementsCache[$strName];

        $objStatement = mysqli_stmt_init($this->linkDB);
        if(!mysqli_stmt_prepare($objStatement , $strQuery)) {
            $this->strErrorMessage = $objStatement->error;
            return false;
        }

        $this->arrStatementsCache[$strName] = $objStatement;

        return $objStatement;
    }

}

?>