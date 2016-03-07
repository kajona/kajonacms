<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Validators\NumericValidator;


/**
 * A simple form-element for floats, makes use of localized decimal-separators
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class FormentryFloat extends FormentryBase implements FormentryPrintableInterface {


    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new NumericValidator());
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

        $strValue = uniStrReplace(".", Carrier::getInstance()->getObjLang()->getLang("numberStyleDecimal", "system"), $this->getStrValue());
        $strReturn .= $objToolkit->formInputText($this->getStrEntryName(), $this->getStrLabel(), $strValue, "inputText", "", $this->getBitReadonly());

        return $strReturn;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        return $this->getStrValue();
    }

    public function setValueToObject() {
        $strOldValue = $this->getStrValue();
        $this->convertValueToFloat();
        $bitReturn = parent::setValueToObject();
        $this->setStrValue($strOldValue);
        return $bitReturn;
    }

    public function validateValue() {
        $strOldValue = $this->getStrValue();
        $this->convertValueToFloat();
        $bitReturn = parent::validateValue();
        $this->setStrValue($strOldValue);
        return $bitReturn;
    }


    private function convertValueToFloat() {
        $strValue = $strValue = uniStrReplace(array(",", Carrier::getInstance()->getObjLang()->getLang("numberStyleDecimal", "system")), ".", $this->getStrValue());
        $this->setStrValue($strValue);
    }
}
