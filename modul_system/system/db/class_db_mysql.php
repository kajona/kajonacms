<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_db_mysql.php                                                                                  *
* 	Driver for Mysql-DB using mysql-client to connect                                                   *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/

//Interface
include_once(_systempath_."/db/interface_db_driver.php");

/**
 * db-driver for MySQL using the php-mysql interface
 *
 * @package modul_system
 */
class class_db_mysql implements interface_db_driver {

    private $strHost = "";
    private $strUsername = "";
    private $strPass = "";
    private $strDbName = "";
    private $intPort = "";
    private $strDumpBin = "mysqldump";              //Binary to dump db (if not in path, add the path here)
    private $strRestoreBin = "mysql";               //Binary to dump db (if not in path, add the path here)

   /**
     * This method makes sure to connect to the database properly
     *
     * @param string $strHost
     * @param string $strUsername
     * @param string $strPass
     * @param string $strDbName
     * @param int $intPort
     * @return bool
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

		$strHost = $strHost.":". $intPort;
		if(@mysql_connect($strHost, $strUsername, $strPass)) {
			if(@mysql_select_db($strDbName)) {
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
        @mysql_close();
    }

    /**
     * Sends a query (e.g. an update) to the database
     *
     * @param string $strQuery
     * @return bool
     */
    public function _query($strQuery) {
		$bitReturn = @mysql_query($strQuery);
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
		$resultSet = @mysql_query($strQuery);
		if(!$resultSet)
			return false;

		while($arrRow = @mysql_fetch_array($resultSet)) {
			$arrReturn[$intCounter++] = $arrRow;
		}
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
     * Returns the last error reported by the database.
     * Is being called after unsuccessful queries
     *
     * @return string
     */
    public function getError() {
		$strError = @mysql_error();
		return $strError;
    }

    /**
     * Returns ALL tables in the database currently connected to
     *
     * @return mixed
     */
    public function getTables() {
		$arrTemp = $this->getArray("SHOW TABLE STATUS", false);
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
        $arrTemp = $this->getArray("SHOW COLUMNS FROM ".$strTableName, false);
        foreach ($arrTemp as $arrOneColumn) {
            $arrReturn[] = array(
                        "columnName" => $arrOneColumn["Field"],
                        "columnType" => $arrOneColumn["Type"],
            );
        }
        return $arrReturn;
    }

    /**
     * Used to send a create table statement to the database
     * By passing the query through this method, the driver can
     * add db-specific commands
     *
     * @param string $strQuery
     * @param bool $bitTxSafe
     * @return bool
     */
    public function createTable($strQuery, $bitTxSafe = true) {
        if(!$bitTxSafe)
            $strCreateAddon = " ENGINE = myisam CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
        else
            $strCreateAddon = " ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
            
        $strQuery = $strQuery.$strCreateAddon;
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
        $arrReturn["dbdriver"] = "mysql-extension";
        $arrReturn["dbserver"] = "MySQL ".mysql_get_server_info();
        $arrReturn["dbclient"] = mysql_get_client_info();
        $arrReturn["dbconnection"] = mysql_get_host_info();
        return $arrReturn;
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
            $strParamPass = " -p".$this->strPass;
        }

        $strCommand = $this->strDumpBin." -h".$this->strHost." -u".$this->strUsername.$strParamPass." -P".$this->intPort." ".$this->strDbName." ".$strTables." > \"".$strFilename."\"";
		//Now do a systemfork
		$intTemp = "";
		$strResult = system($strCommand, $intTemp);
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
            $strParamPass = " -p".$this->strPass;
        }

        $strCommand = $this->strRestoreBin." -h".$this->strHost." -u".$this->strUsername.$strParamPass." -P".$this->intPort." ".$this->strDbName." < \"".$strFilename."\"";
        $intTemp = "";
        $strResult = system($strCommand, $intTemp);
	    return $intTemp == 0;
    }
}

?>
