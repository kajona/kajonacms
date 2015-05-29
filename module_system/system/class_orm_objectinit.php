<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * The orm object init class is used to init an object from the database.
 * Pass an object with a given systemid using the constructor and call
 * initObjectFromDb() afterwards.
 * The mapper will take care to fill all properties with the matching values
 * from the database.
 * Therefore it is essential to have getters and setters for each mapped
 * property (java bean syntax).
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_orm_objectinit extends class_orm_base {


    /**
     * Initializes the object from the database.
     * Loads all mapped columns to the properties.
     * Requires that the object is identified by its systemid.
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
                           AND system.system_id = ? ";

                if($this->bitLogcialDeleteAvailable) {
                    $strQuery .= $this->getDeletedWhereRestriction();
                }

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

                //skip columns from the system-table, they are set later on
                if(count($arrColumn) == 2 && $arrColumn[0] == "system") {
                    continue;
                }

                $strSetter = $objReflection->getSetter($strPropertyName);
                if($strSetter !== null)
                    call_user_func(array($this->getObjObject(), $strSetter), $arrRow[$strColumn]);
            }

            $this->initAssignmentProperties();
        }
    }

    /**
     * Injects the lazy loading objects for assignment properties into the current object
     * @return void
     */
    private function initAssignmentProperties() {
        $objReflection = new class_reflection($this->getObjObject());

        //get the mapped properties
        $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_OBJECTLIST, class_reflection_enum::PARAMS());

        foreach($arrProperties as $strPropertyName => $arrValues) {

            $objPropertyLazyLoader = new class_orm_assignment_array($this->getObjObject(), $strPropertyName);

            $strSetter = $objReflection->getSetter($strPropertyName);
            if($strSetter !== null)
                call_user_func(array($this->getObjObject(), $strSetter), $objPropertyLazyLoader);
        }

    }

}
