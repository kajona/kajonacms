<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/


/**
 * Defines all methods a previd-changed-listener should implement in order to react on those events
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.0
 */
interface interface_previdchanged_listener {

    /**
     * Callback-method invoked every time a records previd was changed.
     * Please note that the event is only triggered on changes, not during a records creation.
     *
     * @param string $strSystemid
     * @param string $strOldPrevId
     * @param string $strNewPrevid
     *
     * @abstract
     * @return mixed
     */
    public function handlePrevidChangedEvent($strSystemid, $strOldPrevId, $strNewPrevid);

}
