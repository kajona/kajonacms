<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * The dependent dropdown is only useful in combination with a masterdropdown.
 *
 *
 * @author sidler@mulchprod.de
 * @since 4.6
 * @package module_formgenerator
 */
class class_formentry_dependentdropdown extends class_formentry_base implements interface_formentry_printable {

    const STR_VALUE_ANNOTATION = "@fieldValuePrefix";

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new class_text_validator());
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {

        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        return $objToolkit->formInputDropdown($this->getStrEntryName(), array(), $this->getStrLabel(), "", "", !$this->getBitReadonly(), " data-kajona-selected='".$this->getStrValue()."' ");
    }



    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        return "todo";
    }








}
