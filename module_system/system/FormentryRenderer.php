<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * General formentry renderer which can render values from an object to a string
 *
 * @package module_system
 * @author christoph.kappestein@artemeon.de
 */
class FormentryRenderer
{
    /**
     * @param mixed $objValue
     * @return string
     */
    public static function renderDate($objValue)
    {
        if ($objValue instanceof Date) {
            $objDate = $objValue;
        } else {
            if (empty($objValue)) {
                return "";
            }

            $objDate = new Date($objValue);
        }

        return dateToString($objDate, false);
    }

    /**
     * @param mixed $objValue
     * @param string $strSeperator
     * @return string
     */
    public static function renderSystemIds($objValue, $strSeperator = ", ")
    {
        $arrSystemIds = array();

        if (!is_array($objValue)) {
            if (validateSystemid($objValue)) {
                $arrSystemIds = array($objValue);
            } elseif (strpos($objValue, ",") !== false) {
                $arrSystemIds = array_filter(explode(",", $objValue), function ($strSystemId) {
                    return validateSystemid($strSystemId);
                });
            }
        } elseif (is_array($objValue)) {
            $arrSystemIds = array_filter(array_map(function($objValue){
                if (is_string($objValue)) {
                    return validateSystemid($objValue) ? $objValue : null;
                } elseif ($objValue instanceof Model) {
                    return $objValue->getSystemid();
                } else {
                    return null;
                }
            }, $objValue));
        }

        $arrNames = array();
        foreach ($arrSystemIds as $strSystemId) {
            $objObject = Objectfactory::getInstance()->getObject($strSystemId);
            if ($objObject instanceof ModelInterface) {
                $arrNames[] = $objObject->getStrDisplayName();
            }
        }

        return implode($strSeperator, $arrNames);
    }

    /**
     * @param mixed $objValue
     * @param array $arrDDValues
     * @return mixed
     */
    public static function renderDropdown($objValue, array $arrDDValues)
    {
        if (array_key_exists($objValue, $arrDDValues)) {
            return $arrDDValues[$objValue];
        }

        return $objValue;
    }

    /**
     * @param mixed $objValue
     * @return string
     */
    public static function renderText($objValue)
    {
        return $objValue;
    }
}
