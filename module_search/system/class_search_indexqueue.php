<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_xml.php 6322 2014-01-02 08:31:49Z sidler $                                         *
********************************************************************************************************/

/**
 * A simple wrapper object to the deferreds indexer queue
 *
 * @package module_search
 * @since 4.6
 */
class class_search_indexqueue {


    /**
     * Helper to wrap the queue content loader action
     * @param class_search_enum_indexaction $objAction
     * @param null $intMin
     * @param null $intMax
     *
     * @return array
     */
    public function getRows(class_search_enum_indexaction $objAction, $intMin = null, $intMax = null) {
        $strQuery = "SELECT search_queue_systemid FROM "._dbprefix_."search_queue WHERE search_queue_action = ? GROUP BY search_queue_systemid";
        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($objAction.""), $intMin, $intMax);
    }

    /**
     * Helper to wrap the queue content loader action
     *
     * @param class_search_enum_indexaction $objAction
     * @param $strSystemid
     *
     * @return array
     */
    public function getRowsBySystemid(class_search_enum_indexaction $objAction, $strSystemid) {
        $strQuery = "SELECT search_queue_systemid FROM "._dbprefix_."search_queue WHERE search_queue_action = ? AND search_queue_systemid = ? GROUP BY search_queue_systemid";
        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($objAction."",$strSystemid));
    }

    /**
     * Deletes all entries for the given systemid from the queue
     * @param $strSystemid
     *
     * @return bool
     */
    public function deleteBySystemid($strSystemid) {
        $strRemove = "DELETE FROM "._dbprefix_."search_queue WHERE search_queue_systemid = ?";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strRemove, array($strSystemid));
    }


    /**
     * Deletes all entries matching both, the systemid and the action from the queue
     *
     * @param $strSystemid
     * @param class_search_enum_indexaction $objAction
     *
     * @return bool
     */
    public function deleteBySystemidAndAction($strSystemid, class_search_enum_indexaction $objAction) {
        $strRemove = "DELETE FROM "._dbprefix_."search_queue WHERE search_queue_systemid = ? AND search_queue_action = ?";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strRemove, array($strSystemid, $objAction.""));
    }

    /**
     * Adds rows to the current queue
     *
     * @param $arrRows array("search_queue_id", "search_queue_systemid", "search_queue_action")
     *
     * @return bool
     */
    public function addRowsToQueue($arrRows) {
        return class_carrier::getInstance()->getObjDB()->multiInsert("search_queue", array("search_queue_id", "search_queue_systemid", "search_queue_action"), $arrRows);
    }
}
