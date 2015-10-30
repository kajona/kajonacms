<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * The base-class for all form-entries.
 * Holds common values and common method-logic to reduce the amount
 * of own code as much as possible.
 * In addition to extending class_formentry_base, make sure to implement interface_formentry, too.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class class_formentry_base {

    /**
     * @var class_model
     */
    private $objSourceObject = null;

    /**
     * The name of the property as used in the forms, leading type-prefix is removed
     * @var null
     */
    private $strSourceProperty = null;
    private $strFormName = "";

    /**
     * @var interface_validator
     */
    private $objValidator;


    private $strLabel = null;
    private $strValidationErrorMsg = "";
    private $strEntryName = null;
    private $bitMandatory = false;
    private $strValue = null;
    private $strHint = null;
    private $bitReadonly = false;




    /**
     * Creates a new instance of the current field.
     *
     * @param $strFormName
     * @param $strSourceProperty
     * @param class_model $objSourceObject
     */
    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        $this->strSourceProperty = $strSourceProperty;
        $this->objSourceObject = $objSourceObject;
        $this->strFormName = $strFormName;

        if($strFormName != "")
            $strFormName .= "_";

        $this->strEntryName = uniStrtolower($strFormName.$strSourceProperty);

        if($objSourceObject != null) {
            $this->updateLabel();
        }
        $this->updateValue();
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
        if(isset($arrParams[$this->strEntryName])) {
            $this->setStrValue($arrParams[$this->strEntryName]);
        }
        else {
            $this->setStrValue($this->getValueFromObject());
        }
    }

    /**
     * Loads the fields label-text, based on a combination of form-name and property-name.
     * The generated label may be overwritten if necessary.
     */
    public function updateLabel($strKey = "") {

        //check, if label is set as a property
        if($strKey != "") {
            $this->strLabel = class_carrier::getInstance()->getObjLang()->getLang($strKey, $this->objSourceObject->getArrModule("modul"));
        }
        else {
            $this->strLabel = class_carrier::getInstance()->getObjLang()->getLang("form_".$this->strFormName."_".$this->strSourceProperty, $this->objSourceObject->getArrModule("modul"));
            $strKey = "form_".$this->strFormName."_".$this->strSourceProperty;
        }

        $strHint = $strKey."_hint";
        if(class_carrier::getInstance()->getObjLang()->getLang($strHint, $this->objSourceObject->getArrModule("modul")) != "!".$strHint."!")
            $this->setStrHint(class_carrier::getInstance()->getObjLang()->getLang($strHint, $this->objSourceObject->getArrModule("modul")));
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

        if($this->objSourceObject == null)
            return "";

        //try to get the matching getter
        $objReflection = new class_reflection($this->objSourceObject);
        $strGetter = $objReflection->getGetter($this->strSourceProperty);
        if($strGetter === null)
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

        if($this->objSourceObject == null)
            return "";

        $objReflection = new class_reflection($this->objSourceObject);
        $strSetter = $objReflection->getSetter($this->strSourceProperty);
        if($strSetter === null)
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
        if(trim($strHint) != "") {
            $strHint = nl2br($strHint);
        }
        $this->strHint = $strHint;
        return $this;
    }

    public function getStrHint() {
        return $this->strHint;
    }

    /**
     * @param $bitReadonly
     * @return class_formentry_base
     */
    public function setBitReadonly($bitReadonly) {
        $this->bitReadonly = $bitReadonly;
        return $this;
    }

    public function getBitReadonly() {
        return $this->bitReadonly;
    }

    public function getStrSourceProperty() {
        return $this->strSourceProperty;
    }

    public function getObjSourceObject() {
        return $this->objSourceObject;
    }

    /**
     * @param \class_model $objSourceObject
     */
    public function setObjSourceObject($objSourceObject) {
        $this->objSourceObject = $objSourceObject;
    }


    public function setStrValidationErrorMsg($strValidationErrorMsg) {
        $this->strValidationErrorMsg = $strValidationErrorMsg;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrValidationErrorMsg() {
        if($this->strValidationErrorMsg != "") {
            return $this->strValidationErrorMsg;
        }
        else {
            if($this->getObjValidator() instanceof interface_validator_extended) {
                return "'".$this->getStrLabel()."': ".$this->getObjValidator()->getValidationMessage();
            }
            else {
                return "'".$this->getStrLabel()."'";
            }
        }
    }

    protected function getAnnotationParamsForCurrentProperty() {
        //params

        if($this->getObjSourceObject() != null) {
            $objReflection = new class_reflection($this->getObjSourceObject());

            $arrProperties = $objReflection->getPropertiesWithAnnotation("@fieldType");
            $strSourceProperty = null;
            foreach ($arrProperties as $strPropertyName => $strValue) {

                $strPropertyWithoutPrefix = "";

                $strStart = uniSubstr($strPropertyName, 0, 3);
                if (in_array($strStart, array("int", "bit", "str", "arr", "obj"))) {
                    $strPropertyWithoutPrefix = uniStrtolower(uniSubstr($strPropertyName, 3));
                }

                $strStart = uniSubstr($strPropertyName, 0, 4);
                if (in_array($strStart, array("long"))) {
                    $strPropertyWithoutPrefix = uniStrtolower(uniSubstr($strPropertyName, 4));
                }

                $strStart = uniSubstr($strPropertyName, 0, 5);
                if (in_array($strStart, array("float"))) {
                    $strPropertyWithoutPrefix = uniStrtolower(uniSubstr($strPropertyName, 5));
                }

                if ($strPropertyWithoutPrefix == $this->getStrSourceProperty()) {
                    $strSourceProperty = $strPropertyName;
                    break;
                }
            }
            //get key vlaues
            return $objReflection->getAnnotationValueForProperty($strSourceProperty, "@fieldType", class_reflection_enum::PARAMS());
        }

        return array();
    }


}
