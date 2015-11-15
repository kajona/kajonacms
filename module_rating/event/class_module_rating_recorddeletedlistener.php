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
        if($strSourceClass == "class_module_rating_rate") {
            //delete history entries of the current rating
            return class_carrier::getInstance()->getObjDB()->_pQuery("DELETE FROM "._dbprefix_."rating_history"." WHERE rating_history_rating=? ", array($strSystemid));
        }

        //if another record was deleted, remove the ratings alltogether
        $objOrmList = new class_orm_objectlist();
        $objOrmList->addWhereRestriction(new class_orm_objectlist_property_restriction("strRatingSystemid", class_orm_comparator_enum::Equal(), $strSystemid));
        $arrRatings = $objOrmList->getObjectList("class_module_rating_rate");

        foreach($arrRatings as $objRating) {
            $bitReturn = $bitReturn && $objRating->deleteObjectFromDatabase();
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