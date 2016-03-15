<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Validators;

use Kajona\System\System\Model;


/**
 * Base implementation of an object validator
 *
 * @author stefan.meyer1@yahoo.de
 * @since 4.6
 * @package module_system
 */
abstract class ObjectvalidatorBase  {

    private $arrValidationMessages = array();

    /**
     * Validates the passed object.
     *
     * Return a boolean value to indicate whether the obejct is valid or not.
     * If you want to provide additional error-messages (e.g. for a form), add them via
     * $this->addValidationError(key, error)
     * while key could be the name of the formentry.
     *
     * @abstract
     * @param Model $objObject - the model object to the given form
     * @return bool
     */
    public abstract function validateObject(Model $objObject);


    /**
     * Adds an additional, user-specific validation-error to the current list of errors.
     *
     * @param string $strEntry
     * @param string $strMessage
     * @return void
     */
    public function addValidationError($strEntry, $strMessage) {
        if(!array_key_exists($strEntry, $this->arrValidationMessages)) {
            $this->arrValidationMessages[$strEntry] = array();
        }
        $this->arrValidationMessages[$strEntry][] = $strMessage;
    }

    /**
     * @return array
     */
    public function getArrValidationMessages() {
        return $this->arrValidationMessages;
    }


}
