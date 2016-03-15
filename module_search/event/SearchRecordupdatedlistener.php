<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\Event;

use Kajona\Search\System\SearchIndexwriter;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\SystemEventidentifier;


/**
 * Handles record-updated events. If catched, the index-entries assigned to the systemid
 * will be updated.
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class SearchRecordupdatedlistener implements GenericeventListenerInterface
{

    public static $BIT_UPDATE_INDEX_ON_END_OF_REQUEST = true;

    /**
     * Triggered as soon as a record is updated
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments)
    {

        $objRecord = $arrArguments[0];

        if (self::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST) {
            SearchRequestEndprocessinglistener::addIdToIndex($objRecord);
        }
        else {
            $objIndex = new SearchIndexwriter();
            $objIndex->indexObject($objRecord);

        }

        return true;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     *
     * @return void
     */
    public static function staticConstruct()
    {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDUPDATED, new SearchRecordupdatedlistener());
    }


}

//register the listener
SearchRecordupdatedlistener::staticConstruct();
