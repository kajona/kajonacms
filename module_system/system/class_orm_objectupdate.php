<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * The objectupdate class is used to save an objects' state back to the database.
 * Therefore the passed object is analyzed, all properties with a matching target-column
 * are synced back to the database.
 * Therefor it is essential to have getters and setters for those properties (java bean standard).
 * If the current object is unknown to the database (no systemid), a new record is created.
 * The new records' systemid is assigned to the object afterwards.
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

        if(!validateSystemid($this->getObjObject()->getSystemid()) || !$this->hasTargetTable()) {
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

        //see, if we should process object lists, too
        if($bitReturn) {
            $bitReturn = $this->updateAssignments();
        }

        return $bitReturn;


    }

    /**
     * Triggers the update sequence for assignment properties
     * @return bool
     */
    private function updateAssignments() {
        $bitReturn = true;

        $objReflection = new class_reflection($this->getObjObject());

        //get the mapped properties
        $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_OBJECTLIST, class_reflection_enum::PARAMS());

        foreach($arrProperties as $strPropertyName => $arrValues) {

            $strTableName = $objReflection->getAnnotationValueForProperty($strPropertyName, class_orm_base::STR_ANNOTATION_OBJECTLIST);

            if(!isset($arrValues["source"]) || !isset($arrValues["target"]) || empty($strTableName)) {
                return false;
            }

            $arrAssignmentsFromObject = $this->getAssignmentValuesFromObject($strPropertyName);
            $arrAssignmentsFromDatabase = $this->getAssignmentsFromDatabase($strPropertyName);

            //only do s.th. if the array differs
            $arrNewAssignments = array_diff($arrAssignmentsFromObject, $arrAssignmentsFromDatabase);
            $arrDeletedAssignments = array_diff($arrAssignmentsFromDatabase, $arrAssignmentsFromObject);

            $objDB = class_carrier::getInstance()->getObjDB();

            $arrInserts = array();
            foreach($arrAssignmentsFromObject as $strOneTargetId)
                $arrInserts[] = array($this->getObjObject()->getSystemid(), $strOneTargetId);

            $bitReturn = $bitReturn && $objDB->_pQuery("DELETE FROM ".$objDB->encloseTableName(_dbprefix_.$strTableName)." WHERE ".$objDB->encloseColumnName($arrValues["source"])." = ? ", array($this->getObjObject()->getSystemid()));
            $bitReturn = $bitReturn && $objDB->multiInsert($strTableName, array($arrValues["source"], $arrValues["target"]), $arrInserts);
        }

        return $bitReturn;
    }

    /**
     * Reads the assignment values currently stored in the database for a given property of the current object.
     * @param $strPropertyName
     *
     * @return string[] array of systemids
     * @todo move to common base class
     */
    private function getAssignmentsFromDatabase($strPropertyName) {
        $objReflection = new class_reflection($this->getObjObject());

        $strTableName = $objReflection->getAnnotationValueForProperty($strPropertyName, class_orm_base::STR_ANNOTATION_OBJECTLIST);
        $arrMappingColumns = $objReflection->getAnnotationValueForProperty($strPropertyName, class_orm_base::STR_ANNOTATION_OBJECTLIST, class_reflection_enum::PARAMS());

        $objDB = class_carrier::getInstance()->getObjDB();
        $strQuery = "SELECT * FROM ".$objDB->encloseTableName(_dbprefix_.$strTableName)." WHERE ".$objDB->encloseColumnName($arrMappingColumns["source"])." = ? ";
        $arrRows = $objDB->getPArray($strQuery, array($this->getObjObject()->getSystemid()), null, null);

        $strTargetCol = $arrMappingColumns["target"];
        array_walk($arrRows, function(array &$arrSingleRow) use ($strTargetCol) {
            $arrSingleRow = $arrSingleRow[$strTargetCol];
        });

        return $arrRows;
    }

    /**
     * Internal helper to fetch the values of an assignment property.
     * Capable of handling both, objects and systemids.
     *
     * @param $strPropertyName
     *
     * @return array
     */
    private function getAssignmentValuesFromObject($strPropertyName) {
        $objReflection = new class_reflection($this->getObjObject());

        $strGetter = $objReflection->getGetter($strPropertyName);
        $arrValues = array();
        if($strGetter !== null) {
            $arrValues = call_user_func(array($this->getObjObject(), $strGetter));

            if(!is_array($arrValues))
                $arrValues = array();
        }

        $arrReturn = array();
        foreach($arrValues as $objOneValue) {

            if(is_object($objOneValue) && $objOneValue instanceof class_model)
                $arrReturn[] = $objOneValue->getSystemid();
            else if(is_string($objOneValue) && validateSystemid($objOneValue))
                $arrReturn[] = $objOneValue;
        }

        return $arrReturn;
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
