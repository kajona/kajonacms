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
     *
     * The returning array contains the given error messages. Each key in the array contains an array of error messages.
     * Format of the returned array is:
     *      array("<messageKey>" => array())
     *
     *
     * @abstract
     * @param class_admin_formgenerator $objForm - the form object
     * @param class_model $objObject - the model object to the given form
     * @return array
     */
    public function validateObject(class_admin_formgenerator $objForm, class_model $objObject);

}
