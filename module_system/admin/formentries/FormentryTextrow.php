<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;


/**
 * A text-row is a graphical element, similar to a divider, but this case in order to render a text-field.
 * Use the value to pass the text to render.
 * @author sidler@mulchprod.de
 * @since 4.1
 * @package module_formgenerator
 */
class FormentryTextrow extends class_formentry_base implements interface_formentry_printable {


    public function __construct($strName = "") {
        if($strName == "")
            $strName = generateSystemid();
        parent::__construct("", $strName);

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
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        $strReturn .= $objToolkit->formTextRow($this->getStrValue());
        return $strReturn;
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
