<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\Event;

use Kajona\Dashboard\Service\DashboardInitializerService;
use Kajona\Dashboard\System\ServiceProvider;
use Kajona\System\System\Carrier;
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

        //Fetch the service to init the new dashboard
        /** @var DashboardInitializerService $objService */
        $objService = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_DASHBOARD_INITIALIZER);
        return $objService->createInitialDashboard($strUserid);
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
