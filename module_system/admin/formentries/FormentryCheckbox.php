<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\Validators\DummyValidator;


/**
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class FormentryCheckbox extends FormentryBase implements FormentryPrintableInterface {

    private $strOpener = "";

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new DummyValidator());
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
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $strReturn .= $objToolkit->formInputCheckbox($this->getStrEntryName(), $this->getStrLabel(), $this->getStrValue() == true, "", $this->getBitReadonly());

        return $strReturn;
    }

    /**
     * @param $strValue
     * @return FormentryBase
     */
    public function setStrValue($strValue) {
        parent::setStrValue($strValue != false);
        return $this;
    }


    /**
     * Queries the params-array or the source-object for the mapped value.
     * If found in the params-array, the value will be used, otherwise
     * the source-objects' getter is invoked.
     */
    protected function updateValue() {
        $arrParams = Carrier::getAllParams();


        if(isset($arrParams[$this->getStrEntryName()])) {
            $this->setStrValue(true);
        }
        elseif(count($_POST) > 0) {
            $this->setStrValue(false);
        }
        else {
            $this->setStrValue($this->getValueFromObject());
        }
    }

    /**
     * @param $strOpener
     * @return FormentryText
     */
    public function setStrOpener($strOpener) {
        $this->strOpener = $strOpener;
        return $this;
    }

    public function getStrOpener() {
        return $this->strOpener;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        return $this->getStrValue() == true ? Carrier::getInstance()->getObjLang()->getLang("commons_yes", "commons") : Carrier::getInstance()->getObjLang()->getLang("commons_no", "commons");
    }

    /**
     * Calls the source-objects setter and stores the value.
     * If you want to skip a single setter, remove the field before.
     *
     * @throws Exception
     * @return mixed
     */
    public function setValueToObject()
    {
        if($this->getBitReadonly() == true) {
            return true;
        }
        return parent::setValueToObject();
    }


}
