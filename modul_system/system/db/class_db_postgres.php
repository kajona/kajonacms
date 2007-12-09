<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_db_postgres.php                                                                               *
* 	Driver for postgres-DB using postgres-client to connect                                             *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_db_postgresi.php 1641 2007-08-04 22:04:27Z sidler $	                                    *
********************************************************************************************************/

//Interface
include_once(_systempath_."/db/interface_db_driver.php");

/**
 * db-driver for postgres using the php-postgres-interface
 *
 * @package modul_system
 */
class class_db_postgres implements interface_db_driver {

    private $linkDB;						//DB-Link
    private $strHost = "";
    private $strUsername = "";
    private $strPass = "";
    private $strDbName = "";
    private $intPort = "";
    private $strDumpBin = "pg_dump";              //Binary to dump db (if not in path, add the path here)
    private $strRestoreBin = "postgres";          //Binary to dump db (if not in path, add the path here)

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
			$intPort = "5432";

		//save connection-details
		$this->strHost = $strHost;
		$this->strUsername = $strUsername;
		$this->strPass = $strPass;
		$this->strDbName = $strDbName;
		$this->intPort = $intPort;

		$this->linkDB = pg_connect("host='".$strHost."' port='".$intPort."' dbname='".$strDbName."' user='".$strUsername."' password='".$strPass."'");
				
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
        pg_close($this->linkDB);
    }

    /**
     * Sends a query (e.g. an update) to the database
     *
     * @param string $strQuery
     * @return bool
     */
    public function _query($strQuery) {
		$bitReturn = pg_query($this->linkDB, $strQuery);
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
		$resultSet = @pg_query($this->linkDB, $strQuery);
		if(!$resultSet)
			return false;
		while($arrRow = pg_fetch_array($resultSet)) {
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
        //calculate the end-value: postgres limit: start, nr of records, so:
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
		$strError = pg_last_error($this->linkDB);
		return $strError;
    }

    /**
     * Returns ALL tables in the database currently connected to
     *
     * @return mixed
     */
    public function getTables() {
		$arrTemp = $this->getArray(
				"SELECT table_name as name 
				   FROM information_schema.tables 
				   WHERE table_schema = 'public'"
		, false);
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
        //nothing to do here
    	/*
    	if(!$bitTxSafe)
            $strCreateAddon = "";
        else    
            $strCreateAddon = "";
            
        $strQuery = $strQuery.$strCreateAddon;
		*/
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
    	$arrInfo = pg_version($this->linkDB);
        $arrReturn["dbdriver"] = "postgres-extension";
        $arrReturn["dbserver"] = "postgres ".$arrInfo["server"];
        $arrReturn["dbclient"] = $arrInfo["client"];
        $arrReturn["dbconnection"] = $arrInfo["protocol"];
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