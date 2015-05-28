<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Removes category-assignments on record-deletions
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 *
 */
class class_module_faqs_recorddeletedlistener implements interface_genericevent_listener {


    /**
     * Searches for tags assigned to the systemid to be deleted.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        if($strSourceClass == "class_module_faqs_category") {
            return class_carrier::getInstance()->getObjDB()->_pQuery("DELETE FROM "._dbprefix_."faqs_member WHERE faqsmem_category = ? ", array($strSystemid));
        }
        return true;
    }



    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, new class_module_faqs_recorddeletedlistener());
    }


}

class_module_faqs_recorddeletedlistener::staticConstruct();