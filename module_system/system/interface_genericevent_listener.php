<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/


/**
 * The generic event-listener may be used to listen to events even
 * if the implementing interface may not be available in the current system-setup.
 * This is useful if you want to listen to events fired by other modules without using
 * a hard implements-dependency.
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.5
 */
interface interface_genericevent_listener {

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
    public function handleEvent($strEventIdentifier, array $arrArguments);

}
