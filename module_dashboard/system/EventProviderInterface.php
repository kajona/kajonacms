<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\System;

use Kajona\System\System\GenericPluginInterface;

/**
 * EventProviderInterface
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
interface EventProviderInterface extends GenericPluginInterface {

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
     * @param \Kajona\System\System\Date $objStartDate
     * @param \Kajona\System\System\Date $objEndDate
     * @return EventEntry[]
     */
    public function getEventsByCategoryAndDate($strCategory, \Kajona\System\System\Date $objStartDate, \Kajona\System\System\Date $objEndDate);

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
