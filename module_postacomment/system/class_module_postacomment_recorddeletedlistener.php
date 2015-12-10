<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * Removes comments added to the passed systemid
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 */
class class_module_postacomment_recorddeletedlistener implements interface_genericevent_listener {


    /**
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        $bitReturn = true;
        //module installed?
        if($strSourceClass == "class_module_postacomment_post" || class_module_system_module::getModuleByName("postacomment") == null)
            return true;

        $objOrm = new class_orm_objectlist();
        $objOrm->setObjHandleLogicalDeleted(class_orm_deletedhandling_enum::INCLUDED);
        $objOrm->addWhereRestriction(new class_orm_objectlist_restriction(" AND (postacomment_page = ? OR  postacomment_systemid = ? ) ", array($strSystemid, $strSystemid)));
        $arrComments = $objOrm->getObjectList("class_module_postacomment_post");

        foreach($arrComments as $objPost) {

            if($strEventName == class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY)
                $objPost->deleteObject();

            if($strEventName == class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED)
                $objPost->deleteObjectFromDatabase();

        }

        return $bitReturn;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, new class_module_postacomment_recorddeletedlistener());
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY, new class_module_postacomment_recorddeletedlistener());
    }

}

class_module_postacomment_recorddeletedlistener::staticConstruct();