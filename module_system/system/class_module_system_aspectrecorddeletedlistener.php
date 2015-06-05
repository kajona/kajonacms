<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Updates the default aspect in case the default one was deleted
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 */
class class_module_system_aspectrecorddeletedlistener implements interface_genericevent_listener {


    /**
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        if($strSourceClass == "class_module_system_aspect") {

            //if we have just one aspect remaining, set this one as default
            if(class_module_system_aspect::getObjectCount() == 1) {
                /** @var class_module_system_aspect[] $arrObjAspects */
                $arrObjAspects = class_module_system_aspect::getObjectList();
                $objOneAspect = $arrObjAspects[0];
                $objOneAspect->setBitDefault(1);
                return $objOneAspect->updateObjectToDb();
            }
        }

        return true;
    }

}

//static inits
class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY, new class_module_system_aspectrecorddeletedlistener());
