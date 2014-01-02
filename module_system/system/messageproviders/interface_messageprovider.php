<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * A message-provider is used to emit and to process messages, e.g. from modules to a user or a group.
 * Please be aware, that messages sent to a group are duplicated for each member automatically.
 *
 * A single message-provider is responsible to react on special events in the lifecycle of a message.
 * This includes the deletion of a message or the "set read" status change.
 * Fetching this events could be useful if you want to delete depending messages or other scenarios.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_messaging
 */
interface interface_messageprovider {

    /**
     * Called whenever a message is being deleted
     *
     * @abstract
     * @param class_module_messaging_message $objMessage
     */
    public function onDelete(class_module_messaging_message $objMessage);

    /**
     * Called whenever a message is set as read
     *
     * @abstract
     * @param class_module_messaging_message $objMessage
     */
    public function onSetRead(class_module_messaging_message $objMessage);

    /**
     * Returns the name of the message-provider
     *
     * @abstract
     * @return string
     */
    public function getStrName();

}