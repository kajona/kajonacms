<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_versionable.php 4413 2012-01-03 19:38:11Z sidler $                               *
********************************************************************************************************/

/**
 * The base-class for all form-entries.
 * Holds common values and common method-logic to reduce the amount
 * of own code as much as possible.
 * In addition to extending class_formentry_base, make sure to implement interface_formentry, too.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
class class_formentry_base {

    /**
     * @var class_model
     */
    private $objSourceObject = null;
    private $strSourceProperty = null;
    private $strFormName = "";

    /**
     * @var interface_validator
     */
    private $objValidator;


    private $strLabel = null;
    private $strEntryName = null;
    private $bitMandatory = false;
    private $strValue = null;
    private $strHint = null;



    /**
     * Creates a new instance of the current field.
     *
     * @param $strFormName
     * @param $strSourceProperty
     * @param class_model $objSourceObject
     */
    public function __construct($strFormName, $strSourceProperty, class_model $objSourceObject = null) {
        $this->strSourceProperty = $strSourceProperty;
        $this->objSourceObject = $objSourceObject;
        $this->strFormName = $strFormName;

        if($strFormName != "")
            $strFormName .= "_";

        $this->strEntryName = uniStrtolower($strFormName.$strSourceProperty);

        if($objSourceObject != null) {
            $this->updateLabel();
            $this->updateValue();
        }
    }

    /**
     * Uses the current validator and validates the current value.
     * @return bool
     */
    public function validateValue() {
        return $this->getObjValidator()->validate($this->getStrValue());
    }

    /**
     * Queries the params-array or the source-object for the mapped value.
     * If found in the params-array, the value will be used, otherwise
     * the source-objects' getter is invoked.
     */
    protected function updateValue() {
        $arrParams = class_carrier::getAllParams();
        if(isset($arrParams[$this->strEntryName]))
            $this->strValue = $arrParams[$this->strEntryName];
        else
            $this->strValue = $this->getValueFromObject();
    }

    /**
     * Loads the fields label-text, based on a combination of form-name and property-name.
     * The generated label may be overwritten if necessary.
     */
    protected function updateLabel() {
        $this->strLabel = class_carrier::getInstance()->getObjLang()->getLang("form_".$this->strFormName."_".$this->strSourceProperty, $this->objSourceObject->getArrModule("modul"));
    }

    /**
     * Calls the source-objects getter and loads the value.
     * Only used, if the field is not already populated to the
     * global params-array.
     *
     * @throws class_exception
     * @return mixed
     */
    protected function getValueFromObject() {

        //try to get the matching getter
        $strGetter = "getStr".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strGetter))
            $strGetter = "getInt".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strGetter))
            $strGetter = "getFloat".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strGetter))
            $strGetter = "getBit".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strGetter))
            $strGetter = "get".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strGetter))
            throw new class_exception("unable to find getter for value-property ".$this->strSourceProperty."@".get_class($this->objSourceObject), class_exception::$level_ERROR);

        return call_user_func(array($this->objSourceObject, $strGetter));

    }

    /**
     * Calls the source-objects setter and stores the value.
     * If you want to skip a single setter, remove the field before.
     *
     * @throws class_exception
     * @return mixed
     */
    public function setValueToObject() {

        //try to get the matching getter
        $strSetter = "setStr".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strSetter))
            $strSetter = "setInt".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strSetter))
            $strSetter = "setFloat".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strSetter))
            $strSetter = "setBit".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strSetter))
            $strSetter = "set".$this->strSourceProperty;

        if(!method_exists($this->objSourceObject, $strSetter))
            throw new class_exception("unable to find setter for value-property ".$this->strSourceProperty."@".get_class($this->objSourceObject), class_exception::$level_ERROR);

        return call_user_func(array($this->objSourceObject, $strSetter), $this->getStrValue());

    }

    /**
     * @param bool $bitMandatory
     * @return class_formentry_base
     */
    public function setBitMandatory($bitMandatory) {
        $this->bitMandatory = $bitMandatory;
        return $this;
    }

    public function getBitMandatory() {
        return $this->bitMandatory;
    }

    /**
     * @param string $strLabel
     * @return class_formentry_base
     */
    public function setStrLabel($strLabel) {
        $this->strLabel = $strLabel;
        return $this;
    }

    public function getStrLabel() {
        return $this->strLabel;
    }

    /**
     * @param \interface_validator $objValidator
     * @return \class_formentry_base
     */
    public function setObjValidator(interface_validator $objValidator) {
        $this->objValidator = $objValidator;
        return $this;
    }

    /**
     * @return \interface_validator
     */
    public function getObjValidator() {
        return $this->objValidator;
    }

    public function setStrFormName($strFormName) {
        $this->strFormName = $strFormName;
    }

    public function getStrFormName() {
        return $this->strFormName;
    }

    /**
     * @param $strEntryName
     * @return class_formentry_base
     */
    public function setStrEntryName($strEntryName) {
        $this->strEntryName = $strEntryName;
        return $this;
    }

    public function getStrEntryName() {
        return $this->strEntryName;
    }

    /**
     * @param $strValue
     * @return class_formentry_base
     */
    public function setStrValue($strValue) {
        $this->strValue = $strValue;
        return $this;
    }

    public function getStrValue() {
        return $this->strValue;
    }

    /**
     * @param $strHint
     * @return class_formentry_base
     */
    public function setStrHint($strHint) {
        $this->strHint = $strHint;
        return $this;
    }

    public function getStrHint() {
        return $this->strHint;
    }


}
