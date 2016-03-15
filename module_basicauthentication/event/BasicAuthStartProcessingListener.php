<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\BasicAuthentication\Event;

use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\SystemEventidentifier;

/**
 * Listener processing basic http authentication headers.
 * If passed, the systems tries to log in the user.
 *
 * @package module_basicauth
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class BasicAuthStartProcessingListener implements GenericeventListenerInterface
{
    /**
     * Handles the event as dispatched by the request-dispatcher
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool|void
     */
    public function handleEvent($strEventName, array $arrArguments) {

        $objSession = Carrier::getInstance()->getObjSession();

        if(isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"]) && !$objSession->isLoggedin()) {
            $objSession->login($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]);
        }

        return true;
    }
}

CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_REQUEST_STARTPROCESSING, new BasicAuthStartProcessingListener());
