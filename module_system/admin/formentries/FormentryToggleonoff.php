<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_formentry_toggleOnOff.php 6322 2014-01-02 08:31:49Z sidler $                               *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\System\Carrier;


/**
 * Renders a on off toggle field. Same behaviour as a checkbox but with a more user friendly appearance.
 *
 * @author stefan.meyer1@yahoo.de
 * @since 4.3
 * @package module_formgenerator
 */
class FormentryToggleonoff extends FormentryCheckbox {

    private $strOnSwitchJSCallback = null;

    /**
     * @param string $strFormName
     * @param string $strSourceProperty
     * @param null $objSourceObject
     */
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
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
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
     *
     * @return $this
     */
    public function setStrOnSwitchJSCallback($strOnSwithJSCallback) {
        $this->strOnSwitchJSCallback = $strOnSwithJSCallback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStrOnSwitchJSCallback() {
        return $this->strOnSwitchJSCallback;
    }

}
