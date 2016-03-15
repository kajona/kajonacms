<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Postacomment\System\Messageproviders;

use Kajona\System\System\Carrier;
use Kajona\System\System\Messageproviders\MessageproviderInterface;

/**
 * The postacomment message-provider is able to send mails as soon as a new comment is available.
 * By default, all users with edit-permissions of the postacomment-module are notified.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class MessageproviderPostacomment implements MessageproviderInterface
{


    /**
     * Returns the name of the message-provider
     *
     * @return string
     */
    public function getStrName()
    {
        return Carrier::getInstance()->getObjLang()->getLang("messageprovider_postacomment_name", "postacomment");
    }

    /**
     * Returns a short identifier, mainly used to reference the provider in the config-view
     *
     * @return string
     */
    public function getStrIdentifier()
    {
        return "postacomment";
    }
}
