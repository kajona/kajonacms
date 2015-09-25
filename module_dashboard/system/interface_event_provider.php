<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * interface_event_provider
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
interface interface_event_provider extends interface_generic_plugin {

    const EXTENSION_POINT = "core.dashboard.admin.event_provider";

    /**
     * Returns an human readable name of this provider
     *
     * @return string
     */
    public function getName();

    /**
     * Returns all events for a specific date. This includes also events which are completed in the past. This is used
     * to display i.e. events on a calendar
     *
     * @param string $strCategory
     * @param class_date $objStartDate
     * @param class_date $objEndDate
     * @return class_event_entry[]
     */
    public function getEventsByCategoryAndDate($strCategory, class_date $objStartDate, class_date $objEndDate);

    /**
     * Returns an array of all available categories
     *
     * @return array
     */
    public function getCategories();

    /**
     * Returns whether the currently logged in user can view these events
     *
     * @return boolean
     */
    public function rightView();

}
