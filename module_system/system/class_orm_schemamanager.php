<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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

    static $arrColumnDataTypes = array(
        class_db_datatypes::STR_TYPE_INT,
        class_db_datatypes::STR_TYPE_LONG,
        class_db_datatypes::STR_TYPE_DOUBLE,
        class_db_datatypes::STR_TYPE_CHAR10,
        class_db_datatypes::STR_TYPE_CHAR20,
        class_db_datatypes::STR_TYPE_CHAR100,
        class_db_datatypes::STR_TYPE_CHAR254,
        class_db_datatypes::STR_TYPE_CHAR500,
        class_db_datatypes::STR_TYPE_TEXT,
        class_db_datatypes::STR_TYPE_LONGTEXT
    );


    public function createTable($strClass) {
        $this->setObjObject($strClass);
        if(!$this->hasTargetTable()) {
            throw new class_orm_exception("Class ".$strClass." provides no target-table!", class_orm_exception::$level_ERROR);
        }

        $arrTargetTables = $this->collectTableDefinitions($strClass);
        $this->processTableDefinitions($arrTargetTables);
    }

    /**
     * @param class_orm_schemamanager_table[] $arrTableDefinitions
     *
     * @throws class_orm_exception
     * @return void
     */
    private function processTableDefinitions($arrTableDefinitions) {

        foreach($arrTableDefinitions as $objOneTable) {

            $arrIndex = array();
            $arrPrimary = array();

            $arrFields = array();
            /** @var class_orm_schemamanager_row $objOneColumn */
            foreach($objOneTable->getArrRows() as $objOneColumn) {
                $arrFields[$objOneColumn->getStrName()] = array($objOneColumn->getStrDatatype(), $objOneColumn->getBitNull());

                if($objOneColumn->getBitPrimaryKey())
                    $arrPrimary[] = $objOneColumn->getStrName();

                if($objOneColumn->getBitIndex())
                    $arrIndex[] = $objOneColumn->getStrName();
            }


            if(!class_carrier::getInstance()->getObjDB()->createTable($objOneTable->getStrName(), $arrFields, $arrPrimary, $arrIndex, $objOneTable->getBitTxSafe()))
                throw new class_orm_exception("error creating table ".$objOneTable->getStrName(), class_orm_exception::$level_ERROR);

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

        $arrTxSafe = $objReflection->getAnnotationValuesFromClass(self::STR_ANNOTATION_TARGETTABLETXSAFE);

        /** @var class_orm_schemamanager_table[] $arrCreateTables */
        $arrCreateTables = array();

        foreach($arrTargetTables as $strValue) {
            $arrTable = explode(".", $strValue);

            if(count($arrTable) != 2)
                throw new class_orm_exception("Target table for ".$strClass." is not in table.primaryColumn format", class_orm_exception::$level_ERROR);

            $objTable = new class_orm_schemamanager_table($arrTable[0]);
            if(count($arrTxSafe) == 1)
                $objTable->setBitTxSafe($arrTxSafe[0] == "false" ? false : true);

            $objTable->addRow(new class_orm_schemamanager_row($arrTable[1], class_db_datatypes::STR_TYPE_CHAR20, false, true));
            $arrCreateTables[$arrTable[0]] = $objTable;
        }

        //merge them with the list of mapped columns
        $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_ANNOTATION_TABLECOLUMN);
        foreach($arrProperties as $strProperty => $strTableColumn) {
            //fetch the target data-type
            $strTargetDataType = $objReflection->getAnnotationValueForProperty($strProperty, self::STR_ANNOTATION_TABLECOLUMNDATATYPE);
            if($strTargetDataType == null)
                $strTargetDataType = class_db_datatypes::STR_TYPE_CHAR254;

            if(!in_array($strTargetDataType, self::$arrColumnDataTypes))
                throw new class_orm_exception("Datatype ".$strTargetDataType." is unknown (".$strProperty."@".$strClass.")", class_orm_exception::$level_ERROR);

            $arrColumn = explode(".", $strTableColumn);

            if(count($arrColumn) != 2 && count($arrTargetTables) > 1) {
                throw new class_orm_exception("Syntax for tableColumn annotation at property ".$strProperty."@".$strClass." not in format table.columnName", class_exception::$level_ERROR);
            }
            if(count($arrColumn) == 1 && count($arrTargetTables) == 1) {
                //copy the column name, table is the current one
                $arrTable = explode(".", $arrTargetTables[0]);
                $arrColumn[1] = $arrColumn[0];
                $arrColumn[0] = $arrTable[0];
            }


            $objRow = new class_orm_schemamanager_row($arrColumn[1], $strTargetDataType);

            if($objReflection->hasPropertyAnnotation($strProperty, class_orm_base::STR_ANNOTATION_TABLECOLUMNINDEX))
                $objRow->setBitIndex(true);

            if($objReflection->hasPropertyAnnotation($strProperty, class_orm_base::STR_ANNOTATION_TABLECOLUMNPRIMARYKEY))
                $objRow->setBitPrimaryKey(true);

            if(isset($arrCreateTables[$arrColumn[0]])) {
                $objTable = $arrCreateTables[$arrColumn[0]];
                $objTable->addRow($objRow);
            }
        }

        return $arrCreateTables;
    }


}
