<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Removes tag-assignments on record-deletions
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 *
 */
class class_module_tags_recorddeletedlistener implements interface_recorddeleted_listener {


    /**
     * Searches for tags assigned to the systemid to be deleted.
     *
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param string $strSystemid
     * @param string $strSourceClass
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid, $strSourceClass) {
        if($strSourceClass == "class_module_tags_tag" || class_module_system_module::getModuleByName("tags") == null)
            return true;

        //delete memberships. Fire a plain query, faster then searching.
        $strQuery = "DELETE FROM "._dbprefix_."tags_member WHERE tags_systemid=?";
        $bitReturn = class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strSystemid));

        return $bitReturn;
    }




}
