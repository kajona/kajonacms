<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * @author stefan.meyer1@yahoo.de
 * @since 4.3
 * @package module_formgenerator
 */
class class_formentry_toggleOnOff extends class_formentry_checkbox {

    private $strOnSwitchJSCallback = null;

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);
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
        if($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        //enable, disable, style
        $strReturn .= $objToolkit->formInputOnOff($this->getStrEntryName(), $this->getStrLabel(), $this->getStrValue() == true, $this->getBitReadonly(), $this->getStrOnSwitchJSCallback());

        return $strReturn;
    }

    /**
     * @param mixed $strOnSwithJSCallback
     */
    public function setStrOnSwitchJSCallback($strOnSwithJSCallback) {
        $this->strOnSwitchJSCallback = $strOnSwithJSCallback;
    }

    /**
     * @return mixed
     */
    public function getStrOnSwitchJSCallback() {
        return $this->strOnSwitchJSCallback;
    }

}
