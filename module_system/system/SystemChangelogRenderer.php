<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

namespace Kajona\System\System;

use AGP\Prozessverwaltung\Admin\Formentries\FormentryOe;
use AGP\Prozessverwaltung\Admin\Formentries\FormentryProzess;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryDate;
use Kajona\System\Admin\Formentries\FormentryDatetime;
use Kajona\System\Admin\Formentries\FormentryDropdown;
use Kajona\System\Admin\Formentries\FormentryObjectlist;
use Kajona\System\Admin\Formentries\FormentryObjecttags;
use Kajona\System\Admin\Formentries\FormentryYesno;

/**
 * Class which provides a default render implementation for the VersionableInterface. The implementation looks at the
 * property and tries to find the best way to render a value depending on the available annotations
 *
 * <code>
 * SystemChangelogRenderer::renderPropertyName();
 * SystemChangelogRenderer::renderValue();
 * </code>
 *
 * @package module_system
 * @author christoph.kappestein@artemeon.de
 * @module system
 * @moduleId _system_modul_id_
 */
class SystemChangelogRenderer
{
    /**
     * @var Reflection
     */
    protected $objReflection;

    /**
     * @var Lang
     */
    protected $objLang;

    /**
     * @var string
     */
    protected $strModule;

    /**
     * @var SystemChangelogRenderer[]
     */
    private static $arrRenderer = array();

    public function __construct(Reflection $objReflection)
    {
        $this->objReflection = $objReflection;
        $this->objLang = Lang::getInstance();

        $arrModule = $objReflection->getAnnotationValuesFromClass("@module");
        $this->strModule = is_array($arrModule) ? current($arrModule) : $arrModule;
    }

    /**
     * We try to get the fitting property name through a form lang property
     *
     * @param string $strProperty
     * @return string
     */
    public function getVersionPropertyName($strProperty)
    {
        $strLabel = $this->objReflection->getAnnotationValueForProperty($strProperty, AdminFormgenerator::STR_LABEL_ANNOTATION);
        if (!empty($strLabel)) {
            $strPropertyName = $this->objLang->getLang($strLabel, $this->strModule);
            if (!empty($strPropertyName)) {
                return $strPropertyName;
            }
        } else {
            return $this->getFallbackName($strProperty);
        }

        return $strProperty;
    }

    /**
     * Renders the value depending on the field type annotation
     *
     * @param string $strProperty
     * @param mixed $strValue
     * @return string
     */
    public function getVersionValue($strProperty, $strValue)
    {
        $strType = $this->objReflection->getAnnotationValueForProperty($strProperty, AdminFormgenerator::STR_TYPE_ANNOTATION);
        if (empty($strType)) {
            $strType = $this->getFallbackType($strProperty);
        }

        if (!empty($strType)) {
            $strDDValues = $this->objReflection->getAnnotationValueForProperty($strProperty, FormentryDropdown::STR_DDVALUES_ANNOTATION);
            if (!empty($strDDValues)) {
                $arrDDValues = FormentryDropdown::convertDDValueStringToArray($strDDValues, $this->strModule);
            } else {
                $arrDDValues = null;
            }

            return $this->renderData($strType, $strValue, $arrDDValues);
        }

        return $strValue;
    }

    /**
     * Returns a fallback name for known system properties
     *
     * @param string $strProperty
     * @return string
     */
    private function getFallbackName($strProperty)
    {
        $arrRights = $this->objLang->getLang("permissions_root_header", "system");
        switch ($strProperty) {
            case "rightView":
                return $arrRights[0];

            case "rightEdit":
                return $arrRights[1];

            case "rightDelete":
                return $arrRights[2];

            case "rightRight":
                return $arrRights[3];

            case "rightRight1":
                return $arrRights[4];

            case "rightRight2":
                return $arrRights[5];

            case "rightRight3":
                return $arrRights[6];

            case "rightRight4":
                return $arrRights[7];

            case "rightRight5":
                return $arrRights[8];

            case "rightChangelog":
                return $arrRights[9];

            case "rightInherit":
                return $this->objLang->getLang("titel_erben", "system");

            case "intRecordStatus":
                return $this->objLang->getLang("commons_record_status", "system");

            case "intRecordDeleted":
                return $this->objLang->getLang("commons_record_deleted", "system");

            case "objStartDate":
                return $this->objLang->getLang("commons_record_startdate", "system");

            case "objEndDate":
                return $this->objLang->getLang("commons_record_enddate", "system");

            case "objSpecialDate":
                return $this->objLang->getLang("commons_record_specialdate", "system");

            case "strPrevId":
                return $this->objLang->getLang("commons_record_prev", "system");

            case "strOwner":
                return $this->objLang->getLang("commons_record_owner", "system");

            default:
                return $strProperty;
        }
    }

    /**
     * Returns a fallback type for known system properties
     *
     * @param string $strProperty
     * @return string
     */
    private function getFallbackType($strProperty)
    {
        switch ($strProperty) {
            case "rightView":
            case "rightEdit":
            case "rightDelete":
            case "rightRight":
            case "rightRight1":
            case "rightRight2":
            case "rightRight3":
            case "rightRight4":
            case "rightRight5":
            case "rightChangelog":
            case "strPrevId":
            case "strOwner":
                return FormentryObjectlist::class;

            case "objStartDate":
            case "objEndDate":
            case "objSpecialDate":
                return FormentryDate::class;

            case "intRecordDeleted":
                return FormentryYesno::class;

            default:
                return null;
        }
    }

    /**
     * Returns a fitting string representation of the data depending on the provided type
     *
     * @param string $strType
     * @param string $strValue
     * @param array $arrDDValues
     * @return string
     */
    private function renderData($strType, $strValue, $arrDDValues)
    {
        if (empty($strType) && validateSystemid($strValue)) {
            $strType = FormentryObjectlist::class;
        }

        switch ($strType) {
            case FormentryDate::class:
            case FormentryDatetime::class:
            case "date":
            case "datetime":
                return SystemChangelogHelper::getStrValueForDate($strValue);
                break;

            case FormentryDropdown::class:
            case "dropdown":
                if (!empty($arrDDValues) && array_key_exists($strValue, $arrDDValues)) {
                    return $arrDDValues[$strValue];
                } else {
                    return $strValue;
                }
                break;

            case FormentryObjecttags::class:
            case FormentryObjectlist::class:
            case FormentryProzess::class:
            case FormentryOe::class:
            case "objecttags":
            case "objectlist":
            case "prozess":
            case "oe":
                return SystemChangelogHelper::getStrValueForObjects($strValue);
                break;

            case FormentryYesno::class:
                $arrDDValues = array(
                    0 => Carrier::getInstance()->getObjLang()->getLang("commons_no", "system"),
                    1 => Carrier::getInstance()->getObjLang()->getLang("commons_yes", "system"),
                );
                if (!empty($arrDDValues) && array_key_exists($strValue, $arrDDValues)) {
                    return $arrDDValues[$strValue];
                } else {
                    return $strValue;
                }

                break;

            default:
                return $strValue;
        }
    }

    /**
     * Tries to find a fitting property name based on the annotations of the provided model
     *
     * @param Model $objObject
     * @param string $strProperty
     * @return string
     */
    public static function renderPropertyName(Model $objObject, $strProperty)
    {
        $strClass = get_class($objObject);
        if (!isset(self::$arrRenderer[$strClass])) {
            self::$arrRenderer[$strClass] = new self(new Reflection($strClass));
        }

        return self::$arrRenderer[$strClass]->getVersionPropertyName($strProperty);
    }

    /**
     * Tries to find a fitting string representation of the data based on the annotations of the provided model
     *
     * @param Model $objObject
     * @param string $strProperty
     * @param string $strValue
     * @return string
     */
    public static function renderValue(Model $objObject, $strProperty, $strValue)
    {
        $strClass = get_class($objObject);
        if (!isset(self::$arrRenderer[$strClass])) {
            self::$arrRenderer[$strClass] = new self(new Reflection($strClass));
        }

        return self::$arrRenderer[$strClass]->getVersionValue($strProperty, $strValue);
    }
}
