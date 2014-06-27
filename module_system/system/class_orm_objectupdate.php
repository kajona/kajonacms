<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * The objectupdate class is used to save an objects' state back to the database
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_orm_objectupdate extends class_orm_base {


    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize the current object with the database.
     * Use only updates, inserts are not required to be implemented.
     * Provides a default implementation based on the current objects column mappings.
     * Override this method whenever you want to perform additional actions or escaping.
     *
     * @throws class_exception
     * @return bool
     */
    public function updateStateToDb() {

        if(!validateSystemid($this->getObjObject()->getSystemid())) {
            return true;
        }

        if($this->getObjObject() instanceof interface_versionable) {
            $objChanges = new class_module_system_changelog();
            $objChanges->createLogEntry($this->getObjObject(), class_module_system_changelog::$STR_ACTION_EDIT);
        }

        //fetch properties with annotations
        $objReflection = new class_reflection($this->getObjObject());
        $arrTargetTables = $objReflection->getAnnotationValuesFromClass(class_orm_base::STR_ANNOTATION_TARGETTABLE);
        if(count($arrTargetTables) == 0) {
            //no table mapping found - skip
            return true;
        }

        $bitReturn = true;

        foreach($arrTargetTables as $strOneTable) {
            $arrTableDef = explode(".", $strOneTable);

            //scan all properties
            $arrColValues = array();
            $arrEscapes = array();

            //get the mapped properties
            $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_TABLECOLUMN);

            foreach($arrProperties as $strPropertyName => $strColumn) {

                //check if there are table annotation available
                $arrColumnDef = explode(".", $strColumn);

                //if the column doesn't declare a target table whereas the class defines more then one - skip it.
                if(count($arrColumnDef) == 1 && count($arrTargetTables) > 1 )
                    throw new class_orm_exception("property ".$strPropertyName." declares no target table, class ".get_class($this->getObjObject())." declares more than one target table.", class_exception::$level_FATALERROR);


                //skip if property targets another table
                if(count($arrColumnDef) == 2 && $arrColumnDef[0] != $arrTableDef[0])
                    continue;

                if(count($arrColumnDef) == 2)
                    $strColumn = $arrColumnDef[1];

                //all prerequisites match, start creating query
                $strGetter = $objReflection->getGetter($strPropertyName);
                if($strGetter !== null) {
                    //explicit casts required? could be relevant, depending on the target column type / database system
                    $mixedValue = call_user_func(array($this->getObjObject(), $strGetter));
                    if($mixedValue !== null && (uniStrtolower(uniSubstr($strGetter, 0, 6)) == "getint" || uniStrtolower(uniSubstr($strGetter, 0, 6)) == "getbit")) {
                        //different casts on 32bit / 64bit
                        if($mixedValue > PHP_INT_MAX)
                            $mixedValue = (float)$mixedValue;
                        else
                            $mixedValue = (int)$mixedValue;
                    }
                    $arrColValues[$strColumn] = $mixedValue;
                    $arrEscapes[] = !$objReflection->hasPropertyAnnotation($strPropertyName, class_orm_base::STR_ANNOTATION_BLOCKESCAPING);
                }
            }

            //update table
            if(count($arrColValues) > 0)
                $bitReturn = $bitReturn && $this->updateSingleTable($arrColValues, $arrEscapes, $arrTableDef[0], $arrTableDef[1]);


        }

        return $bitReturn;


    }

    /**
     * Called internally to update a single target-table
     *
     * @param array $arrColValues
     * @param bool[] $arrEscapes
     * @param string $strTargetTable
     * @param string $strPrimaryCol
     *
     * @return bool
     */
    private function updateSingleTable($arrColValues, $arrEscapes, $strTargetTable, $strPrimaryCol) {

        $objDB = class_carrier::getInstance()->getObjDB();

        $arrValues = array();

        $strQuery = "UPDATE ".$objDB->encloseTableName(_dbprefix_.$strTargetTable)." SET ";

        $intI = 0;
        foreach($arrColValues as $strColumn => $objValue) {
            $strQuery .= $objDB->encloseColumnName($strColumn)." = ? ";
            $arrValues[] = $objValue;

            if(++$intI < count($arrColValues))
                $strQuery .= ", ";
        }

        $strQuery .= " WHERE ".$objDB->encloseColumnName($strPrimaryCol)." = ? ";
        $arrValues[] = $this->getObjObject()->getSystemid();

        return $objDB->_pQuery($strQuery, $arrValues, $arrEscapes);

    }



}
