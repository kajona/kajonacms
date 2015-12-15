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
                    $mixedValue = $this->getObjObject()->{$strGetter}();
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


        if($this->getObjObject() instanceof interface_versionable) {
            $objChanges = new class_module_system_changelog();
            $objChanges->createLogEntry($this->getObjObject(), class_module_system_changelog::$STR_ACTION_EDIT);
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

            $objCfg = class_orm_assignment_config::getConfigForProperty($this->getObjObject(), $strPropertyName);

            //try to load the orm config of the arrayObject - if given
            $strGetter = $objReflection->getGetter($strPropertyName);
            $arrValues = null;
            if($strGetter !== null) {
                $arrValues = $this->getObjObject()->{$strGetter}();
            }
            $objAssignmentDeleteHandling = $this->getIntCombinedLogicalDeletionConfig();
            if($arrValues != null && $arrValues instanceof class_orm_assignment_array) {
                $objAssignmentDeleteHandling = $arrValues->getObjDeletedHandling();
            }


            //try to restore the object-set from the database using the same config as when initializing the object
            $objOldHandling = $this->getIntCombinedLogicalDeletionConfig();
            $this->setObjHandleLogicalDeleted($objAssignmentDeleteHandling);
            $arrAssignmentsFromObject = $this->getAssignmentValuesFromObject($strPropertyName, $objCfg->getArrTypeFilter());
            $arrAssignmentsFromDatabase = $this->getAssignmentsFromDatabase($strPropertyName);
            $this->setObjHandleLogicalDeleted($objOldHandling);

            //if the delete handling was set to excluded when loading the assignment, the logically deleted nodes should be merged with the values from db
            if($objAssignmentDeleteHandling->equals(class_orm_deletedhandling_enum::EXCLUDED())) {
                $this->setObjHandleLogicalDeleted(class_orm_deletedhandling_enum::EXCLUSIVE());
                $arrDeletedIds = $this->getAssignmentsFromDatabase($strPropertyName);
                $this->setObjHandleLogicalDeleted($objOldHandling);

                foreach($arrDeletedIds as $strOneId) {
                    if(!in_array($strOneId, $arrAssignmentsFromDatabase)) {
                        $arrAssignmentsFromDatabase[] = $strOneId;
                    }

                    if(!in_array($strOneId, $arrAssignmentsFromObject)) {
                        $arrAssignmentsFromObject[] = $strOneId;
                    }
                }


            }


            sort($arrAssignmentsFromObject);
            sort($arrAssignmentsFromDatabase);

            //only do s.th. if the array differs
            $arrNewAssignments = array_diff($arrAssignmentsFromObject, $arrAssignmentsFromDatabase);
            $arrDeletedAssignments = array_diff($arrAssignmentsFromDatabase, $arrAssignmentsFromObject);

            //skip in case there's nothing to do
            if(count($arrNewAssignments) == 0 && count($arrDeletedAssignments) == 0)
                continue;

            $objDB = class_carrier::getInstance()->getObjDB();

            $arrInserts = array();
            foreach($arrAssignmentsFromObject as $strOneTargetId)
                $arrInserts[] = array($this->getObjObject()->getSystemid(), $strOneTargetId);

                $bitReturn = $bitReturn && $objDB->_pQuery(
                "DELETE FROM ".$objDB->encloseTableName(_dbprefix_.$objCfg->getStrTableName())." WHERE ".$objDB->encloseColumnName($objCfg->getStrSourceColumn())." = ?", array($this->getObjObject()->getSystemid())
                    );
            $bitReturn = $bitReturn && $objDB->multiInsert($objCfg->getStrTableName(), array($objCfg->getStrSourceColumn(), $objCfg->getStrTargetColumn()), $arrInserts);

            $bitReturn = $bitReturn && class_core_eventdispatcher::getInstance()->notifyGenericListeners(
                class_system_eventidentifier::EVENT_SYSTEM_OBJECTASSIGNMENTSUPDATED,
                array(array_values($arrNewAssignments), array_values($arrDeletedAssignments), array_values($arrAssignmentsFromObject), $this->getObjObject(), $strPropertyName)
            );

            if($objReflection->hasPropertyAnnotation($strPropertyName, class_module_system_changelog::ANNOTATION_PROPERTY_VERSIONABLE)) {
                $objChanges = new class_module_system_changelog();
                $objChanges->setOldValueForSystemidAndProperty($this->getObjObject()->getSystemid(), $strPropertyName, implode(",", $arrAssignmentsFromDatabase));
            }
        }

        return $bitReturn;
    }


    /**
     * Internal helper to fetch the values of an assignment property.
     * Capable of handling both, objects and systemids.
     *
     * @param string $strPropertyName
     * @param string[]|null $arrClassFilter
     * @return array
     */
    private function getAssignmentValuesFromObject($strPropertyName, $arrClassFilter) {
        $objReflection = new class_reflection($this->getObjObject());

        $strGetter = $objReflection->getGetter($strPropertyName);
        $arrValues = array();
        if($strGetter !== null) {
            $arrValues = $this->getObjObject()->{$strGetter}();

            if(!is_array($arrValues) && !($arrValues instanceof ArrayObject))
                $arrValues = array();
        }

        $arrReturn = array();
        foreach($arrValues as $objOneValue) {

            if(is_object($objOneValue) && $objOneValue instanceof class_model) {
                if($arrClassFilter == null || count(array_filter($arrClassFilter, function($strSingleClass) use ($objOneValue) { return $objOneValue instanceof $strSingleClass; })) > 0) {
                    $arrReturn[] = $objOneValue->getSystemid();
                }
            }
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
