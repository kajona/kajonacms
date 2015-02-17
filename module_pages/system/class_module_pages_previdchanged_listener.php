<?php

/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                              *
********************************************************************************************************/

/**
 * Updates the navigation path for pages when moved.
 *
 * @package module_system
 * @author ph.wolfer@gmail.com
 */
class class_module_pages_previdchanged_listener implements interface_genericevent_listener {

    /**
     * Callback-method invoked every time a records previd was changed.
     * Please note that the event is only triggered on changes, not during a records creation.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strOldPrevId, $strNewPrevid) = $arrArguments;

        if ($strOldPrevId == $strNewPrevid) {
            return;
        }
        
        $objInstance = class_objectfactory::getInstance()->getObject($strSystemid);
        
        if ($objInstance instanceof class_module_pages_page) {
            $objInstance->updateObjectToDb();
        }
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_PREVIDCHANGED, new class_module_pages_previdchanged_listener());
    }
}

//register the listener
class_module_pages_previdchanged_listener::staticConstruct();
