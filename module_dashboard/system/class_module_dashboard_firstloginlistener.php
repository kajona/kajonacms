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
 * The event is used to create an initial set of widgets on the users' dashboard.
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 *
 */
class class_module_dashboard_firstloginlistener implements interface_genericevent_listener {

    /**
     * handles the event
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        list($strUserid) = $arrArguments;

        $bitReturn = true;

        //get all widgets and call them in order
        $arrWidgets = class_module_dashboard_widget::getListOfWidgetsAvailable();
        foreach($arrWidgets as $strOneWidgetClass) {
            /** @var $objInstance interface_adminwidget */
            $objInstance = new $strOneWidgetClass();
            $objInstance->onFistLogin($strUserid);
        }

        return $bitReturn;
    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_USERFIRSTLOGIN, new class_module_dashboard_firstloginlistener());
    }

}

class_module_dashboard_firstloginlistener::staticConstruct();


