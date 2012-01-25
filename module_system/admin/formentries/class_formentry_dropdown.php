<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_versionable.php 4413 2012-01-03 19:38:11Z sidler $                               *
********************************************************************************************************/

/**
 * A yes-no field renders a dropdown containing a list of entries.
 * Make sure to pass the list of possible entries before rendering the form.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
class class_formentry_dropdown extends class_formentry_base implements interface_formentry {

    private $arrKeyValues = array();

    public function __construct($strFormName, $strSourceProperty, class_model $objSourceObject) {
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
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        $strReturn .=  $objToolkit->formInputDropdown($this->getStrEntryName(), $this->arrKeyValues, $this->getStrLabel(), $this->getStrValue());
        return $strReturn;
    }

    public function setArrKeyValues($arrKeyValues) {
        $this->arrKeyValues = $arrKeyValues;
    }

    public function getArrKeyValues() {
        return $this->arrKeyValues;
    }


}
