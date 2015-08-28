<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Interface
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
interface interface_todo_provider extends interface_generic_plugin {

    const EXTENSION_POINT = "core.dashboard.admin.todo_event";

    /**
     * Returns all todo entries which are available in the given date range
     *
     * @param class_date $objStartDate
     * @param class_date $objEndDate
     * @return class_todo_entry[]
     */
    public function getEvents(class_date $objStartDate, class_date $objEndDate);

    /**
     * Returns all todo entries for a specific category and the given date range
     *
     * @param $strCategory
     * @param class_date $objStartDate
     * @param class_date $objEndDate
     * @return mixed
     */
    public function getEventsByCategory($strCategory, class_date $objStartDate, class_date $objEndDate);

    /**
     * Returns an array of all available categories
     *
     * @return array
     */
    public function getCategories();

}
