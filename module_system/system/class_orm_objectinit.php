<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * The orm object init class is used to init an object from the database.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_orm_objectinit extends class_orm_base {


    /**
     * Initializes the object from the database.
     * Loads all mapped columns to the properties
     *
     * @return void
     */
    public function initObjectFromDb() {
        //try to do a default init
        $objReflection = new class_reflection($this->getObjObject());

        if(validateSystemid($this->getObjObject()->getSystemid()) && $this->hasTargetTable()) {

            if(class_orm_rowcache::getCachedInitRow($this->getObjObject()->getSystemid()) !== null) {
                $arrRow = class_orm_rowcache::getCachedInitRow($this->getObjObject()->getSystemid());
            }
            else {
                $strQuery = "SELECT *
                          ".$this->getQueryBase()."
                           AND system_id = ? ";

                $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($this->getObjObject()->getSystemid()));
            }

            if(method_exists($this->getObjObject(), "setArrInitRow"))
                $this->getObjObject()->setArrInitRow($arrRow);

            //get the mapped properties
            $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_TABLECOLUMN);

            foreach($arrProperties as $strPropertyName => $strColumn) {

                $arrColumn = explode(".", $strColumn);

                if(count($arrColumn) == 2)
                    $strColumn = $arrColumn[1];

                if(!isset($arrRow[$strColumn])) {
                    continue;
                }

                $strSetter = $objReflection->getSetter($strPropertyName);
                if($strSetter !== null)
                    call_user_func(array($this->getObjObject(), $strSetter), $arrRow[$strColumn]);
            }

        }
    }


}
