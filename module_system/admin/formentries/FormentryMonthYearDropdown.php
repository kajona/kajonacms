<?php
/*"******************************************************************************************************
*   (c) 2013-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\System\Carrier;
use Kajona\System\System\Date;
use Kajona\System\System\StringUtil;


/**
 * Renders two dropdown boxes, on for month and one for the year.
 *
 * @author stefan.meyer1@yahoo.de
 * @since 4.4
 * @package module_formgenerator
 */
class FormentryMonthYearDropdown extends FormentryDate
{

    const DAY_SUFFIX = "_day";
    const MONTH_SUFFIX = "_month";
    const YEAR_SUFFIX = "_year";

    private static $arrDropDownMonth = null;
    private static $arrDropDownYear = null;

    private $bitRenderDay = false;


    public static function classInit()
    {
        if (self::$arrDropDownMonth == null) {
            self::$arrDropDownMonth = self::getArrMonths();
        }
        if (self::$arrDropDownYear == null) {
            self::$arrDropDownYear = self::getArrYear();
        }
    }


    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField()
    {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");

        //create a date object if possible
        $objDate = null;
        if ($this->getStrValue() instanceof Date) {
            $objDate = $this->getStrValue();
        } elseif ($this->getStrValue() != "") {
            $objDate = new Date($this->getStrValue());
        }

        //set selected value
        $intMonth = null;
        $intYear = null;
        $intDay = 1;
        if ($objDate != null) {
            $intMonth = $objDate->getIntMonth();
            $intYear = $objDate->getIntYear();
            $intDay = $objDate->getIntDay();
        }

        //create hint and form elements
        $strReturn = "";
        if ($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        if ($this->bitRenderDay) {
            $strReturn .= $objToolkit->formInputText($this->getStrEntryName()."ph", $this->getStrLabel(), $intDay, "", "", true);
            $strReturn .= $objToolkit->formInputHidden($this->getStrEntryName().self::DAY_SUFFIX, $intDay);
        } else {
            $strReturn .= $objToolkit->formInputHidden($this->getStrEntryName().self::DAY_SUFFIX, $intDay);
        }
        $strReturn .= $objToolkit->formInputDropdown(
            $this->getStrEntryName().self::MONTH_SUFFIX,
            self::$arrDropDownMonth,
            $this->bitRenderDay ? "" : $this->getStrLabel(),
            $intMonth,
            "",
            !$this->getBitReadonly()
        );

        $strReturn .= $objToolkit->formInputDropdown(
            $this->getStrEntryName().self::YEAR_SUFFIX,
            self::$arrDropDownYear,
            "",
            $intYear,
            "",
            !$this->getBitReadonly()
        );

        if($this->getBitMandatory()) {
            $strReturn .= "<script type='text/javascript'>
                require(['forms'], function(forms) {
                    forms.renderMandatoryFields([[ '".$this->getStrEntryName().self::MONTH_SUFFIX."', '' ], ['".$this->getStrEntryName().self::YEAR_SUFFIX."' ,  '' ]]); 
                });
            </script>";
        }

        return $strReturn;
    }


    /**
     * Creates an associative array of months. The first entry has as key "1" with value "January".
     * e.g.
     * Format of the returned array is:     *
     * "1" => "January",
     * "2" => "February",
     * "3" => "March",
     * ......
     *
     * @return array
     */
    private static function getArrMonths()
    {
        $strMonthNames = Carrier::getInstance()->getObjLang()->getLang("toolsetCalendarMonth", "system");
        $strMonthNames = StringUtil::replace("\"", "", $strMonthNames);
        $arrMonthNames = explode(",", $strMonthNames);

        $arrDropDownMonth = array();
        for ($intI = 0; $intI < count($arrMonthNames); $intI++) {
            $arrDropDownMonth[($intI + 1).""] = $arrMonthNames[$intI];
        }

        return $arrDropDownMonth;
    }

    /**
     * Creates and array which contains the years until 2099 starting from the current year
     *
     * @return array
     */
    private static function getArrYear()
    {
        $arrDropDownYear = array();

        for ($intI = 2000; $intI < 2100; $intI++) {
            $arrDropDownYear[$intI.""] = $intI;
        }

        return $arrDropDownYear;
    }

    /**
     * @return boolean
     */
    public function isBitRenderDay()
    {
        return $this->bitRenderDay;
    }

    /**
     * @param boolean $bitRenderDay
     */
    public function setBitRenderDay($bitRenderDay)
    {
        $this->bitRenderDay = $bitRenderDay;
    }

    public function validateValue()
    {
        if ($this->getBitMandatory()) {
            $arrParams = Carrier::getAllParams();

            if (array_key_exists($this->getStrEntryName().self::DAY_SUFFIX, $arrParams)

            ) {
                $objDate = new Date("0");
                $objDate->generateDateFromParams($this->getStrEntryName(), $arrParams);
                return $this->getObjValidator()->validate($objDate) && $objDate->getIntMonth() > 0 && $objDate->getIntYear() > 0;
            }
        }

        return parent::validateValue();
    }
}

//TODO: remove, add to construct
FormentryMonthYearDropdown::classInit();