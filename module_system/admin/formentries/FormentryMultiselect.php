<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\System\Carrier;


/**
 * A yes-no field renders a dropdown containing a list of entries.
 * Make sure to pass the list of possible entries before rendering the form.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class FormentryMultiselect extends FormentryDropdown {

    protected $arrKeyValues = array();

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }


        $arrSelectedKeys = array();
        if($this->getStrValue() !== "") {
            $arrSelectedKeys = explode(",", $this->getStrValue());
        }
        $strReturn .= $objToolkit->formInputMultiselect($this->getStrEntryName(), $this->arrKeyValues, $this->getStrLabel(), $arrSelectedKeys, "", !$this->getBitReadonly());
        return $strReturn;
    }

    public function setStrValue($strValue) {
        if(is_array($strValue))
            $strValue = implode(",", $strValue);

        return parent::setStrValue($strValue);
    }


    public function validateValue() {
        foreach(explode(",", $this->getStrValue()) as $strOneSelect) {
            if(!in_array($strOneSelect, array_keys($this->arrKeyValues))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        $arrSelected = $this->getStrValue();
        if($arrSelected === "" || $arrSelected === null || (is_array($arrSelected) && count($arrSelected) == 0)) {
            return "";
        }

        if(!is_array($arrSelected)) {
            $arrSelected = explode(",", $this->getStrValue());
        }

        array_walk($arrSelected, function(&$strValue) {
            $strValue = $this->arrKeyValues[$strValue];
        });

        return implode(", ", $arrSelected);
    }

    /**
     * @param $arrKeyValues
     *
     * @return FormentryDropdown
     */
    public function setArrKeyValues($arrKeyValues) {
        $this->arrKeyValues = $arrKeyValues;
        return $this;
    }

    public function getArrKeyValues() {
        return $this->arrKeyValues;
    }

}
