<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Date;
use Kajona\System\System\Reflection;
use Kajona\System\System\Validators\DateValidator;


/**
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class FormentryDate extends FormentryBase implements FormentryPrintableInterface {


    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new DateValidator());
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $objDate = null;
        if($this->getStrValue() instanceof Date)
            $objDate = $this->getStrValue();
        elseif($this->getStrValue() != "")
            $objDate = new Date($this->getStrValue());

        if($this->getBitReadonly())
            $strReturn .= $objToolkit->formInputText($this->getStrEntryName(), $this->getStrLabel(), dateToString($objDate, false), "", "", true);
        else
            $strReturn .= $objToolkit->formDateSingle($this->getStrEntryName(), $this->getStrLabel(), $objDate, "", false, $this->getBitReadonly());

        return $strReturn;
    }



    protected function updateValue() {
        $arrParams = Carrier::getAllParams();
        if((isset($arrParams[$this->getStrEntryName()."_day"]) && $arrParams[$this->getStrEntryName()."_day"] != "") || isset($arrParams[$this->getStrEntryName()])) {

            if(isset($arrParams[$this->getStrEntryName()]) && $arrParams[$this->getStrEntryName()] == "") {
                $this->setStrValue(null);
            }
            else {
                $objDate = new Date();
                $objDate->generateDateFromParams($this->getStrEntryName(), $arrParams);
                $this->setStrValue($objDate->getLongTimestamp());
            }
        }
        else
            $this->setStrValue($this->getValueFromObject());

    }

    public function validateValue() {
        $objDate = new Date("0");

        $arrParams = Carrier::getAllParams();
        if(array_key_exists($this->getStrEntryName(), $arrParams)) {
            $objDate->generateDateFromParams($this->getStrEntryName(), $arrParams);
        }
        else {
            $objDate = new Date($this->getStrValue());
        }

        return $this->getObjValidator()->validate($objDate);
    }

    public function setValueToObject() {

        $objReflection = new Reflection($this->getObjSourceObject());
        $strSetter = $objReflection->getSetter($this->getStrSourceProperty());

        if($strSetter !== null && uniStrtolower(uniSubstr($strSetter, 0, 6)) == "setobj" && !$this->getStrValue() instanceof Date && $this->getStrValue() > 0)
            $this->setStrValue(new Date($this->getStrValue()));

        return parent::setValueToObject();
    }



    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        $objDate = null;
        if($this->getStrValue() instanceof Date)
            $objDate = $this->getStrValue();
        elseif($this->getStrValue() != "")
            $objDate = new Date($this->getStrValue());

        if($objDate != null)
            return dateToString($objDate, false);

        return "";
    }

}
