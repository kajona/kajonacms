<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * The extended interface adds some special configuration options to messageproviders.
 * This includes whether it is allowed to switch of a messageprovider or some default values, such as enabled by mail by default.
 *
 * @author sidler@mulchprod.de
 * @since 4.3
 * @package module_messaging
 */
interface interface_messageprovider_extended extends interface_messageprovider {

    /**
     * If set to true, the messageprovider may not be disabled by the user.
     * Messages are always sent to the user.
     *
     * @return bool
     */
    public function isAlwaysActive();

    /**
     * If set to true, all messages sent by this provider will be sent by mail, too.
     * The user is not allowed to disable the by-mail flag.
     * Set this to true with care.
     *
     * @return mixed
     */
    public function isAlwaysByMail();
}