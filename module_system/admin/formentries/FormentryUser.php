<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\UserUser;
use Kajona\System\System\Validators\DifferentuserValidator;
use Kajona\System\System\Validators\DummyValidator;
use Kajona\System\System\Validators\UserValidator;


/**
 *
 * The user-selector makes use of tow form-fields, the name and the systemid of the element.
 * The entry work with system-ids only.
 *
 * @author sidler@mulchprod.de
 * @since 4.2
 * @package module_formgenerator
 */
class FormentryUser extends FormentryBase implements FormentryPrintableInterface {

    private $bitUser = true;
    private $bitGroups = false;
    private $bitBlockCurrentUser = false;
    private $arrValidateId = null;

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new UserValidator());
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


        if($this->getBitReadonly()) {
            $strUsername = "";
            $strUserid = "";
            if(validateSystemid($this->getStrValue())) {
                $objUser = new UserUser($this->getStrValue());
                if($objUser->getIntActive() == 1) {
                    $strUsername = $objUser->getStrDisplayName();
                    $strUserid = $this->getStrValue();
                }
            }

            $strReturn .= $objToolkit->formInputText($this->getStrEntryName(), $this->getStrLabel(), $strUsername, "", "", true);
            $strReturn .= $objToolkit->formInputHidden($this->getStrEntryName()."_id", $strUserid);

        }
        else {
            $strReturn .= $objToolkit->formInputUserSelector($this->getStrEntryName(), $this->getStrLabel(), $this->getStrValue(), "", $this->bitUser, $this->bitGroups, $this->bitBlockCurrentUser, $this->arrValidateId);
        }

        return $strReturn;
    }


    /**
     * Overwritten base method, processes the hidden fields, too.
     */
    protected function updateValue() {
        $arrParams = Carrier::getAllParams();
        if(isset($arrParams[$this->getStrEntryName()."_id"]))
            $this->setStrValue($arrParams[$this->getStrEntryName()."_id"]);
        else
            $this->setStrValue($this->getValueFromObject());
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        if(validateSystemid($this->getStrValue())) {
            $objUser = new UserUser($this->getStrValue());
            return $objUser->getStrDisplayName();
        }

        return "";
    }


    /**
     * @param mixed $bitBlockCurrentUser
     * @return FormentryUser
     */
    public function setBitBlockCurrentUser($bitBlockCurrentUser) {
        $this->bitBlockCurrentUser = $bitBlockCurrentUser;

        if($this->bitBlockCurrentUser) {
            $this->setObjValidator(new DifferentuserValidator());
        }
        else {
            $this->setObjValidator(new UserValidator());
        }

        return $this;
    }

    /**
     * @param mixed $bitGroups
     * @return FormentryUser
     */
    public function setBitGroups($bitGroups) {
        $this->bitGroups = $bitGroups;
        return $this;
    }

    /**
     * @param mixed $bitUser
     * @return FormentryUser
     */
    public function setBitUser($bitUser) {
        $this->bitUser = $bitUser;
        return $this;
    }

    /**
     * @param null $arrValidateId
     *
     * @deprecated
     * @return $this
     */
    public function setArrValidateId($arrValidateId) {
        if(!is_array($arrValidateId)) {
            $arrValidateId = array($arrValidateId);
        }

        $this->arrValidateId = $arrValidateId;
        return $this;
    }





}
