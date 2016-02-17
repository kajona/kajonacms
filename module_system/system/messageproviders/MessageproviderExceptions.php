<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Messageproviders;

use Kajona\System\System\Carrier;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\UserGroup;


/**
 * The exceptions-messageprovider sends messages in case of exceptions.
 * By default, messages are sent to all members of the admin-group.
 *
 * @author sidler@mulchprod.de
 * @package module_messaging
 * @since 4.0
 */
class MessageproviderExceptions implements MessageproviderExtendedInterface {



    /**
     * Returns the name of the message-provider
     *
     * @return string
     */
    public function getStrName() {
        return Carrier::getInstance()->getObjLang()->getLang("messageprovider_exceptions_name", "system");
    }

    /**
     * If set to true, the messageprovider may not be disabled by the user.
     * Messages are always sent to the user.
     *
     * @return bool
     */
    public function isAlwaysActive() {
        return false;
    }

    /**
     * If set to true, all messages sent by this provider will be sent by mail, too.
     * The user is not allowed to disable the by-mail flag.
     * Set this to true with care.
     *
     * @return mixed
     */
    public function isAlwaysByMail() {
        return false;
    }

    /**
     * This method is queried when the config-view is rendered.
     * It controls whether a message-provider is shown in the config-view or not.
     *
     * @return mixed
     * @since 4.5
     */
    public function isVisibleInConfigView() {
        $objAdminGroup = new UserGroup(SystemSetting::getConfigValue("_admins_group_id_"));
        return in_array(Carrier::getInstance()->getObjSession()->getUserID(), $objAdminGroup->getObjSourceGroup()->getUserIdsForGroup());
    }

}
