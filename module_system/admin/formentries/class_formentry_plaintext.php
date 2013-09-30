<?php
/*"******************************************************************************************************
*   (c) 2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_formentry_button.php 5884 2013-09-29 01:21:57Z sidler $                               *
********************************************************************************************************/

/**
 * A formentry to add special code to forms, in most cases hidden js-code
 * @author sidler@mulchprod.de
 * @since 4.3
 * @package module_system
 */
class class_formentry_plaintext extends class_formentry_base implements interface_formentry_printable {


    public function __construct($strName = "") {
        parent::__construct("", $strName != "" ? $strName : generateSystemid());

        //set the default validator
        $this->setObjValidator(new class_dummy_validator());
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        return $this->getStrValue();
    }

    public function updateLabel($strKey = "") {
        return "";
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        return $this->getStrValue();
    }

}
