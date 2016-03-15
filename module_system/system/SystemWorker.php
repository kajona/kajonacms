<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Class to provide methods used by the system for db tasks as a db check
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @module system
 * @moduleId _system_modul_id_
 */
class SystemWorker {


    /**
     * Fetches a list of records currently marked as deleted
     *
     * @return Model[]
     */
    public static function getDeletedRecords($intStart = null, $intEnd = null) {
        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);
        $strQuery = "SELECT system_id FROM "._dbprefix_."system WHERE system_deleted = 1 ORDER BY system_id DESC";
        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            $arrReturn[] = Objectfactory::getInstance()->getObject($arrOneRow["system_id"]);
        }

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        return $arrReturn;
    }

    /**
     * Counts the number of records currently marked as deleted
     *
     * @return int
     */
    public static function getDeletedRecordsCount() {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_deleted = 1 ";
        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        return $arrRow["COUNT(*)"];
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

        $arrReturn = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

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
        $arrRecords = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), null, null, false);
        //Check every record for its prev_id. To get valid results, flush the db-cache
        Carrier::getInstance()->getObjDB()->flushQueryCache();
        foreach ($arrRecords as $arrOneRecord) {
            $strQuery = "SELECT COUNT(*) as number
                           FROM "._dbprefix_."system
                          WHERE system_id = ?";
            $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($arrOneRecord["system_prev_id"]));
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
        $strQuery = "SELECT right_id, system_comment
                       FROM "._dbprefix_."system_right
                       LEFT JOIN "._dbprefix_."system
                        ON (right_id = system_id)
                       WHERE system_id IS NULL ";
        $arrReturn = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

        return $arrReturn;
    }

    /**
     * Checks, if all date-records have a corresponding system-record
     * Returns an array of corrupted records
     *
     * @return array
     */
    public function checkDateSystemRelations() {
        $strQuery = "SELECT system_date_id
                       FROM "._dbprefix_."system_date
                       LEFT JOIN "._dbprefix_."system
                        ON (system_date_id = system_id)
                       WHERE system_id IS NULL ";
        $arrReturn = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

        return $arrReturn;
    }

}
