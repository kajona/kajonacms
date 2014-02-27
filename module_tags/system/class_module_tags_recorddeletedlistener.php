<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
class class_module_tags_recorddeletedlistener implements interface_genericevent_listener {


    /**
     * Searches for tags assigned to the systemid to be deleted.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        if($strSourceClass == "class_module_tags_tag" || class_module_system_module::getModuleByName("tags") == null)
            return true;

        //delete memberships. Fire a plain query, faster then searching.
        $strQuery = "DELETE FROM "._dbprefix_."tags_member WHERE tags_systemid=?";
        $bitReturn = class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strSystemid));

        return $bitReturn;
    }



    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, new class_module_tags_recorddeletedlistener());
    }


}

class_module_tags_recorddeletedlistener::staticConstruct();