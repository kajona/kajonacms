<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Base implementation of an object validator
 *
 * @author stefan.meyer1@yahoo.de
 * @since 4.6
 * @package module_system
 */
abstract class class_objectvalidator_base implements interface_object_validator {
    protected $arrValidationMessages = array();


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
}
