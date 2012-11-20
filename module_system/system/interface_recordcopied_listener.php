<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/


/**
 * Defines all methods a record-copied-changed-listener should implement in order to react on those events
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.0
 */
interface interface_recordcopied_listener {

    /**
     * Called whenever a record was copied.
     * Useful to perform additional actions, e.g. update / duplicate foreign assignments.
     *
     * @abstract
     *
     * @param $strOldSystemid
     * @param $strNewSystemid
     *
     * @internal param $strSystemid
     * @internal param $intNewStatus
     * @return bool
     */
    public function handleRecordCopiedEvent($strOldSystemid, $strNewSystemid);

}
