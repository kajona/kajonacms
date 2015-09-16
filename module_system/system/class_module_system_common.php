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
 * @deprecated will be removed in v5
 */
class class_module_system_common extends class_model implements interface_model {


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
     * @deprecated will be removed in v5
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
