<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 *
 * The user-selector makes use of tow form-fields, the name and the systemid of the element.
 * The entry work with system-ids only.
 *
 * @author sidler@mulchprod.de
 * @since 4.2
 * @package module_formgenerator
 */
class class_formentry_user extends class_formentry_base implements interface_formentry {

    private $bitUser = true;
    private $bitGroups = false;
    private $bitBlockCurrentUser = false;

    public function __construct($strFormName, $strSourceProperty, class_model $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new class_systemid_validator());
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

        $strReturn .= $objToolkit->formInputUserSelector($this->getStrEntryName(), $this->getStrLabel(), $this->getStrValue(), "", $this->bitUser, $this->bitGroups, $this->bitBlockCurrentUser);

        return $strReturn;
    }


    /**
     * Overwritten base method, processes the hidden fields, too.
     */
    protected function updateValue() {
        $arrParams = class_carrier::getAllParams();
        if(isset($arrParams[$this->getStrEntryName()."_id"]))
            $this->setStrValue($arrParams[$this->getStrEntryName()."_id"]);
        else
            $this->setStrValue($this->getValueFromObject());
    }


    /**
     * @param mixed $bitBlockCurrentUser
     * @return class_formentry_user
     */
    public function setBitBlockCurrentUser($bitBlockCurrentUser) {
        $this->bitBlockCurrentUser = $bitBlockCurrentUser;
        return $this;
    }

    /**
     * @param mixed $bitGroups
     * @return class_formentry_user
     */
    public function setBitGroups($bitGroups) {
        $this->bitGroups = $bitGroups;
        return $this;
    }

    /**
     * @param mixed $bitUser
     * @return class_formentry_user
     */
    public function setBitUser($bitUser) {
        $this->bitUser = $bitUser;
        return $this;
    }





}
