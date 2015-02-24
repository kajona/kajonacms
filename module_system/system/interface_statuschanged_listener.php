<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * Defines all methods a status-changed-listener should implement in order to react on those events
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.0
 *
 * @deprecated migrate to the generic approach
 * @see interface_genericevent_listener
 */
interface interface_statuschanged_listener {

    /**
     * Called whenever a records' status was changed by the model.
     * Implement this method to be notified when a status is changed.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param string $strSystemid
     * @param int $intNewStatus
     *
     * @abstract
     * @return bool
     */
    public function handleStatusChangedEvent($strSystemid, $intNewStatus);

}
