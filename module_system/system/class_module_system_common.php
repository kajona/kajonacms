<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Class to provide methods used by the system for general issues
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_module_system_common extends class_model implements interface_model  {



    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);

		parent::__construct($strSystemid);

    }


    /**
     * Deletes an entry from the dates-records
     *
     * @return bool
     */
    public function deleteDateRecord() {
        $strQuery = "DELETE FROM "._dbprefix_."system_date WHERE system_date_id= ?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
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
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_date WHERE system_date_id = ?", array($this->getSystemid()), 0, false);
        if((int)$arrRow["COUNT(*)"] == 0) {
            $strQuery = "INSERT INTO "._dbprefix_."system_date
            				(system_date_special, system_date_id) VALUES
            				(?, ?)";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."system_date
                            SET system_date_special = ?
                          WHERE system_date_id = ?";
        }
        return $this->objDB->_pQuery($strQuery, array($intSpecialDate, $this->getSystemid()));
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
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_date WHERE system_date_id = ?", array($this->getSystemid()), 0, false);
        if((int)$arrRow["COUNT(*)"] == 0) {
            $strQuery = "INSERT INTO "._dbprefix_."system_date
            				(system_date_start, system_date_id) VALUES
            				(?, ?)";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."system_date
                            SET system_date_start = ?
                          WHERE system_date_id = ?";
        }
        return $this->objDB->_pQuery($strQuery, array($intStartDate, $this->getSystemid()));
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
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."system_date WHERE system_date_id = ?", array($this->getSystemid()), 0, false);
        if((int)$arrRow["COUNT(*)"] == 0) {
            $strQuery = "INSERT INTO "._dbprefix_."system_date
            				(system_date_end, system_date_id) VALUES
            				(?, ? )";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."system_date
                            SET system_date_end = ?
                          WHERE system_date_id = ?";
        }
        return $this->objDB->_pQuery($strQuery, array($intEndDate, $this->getSystemid()));
    }

    /**
     * Returns the end-date as defined in the date-table
     *
     * @return class_date
     */
    public function getEndDate() {
        $arrRecord = $this->getSystemRecord();
        if($arrRecord["system_date_end"] > 0)
            return new class_date($arrRecord["system_date_end"]);
        else
            return null;
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
        $arrSystemRow = $this->objDB->getPRow("SELECT * FROM "._dbprefix_."system WHERE system_id= ?", array($this->getSystemid()));
        $arrRightsRow = $this->objDB->getPRow("SELECT * FROM "._dbprefix_."system_right WHERE right_id= ?", array($this->getSystemid()));
        $arrDateRow = $this->objDB->getPRow("SELECT * FROM "._dbprefix_."system_date WHERE system_date_id= ?", array($this->getSystemid()));

        if($strNewSystemPrevId == "") {
            $strNewSystemPrevId = $arrSystemRow["system_prev_id"];
        }

        //determin the correct new sort-id - append by default
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_prev_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($strNewSystemPrevId), 0, false);
        $intSiblings = $arrRow["COUNT(*)"];

        $this->objDB->transactionBegin();
        //start by inserting the new systemrecords
        $strQuerySystem = "INSERT INTO "._dbprefix_."system
        (system_id, system_prev_id, system_module_nr, system_sort, system_owner, system_create_date, system_lm_user, system_lm_time, system_lock_id, system_lock_time, system_status, system_comment, system_class) VALUES
        	(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if($this->objDB->_pQuery($strQuerySystem, array(
                $strNewSystemid,
                $strNewSystemPrevId,
                $arrSystemRow["system_module_nr"],
               ((int)$intSiblings+1),
                $arrSystemRow["system_owner"],
                $arrSystemRow["system_create_date"],
                $arrSystemRow["system_lm_user"],
                $arrSystemRow["system_lm_time"],
                $arrSystemRow["system_lock_id"],
               ($arrSystemRow["system_lock_time"] != "" ? $arrSystemRow["system_lock_time"] : 0),
                $arrSystemRow["system_status"],
                $arrSystemRow["system_comment"],
                $arrSystemRow["system_class"]
            ))) {

            if(count($arrRightsRow) > 0) {
                $strQueryRights = "INSERT INTO "._dbprefix_."system_right
                (right_id, right_inherit, right_view, right_edit, right_delete, right_right, right_right1, right_right2, right_right3, right_right4, right_right5) VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if(!$this->objDB->_pQuery($strQueryRights, array(
                    $strNewSystemid,
                    $arrRightsRow["right_inherit"],
                    $arrRightsRow["right_view"],
                    $arrRightsRow["right_edit"],
                    $arrRightsRow["right_delete"],
                    $arrRightsRow["right_right"],
                    $arrRightsRow["right_right1"],
                    $arrRightsRow["right_right2"],
                    $arrRightsRow["right_right3"],
                    $arrRightsRow["right_right4"],
                    $arrRightsRow["right_right5"]
                ))) {
                    $this->objDB->transactionRollback();
                    return false;
                }
            }

            if(count($arrDateRow) > 0) {
                $strQueryDate = "INSERT INTO "._dbprefix_."system_date
                (system_date_id, system_date_start, system_date_end, system_date_special ) VALUES
                (?, ?, ?, ?)";

                if(!$this->objDB->_pQuery($strQueryDate, array($strNewSystemid, $arrDateRow["system_date_start"], $arrDateRow["system_date_end"], $arrDateRow["system_date_special"]) )) {
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
     * @param bool|int $intModuleFilter
     * @return array class_module_system_common
     * @since 3.3.0
     */
    public static function getLastModifiedRecords($intMaxNrOfRecords, $intModuleFilter = false) {
        $arrReturn = array();

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."system
                   ".($intModuleFilter !== false ? "WHERE system_module_nr = ? " : "")."
                   ORDER BY system_lm_time DESC";

        $arrParams = array();
        if($intModuleFilter !== false)
            $arrParams[] = (int)$intModuleFilter;

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, 0, $intMaxNrOfRecords-1);
        foreach($arrIds as $arrSingleRow) {
            $arrReturn[] = new class_module_system_common($arrSingleRow["system_id"]);
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
        $arrReturn["systeminfo_php_safemode"] = (ini_get("safe_mode") ? $this->getLang("commons_yes", "system")  : $this->getLang("commons_no", "system") );
        $arrReturn["systeminfo_php_urlfopen"] = (ini_get("allow_url_fopen") ? $this->getLang("commons_yes", "system")  : $this->getLang("commons_no", "system") );
        $arrReturn["systeminfo_php_regglobal"] = (ini_get("register_globals") ? $this->getLang("commons_yes", "system")  : $this->getLang("commons_no", "system") );
		$arrReturn["postmaxsize"] = bytesToString(ini_get("post_max_size"), true);
		$arrReturn["uploadmaxsize"] = bytesToString(ini_get("upload_max_filesize"), true);
		$arrReturn["uploads"] = (class_carrier::getInstance()->getObjConfig()->getPhpIni("file_uploads") == 1 ? $this->getLang("commons_yes" , "system") : $this->getLang("commons_no", "system") );

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
            $arrReturn["speicherplatz"] = bytesToString(@disk_free_space(_realpath_)) ."/". bytesToString(@disk_total_space(_realpath_)) . $this->getLang("diskspace_free", "system");
        }
		return $arrReturn;
	}


    /**
	 * Creates Infos about the GDLib
	 *
	 * @return string
	 */
	public function getGDInfos() {
		$arrReturn = array();
		if(function_exists("gd_info")) 	{
			$arrGd = gd_info();
			$arrReturn["version"] = $arrGd["GD Version"];
			$arrReturn["gifread"] = (isset($arrGd["GIF Read Support"]) && $arrGd["GIF Read Support"] ? $this->getLang("commons_yes", "system") : $this->getLang("commons_no", "system"));
			$arrReturn["gifwrite"] = (isset($arrGd["GIF Create Support"]) && $arrGd["GIF Create Support"] ? $this->getLang("commons_yes", "system") : $this->getLang("commons_no", "system"));
			$arrReturn["jpg"] = (( (isset($arrGd["JPG Support"]) && $arrGd["JPG Support"]) || (isset($arrGd["JPEG Support"]) && $arrGd["JPEG Support"]) ) ? $this->getLang("commons_yes", "system") : $this->getLang("commons_no", "system"));
			$arrReturn["png"] = (isset($arrGd["PNG Support"]) && $arrGd["PNG Support"] ? $this->getLang("commons_yes", "system") : $this->getLang("commons_no", "system"));
		}
		else
			$arrReturn[""] = $this->getLang("keinegd");
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

    /**
     * Deletes the current object from the system
     * @return bool
     */
    public function deleteObject() {
        return true;
    }



    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }

    /**
     * Returns a list of tables the current object is persisted to.
     * A new record is created in each table, as soon as a save-/update-request was triggered by the framework.
     * The array should contain the name of the table as the key and the name
     * of the primary-key (so the column name) as the matching value.
     * E.g.: array(_dbprefix_."pages" => "page_id)
     *
     * @return array [table => primary row name]
     */
    protected function getObjectTables() {
        return array();
    }

}
