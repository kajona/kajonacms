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
class class_module_system_worker extends class_model implements interface_model  {



    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);

		parent::__construct($strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    public function getObjectTables() {
        return array();
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    public function getObjectDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
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
     * @return bool
     */
    public function updateStateToDb() {
        return true;
    }

    /**
     * Deletes the current object from the system
     * @return bool
     */
    public function deleteObject() {
        return true;
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
    public function checkDateSystemRelations() {
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
