<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * An eventlistener to handle events of type core.system.request.aftercontentsend. Creates
 * an entry in the stats data table
 *
 *
 * @package module_stats
 * @since 4.6
 * @author sidler@mulchprod.de
 */
class class_module_stats_processinglistener implements interface_genericevent_listener {

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
    public function handleEvent($strEventIdentifier, array $arrArguments) {
        /** @var class_request_entrypoint_enum $objEntrypoint */
        $objEntrypoint = $arrArguments[0];

        if($objEntrypoint->equals(class_request_entrypoint_enum::INDEX()) && class_carrier::getInstance()->getParam("admin") == "") {

            //process stats request
            $objStats = class_module_system_module::getModuleByName("stats");
            if($objStats != null) {
                //Collect Data
                $objLanguage = new class_module_languages_language();
                $objStats = new class_module_stats_worker();
                $objStats->createStatsEntry(
                    getServer("REMOTE_ADDR"), time(), class_carrier::getInstance()->getParam("page"), rtrim(getServer("HTTP_REFERER"), "/"), getServer("HTTP_USER_AGENT"), $objLanguage->getPortalLanguage()
                );


            }
        }
    }

}
class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, new class_module_stats_processinglistener());
