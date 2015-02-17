<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A validator is used to validate a chunk of data.
 * In most cases, validators are used to ensure submitted data
 * matches the backends' requirements.
 *
 * @author stefan.meyer1@yahoo.de
 * @package module_system
 * @since 4.4
 */
interface interface_validator_extended extends interface_validator{

    /**
     * Gets the validation message of the validator.
     *
     * @return string
     */
    public function getValidationMessage();
}
