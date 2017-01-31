<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;


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
class OrmSchemamanager extends OrmBase {

    static $arrColumnDataTypes = array(
        DbDatatypes::STR_TYPE_INT,
        DbDatatypes::STR_TYPE_LONG,
        DbDatatypes::STR_TYPE_DOUBLE,
        DbDatatypes::STR_TYPE_CHAR10,
        DbDatatypes::STR_TYPE_CHAR20,
        DbDatatypes::STR_TYPE_CHAR100,
        DbDatatypes::STR_TYPE_CHAR254,
        DbDatatypes::STR_TYPE_CHAR500,
        DbDatatypes::STR_TYPE_TEXT,
        DbDatatypes::STR_TYPE_LONGTEXT
    );


    public function createTable($strClass) {
        $this->setObjObject($strClass);
        if(!$this->hasTargetTable()) {
            throw new OrmException("Class ".$strClass." provides no target-table!", OrmException::$level_ERROR);
        }

        $arrTargetTables = $this->collectTableDefinitions($strClass);
        $arrTargetTables = array_merge($arrTargetTables, $this->collectAssignmentDefinitions($strClass));
        $this->processTableDefinitions($arrTargetTables);
    }

    /**
     * @param OrmSchemamanagerTable[] $arrTableDefinitions
     *
     * @throws OrmException
     * @return void
     */
    private function processTableDefinitions($arrTableDefinitions) {

        foreach($arrTableDefinitions as $objOneTable) {

            $arrIndex = array();
            $arrPrimary = array();

            $arrFields = array();
            /** @var OrmSchemamanagerRow $objOneColumn */
            foreach($objOneTable->getArrRows() as $objOneColumn) {
                $arrFields[$objOneColumn->getStrName()] = array($objOneColumn->getStrDatatype(), $objOneColumn->getBitNull());

                if($objOneColumn->getBitPrimaryKey())
                    $arrPrimary[] = $objOneColumn->getStrName();

                if($objOneColumn->getBitIndex())
                    $arrIndex[] = $objOneColumn->getStrName();
            }


            if(!Carrier::getInstance()->getObjDB()->createTable($objOneTable->getStrName(), $arrFields, $arrPrimary, $arrIndex, $objOneTable->getBitTxSafe()))
                throw new OrmException("error creating table ".$objOneTable->getStrName(), OrmException::$level_ERROR);

        }


    }

    /**
     * @param string $strClass
     *
     * @return array
     * @throws OrmException
     */
    private function collectTableDefinitions($strClass) {
        $objReflection = new Reflection($strClass);

        $arrTargetTables = $objReflection->getAnnotationValuesFromClass(self::STR_ANNOTATION_TARGETTABLE);

        $arrTxSafe = $objReflection->getAnnotationValuesFromClass(self::STR_ANNOTATION_TARGETTABLETXSAFE);

        /** @var OrmSchemamanagerTable[] $arrCreateTables */
        $arrCreateTables = array();

        foreach($arrTargetTables as $strValue) {
            $arrTable = explode(".", $strValue);

            if(count($arrTable) != 2)
                throw new OrmException("Target table for ".$strClass." is not in table.primaryColumn format", OrmException::$level_ERROR);

            $objTable = new OrmSchemamanagerTable($arrTable[0]);
            if(count($arrTxSafe) == 1)
                $objTable->setBitTxSafe($arrTxSafe[0] == "false" ? false : true);

            $objTable->addRow(new OrmSchemamanagerRow($arrTable[1], DbDatatypes::STR_TYPE_CHAR20, false, true));
            $arrCreateTables[$arrTable[0]] = $objTable;
        }

        //merge them with the list of mapped columns
        $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_ANNOTATION_TABLECOLUMN);
        foreach($arrProperties as $strProperty => $strTableColumn) {
            //fetch the target data-type
            $strTargetDataType = $objReflection->getAnnotationValueForProperty($strProperty, self::STR_ANNOTATION_TABLECOLUMNDATATYPE);
            if($strTargetDataType == null)
                $strTargetDataType = DbDatatypes::STR_TYPE_CHAR254;

            if(!in_array($strTargetDataType, self::$arrColumnDataTypes))
                throw new OrmException("Datatype ".$strTargetDataType." is unknown (".$strProperty."@".$strClass.")", OrmException::$level_ERROR);

            $arrColumn = explode(".", $strTableColumn);

            if(count($arrColumn) != 2 && count($arrTargetTables) > 1) {
                throw new OrmException("Syntax for tableColumn annotation at property ".$strProperty."@".$strClass." not in format table.columnName", OrmException::$level_ERROR);
            }
            if(count($arrColumn) == 1 && count($arrTargetTables) == 1) {
                //copy the column name, table is the current one
                $arrTable = explode(".", $arrTargetTables[0]);
                $arrColumn[1] = $arrColumn[0];
                $arrColumn[0] = $arrTable[0];
            }


            $objRow = new OrmSchemamanagerRow($arrColumn[1], $strTargetDataType);

            if($objReflection->hasPropertyAnnotation($strProperty, OrmBase::STR_ANNOTATION_TABLECOLUMNINDEX))
                $objRow->setBitIndex(true);

            if($objReflection->hasPropertyAnnotation($strProperty, OrmBase::STR_ANNOTATION_TABLECOLUMNPRIMARYKEY))
                $objRow->setBitPrimaryKey(true);

            if(isset($arrCreateTables[$arrColumn[0]])) {
                $objTable = $arrCreateTables[$arrColumn[0]];
                $objTable->addRow($objRow);
            }
        }

        return $arrCreateTables;
    }


    /**
     * Processes all object assignments in order to generate the relevant tables
     *
     * @param $strClass
     *
     * @return array
     */
    private function collectAssignmentDefinitions($strClass) {

        $arrAssignmentTables = array();
        $objReflection = new Reflection($strClass);

        //get the mapped properties
        $arrProperties = $objReflection->getPropertiesWithAnnotation(OrmBase::STR_ANNOTATION_OBJECTLIST, ReflectionEnum::PARAMS);

        foreach($arrProperties as $strPropertyName => $arrValues) {

            $strTableName = $objReflection->getAnnotationValueForProperty($strPropertyName, OrmBase::STR_ANNOTATION_OBJECTLIST);

            if(!isset($arrValues["source"]) || !isset($arrValues["target"]) || empty($strTableName)) {
                continue;
            }

            $objTable = new OrmSchemamanagerTable($strTableName);
            $objTable->addRow(new OrmSchemamanagerRow($arrValues["source"], DbDatatypes::STR_TYPE_CHAR20, false, true));
            $objTable->addRow(new OrmSchemamanagerRow($arrValues["target"], DbDatatypes::STR_TYPE_CHAR20, false, true));

            $arrAssignmentTables[] = $objTable;
        }

        return $arrAssignmentTables;
    }

}
