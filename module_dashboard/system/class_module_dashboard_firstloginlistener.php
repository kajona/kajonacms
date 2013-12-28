<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
class class_module_dashboard_firstloginlistener implements interface_userfirstlogin_listener {



    /**
     * Callback method, triggered each time a user logs into the system for the very first time.
     * May be used to trigger actions or initial setups for the user.
     *
     * @param string $strUserid
     *
     * @return bool
     */
    public function handleUserFirstLoginEvent($strUserid) {
        $bitReturn = true;

        //get all widgets and call them in order
        $arrWidgets = class_module_dashboard_widget::getListOfWidgetsAvailable();
        foreach($arrWidgets as $strOneWidgetClass) {
            /** @var $objInstance interface_adminwidget */
            $objInstance = new $strOneWidgetClass();

            $bitReturn = $bitReturn && $objInstance->onFistLogin($strUserid);

        }

        return $bitReturn;
    }



}


