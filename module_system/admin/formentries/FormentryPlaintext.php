<?php
/*"******************************************************************************************************
*   (c) 2013-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;


/**
 * A formentry to add special code to forms, in most cases hidden js-code
 *
 * @author sidler@mulchprod.de
 * @since 4.3
 * @package module_system
 */
class FormentryPlaintext extends class_formentry_base implements interface_formentry_printable {


    /**
     * @param string $strName
     */
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

    /**
     * @param string $strKey
     *
     * @return string
     */
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
