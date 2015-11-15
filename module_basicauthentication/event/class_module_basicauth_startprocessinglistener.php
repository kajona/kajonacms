<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Listener processing basic http authentication headers.
 * If passed, the systems tries to log in the user.
 *
 * @package module_basicauth
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class class_module_basicauth_startprocessinglistener implements interface_genericevent_listener {


    /**
     * Handles the event as dispatched by the request-dispatcher
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool|void
     */
    public function handleEvent($strEventName, array $arrArguments) {

        $objSession = class_carrier::getInstance()->getObjSession();

        if(isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"]) && !$objSession->isLoggedin()) {
            $objSession->login($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]);
        }

        return true;
    }

}
class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_STARTPROCESSING, new class_module_basicauth_startprocessinglistener());