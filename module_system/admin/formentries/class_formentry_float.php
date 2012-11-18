<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * A simple form-element for floats, makes use of localized decimal-separators
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class class_formentry_float extends class_formentry_base implements interface_formentry {


    public function __construct($strFormName, $strSourceProperty, class_model $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new class_numeric_validator());
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $strValue = uniStrReplace(".", class_carrier::getInstance()->getObjLang()->getLang("numberStyleDecimal", "system"), $this->getStrValue());
        $strReturn .= $objToolkit->formInputText($this->getStrEntryName(), $this->getStrLabel(), $strValue, "inputText", "", $this->getBitReadonly());

        return $strReturn;
    }

    public function setValueToObject() {

        $this->convertValueToFloat();
        return parent::setValueToObject();
    }

    public function validateValue() {
        $this->convertValueToFloat();
        return parent::validateValue();
    }


    private function convertValueToFloat() {
        $strValue = $strValue = uniStrReplace(array(",", class_carrier::getInstance()->getObjLang()->getLang("numberStyleDecimal", "system")), ".", $this->getStrValue());
        $this->setStrValue($strValue);
    }
}
