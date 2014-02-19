<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * This messageprovider may be used to send messages directly to a user, so with
 * a kind of "direct messaging" style.
 *
 * @author sidler@mulchprod.de
 * @package module_messaging
 * @since 4.3
 */
class class_messageprovider_personalmessage implements interface_messageprovider_extended {

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
        return class_carrier::getInstance()->getObjLang()->getLang("messageprovider_personalmessage_name", "system");
    }

    /**
     * If set to true, the messageprovider may not be disabled by the user.
     * Messages are always sent to the user.
     *
     * @return bool
     */
    public function isAlwaysActive() {
        return true;
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
        return true;
    }

}
