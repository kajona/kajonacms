<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
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
                            SET system_date_start = ".dbsafeString($intStartDate).",
                                system_date_id = '".dbsafeString($this->getSystemid())."'";
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
                            SET system_date_end = ".dbsafeString($intEndDate).",
                                system_date_id = '".dbsafeString($this->getSystemid())."'";
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
                            SET system_date_special = ".dbsafeString($intSpecialDate).",
                                system_date_id = '".dbsafeString($this->getSystemid())."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."system_date
                            SET system_date_special = ".dbsafeString($intSpecialDate)."
                          WHERE system_date_id = '".dbsafeString($this->getSystemid())."'";
        }
        return $this->objDB->_query($strQuery);
    }

}
?>