<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\System\Validators;

use Kajona\System\System\Carrier;
use Kajona\System\System\Date;
use Kajona\System\System\ValidatorExtendedInterface;


/**
 * A simple validator to validate a string.
 * By default, the string must contain a single char, the max length is unlimited.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
class DateValidator implements ValidatorExtendedInterface {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {

        if(is_numeric($objValue))
            $objValue = new Date($objValue);

        if(is_object($objValue) && $objValue instanceof Date)
            return $objValue->getLongTimestamp() != 0;

        return false;
    }


    /**
     * Gets the validation message of the validator.
     *
     * @return string
     */
    public function getValidationMessage() {
        $objLang = Carrier::getInstance()->getObjLang();
        $strDateFormat = $objLang->getLang("dateStyleShort", "system");
        return $objLang->getLang("commons_validator_date_validationmessage", "system", array($strDateFormat));
    }
}
