<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * A objectlist restriction may be used to create where restrictions for the objectList and objectCount queries.
 * This restrcition creates an IN statement e.g. "AND <columnname> IN (<parameters>)"
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 * @since 4.8
 */
class class_orm_objectlist_property_in_restriction extends class_orm_objectlist_restriction{

    private $strProperty = "";
    private $arrParams = array();
    private $strCondition = "";

    function __construct($strProperty, array $arrParams, $strCondition = "AND") {

        $this->arrParams = $arrParams;
        $this->strCondition = $strCondition;
        $this->strProperty =  $strProperty;
    }

    /**
     * @param array $arrParams
     *
     * @throws class_orm_exception
     */
    public function setArrParams($arrParams) {
        throw new class_orm_exception("Setting params for property IN restrictions is not supported", class_exception::$level_ERROR);
    }

    /**
     * @return array
     */
    public function getArrParams() {
        return $this->arrParams;
    }

    /**
     * @param string $strWhere
     *
     * @throws class_orm_exception
     */
    public function setStrWhere($strWhere) {
        throw new class_orm_exception("Setting a where restriction for property IN restrictions is not supported", class_exception::$level_ERROR);
    }

    /**
     * Here comes the magic, generation a where restriction out of the passed property name and the comparator
     *
     * @return string
     * @throws class_orm_exception
     */
    public function getStrWhere() {
        $objReflection = new class_reflection($this->getStrTargetClass());

        $strPropertyValue = $objReflection->getAnnotationValueForProperty($this->strProperty, class_orm_base::STR_ANNOTATION_TABLECOLUMN);

        if($strPropertyValue == null)
            throw new class_orm_exception("Failed to load annotation ".class_orm_base::STR_ANNOTATION_TABLECOLUMN." for property ".$this->strProperty."@".$this->getStrTargetClass(), class_exception::$level_ERROR);


        $arrParamsPlaceholder = array_map(function($objParameter){ return "?";}, $this->arrParams);
        $strPlaceholder = implode(",", $arrParamsPlaceholder);

        return "{$this->strCondition} {$strPropertyValue} IN ({$strPlaceholder})";
    }

}
