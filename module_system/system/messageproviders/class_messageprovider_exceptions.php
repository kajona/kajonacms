<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * The exceptions-messageprovider sends messages in case of exceptions.
 * By default, messages are sent to all members of the admin-group.
 *
 * @author sidler@mulchprod.de
 * @package module_messaging
 * @since 4.0
 */
class class_messageprovider_exceptions implements interface_messageprovider_extended {

    /**
     * Called whenever a message is being deleted
     *
     * @param class_module_messaging_message $objMessage
     */
    public function onDelete(class_module_messaging_message $objMessage) {
    }

    /**
     * Called whenever a message is set as read
     *
     * @param class_module_messaging_message $objMessage
     */
    public function onSetRead(class_module_messaging_message $objMessage) {
    }

    /**
     * Returns the name of the message-provider
     *
     * @return string
     */
    public function getStrName() {
        return class_carrier::getInstance()->getObjLang()->getLang("messageprovider_exceptions_name", "system");
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
        $objAdminGroup = new class_module_user_group(_admins_group_id_);
        return in_array(class_carrier::getInstance()->getObjSession()->getUserID(), $objAdminGroup->getObjSourceGroup()->getUserIdsForGroup());
    }

}
