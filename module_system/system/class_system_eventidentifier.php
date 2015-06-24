<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

/**
 * Central namespace for all events thrown and managed by the core / module system.
 * This list shows only the events of the core, other modules may provide additional events.
 *
 * @package module_system
 * @since 4.5
 */
interface class_system_eventidentifier {


    /**
     * Invoked right before starting to process the current request. The event is triggered
     * by the request-dispatcher right before the request is given over to the controller.
     *
     * The params-array contains four entries:
     * @param bool $bitAdmin
     * @param string $strModule
     * @param string $strAction
     * @param string $strLanguageParam
     *
     * @since 4.6
     */
    const EVENT_SYSTEM_REQUEST_STARTPROCESSING = "core.system.request.startprocessing";

    /**
     * Invoked right before finishing to process the current request. The event is triggered
     * by the request-dispatcher right before closing the session and passing back the response object.
     * You may modify the request by accessing the response-object.
     *
     * The params-array contains four entries:
     * @param bool $bitAdmin
     * @param string $strModule
     * @param string $strAction
     * @param string $strLanguageParam
     *
     * @since 4.6
     */
    const EVENT_SYSTEM_REQUEST_ENDPROCESSING = "core.system.request.endprocessing";

    /**
     * Invoked right after sending the response back to the browser, but before starting to
     * shut down the request.
     * This means you are not able to change the response anymore, also the session is already closed to
     * keep other threads from waiting. Use this event to perform internal cleanups if required.
     *
     * @param class_request_entrypoint_enum $objEntrypoint
     *
     * @since 4.6
     */
    const EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND = "core.system.request.aftercontentsend";

    /**
     * The event is triggered after the source-object was updated to the database.
     *
     * The params-array contains a single entry:
     * @param class_model $objRecord
     * @param bool $bitRecordCreated - true => if the record was created, false => if it is only an update
     *
     * @since 4.5
     */
    const EVENT_SYSTEM_RECORDUPDATED = "core.system.recordupdated";

    /**
     * Triggered as soon as a property mapping to objects is updated. Therefore the event is triggered as soon
     * as assignments are added or removed from an object.
     * The event gets a list of all three relevant items: assignments added, assignments removed, assignments remaining.
     * The relevant object and the name of the changed property are passed, too.
     * Return a valid bool value, otherwise the transaction will be rolled back!
     *
     * The params-array contains the following entries:
     * @param string[] $arrNewAssignments
     * @param string[] $areRemovedAssignments
     * @param string[] $areCurrentAssignments
     * @param class_root $objObject
     * @param string $strProperty
     *
     * @return bool
     *
     * @since 4.7
     */
    const EVENT_SYSTEM_OBJECTASSIGNMENTSUPDATED = "core.system.objectassignmentsupdated";

    /**
     * Called whenever a record was copied.
     * Event will be fired BEFORE child objects are being copied.
     * Useful to perform additional actions, e.g. update / duplicate foreign assignments.
     *
     * The params-array contains two entries:
     * @param string $strOldSystemid
     * @param string $strNewSystemid
     * @param class_model $objNewObjectCopy
     *
     * @since 4.5
     */
    const EVENT_SYSTEM_RECORDCOPIED = "core.system.recordcopied";


    /**
     * Called whenever a record was copied.
     * Event will be fired AFTER child objects were copied.
     * Useful to perform additional actions, e.g. update / duplicate foreign assignments.
     *
     * The params-array contains two entries:
     * @param string $strOldSystemid
     * @param string $strNewSystemid
     * @param class_model $objNewObjectCopy
     *
     * @since 4.6
     */
    const EVENT_SYSTEM_RECORDCOPYFINISHED = "core.system.recordcopyfinished";


    /**
     * Invoked every time a records previd was changed.
     * Please note that the event is only triggered on changes, not during a records creation.
     *
     * @param string $strSystemid
     * @param string $strOldPrevId
     * @param string $strNewPrevid
     *
     * @since 4.5
     */
    const EVENT_SYSTEM_PREVIDCHANGED = "core.system.previdchanged";


    /**
     * Invoked every time a records status was changed.
     * Please note that the event is only triggered on changes, not during a records creation.
     *
     * @param string $strSystemid
     * @param class_root $objObject
     * @param string $intOldStatus
     * @param string $intNewStatus
     *
     * @since 4.8
     */
    const EVENT_SYSTEM_STATUSCHANGED = "core.system.statuschanged";

    /**
     * Called whenever a records was deleted from the database using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to perform additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     *
     * @param string $strSystemid
     * @param string $strSourceClass The class-name of the object deleted
     *
     * @since 4.5
     */
    const EVENT_SYSTEM_RECORDDELETED = "core.system.recorddeleted";

    /**
     * Called whenever a records was deleted logically, so set inactive. The record is NOT removed from the database!
     *
     * Implement this method to be notified when a record is deleted, e.g. to perform additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     *
     * @param string $strSystemid
     * @param string $strSourceClass The class-name of the object deleted
     *
     * @since 4.8
     */
    const EVENT_SYSTEM_RECORDDELETED_LOGICALLY = "core.system.recorddeleted.logically";

    /**
     * Called whenever a records is restored from the database.
     * The event is fired after the record was restored but before the transaction will be committed.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     *
     * @param string $strSystemid
     * @param string $strSourceClass The class-name of the object deleted
     * @param class_model $objObject The object which is being restored
     *
     * @since 4.8
     */
    const EVENT_SYSTEM_RECORDRESTORED_LOGICALLY = "core.system.recordrestored.logically";

    /**
     * Callback method, triggered each time a user logs into the system for the very first time.
     * May be used to trigger actions or initial setups for the user.
     *
     * @param string $strUserid
     *
     * @return bool
     * @since 4.5
     */
    const EVENT_SYSTEM_USERFIRSTLOGIN = "core.system.userfirstlogin";

    /**
     * Callback method, triggered each time a user logs into the system.
     * May be used to trigger individual actions for the user.
     *
     * @param string $strUserid
     *
     * @return bool
     * @since 4.5
     */
    const EVENT_SYSTEM_USERLOGIN = "core.system.userlogin";

    /**
     * Callback method, triggered each time a user logs out of the system.
     * May be used to trigger actions like cleanup actions for the user.
     *
     * @param string $strUserid
     *
     * @return bool
     * @since 4.5
     */
    const EVENT_SYSTEM_USERLOGOUT = "core.system.userlogout";
}
