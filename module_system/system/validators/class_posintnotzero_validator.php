<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * A simple validator to validate a positive integer.
 * By default, the string must contain a single char, the max length is unlimited.
 *
 * @author sidler@mulchprod.de
 * @since 4.4
 * @package module_system
 */
class class_posintnotzero_validator extends class_posint_validator implements interface_validator_extended {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {
        return parent::validate($objValue) && $objValue > 0;
    }


    /**
     * Gets the validation message of the validator.
     *
     * @return string
     */
    public function getValidationMessage() {
        $objLang = class_carrier::getInstance()->getObjLang();
        return $objLang->getLang("commons_validator_posint_validationmessage", "system");
    }
}
