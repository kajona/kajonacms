<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
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
     * @return string
     */
    public static function getStrValueForUser($strUserId)
    {
        if (validateSystemid($strUserId)) {
            return Objectfactory::getInstance()->getObject($strUserId)->getStrDisplayName();
        }

        return "";
    }

    /**
     * Gets a string representation for a given object id.
     * If the given param $strObjectIds contains a comma separated value of system id's, all display name of the objects
     * will be returned. Does also work with  an array of objects or system ids
     *
     * @param string|array $strObjectIds
     * @return string
     */
    public static function getStrValueForObjects($strObjectIds)
    {
        $arrSystemIds = array();

        if (is_string($strObjectIds)) {
            $arrSystemIds = array_filter(explode(",", $strObjectIds), function ($strSystemId) {
                return validateSystemid($strSystemId);
            });
        } elseif (is_array($strObjectIds)) {
            $arrSystemIds = array_filter(array_map(function($objValue){
                if (is_string($objValue)) {
                    return validateSystemid($objValue) ? $objValue : null;
                } elseif ($objValue instanceof Model) {
                    return $objValue->getSystemid();
                } else {
                    return null;
                }
            }, $strObjectIds));
        }

        $arrNames = array();
        foreach ($arrSystemIds as $strSystemId) {
            $objObject = Objectfactory::getInstance()->getObject($strSystemId);
            if ($objObject instanceof ModelInterface) {
                $arrNames[] = $objObject->getStrDisplayName();
            }
        }

        return implode(", ", $arrNames);
    }

    /**
     * Gets the string representation of a date
     *
     * @param string|Date $strDate
     * @return string
     */
    public static function getStrValueForDate($strDate)
    {
        if ($strDate instanceof Date) {
            $objDate = $strDate;
        } else {
            // empty includes "", 0, 0.0, "0", null, false and array()
            if (empty($strDate)) {
                return "";
            }

            $objDate = new Date($strDate);
        }

        return dateToString($objDate, false);
    }

}
