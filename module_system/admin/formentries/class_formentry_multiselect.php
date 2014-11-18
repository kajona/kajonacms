<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * A yes-no field renders a dropdown containing a list of entries.
 * Make sure to pass the list of possible entries before rendering the form.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class class_formentry_multiselect extends class_formentry_dropdown implements interface_formentry {

    private $arrKeyValues = array();

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }
        $strReturn .= $objToolkit->formInputMultiselect($this->getStrEntryName(), $this->arrKeyValues, $this->getStrLabel(), explode(",", $this->getStrValue()));
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
     * @param $arrKeyValues
     *
     * @return class_formentry_dropdown
     */
    public function setArrKeyValues($arrKeyValues) {
        $this->arrKeyValues = $arrKeyValues;
        return $this;
    }

    public function getArrKeyValues() {
        return $this->arrKeyValues;
    }

}
