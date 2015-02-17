<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * A simple validator to validate a string.
 * By default, the string must contain a single char, the max length is unlimited.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
class class_date_validator implements interface_validator_extended {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {

        if(is_numeric($objValue))
            $objValue = new class_date($objValue);

        if(is_object($objValue) && $objValue instanceof class_date)
            return $objValue->getLongTimestamp() != 0;

        return false;
    }


    /**
     * Gets the validation message of the validator.
     *
     * @return string
     */
    public function getValidationMessage() {
        $objLang = class_carrier::getInstance()->getObjLang();
        $strDateFormat = $objLang->getLang("dateStyleShort", "system");
        return $objLang->getLang("commons_validator_date_validationmessage", "system", array($strDateFormat));
    }
}
