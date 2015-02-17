<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/


/**
 * Interface to be implemented by listeners on users' first logins
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.0
 *
 * @deprecated migrate to the generic approach
 * @see interface_genericevent_listener
 */
interface interface_userfirstlogin_listener {

    /**
     * Callback method, triggered each time a user logs into the system for the very first time.
     * May be used to trigger actions or initial setups for the user.
     *
     * @param string $strUserid
     *
     * @return bool
     */
    public function handleUserFirstLoginEvent($strUserid);

}
