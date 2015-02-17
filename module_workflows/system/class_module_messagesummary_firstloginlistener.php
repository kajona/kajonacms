<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Listener to handle first logins of users.
 * The event is used to enable the message summary be default. may be set inactive by the user afterwards.
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 */
class class_module_messagesummary_firstloginlistener implements interface_genericevent_listener {

    /**
     * handles the event
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        list($strUserid) = $arrArguments;

        //set the messagesummary active and by mail by default
        $objConfig = class_module_messaging_config::getConfigForUserAndProvider($strUserid, new class_messageprovider_summary());
        $objConfig->setBitBymail(true);
        $objConfig->setBitEnabled(true);
        return $objConfig->updateObjectToDb();
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_USERFIRSTLOGIN, new class_module_messagesummary_firstloginlistener());
    }

}

class_module_messagesummary_firstloginlistener::staticConstruct();
