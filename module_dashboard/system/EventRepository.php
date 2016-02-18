<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\System;

use Kajona\System\System\Pluginmanager;

/**
 * Repository class which provides all methods to retrieve event entries
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
class EventRepository
{
    /**
     * Internal cache
     *
     * @var array
     */
    protected static $categories = array();

    /**
     * Internal cache
     *
     * @var array
     */
    protected static $events = array();

    /**
     * Uses the pluginmanager to query all event provider to get a list of events for the specific category and date
     *
     * @return EventEntry[]
     */
    public static function getEventsByCategoryAndDate($strCategory, \Kajona\System\System\Date $objStartDate, \Kajona\System\System\Date $objEndDate)
    {
        $strKey = $strCategory . substr($objStartDate->getLongTimestamp(), 0, 8) . substr($objEndDate->getLongTimestamp(), 0, 8);
        if (isset(self::$events[$strKey])) {
            return self::$events[$strKey];
        }

        $objPluginManager = new Pluginmanager(EventProviderInterface::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        /** @var EventEntry[] $arrEvents */
        $arrEvents = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof EventProviderInterface && $objPlugin->rightView()) {
                $arrEvents = array_merge($arrEvents, $objPlugin->getEventsByCategoryAndDate($strCategory, $objStartDate, $objEndDate));
            }
        }

        self::sortEvents($arrEvents);

        return self::$events[$strKey] = $arrEvents;
    }

    /**
     * Returns all available open events
     *
     * @return EventEntry[]
     */
    public static function getAllEventsByDate(\Kajona\System\System\Date $objDate)
    {
        $arrCategories = self::getAllCategories();
        $arrEvents = array();

        foreach ($arrCategories as $strTitle => $arrCategory) {
            foreach ($arrCategory as $strKey => $strCategoryName) {
                $arrEvents = array_merge($arrEvents, self::getEventsByCategoryAndDate($strKey, $objDate));
            }
        }

        self::sortEvents($arrEvents);

        return $arrEvents;
    }

    /**
     * Returns all available categories
     *
     * @return array
     */
    public static function getAllCategories()
    {
        if (self::$categories) {
            return self::$categories;
        }

        $objPluginManager = new Pluginmanager(EventProviderInterface::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        $arrCategories = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof EventProviderInterface && $objPlugin->rightView()) {
                $arrCategories[$objPlugin->getName()] = $objPlugin->getCategories();
            }
        }

        return self::$categories = $arrCategories;
    }

    /**
     * Sorts all events after the valid date
     *
     * @param array $arrEvents
     */
    protected static function sortEvents(array &$arrEvents)
    {
        usort($arrEvents, function(EventEntry $objEntryA, EventEntry $objEntryB){
            $intA = $objEntryA->getObjValidDate() instanceof \Kajona\System\System\Date ? $objEntryA->getObjValidDate()->getTimeInOldStyle() : 0;
            $intB = $objEntryB->getObjValidDate() instanceof \Kajona\System\System\Date ? $objEntryB->getObjValidDate()->getTimeInOldStyle() : 0;
            if ($intA == $intB) {
                return 0;
            }
            return ($intA < $intB) ? -1 : 1;
        });
    }
}
