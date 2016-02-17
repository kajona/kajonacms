<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Messageproviders;

/**
 * The extended interface adds some special configuration options to messageproviders.
 * This includes whether it is allowed to switch of a messageprovider or some default values, such as enabled by mail by default.
 *
 * @author sidler@mulchprod.de
 * @since 4.3
 * @package module_messaging
 */
interface MessageproviderExtendedInterface extends MessageproviderInterface {

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

    /**
     * This method is queried when the config-view is rendered.
     * It controls whether a message-provider is shown in the config-view or not.
     *
     * @return bool
     * @since 4.5
     */
    public function isVisibleInConfigView();

}