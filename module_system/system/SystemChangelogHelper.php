<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Helper class for changelog
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 *
 * @module system
 * @moduleId _system_modul_id_
 */
class SystemChangelogHelper
{

    /**
     * Gets a string representation for a given user id
     *
     * @param $strUserId
     *
     * @return string
     */
    public static function getStrValueForUser($strUserId)
    {
        if (validateSystemid($strUserId)) {
            $objResp = new UserUser($strUserId);
            return $objResp->getStrDisplayName();
        }

        return "";
    }

    /**
     * Gets a string representation for a given object id.
     * If the given param $strObjectIds contains a comma separated value of system id's, all display name of the objects
     * will be returned.
     *
     * @param $strObjectIds
     *
     * @return string
     */
    public static function getStrValueForObjects($strObjectIds)
    {
        $arrReturn = array();
        if ($strObjectIds != "") {
            $arrIds = explode(",", $strObjectIds);
            foreach ($arrIds as $strId) {
                $objObject = Objectfactory::getInstance()->getObject($strId);
                if ($objObject != null) {
                    $arrReturn[] = $objObject->getStrDisplayName();
                }
            }
        }
        return implode(",", $arrReturn);
    }

    /**
     * Gets the string representation of a date
     *
     * @param $strDate
     *
     * @return string
     */
    public static function getStrValueForDate($strDate)
    {
        if ($strDate == "" || $strDate == "0") {
            return "";
        }
        $objDate = new Date($strDate);
        return dateToString($objDate, false);
    }

}
