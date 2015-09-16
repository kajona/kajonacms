<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Validates, if the passed value is an existing file
 *
 * @author sidler@mulchprod.de
 * @since 4.7
 * @package module_system
 */
class class_file_validator implements interface_validator {

    private $strBaseDir = "";

    function __construct($strBaseDir = null) {
        $this->strBaseDir = $strBaseDir."/";
    }


    /**
     * Validates the passed chunk of data.
     * In most cases, this'll be a string-object.
     *
     * @param string $objValue
     * @return bool
     */
    public function validate($objValue) {

        if(!is_string($objValue) || uniStrlen($objValue) == 0)
            return false;

        return is_file(_realpath_.$this->strBaseDir.$objValue);
    }

}
