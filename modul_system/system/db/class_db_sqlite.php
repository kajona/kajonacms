<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/

/**
 * db-driver for sqlite using the php-sqlite-interface
 *
 * @author (c) 2009 by Philipp Wolfer <ph.wolfer@googlemail.com>
 * @since 3.3.0
 * @package modul_system
 */
class class_db_sqlite implements interface_db_driver {

    private $linkDB;
    private $strDbFile;

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
    public function dbconnect($strHost, $strUsername, $strPass, $strDbName, $intPort)
    {
        $this->strDbFile = '/system/db/'.$strDbName.'.db';

        try {
            $this->linkDB = new SQLiteDatabase(_realpath_.$this->strDbFile);
            $this->_query('PRAGMA encoding = "UTF-8"');
            $this->_query('PRAGMA short_column_names = ON');
        }
        catch (SQLiteException $e) {
            throw new class_exception("Error connecting to database: ".$e, class_exception::$level_FATALERROR);
        }
    }
    
    /**
     * Closes the connection to the database 
     */
    public function dbclose()
    {
        unset($this->linkDB);
    }

    /**
     * Sends a query (e.g. an update) to the database
     *
     * @param string $strQuery
     * @return bool
     */
    public function _query($strQuery)
    {
        $strQuery = $this->fixQuoting($strQuery);
        if ($this->linkDB->query($strQuery) === false)
            return false;
        return true;
    }

    /**
     * This method is used to retrieve an array of resultsets from the database
     *
     * @param string $strQuery
     * @return array
     */
    public function getArray($strQuery)
    {
        $strQuery = $this->fixQuoting($strQuery);
        $arrReturn = array();
        $resultSet = $this->linkDB->query($strQuery);
		if (!$resultSet)
			return false;
		while ($arrRow = $resultSet->fetch(SQLITE_ASSOC))
			$arrReturn[] = $arrRow;
        return $arrReturn;
    }

    /**
     * Returns just a part of a recodset, defined by the start- and the end-rows,
     * defined by the params
     * <b>Note:</b> Use array-like counters, so the first row is startRow 0 whereas 
     * the n-th row is the (n-1)th key!!!
     *
     * @param string $strQuery
     * @param int $intStart
     * @param int $intEnd
     * @return array
     */
    public function getArraySection($strQuery, $intStart, $intEnd)
    {
        //calculate the end-value: mysql limit: start, nr of records, so:
        $intEnd = $intEnd - $intStart + 1;
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
    public function getError()
    {
        return sqlite_error_string($this->linkDB->lastError());
    }

    /**
     * Returns ALL tables in the database currently connected to.
     * The method should return an array using the following keys:
     * name => Table name
     *
     * @return array
     */
    public function getTables()
    {
        $arrReturn = array();
        $resultSet = $this->linkDB->query("SELECT name FROM sqlite_master WHERE type='table'");
		while ($arrRow = $resultSet->fetch(SQLITE_ASSOC))
        	$arrReturn[] = array("name" => $arrRow["name"]);
        return $arrReturn;
    }
    
    /**
     * Looks up the columns of the given table.
     * Should return an array for each row consting of:
     * array ("columnName", "columnType")
     *
     * @param string $strTableName
     * @return array
     */
    public function getColumnsOfTable($strTableName)
    {
        $arrColumns = array();
        $arrTableInfo = $this->getArray("SELECT sql FROM sqlite_master".
            " WHERE type='table' and name='".$strTableName."'");
        if (!empty($arrTableInfo)) {
            $strTableDef = $arrTableInfo[0]["sql"];

            // Extract the column definitions from the create statement
            $arrMatch = array();
            preg_match("/CREATE TABLE\s+[a-z_]+\s+\((.+)\)/ism", trim($strTableDef), $arrMatch);

            // Get all column names and types
            $strColumnDef = $arrMatch[1];
            $intPrimaryKeyPos = strripos($strColumnDef, "PRIMARY KEY");
            if ($intPrimaryKeyPos !== false)
                $strColumnDef = substr($strColumnDef, 0, $intPrimaryKeyPos);
            preg_match_all("/\s*([a-z_]+)\s+([a-z]+)[^,]+/ism", trim($strColumnDef), $arrMatch, PREG_SET_ORDER);

            foreach ($arrMatch as $arrColumnInfo)
                $arrColumns[] = array(
                    "columnName" => $arrColumnInfo[1],
                    "columnType" => $arrColumnInfo[2]);
        }
        return $arrColumns;
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
     *
     * @param string $strName
     * @param array $arrFields array of fields / columns
     * @param array $arrKeys array of primary keys
     * @param array $arrIndices array of additional indices
     * @param bool $bitTxSafe Should the table support transactions?
     * @return bool
     */
    public function createTable($strName, $arrFields, $arrKeys, $arrIndices = array(), $bitTxSafe = true)
    {
        $arrTables = $this->getTables();
        foreach ($arrTables as $arrTable)
            if ($arrTable["name"] == $strName)
                return true;
        
    	$strQuery = "";

    	//build the mysql code
    	$strQuery .= "CREATE TABLE "._dbprefix_.$strName." ( \n";
    	
    	//loop the fields
    	foreach($arrFields as $strFieldName => $arrColumnSettings) {
    		$strQuery .= " ".$strFieldName." ";
    		
    		$strQuery .= $this->getDatatype($arrColumnSettings[0]);
    		
    		//any default?	
    		if(isset($arrColumnSettings[2]))
    			$strQuery .= " DEFAULT ".$arrColumnSettings[2]." ";

            //nullable?
    		if($arrColumnSettings[1] === true) {
    			$strQuery .= ", \n";
    		}
    		else {
    			$strQuery .= " NOT NULL, \n";
    		}
    				
    	}

    	//primary keys
    	$strQuery .= " PRIMARY KEY (".implode(", ", $arrKeys).") \n";
    	
    	$strQuery .= ") ";
            
        return $this->_query($strQuery);
    }


    /**
     * Starts a transaction
     *
     */
    public function transactionBegin()
    {
        $this->_query("BEGIN TRANSACTION");
    }

    /**
     * Ends a successfull operation by Commiting the transaction
     *
     */
    public function transactionCommit()
    {
        $this->_query("COMMIT TRANSACTION");
    }

    /**
     * Ends a non-successfull transaction by using a rollback
     *
     */
    public function transactionRollback()
    {
        $this->_query("ROLLBACK TRANSACTION");
    }

    /**
     * returns an array with infos about the current database
     * The array returned should have tho following structure:
     * ["dbserver"]
     * ["dbclient"]
     * ["dbconnection"]
     *
     * @return mixed
     */
    public function getDbInfo()
    {
        $arrReturn = array();
        $arrReturn["dbdriver"] = "sqlite-extension";
        $arrReturn["dbserver"] = "SQLite ".$this->strDbFile;
        $arrReturn["dbclient"] = "";
        $arrReturn["dbconnection"] = "";
        return $arrReturn;
    }

    /**
     * Creates an db-dump usind the given filename. the filename is relative to _realpath_
     * The dump must include, and ONLY include the pass tables
     *
     * @param string $strPath
     * @param array $arrTables
     * @return bool Indicates, if the dump worked or not
     *
     */
    public function dbExport($strFilename, $arrTables)
    {
        // FIXME: Only export relevant tables.
        $objFilesystem = new class_filesystem();
        return $objFilesystem->fileCopy($this->strDbFile, $strFilename);
    }

    /**
     * Imports the given db-dump file to the database. The filename ist relativ to _realpath_
     *
     * @param string $strFilename
     * @return bool
     */
    public function dbImport($strFilename)
    {
        $objFilesystem = new class_filesystem();
        return $objFilesystem->fileCopy($strFilename, $this->strDbFile, true);
    }
    
    /**
     * Allows the db-driver to add database-specific surroundings to column-names.
     * E.g. needed by the mysql-drivers
     *
     * @param string $strColum
     * @return string
     */
    public function encloseColumnName($strColumn)
    {
        return $strColumn;
    }
    
    /**
     * Allows the db-driver to add database-specific surroundings to table-names.
     * E.g. needed by the mysql-drivers
     *
     * @param string $strTable
     * @return string
     */
    public function encloseTableName($strTable)
    {
        return $strTable;
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
     * 
     * @param string $strType
     * @return string
     */
    public function getDatatype($strType)
    {
        $strReturn = "";
    	
        if($strType == "int")
            $strReturn .= " INTEGER ";
        elseif($strType == "long")
            $strReturn .= " INTEGER ";
        elseif($strType == "double")
            $strReturn .= " REAL ";    
        elseif($strType == "char10")
            $strReturn .= " TEXT "; 
        elseif($strType == "char20")
            $strReturn .= " TEXT ";
        elseif($strType == "char100")
            $strReturn .= " TEXT ";    
        elseif($strType == "char254")
            $strReturn .= " TEXT ";
        elseif($strType == "char500")
            $strReturn .= " TEXT ";
        elseif($strType == "text")
            $strReturn .= " TEXT ";
        else
            $strReturn .= " TEXT ";
            
        return $strReturn;    
    }

    /**
     * Fixes the quoting of ' in queries.
     * 
     * By default ' is quoted as \', but it must be quoted as '' in sqlite.
     *
     * @param srtin $strSql
     * @return string
     */
    private function fixQuoting($strSql)
    {
        return str_replace("\\'", "''", $strSql);
    }
}

?>
