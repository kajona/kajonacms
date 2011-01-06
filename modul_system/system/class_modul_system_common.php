<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Class to provide methods used by the system for general issues
 *
 * @package modul_system
 */
class class_modul_system_common extends class_model implements interface_model  {



    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModule = array();
        $arrModule["name"] 				= "modul_system";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _system_modul_id_;
		$arrModule["table"]       		= "";
		$arrModule["modul"]				= "system";

		//base class
		parent::__construct($arrModule, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array();
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "";
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {

    }

    /**
     * Updates the current object to the database
     *
     */
    protected function updateStateToDb() {

    }

    /**
     * Deletes an entry from the dates-records
     *
     * @return bool
     */
    public function deleteDateRecord() {
        $strQuery = "DELETE FROM "._dbprefix_."system_date WHERE system_date_id='".dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }

    /**
     * Sets the start date of the current systemid
     *
     * @param class_date $objStartDate
     * @return bool
     */
    public function setStartDate($objStartDate) {
        //check, if an insert or an update is needed
        $strQuery = "";

        $intStartDate = $objStartDate->getLongTimestamp();
        $arrRow = $this->objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system_date WHERE system_date_id = '".dbsafeString($this->getSystemid())."'", 0, false);
        if((int)$arrRow["COUNT(*)"] == 0) {
            $strQuery = "INSERT INTO "._dbprefix_."system_date
            				(system_date_start, system_date_id) VALUES 
            				(".dbsafeString($intStartDate).", '".dbsafeString($this->getSystemid())."')";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."system_date
                            SET system_date_start = ".dbsafeString($intStartDate)."
                          WHERE system_date_id = '".dbsafeString($this->getSystemid())."'";
        }
        return $this->objDB->_query($strQuery);
    }

    /**
     * Sets the end date of the current systemid
     *
     * @param class_date $objEndDate
     * @return bool
     */
    public function setEndDate($objEndDate) {
        //check, if an insert or an update is needed
        $strQuery = "";

        $intEndDate = $objEndDate->getLongTimestamp();
        $arrRow = $this->objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system_date WHERE system_date_id = '".dbsafeString($this->getSystemid())."'", 0, false);
        if((int)$arrRow["COUNT(*)"] == 0) {
            $strQuery = "INSERT INTO "._dbprefix_."system_date
            				(system_date_end, system_date_id) VALUES                 
            				(".dbsafeString($intEndDate).", '".dbsafeString($this->getSystemid())."' )";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."system_date
                            SET system_date_end = ".dbsafeString($intEndDate)."
                          WHERE system_date_id = '".dbsafeString($this->getSystemid())."'";
        }
        return $this->objDB->_query($strQuery);
    }

    /**
     * Sets the special date of the current systemid
     *
     * @param class_date $objSpecialDate
     * @return bool
     */
    public function setSpecialDate($objSpecialDate) {
        //check, if an insert or an update is needed
        $strQuery = "";

        $intSpecialDate = $objSpecialDate->getLongTimestamp();
        $arrRow = $this->objDB->getRow("SELECT COUNT(*) FROM "._dbprefix_."system_date WHERE system_date_id = '".dbsafeString($this->getSystemid())."'", 0, false);
        if((int)$arrRow["COUNT(*)"] == 0) {
            $strQuery = "INSERT INTO "._dbprefix_."system_date
            				(system_date_special, system_date_id) VALUES 
            				(".dbsafeString($intSpecialDate).", '".dbsafeString($this->getSystemid())."')";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."system_date
                            SET system_date_special = ".dbsafeString($intSpecialDate)."
                          WHERE system_date_id = '".dbsafeString($this->getSystemid())."'";
        }
        return $this->objDB->_query($strQuery);
    }
    
    /**
     * Copys the current systemrecord as a new one.
     * Includes the rights-record, if given, and the date-record, if given
     *
     * @param string $strNewSystemid
     * @param string $strNewSystemPrevId
     * @return bool
     */
    public function copyCurrentSystemrecord($strNewSystemid, $strNewSystemPrevId = "") {
        class_logger::getInstance()->addLogRow("copy systemrecord ".$this->getSystemid(), class_logger::$levelInfo);
        //copy table by table
        $arrSystemRow = $this->objDB->getRow("SELECT * FROM "._dbprefix_."system WHERE system_id='".dbsafeString($this->getSystemid())."'");
        $arrRightsRow = $this->objDB->getRow("SELECT * FROM "._dbprefix_."system_right WHERE right_id='".dbsafeString($this->getSystemid())."'");
        $arrDateRow = $this->objDB->getRow("SELECT * FROM "._dbprefix_."system_date WHERE system_date_id='".dbsafeString($this->getSystemid())."'");
        
        if($strNewSystemPrevId == "") {
            $strNewSystemPrevId = $arrSystemRow["system_prev_id"]; 
        }
        
        $this->objDB->transactionBegin();
        //start by inserting the new systemrecords
        $strQuerySystem = "INSERT INTO "._dbprefix_."system
        (system_id, system_prev_id, system_module_nr, system_sort, system_owner, system_lm_user, system_lm_time, system_lock_id, system_lock_time, system_status, system_comment) VALUES
        	('".dbsafeString($strNewSystemid)."', 
        	'".dbsafeString($strNewSystemPrevId)."', 
        	".dbsafeString($arrSystemRow["system_module_nr"]).",
        	".(dbsafeString($arrSystemRow["system_sort"]) != "" ? dbsafeString($arrSystemRow["system_sort"]) : 0 ).",
        	'".dbsafeString($arrSystemRow["system_owner"])."',
        	'".dbsafeString($arrSystemRow["system_lm_user"])."',
        	".dbsafeString($arrSystemRow["system_lm_time"]).",
        	'".dbsafeString($arrSystemRow["system_lock_id"])."',
        	".(dbsafeString($arrSystemRow["system_lock_time"]) != "" ? dbsafeString($arrSystemRow["system_lock_time"]) : 0).",
        	".dbsafeString($arrSystemRow["system_status"]).",
        	'".dbsafeString($arrSystemRow["system_comment"])."')"; 
        
        if($this->objDB->_query($strQuerySystem)) {
            if(count($arrRightsRow) > 0) {
                $strQueryRights = "INSERT INTO "._dbprefix_."system_right 
                (right_id, right_inherit, right_view, right_edit, right_delete, right_right, right_right1, right_right2, right_right3, right_right4, right_right5) VALUES 
                ('".dbsafeString($strNewSystemid)."' ,
                '".dbsafeString($arrRightsRow["right_inherit"])."', 
                '".dbsafeString($arrRightsRow["right_view"])."',
                '".dbsafeString($arrRightsRow["right_edit"])."', 
                '".dbsafeString($arrRightsRow["right_delete"])."',
                '".dbsafeString($arrRightsRow["right_right"])."',
                '".dbsafeString($arrRightsRow["right_right1"])."',
                '".dbsafeString($arrRightsRow["right_right2"])."',
                '".dbsafeString($arrRightsRow["right_right3"])."',
                '".dbsafeString($arrRightsRow["right_right4"])."',
                '".dbsafeString($arrRightsRow["right_right5"])."')";
                
                if(!$this->objDB->_query($strQueryRights)) {
                    $this->objDB->transactionRollback();
                    return false;            
                }
            }
            
            if(count($arrDateRow) > 0) {
                $strQueryDate = "INSERT INTO "._dbprefix_."system_date
                (system_date_id, system_date_start, system_date_end, system_date_special ) VALUES 
                ('".dbsafeString($strNewSystemid)."' ,
                '".dbsafeString($arrDateRow["system_date_start"])."', 
                '".dbsafeString($arrDateRow["system_date_end"])."', 
                '".dbsafeString($arrDateRow["system_date_special"])."')";
                
                if(!$this->objDB->_query($strQueryDate)) {
                    $this->objDB->transactionRollback();
                    return false;            
                }
            }
            
            $this->objDB->transactionCommit();
            return true;
            
            
        }
        
        $this->objDB->transactionRollback();
        return false;
    }

    /**
     * Getter to return the records ordered by the last modified date.
     * Can be filtered via a given module-id
     *
     * @param int $intMaxNrOfRecords
     * @param int $intModuleFilter
     * @return array class_modul_system_common
     * @since 3.3.0
     */
    public static function getLastModifiedRecords($intMaxNrOfRecords, $intModuleFilter = false) {
        $arrReturn = array();

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."system
                   ".($intModuleFilter !== false ? "WHERE system_module_nr = ".(int)$intModuleFilter."" : "")."
                   ORDER BY system_lm_time DESC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getArraySection($strQuery, 0, $intMaxNrOfRecords-1);
        foreach($arrIds as $arrSingleRow) {
            $arrReturn[] = new class_modul_system_common($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
	 * Creates infos about the current php version
	 *
	 * @return mixed
	 *
	 */
    public function getPHPInfo() {
        $arrReturn = array();
        $arrReturn["version"] = phpversion();
		$arrReturn["geladeneerweiterungen"] = implode(", ", get_loaded_extensions());
		$arrReturn["executiontimeout" ] = class_carrier::getInstance()->getObjConfig()->getPhpIni("max_execution_time") ."s";
		$arrReturn["inputtimeout" ] = class_carrier::getInstance()->getObjConfig()->getPhpIni("max_input_time") ."s";
		$arrReturn["memorylimit" ] = bytesToString(ini_get("memory_limit"), true);
		$arrReturn["errorlevel" ] = class_carrier::getInstance()->getObjConfig()->getPhpIni("error_reporting");
        $arrReturn["systeminfo_php_safemode"] = (ini_get("safe_mode") ? $this->getText("systeminfo_yes", "system", "admin")  : $this->getText("systeminfo_no", "system", "admin") );
        $arrReturn["systeminfo_php_urlfopen"] = (ini_get("allow_url_fopen") ? $this->getText("systeminfo_yes", "system", "admin")  : $this->getText("systeminfo_no", "system", "admin") );
        $arrReturn["systeminfo_php_regglobal"] = (ini_get("register_globals") ? $this->getText("systeminfo_yes", "system", "admin")  : $this->getText("systeminfo_no", "system", "admin") );
		$arrReturn["postmaxsize"] = bytesToString(ini_get("post_max_size"), true);
		$arrReturn["uploadmaxsize"] = bytesToString(ini_get("upload_max_filesize"), true);
		$arrReturn["uploads"] = (class_carrier::getInstance()->getObjConfig()->getPhpIni("file_uploads") == 1 ? $this->getText("systeminfo_yes" , "system", "admin") : $this->getText("systeminfo_no", "system", "admin") );

        return $arrReturn;
    }

    	/**
	 * Creates information about the webserver
	 *
	 * @return mixed
	 */
	public function getWebserverInfos() {
		$arrReturn = array();
		$arrReturn["operatingsystem"] = php_uname();
        $arrReturn["systeminfo_webserver_version"] = $_SERVER["SERVER_SOFTWARE"];
        if (function_exists("apache_get_modules")) {
            $arrReturn["systeminfo_webserver_modules"] = implode(", ", @apache_get_modules());
        }
	    if (@disk_total_space(_realpath_)) {
            $arrReturn["speicherplatz"] = bytesToString(@disk_free_space(_realpath_)) ."/". bytesToString(@disk_total_space(_realpath_)) . $this->getText("diskspace_free", "system", "admin");
        }
		return $arrReturn;
	}


    /**
	 * Creates Infos about the GDLib
	 *
	 * @return unknown
	 */
	public function getGDInfos() {
		$arrReturn = array();
		if(function_exists("gd_info")) 	{
			$arrGd = gd_info();
			$arrReturn["version"] = $arrGd["GD Version"];
			$arrReturn["gifread"] = (isset($arrGd["GIF Read Support"]) && $arrGd["GIF Read Support"] ? $this->getText("systeminfo_yes", "system", "admin") : $this->getText("systeminfo_no", "system", "admin"));
			$arrReturn["gifwrite"] = (isset($arrGd["GIF Create Support"]) && $arrGd["GIF Create Support"] ? $this->getText("systeminfo_yes", "system", "admin") : $this->getText("systeminfo_no", "system", "admin"));
			$arrReturn["jpg"] = (( (isset($arrGd["JPG Support"]) && $arrGd["JPG Support"]) || (isset($arrGd["JPEG Support"]) && $arrGd["JPEG Support"]) ) ? $this->getText("systeminfo_yes", "system", "admin") : $this->getText("systeminfo_no", "system", "admin"));
			$arrReturn["png"] = (isset($arrGd["PNG Support"]) && $arrGd["PNG Support"] ? $this->getText("systeminfo_yes", "system", "admin") : $this->getText("systeminfo_no", "system", "admin"));
		}
		else
			$arrReturn[""] = $this->getText("keinegd");
		return $arrReturn;
	}

	/**
	 * Creates Infos about the database
	 *
	 * @return mixed
	 */
	public function getDatabaseInfos() {
		$arrReturn = array();
		//Momentan werden nur mysql / mysqli unterstuetzt
		$arrTables = $this->objDB->getTables(true);
		$intNumber = 0;
		$intSizeData = 0;
		$intSizeIndex = 0;
		//Bestimmen der Datenbankgroesse
		switch($this->objConfig->getConfig("dbdriver")) {
		case "mysql":
			foreach($arrTables as $arrTable) {
				$intNumber++;
				$intSizeData += $arrTable["Data_length"];
				$intSizeIndex += $arrTable["Index_length"];
			}
			$arrInfo = $this->objDB->getDbInfo();
			$arrReturn["datenbanktreiber"] = $arrInfo["dbdriver"];
			$arrReturn["datenbankserver"] = $arrInfo["dbserver"];
			$arrReturn["datenbankclient"] = $arrInfo["dbclient"];
			$arrReturn["datenbankverbindung"] = $arrInfo["dbconnection"];
			$arrReturn["anzahltabellen"] = $intNumber;
			$arrReturn["groessegesamt"] = bytesToString($intSizeData + $intSizeIndex);
			$arrReturn["groessedaten"] = bytesToString($intSizeData);
			#$arrReturn["Groesse Indizes"] = bytes_to_string($int_groesse_index);
			break;

		case "mysqli":
			foreach($arrTables as $arrTable) {
				$intNumber++;
				$intSizeData += $arrTable["Data_length"];
				$intSizeIndex += $arrTable["Index_length"];
			}
			$arrInfo = $this->objDB->getDbInfo();
			$arrReturn["datenbanktreiber"] = $arrInfo["dbdriver"];
			$arrReturn["datenbankserver"] = $arrInfo["dbserver"];
			$arrReturn["datenbankclient"] = $arrInfo["dbclient"];
			$arrReturn["datenbankverbindung"] = $arrInfo["dbconnection"];
			$arrReturn["anzahltabellen"] = $intNumber;
			$arrReturn["groessegesamt"] = bytesToString($intSizeData + $intSizeIndex);
			$arrReturn["groessedaten"] = bytesToString($intSizeData);
			#$arrReturn["Groesse Indizes"] = bytes_to_string($int_groesse_index);
			break;

		case "postgres":
			foreach($arrTables as $arrTable) {
				$intNumber++;
				//$intSizeData += $arrTable["Data_length"];
				//$intSizeIndex += $arrTable["Index_length"];
			}
			$arrInfo = $this->objDB->getDbInfo();
			$arrReturn["datenbanktreiber"] = $arrInfo["dbdriver"];
			$arrReturn["datenbankserver"] = $arrInfo["dbserver"];
			$arrReturn["datenbankclient"] = $arrInfo["dbclient"];
			$arrReturn["datenbankverbindung"] = $arrInfo["dbconnection"];
			$arrReturn["anzahltabellen"] = $intNumber;
			$arrReturn["groessegesamt"] = bytesToString($intSizeData + $intSizeIndex);
			$arrReturn["groessedaten"] = bytesToString($intSizeData);
			#$arrReturn["Groesse Indizes"] = bytes_to_string($int_groesse_index);
			break;

		default:
			foreach($arrTables as $arrTable) {
				$intNumber++;
			}
			$arrInfo = $this->objDB->getDbInfo();
			$arrReturn["datenbanktreiber"] = $arrInfo["dbdriver"];
			$arrReturn["datenbankserver"] = $arrInfo["dbserver"];
			$arrReturn["anzahltabellen"] = $intNumber;
			break;
		}


		return $arrReturn;
	}
}
?>