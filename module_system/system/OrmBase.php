<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Abstract base class for all other orm related handler classes. Provides common methods and general logic shared by
 * all subclasses.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
abstract class OrmBase
{

    /**
     * Static flag to change the handling of deleted objects globally, so for every following
     * ORM operation
     *
     * @var int
     */
    protected static $objHandleLogicalDeletedGlobal = null;

    /**
     * Flag to change the handling of deleted obejcts locally, so only for the current instance of the ORM
     * mapper.
     *
     * @var OrmDeletedhandlingEnum
     */
    private $objHandleLogicalDeleted = null;


    protected static $bitLogcialDeleteAvailable = null;

    const STR_ANNOTATION_TARGETTABLE = "@targetTable";
    const STR_ANNOTATION_TARGETTABLETXSAFE = "@targetTableTxSafe";
    const STR_ANNOTATION_TABLECOLUMN = "@tableColumn";
    const STR_ANNOTATION_TABLECOLUMNDATATYPE = "@tableColumnDatatype";
    const STR_ANNOTATION_TABLECOLUMNPRIMARYKEY = "@tableColumnPrimaryKey";
    const STR_ANNOTATION_TABLECOLUMNINDEX = "@tableColumnIndex";
    const STR_ANNOTATION_BLOCKESCAPING = "@blockEscaping";
    const STR_ANNOTATION_LISTORDER = "@listOrder";
    const STR_ANNOTATION_OBJECTLIST = "@objectList";

    /** @var Root */
    private $objObject = null;

    /** @var array an internal cache to avoid redundant lookups of annotations */
    private static $arrTargetTableCache = array();

    /**
     * @param Root|VersionableInterface|null $objObject
     */
    public function __construct($objObject = null)
    {
        $this->objObject = $objObject;
        if (self::$bitLogcialDeleteAvailable === null) {

            $arrColumns = Database::getInstance()->getColumnsOfTable(_dbprefix_."system");
            self::$bitLogcialDeleteAvailable = count(array_filter($arrColumns, function ($arrOneTable) {
                    return $arrOneTable["columnName"] == "system_deleted";
                })) > 0;
        }
    }

    /**
     * @return Root|VersionableInterface
     */
    protected function getObjObject()
    {
        return $this->objObject;
    }

    /**
     * @param Root $objObject
     *
     * @return void
     */
    public function setObjObject($objObject)
    {
        $this->objObject = $objObject;
    }

    /**
     * Validates if the current object has at least a single target-table set up
     *
     * @return bool
     */
    protected function hasTargetTable()
    {
        $strClass = is_object($this->getObjObject()) ? get_class($this->getObjObject()) : $this->getObjObject();
        if (!empty(self::$arrTargetTableCache[$strClass])) {
            return true;
        }

        $objAnnotations = new Reflection($this->getObjObject());
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass(OrmBase::STR_ANNOTATION_TARGETTABLE);
        self::$arrTargetTableCache[$strClass] = $arrTargetTables;

        return count($arrTargetTables) > 0;
    }


    /**
     * Internal helper, generated the query part without the select- and the real where- parts.
     *
     * @param string $strTargetClass
     *
     * @return string
     * @throws OrmException
     */
    protected function getQueryBase($strTargetClass = "")
    {
        if ($strTargetClass == "") {
            $strTargetClass = $this->getObjObject();
        }

        $objAnnotations = new Reflection($strTargetClass);
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass(OrmBase::STR_ANNOTATION_TARGETTABLE);

        if (count($arrTargetTables) == 0) {
            throw new OrmException("Class ".(is_object($strTargetClass) ? get_class($strTargetClass) : $strTargetClass)." has no target table", OrmException::$level_ERROR);
        }

        $strWhere = "";
        $arrTables = array();
        foreach ($arrTargetTables as $strOneTable) {
            $arrOneTable = explode(".", $strOneTable);
            $strWhere .= "AND system_id=".$arrOneTable[1]." ";
            $arrTables[] = Carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_.$arrOneTable[0])." AS ".Carrier::getInstance()->getObjDB()->encloseTableName($arrOneTable[0])."";
        }

        //build the query
        $strQuery = "FROM ".Carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_."system_right").",
                            ".implode(", ", $arrTables)." ,
                            ".Carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_."system")." AS system
                  LEFT JOIN "._dbprefix_."system_date AS system_date
                         ON system_id = system_date_id
                      WHERE system_id = right_id
                            ".$strWhere."";

        return $strQuery;
    }


    /**
     * Reads the assignment values currently stored in the database for a given property of the current object.
     *
     * @param string $strPropertyName
     *
     * @return string[] array of systemids
     */
    public final function getAssignmentsFromDatabase($strPropertyName)
    {
        $objCfg = OrmAssignmentConfig::getConfigForProperty($this->getObjObject(), $strPropertyName);
        $objDB = Carrier::getInstance()->getObjDB();
        $strQuery = " SELECT *
                        FROM ".$objDB->encloseTableName(_dbprefix_.$objCfg->getStrTableName()).",
                             ".$objDB->encloseTableName(_dbprefix_."system")."
                       WHERE system_id =  ".$objDB->encloseColumnName($objCfg->getStrTargetColumn())."
                         AND ".$objDB->encloseColumnName($objCfg->getStrSourceColumn())." = ?
                             ".$this->getDeletedWhereRestriction();
        $arrRows = $objDB->getPArray($strQuery, array($this->getObjObject()->getSystemid()));

        $strTargetCol = $objCfg->getStrTargetColumn();
        array_walk($arrRows, function (array &$arrSingleRow) use ($strTargetCol) {
            $arrSingleRow = $arrSingleRow[$strTargetCol];
        });

        return $arrRows;
    }


    /**
     * Returns the current config of the deleted-handling, evaluates both, the current instances' config and the
     * global config.
     *
     * @return int
     */
    public function getIntCombinedLogicalDeletionConfig()
    {
        if ($this->objHandleLogicalDeleted !== null) {
            return $this->objHandleLogicalDeleted;
        }

        if (self::$objHandleLogicalDeletedGlobal !== null) {
            return self::$objHandleLogicalDeletedGlobal;
        }

        return OrmDeletedhandlingEnum::EXCLUDED;
    }

    /**
     * Generates the where restriction for queries, based on the current config.
     * Currently the methods returns a string based where restriction.
     *
     * @param string $strSystemTablePrefix
     * @param string $strConjunction
     *
     * @return string
     */
    public function getDeletedWhereRestriction($strSystemTablePrefix = "", $strConjunction = "AND")
    {
        $strQuery = "";

        if ($strSystemTablePrefix != "") {
            $strSystemTablePrefix = $strSystemTablePrefix.".";
        }

        if (self::$bitLogcialDeleteAvailable) {
            if ($this->getIntCombinedLogicalDeletionConfig() === OrmDeletedhandlingEnum::EXCLUDED) {
                $strQuery .= " ".$strConjunction." {$strSystemTablePrefix}system_deleted = 0 ";
            }
            elseif ($this->getIntCombinedLogicalDeletionConfig() === OrmDeletedhandlingEnum::EXCLUSIVE) {
                $strQuery .= " ".$strConjunction." {$strSystemTablePrefix}system_deleted = 1 ";
            }
        }

        return $strQuery;
    }


    /**
     * Static flag to change the handling of deleted objects globally, so for every following
     * ORM operation
     *
     * @param int $objHandleLogicalDeleted
     */
    public static function setObjHandleLogicalDeletedGlobal($objHandleLogicalDeleted = null)
    {
        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_ORMCACHE);
        self::$objHandleLogicalDeletedGlobal = $objHandleLogicalDeleted;
    }

    /**
     * @return OrmDeletedhandlingEnum
     */
    public function getObjHandleLogicalDeleted()
    {
        return $this->objHandleLogicalDeleted;
    }

    /**
     * Flag to change the handling of deleted obejcts locally, so only for the current instance of the ORM
     * mapper.
     *
     * @param int $objHandleLogicalDeleted
     */
    public function setObjHandleLogicalDeleted($objHandleLogicalDeleted)
    {
        $this->objHandleLogicalDeleted = $objHandleLogicalDeleted;
    }

    /**
     * @return int
     */
    public static function getObjHandleLogicalDeletedGlobal()
    {
        return self::$objHandleLogicalDeletedGlobal;
    }
}

/**
 * Most exceptions thrown by the orm system will use the OrmException type in order
 * to react with special catch-blocks
 */
class OrmException extends Exception
{

}

/**
 * Most exceptions thrown by the orm system will use the OrmException type in order
 * to react with special catch-blocks
 */
class class_orm_exception extends OrmException
{

}
