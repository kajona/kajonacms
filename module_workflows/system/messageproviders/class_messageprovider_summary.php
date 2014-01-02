<?php
/*"******************************************************************************************************
*   (c) 2013-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/


/**
 * The summary message creates an overview of unread messages and sends them to the user.
 * In most cases this only makes sense if sent by mail.
 *
 * @author sidler@kajona.de
 * @package module_workflows
 * @since 4.5
 */
class class_messageprovider_summary implements interface_messageprovider {

    /**
     * Called whenever a message is being deleted
     *
     * @param class_module_messaging_message $objMessage
     * @return void
     */
    public function onDelete(class_module_messaging_message $objMessage) {
    }

    /**
     * Called whenever a message is set as read
     *
     * @param class_module_messaging_message $objMessage
     * @return void
     */
    public function onSetRead(class_module_messaging_message $objMessage) {
    }

    /**
     * Returns the name of the message-provider
     *
     * @return string
     */
    public function getStrName() {
        return class_carrier::getInstance()->getObjLang()->getLang("messageprovider_workflows_summary", "workflows");
    }

}
