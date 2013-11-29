<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * The orm-mapper takes care of synchronizing an object to the database.
 * Therefore it tries to initialize the object with the data stored in the database
 * and passes the values back to the database.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.3
 */
class class_orm_mapper {

    const STR_ANNOTATION_TARGETTABLE = "@targetTable";
    const STR_ANNOTATION_TABLECOLUMN = "@tableColumn";
    const STR_ANNOTATION_BLOCKESCAPING = "@blockEscaping";
    const STR_ANNOTATION_LISTORDER = "@listOrder";

    private $objObject = null;

    function __construct($objObject = null) {
        $this->objObject = $objObject;
    }

    /**
     * A generic approach to count the number of object currently available.
     * This method is only a simple approach to determine the number of instances in the
     * database, if you need more specific counts, overwrite this method or add your own
     * implementation to the derived class.
     *
     * @param $strTargetClass
     * @param string $strPrevid
     *
     * @return int
     */
    public function getObjectCount($strTargetClass, $strPrevid = "") {
        $objAnnotations = new class_reflection($strTargetClass);
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass(class_orm_mapper::STR_ANNOTATION_TARGETTABLE);

        if(count($arrTargetTables) == 1) {
            $arrSingleTable = explode(".", $arrTargetTables[0]);
            //build the query
            $arrParams = array();
            $strQuery = "SELECT COUNT(*)
                           FROM ".class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_.$arrSingleTable[0]).",
                                "._dbprefix_."system
                          WHERE system_id = ".class_carrier::getInstance()->getObjDB()->encloseColumnName($arrSingleTable[1])."
                           ".($strPrevid != "" ? " AND system_prev_id = ? " : "")."";

            if($strPrevid != "")
                $arrParams[] = $strPrevid;

            $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
            return $arrRow["COUNT(*)"];
        }

        return 0;
    }


    /**
     * A generic approach to load a list of objects currently available.
     * This method is only a simple approach to determine the instances in the
     * database, if you need more specific loaders, overwrite this method or add your own
     * implementation to the derived class.
     *
     * @param $strTargetClass
     * @param string $strPrevid
     * @param null|int $intStart
     * @param null|int $intEnd
     *
     * @return self[]
     */
    public function getObjectList($strTargetClass, $strPrevid = "", $intStart = null, $intEnd = null) {
        $objAnnotations = new class_reflection($strTargetClass);
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass(class_orm_mapper::STR_ANNOTATION_TARGETTABLE);

        $arrReturn = array();

        if(count($arrTargetTables) == 1) {
            //try to load the sort criteria
            $arrPropertiesOrder = $objAnnotations->getPropertiesWithAnnotation(class_orm_mapper::STR_ANNOTATION_LISTORDER);

            $strOrderBy = " ORDER BY system_sort ASC ";
            if(count($arrPropertiesOrder) > 0) {
                $arrPropertiesORM = $objAnnotations->getPropertiesWithAnnotation(class_orm_mapper::STR_ANNOTATION_TABLECOLUMN);

                foreach($arrPropertiesOrder as $strProperty => $strAnnotation) {
                    if(isset($arrPropertiesORM[$strProperty])) {

                        $arrColumn = explode(".", $arrPropertiesORM[$strProperty]);
                        if(count($arrColumn) == 2)
                            $strColumn = $arrColumn[1];
                        else
                            $strColumn = $arrColumn[0];

                        //get order
                        $strOrder = (uniStrtoupper($strAnnotation) == "DESC" ? "DESC" : "ASC");

                        //get column
                        if($strColumn != "") {
                            $strOrderBy = " ORDER BY ".$strColumn." ".$strOrder;
                            break;
                        }
                    }
                }
            }


            $arrSingleTable = explode(".", $arrTargetTables[0]);
            //build the query
            $arrParams = array();
            $strQuery = "SELECT system_id
                           FROM ".class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_.$arrSingleTable[0]).",
                                "._dbprefix_."system
                          WHERE system_id = ".class_carrier::getInstance()->getObjDB()->encloseColumnName($arrSingleTable[1])."
                           ".($strPrevid != "" ? " AND system_prev_id = ? " : "")."
                           ".$strOrderBy;

            if($strPrevid != "")
                $arrParams[] = $strPrevid;

            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

            foreach($arrRows as $arrOneRow) {
                $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneRow["system_id"]);
            }
        }

        return $arrReturn;
    }

    /**
     * Initializes the object from the database.
     * Loads all mapped columns to the properties
     */
    public function initObjectFromDb() {
        //try to do a default init
        $objReflection = new class_reflection($this->objObject);
        $arrTargetTables = $objReflection->getAnnotationValuesFromClass(class_orm_mapper::STR_ANNOTATION_TARGETTABLE);

        if(validateSystemid($this->objObject->getSystemid()) && count($arrTargetTables) > 0 ) {
            $strWhere = "";
            $arrTables = array();
            foreach($arrTargetTables as $strOneTable) {
                $arrOneTable = explode(".", $strOneTable);
                $strWhere .= "AND system_id=".$arrOneTable[1]." ";
                $arrTables[] = _dbprefix_.$arrOneTable[0];
            }

            $strQuery = "SELECT *
                          FROM "._dbprefix_."system_right,
                               ".implode(", ", $arrTables)." ,
                               ".class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_."system")."
                     LEFT JOIN "._dbprefix_."system_date
                            ON system_id = system_date_id
                         WHERE system_id = right_id
                            ".$strWhere."
                           AND system_id = ? ";

            $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($this->objObject->getSystemid()));

            if(method_exists($this->objObject, "setArrInitRow"))
                $this->objObject->setArrInitRow($arrRow);

            //get the mapped properties
            $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_mapper::STR_ANNOTATION_TABLECOLUMN);

            foreach($arrProperties as $strPropertyName => $strColumn) {

                $arrColumn = explode(".", $strColumn);

                if(count($arrColumn) == 2)
                    $strColumn = $arrColumn[1];

                if(!isset($arrRow[$strColumn])) {
                    continue;
                }

                $strSetter = $objReflection->getSetter($strPropertyName);
                if($strSetter !== null)
                    call_user_func(array($this->objObject, $strSetter), $arrRow[$strColumn]);
            }

        }
    }



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

        if(validateSystemid($this->objObject->getSystemid())) {

            if($this->objObject instanceof interface_versionable) {
                $objChanges = new class_module_system_changelog();
                $objChanges->createLogEntry($this->objObject, class_module_system_changelog::$STR_ACTION_EDIT);
            }

            //fetch properties with annotations
            $objReflection = new class_reflection($this->objObject);
            $arrTargetTables = $objReflection->getAnnotationValuesFromClass(class_orm_mapper::STR_ANNOTATION_TARGETTABLE);
            if(count($arrTargetTables) > 0) {
                $bitReturn = true;

                foreach($arrTargetTables as $strOneTable) {
                    $arrTableDef = explode(".", $strOneTable);

                    //scan all properties
                    $arrColValues = array();
                    $arrEscapes = array();

                    //get the mapped properties
                    $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_mapper::STR_ANNOTATION_TABLECOLUMN);

                    foreach($arrProperties as $strPropertyName => $strColumn) {

                        //check if there are table annotation available
                        $arrColumnDef = explode(".", $strColumn);

                        //if the column doesn't declare a target table whereas the class defines more then one - skip it.
                        if(count($arrColumnDef) == 1 && count($arrTargetTables) > 1 )
                            throw new class_exception("property ".$strPropertyName." declares no target table, class ".get_class($this->objObject)." declares more than one target table.", class_exception::$level_FATALERROR);


                        //skip if property targets another table
                        if(count($arrColumnDef) == 2 && $arrColumnDef[0] != $arrTableDef[0])
                            continue;

                        if(count($arrColumnDef) == 2)
                            $strColumn = $arrColumnDef[1];

                        //all prerequisites match, start creating query
                        $strGetter = $objReflection->getGetter($strPropertyName);
                        if($strGetter !== null) {
                            //explicit casts required? could be relevant, depending on the target column type / database system
                            $mixedValue = call_user_func(array($this->objObject, $strGetter));
                            if($mixedValue !== null && (uniStrtolower(uniSubstr($strGetter, 0, 6)) == "getint" || uniStrtolower(uniSubstr($strGetter, 0, 6)) == "getbit")) {
                                //different casts on 32bit / 64bit
                                if($mixedValue > PHP_INT_MAX)
                                    $mixedValue = (float)$mixedValue;
                                else
                                    $mixedValue = (int)$mixedValue;
                            }
                            $arrColValues[$strColumn] = $mixedValue;
                            $arrEscapes[] = !$objReflection->hasPropertyAnnotation($strPropertyName, class_orm_mapper::STR_ANNOTATION_BLOCKESCAPING);
                        }
                    }

                    //update table
                    if(count($arrColValues) > 0)
                        $bitReturn = $bitReturn && $this->updateSingleTable($arrColValues, $arrEscapes, $arrTableDef[0], $arrTableDef[1]);


                }

                return $bitReturn;
            }

            //no table mapping found - skip
            return true;

        }

        //no update required - skip
        return true;

    }

    /**
     * Called internally to update a single target-table
     *
     * @param $arrColValues
     * @param $arrEscapes
     * @param $strTargetTable
     * @param $strPrimaryCol
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
        $arrValues[] = $this->objObject->getSystemid();

        return $objDB->_pQuery($strQuery, $arrValues, $arrEscapes);

    }



}