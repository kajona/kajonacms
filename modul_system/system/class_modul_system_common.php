<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_system_common.php                                                                       *
* 	Class providing a few system methods                                                                *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");

/**
 * Class to provide methods used by the system for general issues
 *
 * @package modul_system
 */
class class_modul_system_common extends class_model implements interface_model  {



    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_system";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["table"]       		= "";
		$arrModul["modul"]				= "system";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {

    }

    /**
     * Saves the current object as a new object to db.
     *
     *
     */
    public function saveObjectToDb() {

    }


    /**
     * Updates the current object to the database
     *
     */
    public function updateObjectToDb() {

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
     * @param int $intStartDate
     * @return bool
     */
    public function setStartDate($intStartDate) {
        //check, if an insert or an update is needed
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
     * @param int $intEndDate
     * @return bool
     */
    public function setEndDate($intEndDate) {
        //check, if an insert or an update is needed
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
     * @param int $intSpecialDate
     * @return bool
     */
    public function setSpecialDate($intSpecialDate) {
        //check, if an insert or an update is needed
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
        (system_id, system_prev_id, system_module_nr, system_sort, system_lm_user, system_lm_time, system_lock_id, system_lock_time, system_status, system_comment) VALUES 
        	('".dbsafeString($strNewSystemid)."', 
        	'".dbsafeString($strNewSystemPrevId)."', 
        	".dbsafeString($arrSystemRow["system_module_nr"]).",
        	".(dbsafeString($arrSystemRow["system_sort"]) != "" ? dbsafeString($arrSystemRow["system_sort"]) : 0 ).",
        	'".dbsafeString($arrSystemRow["system_lm_user"])."',
        	".dbsafeString($arrSystemRow["system_lm_time"]).",
        	'".dbsafeString($arrSystemRow["system_lock_id"])."',
        	".(dbsafeString($arrSystemRow["system_lock_time"]) != "" ? dbsafeString($arrSystemRow["system_lock_time"]) : 0).",
        	".dbsafeString($arrSystemRow["system_status"]).",
        	'".dbsafeString($arrSystemRow["system_comment"])."')"; 
        
        if($this->objDB->_query($strQuerySystem)) {
            if(count($arrRightsRow) > 0) {
                $strQueryRights = "INSERT INTO "._dbprefix_."system_right 
                (right_id, right_comment, right_inherit, right_view, right_edit, right_delete, right_right, right_right1, right_right2, right_right3, right_right4, right_right5) VALUES 
                ('".dbsafeString($strNewSystemid)."' ,
                '".dbsafeString($arrRightsRow["right_comment"])."', 
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

}
?>