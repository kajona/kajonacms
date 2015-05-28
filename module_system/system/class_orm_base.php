<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Abstract base class for all other orm related handler classes. Provides common methods and general logic shared by
 * all subclasses.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
abstract class class_orm_base {

    /**
     * Records deleted logically will be excluded from result sets.
     * Default mode.
     */
    const INT_LOGICAL_DELETED_EXCLUDED = 0;

    /**
     * The result sets will include records deleted logically only.
     */
    const INT_LOGICAL_DELETED_ONLY = 1;

    /**
     * The result sets will include both, regular records and records deleted logically, so a mixture.
     */
    const INT_LOGICAL_DELETED_DISABLED = 2;


    protected static $intHandleLogicalDeleted = 0;
    protected $bitLogcialDeleteAvailable = true;

    const STR_ANNOTATION_TARGETTABLE = "@targetTable";
    const STR_ANNOTATION_TARGETTABLETXSAFE = "@targetTableTxSafe";
    const STR_ANNOTATION_TABLECOLUMN = "@tableColumn";
    const STR_ANNOTATION_TABLECOLUMNDATATYPE = "@tableColumnDatatype";
    const STR_ANNOTATION_TABLECOLUMNPRIMARYKEY = "@tableColumnPrimaryKey";
    const STR_ANNOTATION_TABLECOLUMNINDEX = "@tableColumnIndex";
    const STR_ANNOTATION_BLOCKESCAPING = "@blockEscaping";
    const STR_ANNOTATION_LISTORDER = "@listOrder";
    const STR_ANNOTATION_OBJECTLIST = "@objectList";

    /** @var class_root */
    private $objObject = null;

    /**
     * @param class_root|interface_versionable|null $objObject
     */
    function __construct($objObject = null) {
        $this->objObject = $objObject;
        $arrColumns = class_db::getInstance()->getColumnsOfTable(_dbprefix_."system");
        $this->bitLogcialDeleteAvailable = count(array_filter($arrColumns, function($arrOneTable) { return $arrOneTable["columnName"] == "system_deleted"; } )) > 0;
    }

    /**
     * @return \class_root|interface_versionable
     */
    protected function getObjObject() {
        return $this->objObject;
    }

    /**
     * @param \class_root $objObject
     * @return void
     */
    public function setObjObject($objObject) {
        $this->objObject = $objObject;
    }

    /**
     * Validates if the current object has at least a single target-table set up
     * @return bool
     */
    protected function hasTargetTable() {
        $objAnnotations = new class_reflection($this->getObjObject());
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass(class_orm_base::STR_ANNOTATION_TARGETTABLE);

        return count($arrTargetTables) > 0;
    }


    /**
     * Internal helper, generated the query part without the select- and the real where- parts.
     *
     * @param string $strTargetClass
     *
     * @return string
     * @throws class_orm_exception
     */
    protected function getQueryBase($strTargetClass = "") {
        if($strTargetClass == "")
            $strTargetClass = $this->getObjObject();

        $objAnnotations = new class_reflection($strTargetClass);
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass(class_orm_base::STR_ANNOTATION_TARGETTABLE);

        if(count($arrTargetTables) == 0) {
            throw new class_orm_exception("Class ".(is_object($strTargetClass) ? get_class($strTargetClass) : $strTargetClass)." has no target table", class_exception::$level_ERROR);
        }

        $strWhere = "";
        $arrTables = array();
        foreach($arrTargetTables as $strOneTable) {
            $arrOneTable = explode(".", $strOneTable);
            $strWhere .= "AND system_id=".$arrOneTable[1]." ";
            $arrTables[] = class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_.$arrOneTable[0])." AS ".class_carrier::getInstance()->getObjDB()->encloseTableName($arrOneTable[0])."";
        }

        //build the query
        $strQuery = "FROM ".class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_."system_right").",
                            ".implode(", ", $arrTables)." ,
                            ".class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_."system")." AS system
                  LEFT JOIN "._dbprefix_."system_date AS system_date
                         ON system_id = system_date_id
                      WHERE system_id = right_id
                            ".$strWhere."";

        return $strQuery;
    }



    /**
     * Reads the assignment values currently stored in the database for a given property of the current object.
     * @param $strPropertyName
     *
     * @return string[] array of systemids
     */
    public final function getAssignmentsFromDatabase($strPropertyName) {
        $objCfg = class_orm_assignment_config::getConfigForProperty($this->getObjObject(), $strPropertyName);
        $objDB = class_carrier::getInstance()->getObjDB();
        $strQuery = "SELECT * FROM ".$objDB->encloseTableName(_dbprefix_.$objCfg->getStrTableName())." WHERE ".$objDB->encloseColumnName($objCfg->getStrSourceColumn())." = ? ";
        $arrRows = $objDB->getPArray($strQuery, array($this->getObjObject()->getSystemid()), null, null);

        $strTargetCol = $objCfg->getStrTargetColumn();
        array_walk($arrRows, function(array &$arrSingleRow) use ($strTargetCol) {
            $arrSingleRow = $arrSingleRow[$strTargetCol];
        });

        return $arrRows;
    }




    /**
     * @return int
     */
    public static function getIntHandleLogicalDeleted() {
        return self::$intHandleLogicalDeleted;
    }

    /**
     * @param int $intHandleLogicalDeleted
     */
    public static function setIntHandleLogicalDeleted($intHandleLogicalDeleted) {
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE);
        self::$intHandleLogicalDeleted = $intHandleLogicalDeleted;
    }


}

/**
 * Most exceptions thrown by the orm system will use the class_orm_exception type in order
 * to react with special catch-blocks
 */
class class_orm_exception extends class_exception {

}