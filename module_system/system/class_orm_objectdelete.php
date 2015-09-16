<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * The objectdelete class takes care of removing an object from the database.
 * Therefore all foreign tables are analyzed and the matching rows are being deleted.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.8
 */
class class_orm_objectdelete extends class_orm_base {


    /**
     * Deletes the current object from the system.
     * By default, all entries are delete from  all tables indicated by the class-doccomment.
     * If you want to trigger additional deletes, overwrite this method.
     * The system-record itself is being deleted automatically, too.
     *
     * @throws class_exception
     * @return bool
     */
    public function deleteObject() {

        if(!validateSystemid($this->getObjObject()->getSystemid()) || !$this->hasTargetTable()) {
            return true;
        }

        $objDB = class_carrier::getInstance()->getObjDB();
        $bitReturn = $this->deleteAssignments();

        $objAnnotations = new class_reflection($this->getObjObject());
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass("@targetTable");

        if(count($arrTargetTables) > 0) {
            foreach($arrTargetTables as  $strOneTable) {
                $arrSingleTable = explode(".", $strOneTable);
                $strQuery = "DELETE FROM ".$objDB->encloseTableName(_dbprefix_.$arrSingleTable[0])."
                                   WHERE ".$objDB->encloseColumnName($arrSingleTable[1])." = ? ";

                $bitReturn = $bitReturn && $objDB->_pQuery($strQuery, array($this->getObjObject()->getSystemid()));
            }
        }
        return $bitReturn;


    }

    /**
     * Clears the assignments of the current object, if given
     *
     * @return bool
     */
    private function deleteAssignments() {
        $bitReturn = true;

        $objReflection = new class_reflection($this->getObjObject());
        $objDB = class_carrier::getInstance()->getObjDB();

        //get the mapped properties
        $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_OBJECTLIST, class_reflection_enum::PARAMS());

        foreach($arrProperties as $strPropertyName => $arrValues) {

            $objCfg = class_orm_assignment_config::getConfigForProperty($this->getObjObject(), $strPropertyName);

            $bitReturn = $bitReturn && $objDB->_pQuery(
                "DELETE FROM ".$objDB->encloseTableName(_dbprefix_.$objCfg->getStrTableName())." WHERE ".$objDB->encloseColumnName($objCfg->getStrSourceColumn())." = ? ", array($this->getObjObject()->getSystemid())
            );
        }



        return $bitReturn;
    }




}
