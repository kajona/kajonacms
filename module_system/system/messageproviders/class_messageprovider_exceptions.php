<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
class class_messageprovider_exceptions implements interface_messageprovider {

    /**
     * Called whenever a message is being deleted
     *
     * @param class_module_messaging_message $objMessage
     */
    public function onDelete(class_module_messaging_message $objMessage) {
        // TODO: Implement onDelete() method.
    }

    /**
     * Called whenever a message is set as read
     *
     * @param class_module_messaging_message $objMessage
     */
    public function onSetRead(class_module_messaging_message $objMessage) {
        // TODO: Implement onSetRead() method.
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
     * Returns a short identifier, mainly used to reference the provider in the config-view
     *
     * @return string
     */
    public function getStrIdentifier() {
        return "exceptions";
    }
}
