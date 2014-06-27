<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Abstract base class for all other orm related handler classes
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
abstract class class_orm_base {

    const STR_ANNOTATION_TARGETTABLE = "@targetTable";
    const STR_ANNOTATION_TABLECOLUMN = "@tableColumn";
    const STR_ANNOTATION_BLOCKESCAPING = "@blockEscaping";
    const STR_ANNOTATION_LISTORDER = "@listOrder";

    /** @var class_root */
    private $objObject = null;

    /**
     * @param null $objObject
     */
    function __construct($objObject = null) {
        $this->objObject = $objObject;
    }

    /**
     * @return \class_root|interface_versionable
     */
    protected function getObjObject() {
        return $this->objObject;
    }


    /**
     * Internal helper, generated the query part without the select- and the real where- parts.
     *
     * @param string $strTargetClass
     *
     * @return string
     * @throws class_orm_exception
     */
    protected function getQueryBase($strTargetClass) {
        $objAnnotations = new class_reflection($strTargetClass);
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass(class_orm_base::STR_ANNOTATION_TARGETTABLE);

        if(count($arrTargetTables) == 0) {
            throw new class_orm_exception("Class ".$strTargetClass." has no target table", class_exception::$level_ERROR);
        }

        $strWhere = "";
        $arrTables = array();
        foreach($arrTargetTables as $strOneTable) {
            $arrOneTable = explode(".", $strOneTable);
            $strWhere .= "AND system_id=".$arrOneTable[1]." ";
            $arrTables[] = class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_.$arrOneTable[0]);
        }

        //build the query
        $strQuery = "FROM ".class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_."system_right").",
                            ".implode(", ", $arrTables)." ,
                            ".class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_."system")."
                  LEFT JOIN "._dbprefix_."system_date
                         ON system_id = system_date_id
                      WHERE system_id = right_id
                            ".$strWhere."";

        return $strQuery;
    }


}

class class_orm_exception extends class_exception {

}