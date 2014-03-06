<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_formentry_upload.php 6322 2014-01-02 08:31:49Z sidler $                               *
********************************************************************************************************/

/**
 * @author stefan.meyer1@yahoo.de
 * @since 4.4
 * @package module_formgenerator
 */
class class_formentry_upload extends class_formentry_base implements interface_formentry {

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
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $strReturn .= $objToolkit->formInputUpload($this->getStrEntryName(), $this->getStrLabel());

        return $strReturn;
    }
}
