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
use AGP\Reportconfigurator\Admin\ReportconfiguratorRendererBase;
use AGP\Reportconfigurator\System\ReportconfiguratorField;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryBase;
use Kajona\System\Admin\Formentries\FormentryDate;
use Kajona\System\Admin\Formentries\FormentryDatetime;
use Kajona\System\Admin\Formentries\FormentryDropdown;
use Kajona\System\Admin\Formentries\FormentryObjectlist;

/**
 * SystemChangelogRenderer
 *
 * @package module_system
 * @author christoph.kappestein@artemeon.de
 *
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

    public function getVersionPropertyName($strProperty)
    {
        if (!in_array(substr($strProperty, 0, 3), array("str", "int", "float", "bit", "long"))) {
            // in this case we have probably already a translated property
            return $strProperty;
        }

        $strLabel = $this->objReflection->getAnnotationValueForProperty($strProperty, AdminFormgenerator::STR_LABEL_ANNOTATION);
        if (!empty($strLabel)) {
            $strPropertyName = $this->objLang->getLang($strLabel, $this->strModule);
            if (!empty($strPropertyName)) {
                return $strPropertyName;
            }
        }

        return $strProperty;
    }

    public function getVersionValue($strProperty, $strValue)
    {
        $strType = $this->objReflection->getAnnotationValueForProperty($strProperty, AdminFormgenerator::STR_TYPE_ANNOTATION);
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

    private function renderData($strType, $strValue, $arrDDValues)
    {
        switch ($strType) {
            case FormentryDate::class:
            case FormentryDatetime::class:
            case "date":
            case "datetime":
                return FormentryRenderer::renderDate($strValue);
                break;

            case FormentryDropdown::class:
            case "dropdown":
                return FormentryRenderer::renderDropdown($strValue, $arrDDValues);
                break;

            case FormentryObjectlist::class:
            case FormentryProzess::class:
            case FormentryOe::class:
            case "objectlist":
            case "prozess":
            case "oe":
                return FormentryRenderer::renderSystemIds($strValue);
                break;

            default:
                return FormentryRenderer::renderText($strValue);
        }
    }
    
    public static function renderPropertyName(Model $objObject, $strProperty)
    {
        $strClass = get_class($objObject);
        if (!isset(self::$arrRenderer[$strClass])) {
            self::$arrRenderer[$strClass] = new self(new Reflection($strClass));
        }

        return self::$arrRenderer[$strClass]->getVersionPropertyName($strProperty);
    }

    public static function renderValue(Model $objObject, $strProperty, $strValue)
    {
        $strClass = get_class($objObject);
        if (!isset(self::$arrRenderer[$strClass])) {
            self::$arrRenderer[$strClass] = new self(new Reflection($strClass));
        }

        return self::$arrRenderer[$strClass]->getVersionValue($strProperty, $strValue);
    }
}
