<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\System;

use Kajona\System\System\Carrier;

/**
 * A simple wrapper object to the deferreds indexer queue
 *
 * @package module_search
 * @since 4.6
 */
class SearchIndexqueue
{


    /**
     * Helper to wrap the queue content loader action
     *
     * @param SearchEnumIndexaction $objAction
     * @param null $intMin
     * @param null $intMax
     *
     * @return array
     */
    public function getRows(SearchEnumIndexaction $objAction, $intMin = null, $intMax = null)
    {
        $strQuery = "SELECT search_queue_systemid FROM "._dbprefix_."search_queue WHERE search_queue_action = ? GROUP BY search_queue_systemid";
        return Carrier::getInstance()->getObjDB()->getPArray($strQuery, array($objAction.""), $intMin, $intMax);
    }

    /**
     * Helper to wrap the queue content loader action
     *
     * @param SearchEnumIndexaction $objAction
     * @param $strSystemid
     *
     * @return array
     */
    public function getRowsBySystemid(SearchEnumIndexaction $objAction, $strSystemid)
    {
        $strQuery = "SELECT search_queue_systemid FROM "._dbprefix_."search_queue WHERE search_queue_action = ? AND search_queue_systemid = ? GROUP BY search_queue_systemid";
        return Carrier::getInstance()->getObjDB()->getPArray($strQuery, array($objAction."", $strSystemid));
    }

    /**
     * Deletes all entries for the given systemid from the queue
     *
     * @param $strSystemid
     *
     * @return bool
     */
    public function deleteBySystemid($strSystemid)
    {
        $strRemove = "DELETE FROM "._dbprefix_."search_queue WHERE search_queue_systemid = ?";
        return Carrier::getInstance()->getObjDB()->_pQuery($strRemove, array($strSystemid));
    }


    /**
     * Deletes all entries matching both, the systemid and the action from the queue
     *
     * @param $strSystemid
     * @param SearchEnumIndexaction $objAction
     *
     * @return bool
     */
    public function deleteBySystemidAndAction($strSystemid, SearchEnumIndexaction $objAction)
    {
        $strRemove = "DELETE FROM "._dbprefix_."search_queue WHERE search_queue_systemid = ? AND search_queue_action = ?";
        return Carrier::getInstance()->getObjDB()->_pQuery($strRemove, array($strSystemid, $objAction.""));
    }

    /**
     * Adds rows to the current queue
     *
     * @param $arrRows array("search_queue_id", "search_queue_systemid", "search_queue_action")
     *
     * @return bool
     */
    public function addRowsToQueue($arrRows)
    {
        return Carrier::getInstance()->getObjDB()->multiInsert("search_queue", array("search_queue_id", "search_queue_systemid", "search_queue_action"), $arrRows);
    }
}
