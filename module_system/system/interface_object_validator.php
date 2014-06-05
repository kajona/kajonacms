<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * A validator is used to validate an object.
 * In most cases, validators are used to ensure submitted data
 * matches the backends' requirements.
 *
 * @author stefan.meyer1@yahoo.de
 * @package module_system
 * @since 4.5
 */
interface interface_object_validator {

    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @abstract
     * @param class_admin_formgenerator $objForm
     * @param class_model $objObject
     * @return bool
     */
    public function validateObject(class_admin_formgenerator $objForm, class_model $objObject);

    /**
     * Returns a string-based name of the current object validator.
     *
     * @abstract
     * @return string
     */
    public function getStrName();

    /**
     * Gets the validation message of the validator.
     *
     * @return array
     */
    public function getValidationMessages();
}
