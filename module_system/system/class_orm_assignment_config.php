<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A simple value holder for the assignment config handling of a mapped property
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.8
 */
class class_orm_assignment_config  {

    private $strTableName = "";
    private $strSourceColumn = "";
    private $strTargetColumn = "";
    private $arrTypeFilter = null;

    function __construct($strTableName, $strSourceColumn, $strTargetColumn, $arrTypeFilter) {
        $this->arrTypeFilter = $arrTypeFilter;
        $this->strSourceColumn = $strSourceColumn;
        $this->strTableName = $strTableName;
        $this->strTargetColumn = $strTargetColumn;
    }

    /**
     * Static factory to parse the @objectList annotation of a single property
     * @param $objObject
     * @param $strProperty
     *
     * @return class_orm_assignment_config
     * @throws class_orm_exception
     */
    public static function getConfigForProperty($objObject, $strProperty) {

        $objReflection = new class_reflection($objObject);
        $arrPropertyParams = $objReflection->getAnnotationValueForProperty($strProperty, class_orm_base::STR_ANNOTATION_OBJECTLIST, class_reflection_enum::PARAMS());
        $strTable = $objReflection->getAnnotationValueForProperty($strProperty, class_orm_base::STR_ANNOTATION_OBJECTLIST, class_reflection_enum::VALUES());

        $arrTypeFilter = isset($arrPropertyParams["type"]) ? $arrPropertyParams["type"] : null;

        if(!isset($arrPropertyParams["source"]) || !isset($arrPropertyParams["target"]) || empty($strTable)) {
            throw new class_orm_exception("@objectList annoation for ".$strProperty."@".get_class($objObject)." is malformed", class_orm_exception::$level_FATALERROR);
        }

        return new class_orm_assignment_config($strTable, $arrPropertyParams["source"], $arrPropertyParams["target"], $arrTypeFilter);
    }

    /**
     * @return null
     */
    public function getArrTypeFilter() {
        return $this->arrTypeFilter;
    }

    /**
     * @return string
     */
    public function getStrSourceColumn() {
        return $this->strSourceColumn;
    }

    /**
     * @return string
     */
    public function getStrTableName() {
        return $this->strTableName;
    }

    /**
     * @return string
     */
    public function getStrTargetColumn() {
        return $this->strTargetColumn;
    }



}