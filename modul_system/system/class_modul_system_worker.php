<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_system_worker.php                                                                       *
* 	Class providing a few system worker methods, e.g. db checks                                         *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");

/**
 * Class to provide methods used by the system for db tasks as a db check
 *
 * @package modul_system
 */
class class_modul_system_worker extends class_model implements interface_model  {



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
     * Checks, if all system records use a valid system_prev_id
     * Returns an array of records using a invalid pred_id
     *
     * @return array
     */
    public function checkSystemTableCurPrevRelations() {
        $arrReturn = array();

        //fetch all records
        $strQuery = "SELECT system_id, system_prev_id, system_comment
                       FROM "._dbprefix_."system
                      WHERE system_id != 0";
        $arrRecords = $this->objDB->getArray($strQuery, false);
        //Check every record for its prev_id. To get valid results, flush the db-cache
        $this->objDB->flushQueryCache();
        foreach ($arrRecords as $arrOneRecord) {
            $strQuery = "SELECT COUNT(*) as number
                           FROM "._dbprefix_."system
                          WHERE system_id = '".dbsafeString($arrOneRecord["system_prev_id"])."'";
            $arrRow = $this->objDB->getRow($strQuery);
            if($arrRow["number"] == "0") {
                $arrReturn[$arrOneRecord["system_id"]] = $arrOneRecord["system_comment"];
            }
        }
        return $arrReturn;
    }

    /**
     * Checks, if all right-records have a corresponding system-record
     * Returns an array of corrupted records
     *
     * @return array
     */
    public function chekRightSystemRelations() {
        $arrReturn = array();
        $strQuery = "SELECT right_id, right_comment
                       FROM "._dbprefix_."system_right
                       LEFT JOIN "._dbprefix_."system
                        ON (right_id = system_id)
                       WHERE system_id IS NULL ";
        $arrReturn = $this->objDB->getArray($strQuery);

        return $arrReturn;
    }

    /**
     * Checks, if all date-records have a corresponding system-record
     * Returns an array of corrupted records
     *
     * @return array
     */
    public function chekDateSystemRelations() {
        $arrReturn = array();
        $strQuery = "SELECT system_date_id
                       FROM "._dbprefix_."system_date
                       LEFT JOIN "._dbprefix_."system
                        ON (system_date_id = system_id)
                       WHERE system_id IS NULL ";
        $arrReturn = $this->objDB->getArray($strQuery);

        return $arrReturn;
    }

}
?>