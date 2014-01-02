<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Copies assigned tags from one record to another
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 *
 */
class class_module_tags_recordcopiedlistener implements interface_recordcopied_listener {


    /**
     * Called whenever a record was copied.
     * copies the tag-assignments from the source object to the target object
     *
     * @param string $strOldSystemid
     * @param string $strNewSystemid
     *
     * @return bool
     */
    public function handleRecordCopiedEvent($strOldSystemid, $strNewSystemid) {
        $strQuery = "SELECT tags_tagid, tags_attribute, tags_owner
                       FROM "._dbprefix_."tags_member
                      WHERE tags_systemid = ?";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strOldSystemid));
        foreach($arrRows as $arrSingleRow) {
            $strQuery = "INSERT INTO "._dbprefix_."tags_member (tags_memberid, tags_tagid, tags_systemid, tags_attribute, tags_owner) VALUES (?, ?, ?, ?, ?)";
            class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array(generateSystemid(), $arrSingleRow["tags_tagid"], $strNewSystemid, $arrSingleRow["tags_attribute"], $arrSingleRow["tags_owner"]));
        }

        return true;
    }


}
