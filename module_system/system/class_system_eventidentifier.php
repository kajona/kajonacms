<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
     * The params-array contains a single entry:
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
     *
     * @since 4.5
     */
    const EVENT_SYSTEM_RECORDUPDATED = "core.system.recordupdated";

    /**
     * Called whenever a record was copied.
     * Useful to perform additional actions, e.g. update / duplicate foreign assignments.
     *
     * The params-array contains two entries:
     * @param string $strOldSystemid
     * @param string $strNewSystemid
     *
     * @since 4.5
     */
    const EVENT_SYSTEM_RECORDCOPIED = "core.system.recordcopied";

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
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
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
