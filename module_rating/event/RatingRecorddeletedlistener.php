<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Rating\Event;

use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmPropertyCondition;
use Kajona\System\System\SystemEventidentifier;


/**
 * Listener to remove ratings from deleted records
 *
 * @package module_rating
 * @author sidler@mulchprod.de
 */
class RatingRecorddeletedlistener implements GenericeventListenerInterface {


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
        if($strSourceClass == "Kajona\\Rating\\System\\RatingRate") {
            //delete history entries of the current rating
            return Carrier::getInstance()->getObjDB()->_pQuery("DELETE FROM "._dbprefix_."rating_history"." WHERE rating_history_rating=? ", array($strSystemid));
        }

        //if another record was deleted, remove the ratings alltogether
        $objOrmList = new OrmObjectlist();
        $objOrmList->addWhereRestriction(new OrmPropertyCondition("strRatingSystemid", \Kajona\System\System\OrmComparatorEnum::Equal(), $strSystemid));
        $arrRatings = $objOrmList->getObjectList("Kajona\\Rating\\System\\RatingRate");

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
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, new RatingRecorddeletedlistener());
    }

}

RatingRecorddeletedlistener::staticConstruct();