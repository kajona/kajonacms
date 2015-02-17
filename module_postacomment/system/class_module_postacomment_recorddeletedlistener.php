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


        $strQuery = "SELECT *
					 FROM "._dbprefix_."postacomment,
						  "._dbprefix_."system_right,
						  "._dbprefix_."system
                 LEFT JOIN "._dbprefix_."system_date
                        ON system_id = system_date_id
					 WHERE system_id = postacomment_id
					      AND system_id = right_id
                          AND (postacomment_page = ? OR  postacomment_systemid = ? )
					 ORDER BY postacomment_page ASC,
						      postacomment_language ASC,
							  postacomment_date DESC";

        $arrComments = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid, $strSystemid));

        if(count($arrComments) > 0) {
            foreach($arrComments as $arrOneComment) {
                class_orm_rowcache::addSingleInitRow($arrOneComment);
                $objPost = class_objectfactory::getInstance()->getObject($arrOneComment["system_id"]);
                $objPost->deleteObject();
            }
        }

        return $bitReturn;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, new class_module_postacomment_recorddeletedlistener());
    }

}

class_module_postacomment_recorddeletedlistener::staticConstruct();