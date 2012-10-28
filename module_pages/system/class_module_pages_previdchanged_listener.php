<?php

/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                              *
********************************************************************************************************/

/**
 * Updates the navigation path for pages when moved.
 *
 * @package module_system
 * @author ph.wolfer@gmail.com
 */
class class_module_pages_previdchanged_listener implements interface_previdchanged_listener {
    
    /**
     * Callback-method invoked every time a records previd was changed.
     * Please note that the event is only triggered on changes, not during a records creation.
     *
     * @abstract
     *
     * @param $strSystemid
     * @param $strOldPrevId
     * @param $strNewPrevid
     *
     * @return mixed
     */
    public function handlePrevidChangedEvent($strSystemid, $strOldPrevId, $strNewPrevid) {
        if ($strOldPrevId == $strNewPrevid) {
            return;
        }
        
        $objInstance = class_objectfactory::getInstance()->getObject($strSystemid);
        
        if ($objInstance instanceof class_module_pages_page) {
            $objInstance->updateObjectToDb();
        }
    }
}

?>
