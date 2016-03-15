<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Eventmanager\System\Messageproviders;
use Kajona\System\System\Carrier;
use Kajona\System\System\Messageproviders\MessageproviderInterface;


/**
 * The eventmanager message-provider is able to send mails as soon as a new participant registered for a given event.
 * By default, all users with edit-permissions of the guestbook-module are notified.
 *
 * @author sidler@mulchprod.de
 * @package module_eventmanager
 * @since 4.2
 */
class MessageproviderEventmanager implements MessageproviderInterface {



    /**
     * Returns the name of the message-provider
     *
     * @return string
     */
    public function getStrName() {
        return Carrier::getInstance()->getObjLang()->getLang("messageprovider_eventmanager_name", "eventmanager");
    }

    /**
     * Returns a short identifier, mainly used to reference the provider in the config-view
     *
     * @return string
     */
    public function getStrIdentifier() {
        return "eventmanager";
    }
}
