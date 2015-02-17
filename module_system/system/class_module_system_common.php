<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Class to provide methods used by the system for general issues
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @module system
 * @moduleId _system_modul_id_
 */
class class_module_system_common extends class_model implements interface_model {


    /**
     * Deletes an entry from the dates-records
     *
     * @return bool
     */
    public function deleteDateRecord() {
        $strQuery = "DELETE FROM " . _dbprefix_ . "system_date WHERE system_date_id= ?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
    }

    /**
     * Sets the special date of the current systemid
     *
     * @param class_date $objSpecialDate
     *
     * @return bool
     */
    public function setSpecialDate($objSpecialDate) {
        //check, if an insert or an update is needed
        $intSpecialDate = $objSpecialDate->getLongTimestamp();
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system_date WHERE system_date_id = ?", array($this->getSystemid()), 0, false);
        if((int)$arrRow["COUNT(*)"] == 0) {
            $strQuery = "INSERT INTO " . _dbprefix_ . "system_date
            				(system_date_special, system_date_id) VALUES
            				(?, ?)";
        }
        else {
            $strQuery = "UPDATE " . _dbprefix_ . "system_date
                            SET system_date_special = ?
                          WHERE system_date_id = ?";
        }
        return $this->objDB->_pQuery($strQuery, array($intSpecialDate, $this->getSystemid()));
    }

    /**
     * Sets the start date of the current systemid
     *
     * @param class_date $objStartDate
     *
     * @return bool
     */
    public function setStartDate($objStartDate) {
        //check, if an insert or an update is needed
        $intStartDate = $objStartDate->getLongTimestamp();
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system_date WHERE system_date_id = ?", array($this->getSystemid()), 0, false);
        if((int)$arrRow["COUNT(*)"] == 0) {
            $strQuery = "INSERT INTO " . _dbprefix_ . "system_date
            				(system_date_start, system_date_id) VALUES
            				(?, ?)";
        }
        else {
            $strQuery = "UPDATE " . _dbprefix_ . "system_date
                            SET system_date_start = ?
                          WHERE system_date_id = ?";
        }
        return $this->objDB->_pQuery($strQuery, array($intStartDate, $this->getSystemid()));
    }

    /**
     * Sets the end date of the current systemid
     *
     * @param class_date $objEndDate
     *
     * @return bool
     */
    public function setEndDate($objEndDate) {
        //check, if an insert or an update is needed
        $intEndDate = $objEndDate->getLongTimestamp();
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "system_date WHERE system_date_id = ?", array($this->getSystemid()), 0, false);
        if((int)$arrRow["COUNT(*)"] == 0) {
            $strQuery = "INSERT INTO " . _dbprefix_ . "system_date
            				(system_date_end, system_date_id) VALUES
            				(?, ? )";
        }
        else {
            $strQuery = "UPDATE " . _dbprefix_ . "system_date
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
        if($arrRecord["system_date_end"] > 0) {
            return new class_date($arrRecord["system_date_end"]);
        }
        else {
            return null;
        }
    }

    /**
     * Copys the current systemrecord as a new one.
     * Includes the rights-record, if given, and the date-record, if given
     *
     * @param string $strNewSystemid
     * @param string $strNewSystemPrevId
     *
     * @return bool
     *
     * @deprecated
     */
    public function copyCurrentSystemrecord($strNewSystemid, $strNewSystemPrevId = "") {
        class_logger::getInstance()->addLogRow("copy systemrecord " . $this->getSystemid(), class_logger::$levelInfo);
        //copy table by table
        $arrSystemRow = $this->objDB->getPRow("SELECT * FROM " . _dbprefix_ . "system WHERE system_id= ?", array($this->getSystemid()));
        $arrRightsRow = $this->objDB->getPRow("SELECT * FROM " . _dbprefix_ . "system_right WHERE right_id= ?", array($this->getSystemid()));
        $arrDateRow = $this->objDB->getPRow("SELECT * FROM " . _dbprefix_ . "system_date WHERE system_date_id= ?", array($this->getSystemid()));

        if($strNewSystemPrevId == "") {
            $strNewSystemPrevId = $arrSystemRow["system_prev_id"];
        }

        //determin the correct new sort-id - append by default
        $strQuery = "SELECT COUNT(*) FROM " . _dbprefix_ . "system WHERE system_prev_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($strNewSystemPrevId), 0, false);
        $intSiblings = $arrRow["COUNT(*)"];

        $this->objDB->transactionBegin();
        //start by inserting the new systemrecords
        $strQuerySystem = "INSERT INTO " . _dbprefix_ . "system
        (system_id, system_prev_id, system_module_nr, system_sort, system_owner, system_create_date, system_lm_user, system_lm_time, system_lock_id, system_lock_time, system_status, system_comment, system_class) VALUES
        	(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if($this->objDB->_pQuery(
            $strQuerySystem,
            array(
                $strNewSystemid,
                $strNewSystemPrevId,
                $arrSystemRow["system_module_nr"],
                ((int)$intSiblings + 1),
                $arrSystemRow["system_owner"],
                $arrSystemRow["system_create_date"],
                $arrSystemRow["system_lm_user"],
                $arrSystemRow["system_lm_time"],
                $arrSystemRow["system_lock_id"],
                ($arrSystemRow["system_lock_time"] != "" ? $arrSystemRow["system_lock_time"] : 0),
                $arrSystemRow["system_status"],
                $arrSystemRow["system_comment"],
                $arrSystemRow["system_class"]
            )
        )
        ) {

            if(count($arrRightsRow) > 0) {
                $strQueryRights = "INSERT INTO " . _dbprefix_ . "system_right
                (right_id, right_inherit, right_view, right_edit, right_delete, right_right, right_right1, right_right2, right_right3, right_right4, right_right5, right_changelog) VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if(!$this->objDB->_pQuery(
                    $strQueryRights,
                    array(
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
                        $arrRightsRow["right_right5"],
                        $arrRightsRow["right_changelog"]
                    )
                )
                ) {
                    $this->objDB->transactionRollback();
                    return false;
                }
            }

            if(count($arrDateRow) > 0) {
                $strQueryDate = "INSERT INTO " . _dbprefix_ . "system_date
                (system_date_id, system_date_start, system_date_end, system_date_special ) VALUES
                (?, ?, ?, ?)";

                if(!$this->objDB->_pQuery($strQueryDate, array($strNewSystemid, $arrDateRow["system_date_start"], $arrDateRow["system_date_end"], $arrDateRow["system_date_special"]))) {
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
     * Can be filtered via a given module-id or a class-based filter
     *
     * @param int $intMaxNrOfRecords
     * @param bool|int $intModuleFilter
     * @param bool $strClassFilter
     *
     * @return array class_model[]
     * @since 3.3.0
     */
    public static function getLastModifiedRecords($intMaxNrOfRecords, $intModuleFilter = false, $strClassFilter = false) {
        $arrReturn = array();

        $strQuery = "SELECT system_id
                       FROM " . _dbprefix_ . "system
                   " . ($intModuleFilter !== false ? "WHERE system_module_nr = ? " : "") . "
                   " . ($strClassFilter !== false ? "WHERE system_class = ? " : "") . "
                   ORDER BY system_lm_time DESC";

        $arrParams = array();
        if($intModuleFilter !== false) {
            $arrParams[] = (int)$intModuleFilter;
        }
        if($strClassFilter !== false) {
            $arrParams[] = $strClassFilter;
        }

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, 0, $intMaxNrOfRecords - 1);
        foreach($arrIds as $arrSingleRow) {
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }




    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }

}
