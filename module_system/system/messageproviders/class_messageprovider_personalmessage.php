<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
class class_messageprovider_personalmessage implements interface_messageprovider {

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

}
