<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Class to provide methods used by the system for db tasks as a db check
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_modul_system_worker extends class_model implements interface_model  {



    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "module_system";
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
     * Checks if there are more nodes on the first level
     * then modules installed.
     *
     * @return array
     */
    public function checkFirstLevelNodeConsistency() {
        $strQuery = "SELECT system_id, system_comment
                       FROM "._dbprefix_."system
                       LEFT JOIN "._dbprefix_."system_module
                        ON (system_id = module_id)
                       WHERE module_id IS NULL
                         AND system_prev_id = '0'
                         AND system_id != '0'";

        $arrReturn = $this->objDB->getPArray($strQuery, array());

        return $arrReturn;
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
                      WHERE system_id != '0'";
        $arrRecords = $this->objDB->getPArray($strQuery, array(), false);
        //Check every record for its prev_id. To get valid results, flush the db-cache
        $this->objDB->flushQueryCache();
        foreach ($arrRecords as $arrOneRecord) {
            $strQuery = "SELECT COUNT(*) as number
                           FROM "._dbprefix_."system
                          WHERE system_id = ?";
            $arrRow = $this->objDB->getPRow($strQuery, array($arrOneRecord["system_prev_id"]));
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
        $strQuery = "SELECT right_id, system_comment
                       FROM "._dbprefix_."system_right
                       LEFT JOIN "._dbprefix_."system
                        ON (right_id = system_id)
                       WHERE system_id IS NULL ";
        $arrReturn = $this->objDB->getPArray($strQuery, array());

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
        $arrReturn = $this->objDB->getPArray($strQuery, array());

        return $arrReturn;
    }

}
?>