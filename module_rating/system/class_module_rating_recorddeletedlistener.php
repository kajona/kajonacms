<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Listener to remove ratings from deleted records
 *
 * @package module_rating
 * @author sidler@mulchprod.de
 */
class class_module_rating_recorddeletedlistener implements interface_genericevent_listener {


    /**
     * Searches for ratings belonging to the systemid
     * to be deleted.
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

        //ratings installed as a module?
        if($strSourceClass == "class_module_rating_rate" || class_module_system_module::getModuleByName("rating") == null)
            return true;

        //ok, so delete matching records
        //fetch the matching ids..
        $strQuery = "SELECT rating_id
                     FROM "._dbprefix_."rating"."
                     WHERE rating_systemid = ? ";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid));

        if(count($arrRows) > 0) {
            foreach($arrRows as $arrOneRow) {
                $objRating = new class_module_rating_rate($arrOneRow["rating_id"]);
                $bitReturn = $bitReturn && $objRating->deleteObject();
            }
        }

        return $bitReturn;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, new class_module_rating_recorddeletedlistener());
    }

}

class_module_rating_recorddeletedlistener::staticConstruct();