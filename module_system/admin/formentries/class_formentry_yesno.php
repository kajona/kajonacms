<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * A yes-no field renders a dropdown containing one entry for yes and one for no.
 * 0 is no whereas 1 is rendered as yes.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class class_formentry_yesno extends class_formentry_base implements interface_formentry {

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
        $objLang = class_carrier::getInstance()->getObjLang();
        $arrYesNo = array(
            0 => $objLang->getLang("commons_no", "system"), 1 => $objLang->getLang("commons_yes", "system")
        );
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        $strReturn .=  $objToolkit->formInputDropdown($this->getStrEntryName(), $arrYesNo, $this->getStrLabel(), $this->getStrValue());
        return $strReturn;
    }
}
