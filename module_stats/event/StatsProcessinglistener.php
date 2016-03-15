<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Stats\Event;

use Kajona\Stats\System\StatsWorker;
use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\SystemModule;


/**
 * An eventlistener to handle events of type core.system.request.aftercontentsend. Creates
 * an entry in the stats data table
 *
 *
 * @package module_stats
 * @since 4.6
 * @author sidler@mulchprod.de
 */
class StatsProcessinglistener implements GenericeventListenerInterface
{

    /**
     * This generic method is called in case of dispatched events.
     * The first param is the name of the event, the second argument is an array of
     * event-specific arguments.
     * Make sure to return a matching boolean value, indicating if the event-process was successful or not. The event source may
     * depend on a valid return value.
     *
     * @param string $strEventIdentifier
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventIdentifier, array $arrArguments)
    {
        /** @var RequestEntrypointEnum $objEntrypoint */
        $objEntrypoint = $arrArguments[0];

        if ($objEntrypoint->equals(RequestEntrypointEnum::INDEX()) && Carrier::getInstance()->getParam("admin") == "") {

            //process stats request
            $objStats = SystemModule::getModuleByName("stats");
            if ($objStats != null) {
                //Collect Data
                $objLanguage = new LanguagesLanguage();
                $objStats = new StatsWorker();
                $objStats->createStatsEntry(
                    getServer("REMOTE_ADDR"), time(), Carrier::getInstance()->getParam("page"), rtrim(getServer("HTTP_REFERER"), "/"), getServer("HTTP_USER_AGENT"), $objLanguage->getPortalLanguage()
                );

            }
        }
    }

}

CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, new StatsProcessinglistener());
