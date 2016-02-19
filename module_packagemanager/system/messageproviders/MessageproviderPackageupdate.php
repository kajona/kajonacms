<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Packagemanager\System\Messageproviders;

use Kajona\System\System\Carrier;
use Kajona\System\System\Messageproviders\MessageproviderExtendedInterface;
use Kajona\System\System\SystemModule;


/**
 * The exceptions-messageprovider sends messages in case of exceptions.
 * By default, messages are sent to all members of the admin-group.
 *
 * @author sidler@mulchprod.de
 * @package module_messaging
 * @since 4.0
 */
class MessageproviderPackageupdate implements MessageproviderExtendedInterface
{


    /**
     * Returns the name of the message-provider
     *
     * @return string
     */
    public function getStrName()
    {
        return Carrier::getInstance()->getObjLang()->getLang("messageprovider_packageupdate_name", "messaging");
    }

    /**
     * If set to true, the messageprovider may not be disabled by the user.
     * Messages are always sent to the user.
     *
     * @return bool
     */
    public function isAlwaysActive()
    {
        return false;
    }

    /**
     * If set to true, all messages sent by this provider will be sent by mail, too.
     * The user is not allowed to disable the by-mail flag.
     * Set this to true with care.
     *
     * @return mixed
     */
    public function isAlwaysByMail()
    {
        return false;
    }

    /**
     * This method is queried when the config-view is rendered.
     * It controls whether a message-provider is shown in the config-view or not.
     *
     * @return bool
     * @since 4.5
     */
    public function isVisibleInConfigView()
    {
        return SystemModule::getModuleByName("packagemanager")->rightView();
    }

}
