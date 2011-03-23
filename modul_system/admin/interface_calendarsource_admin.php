<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_admin.php 3558 2011-01-12 08:42:05Z sidler $                                          *
********************************************************************************************************/

/**
 * This interface indicates whether a single module may be a factory for calendar-events.
 * If given, the dashboard may query implementing classes for entries.
 *
 * @package modul_dashboard
 * @author sidler@mulchprod.de
 * @since 3.4
 */
interface interface_calendarsource_admin {
	
    /**
     * Returns an array of calendar-entries.
     *
     * @param class_date $objStartDate
     * @param class_date $objEndDate
     * @return class_calendarentry
     */
    public function getArrCalendarEntries(class_date $objStartDate, class_date $objEndDate);

    /**
     * Returns an array of entries to be added to the legend.
     * The array should be structured like
     * array("name" => "cssLegendClass")
     *
     * @return array
     */
    public function getArrLegendEntries();

    /**
     * Returns an array of entries to be added to the filter-bar.
     * The calendar stores the filter-criteria to the session.
     * So each criteria returns an two-dim array structured like:
     * array("sessionKey" => "criteria name")
     * If a value was de-selected (!), the session-entry is set to "disabled".
     * All other values represent a checked checkbox.
     *
     * @return array
     */
    public function getArrFilterEntries();

}
?>