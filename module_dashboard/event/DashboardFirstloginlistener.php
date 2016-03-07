<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\Event;

use Kajona\Dashboard\Admin\Widgets\AdminwidgetInterface;
use Kajona\Dashboard\System\DashboardWidget;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\SystemEventidentifier;

/**
 * Listener to handle first logins of users.
 * The event is used to create an initial set of widgets on the users' dashboard.
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 *
 */
class DashboardFirstloginlistener implements GenericeventListenerInterface
{

    /**
     * handles the event
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments)
    {
        list($strUserid) = $arrArguments;

        $bitReturn = true;

        //get all widgets and call them in order
        $arrWidgets = DashboardWidget::getListOfWidgetsAvailable();
        foreach ($arrWidgets as $strOneWidgetClass) {
            /** @var $objInstance AdminwidgetInterface */
            $objInstance = new $strOneWidgetClass();
            $objInstance->onFistLogin($strUserid);
        }

        return $bitReturn;
    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     *
     * @return void
     */
    public static function staticConstruct()
    {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_USERFIRSTLOGIN, new DashboardFirstloginlistener());
    }

}

DashboardFirstloginlistener::staticConstruct();


