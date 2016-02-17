<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Updates the default templatepack
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 */
class class_module_packagemanager_recordupdatedlistener implements interface_genericevent_listener {


    /**
     * Triggered as soon as a record is updated
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {

        $objRecord = $arrArguments[0];

        if($objRecord instanceof class_module_packagemanager_template) {

            if($objRecord->getIntRecordStatus() == 1) {

                $objOrm = new class_orm_objectlist();
                $objOrm->addWhereRestriction(new class_orm_objectlist_systemstatus_restriction(\Kajona\System\System\OrmComparatorEnum::Equal(), 1));
                $arrPacks = $objOrm->getObjectList("class_module_packagemanager_template");

                foreach($arrPacks as $objPack) {
                    if($objPack->getSystemid() != $objRecord->getSystemid()) {
                        $objPack->setIntRecordStatus(0);
                        $objPack->updateObjectToDb();
                    }
                }

                //update the active-pack constant
                $objSetting = class_module_system_setting::getConfigByName("_packagemanager_defaulttemplate_");
                if($objSetting !== null) {
                    $objSetting->setStrValue($objRecord->getStrName());
                    $objSetting->updateObjectToDb();
                }

                class_cache::flushCache("class_element_portal");
            }
        }

        return true;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDUPDATED, new class_module_packagemanager_recordupdatedlistener());
    }


}

//register the listener
class_module_packagemanager_recordupdatedlistener::staticConstruct();