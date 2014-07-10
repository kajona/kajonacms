<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * The schemamanager-class is used to generate the table out of an objects annotations.
 *
 * As per Kajona 4.6, only the initial create table is supported.
 *
 * @todo extend the annotation system with version numbers, to that the schema-manager is able
 *       to generate alter-table statements, too.
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_orm_schemamanager extends class_orm_base {


    public function createTable($strClass) {
        $this->setObjObject($strClass);
        if(!$this->hasTargetTable()) {
            throw new class_orm_exception("Class ".$strClass." provides no target-table!", class_orm_exception::$level_ERROR);
        }

        $arrTargetTables = $this->collectTableDefinitions($strClass);
        $this->processTableDefinitions($arrTargetTables);
    }

    /**
     * @param array $arrTableDefinitions
     *
     * @throws class_orm_exception
     * @return void
     */
    private function processTableDefinitions($arrTableDefinitions) {

        foreach($arrTableDefinitions as $strOneTable => $arrObjColumns) {

            $arrIndex = array();
            $arrPrimary = array();

            $arrFields = array();
            /** @var class_orm_schemamanager_row $objOneColumn */
            foreach($arrObjColumns as $objOneColumn) {
                $arrFields[$objOneColumn->getStrName()] = array($objOneColumn->getStrDatatype(), $objOneColumn->getBitNull());

                if($objOneColumn->getBitPrimaryKey())
                    $arrPrimary[] = $objOneColumn->getStrName();

                if($objOneColumn->getBitIndex())
                    $arrIndex[] = $objOneColumn->getStrName();
            }


            if(!class_carrier::getInstance()->getObjDB()->createTable($strOneTable, $arrFields, $arrPrimary, $arrIndex))
                throw new class_orm_exception("error creating table ".$strOneTable, class_orm_exception::$level_ERROR);

        }


    }

    /**
     * @param string $strClass
     *
     * @return array
     * @throws class_orm_exception
     * @throws class_exception
     */
    private function collectTableDefinitions($strClass) {
        $objReflection = new class_reflection($strClass);

        $arrTargetTables = $objReflection->getAnnotationValuesFromClass(self::STR_ANNOTATION_TARGETTABLE);

        $arrCreateTables = array();

        foreach($arrTargetTables as $strValue) {
            $arrTable = explode(".", $strValue);

            if(count($arrTable) != 2)
                throw new class_orm_exception("Target table for ".$strClass." is not in table.primaryColumn format", class_orm_exception::$level_ERROR);

            $arrCreateTables[$arrTable[0]] = array(new class_orm_schemamanager_row($arrTable[1], class_db_datatypes::STR_TYPE_CHAR20, false, true));
        }

        //merge them with the list of mapped columns
        $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_ANNOTATION_TABLECOLUMN);
        foreach($arrProperties as $strProperty => $strTableColumn) {
            //fetch the target data-type
            $strTargetDataType = $objReflection->getAnnotationValueForProperty($strProperty, self::STR_ANNOTATION_TABLECOLUMNDATATYPE);
            if($strTargetDataType == null)
                $strTargetDataType = class_db_datatypes::STR_TYPE_CHAR254;

            $arrColumn = explode(".", $strTableColumn);

            if(count($arrColumn) != 2)
                throw new class_exception("Syntax for tableColumn annotation at property ".$strProperty."@".$strClass." not in format table.columnName", class_exception::$level_ERROR);

            $arrCreateTables[$arrColumn[0]][] = new class_orm_schemamanager_row($arrColumn[1], $strTargetDataType);
        }

        return $arrCreateTables;
    }


}
