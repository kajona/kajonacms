<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\StringUtil;
use Kajona\System\System\Validators\IntValidator;

/**
 * A simple form-element for integers, makes use of localized thousands-separators
 *
 * @author stefan.meyer1@yahoo.de
 * @since 6.2
 * @package module_formgenerator
 */
class FormentryInt extends FormentryBase implements FormentryPrintableInterface
{


    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null)
    {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new IntValidator());
    }

    public function setStrValue($strValue)
    {
        parent::setStrValue($strValue);

        //check if value comes from ui by checking if param exist. If param exists try to convert the value to a raw value
        if(Carrier::getInstance()->issetParam($this->getStrEntryName())) {
            parent::setStrValue(self::getRawValue($this->getStrValue()));
        }

        return $this;
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
        $strReturn = "";
        if ($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        $strValue = self::getStrUIValue($this->getStrValue());
        $strReturn .= $objToolkit->formInputText($this->getStrEntryName(), $this->getStrLabel(), $strValue, "inputText", "", $this->getBitReadonly());

        return $strReturn;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText()
    {
        return self::getStrUIValue($this->getStrValue());
    }

    /**
     * Converts the value of the formentry to a integer representation (raw value)
     *
     * @param mixed $strInputValue
     *
     * @return int|float|null
     */
    public static function getRawValue($strInputValue)
    {
        $strFieldValue = $strInputValue;

        $strSyleThousand = Carrier::getInstance()->getObjLang()->getLang("numberStyleThousands", "system");
        $strStyleDecimal = Carrier::getInstance()->getObjLang()->getLang("numberStyleDecimal", "system");

        $strValue = StringUtil::replace($strSyleThousand, "", $strFieldValue);//remove first thousand separator
        $strValue = StringUtil::replace(array(",", $strStyleDecimal), ".", $strValue);//replace decimal with decimal point for db

        //in case given $strValue is not numeric, an empty string just return value as is
        if (!is_numeric($strValue) || $strValue === "") {
            return $strFieldValue;
        }

        //if $strValue contains a "." (then it is a float) - so return the float inout value
        if(StringUtil::indexOf($strValue, ".")) {
            return $strValue;
        }

        $intValue = $strValue;
        //different casts on 32bit / 64bit
        if ($intValue > PHP_INT_MAX) {
            $intValue = (float)$intValue;
        }
        else {
            $intValue = (int)$intValue;
        }

        return $intValue;
    }

    /**
     * Converts the value of the formentry to UI representation
     *
     * @param mixed $strInputValue
     *
     * @return string
     */
    public static function getStrUIValue($strInputValue)
    {
        $strValue = $strInputValue;

        if (!is_numeric($strValue)) {
            return $strValue;
        }

        //if $strValue contains a "." (then it is a float) - so return the float input value
        if(StringUtil::indexOf($strValue, ".")) {
            return numberFormat($strValue, 2);
        }

        return numberFormat($strValue, 0);
    }
}
