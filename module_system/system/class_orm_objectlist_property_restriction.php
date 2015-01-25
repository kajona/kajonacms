<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * A objectlist restriction may be used to create where restrictions for the objectList and objectCount queries.
 * Therefore you may pass the name of a property, the comparator and, finally, the expecteed value of the property.
 * Example:
 *  $objQuery->addWhereRestriction(new class_orm_objectlist_property_restriction("strTitle", class_orm_comparator_enum::Equal(), "abc"));
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class class_orm_objectlist_property_restriction extends class_orm_objectlist_restriction {

    private $strProperty = "";

    /**
     * @var class_orm_comparator_enum
     */
    private $objComparator = "";
    private $arrParams = array();

    /**
     * @param string $strProperty
     * @param class_orm_comparator_enum $objComparator
     * @param $strValue
     */
    function __construct($strProperty, class_orm_comparator_enum $objComparator, $strValue) {

        $this->arrParams = array($strValue);
        $this->objComparator = $objComparator;
        $this->strProperty =  $strProperty;
    }

    /**
     * @param array $arrParams
     *
     * @throws class_orm_exception
     */
    public function setArrParams($arrParams) {
        throw new class_orm_exception("Setting params for property restrictions is not supported", class_exception::$level_ERROR);
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
        throw new class_orm_exception("Setting a where restriction for property restrictions is not supported", class_exception::$level_ERROR);
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

        return " AND ".$strPropertyValue. " ".$this->objComparator->getEnumAsSqlString()." ? ";
    }




}
