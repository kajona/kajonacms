<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * The admin-form generator is used to create, validate and mangage forms for the backend.
 * Those forms are created as automatically as possible, so the setup of the field-types, validators
 * and more is done by reflection and code-inspection. Therefore, especially the annotations on the extending
 * class_model-objects are analyzed.
 *
 * There are three ways of adding entries to the current form, each representing a different level of
 * automation.
 * 1. generateFieldsFromObject(), everything is rendered automatically
 * 2. addDynamicField(), adds a single field based on its name
 * 3. addField(), pass a field to add it explicitely
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @module module_formgenerator
 */
class class_admin_formgenerator {

    const  STR_TYPE_ANNOTATION      = "@fieldType";
    const  STR_VALIDATOR_ANNOTATION = "@fieldValidator";
    const  STR_MANDATORY_ANNOTATION = "@fieldMandatory";
    const  STR_LABEL_ANNOTATION     = "@fieldLabel";

    const  BIT_BUTTON_SUBMIT = 2;
    const  BIT_BUTTON_CLOSE  = 4;
    const  BIT_BUTTON_CANCEL = 8;


    private $intButtonConfig = 2;

    /**
     * The list of form-entries
     *
     * @var class_formentry_base[]|interface_formentry[]
     */
    private $arrFields = array();

    /**
     * The internal name of the form. Used to generate the field-identifiers and more.
     * @var string
     */
    private $strFormname = "";

    /**
     * The source-object to be rendered by the form
     * @var class_model
     */
    private $objSourceobject = null;

    private $arrValidationErrors = array();

    /**
     * Creates a new instance of the form-generator.
     *
     * @param $strFormname
     * @param class_model $objSourceobject
     */
    public function __construct($strFormname, class_model $objSourceobject) {
        $this->strFormname = $strFormname;
        $this->objSourceobject = $objSourceobject;
    }

    /**
     * Stores the values saved with the params-array back to the currently associated object.
     * Afterwards, the object may be persisted.
     */
    public function updateSourceObject() {
        foreach($this->arrFields as $objOneField)
            $objOneField->setValueToObject();
    }

    /**
     * Returns an array of required fields.
     * @return string[] where string[fielName] = type
     */
    public function getRequiredFields() {
        $arrReturn = array();
        foreach($this->arrFields as $objOneField)
            if($objOneField->getBitMandatory())
                $arrReturn[$objOneField->getStrEntryName()] = $objOneField->getObjValidator()->getStrName();

        return $arrReturn;
    }

    /**
     * Validates the current form.
     * @return bool
     */
    public function validateForm() {
        foreach($this->arrFields as $objOneField)
            if($objOneField->getBitMandatory() && !$objOneField->validateValue())
                $this->arrValidationErrors[$objOneField->getStrEntryName()] = $objOneField->getStrLabel();

        return count($this->arrValidationErrors) == 0;
    }

    /**
     * @param $strTargetURI
     * @param int $intButtonConfig a list of buttons to attach to the end of the form. if you need more then the obligatory save-button,
     *                             pass them combined by a bitwise or, e.g. class_admin_formgenerator::$BIT_BUTTON_SUBMIT | class_admin_formgenerator::$BIT_BUTTON_CANCEL
     * @return string
     */
    public function renderForm($strTargetURI, $intButtonConfig = 2) {
        $strReturn = "";

        //add a hidden systemid-field
        $objField = new class_formentry_hidden($this->strFormname, "systemid");
        $objField->setStrEntryName("systemid")->setStrValue($this->objSourceobject->getSystemid())->setObjValidator(new class_systemid_validator());
        $this->addField($objField);

        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn .= $objToolkit->formHeader($strTargetURI);
        $strReturn .= $objToolkit->getValidationErrors($this);

        foreach($this->arrFields as $objOneField)
            $strReturn .= $objOneField->renderField();

        if($intButtonConfig & self::BIT_BUTTON_SUBMIT)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_save", "system"), "submit");

        if($intButtonConfig & self::BIT_BUTTON_CANCEL)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_cancel", "system"), "cancel");

        if($intButtonConfig & self::BIT_BUTTON_CLOSE)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_close", "system"), "submit");



        $strReturn .= $objToolkit->formClose();

        if(count($this->arrFields) > 0) {
            reset($this->arrFields);
            $objField = current($this->arrFields);
            $strReturn .= $objToolkit->setBrowserFocus($objField->getStrEntryName());
        }

        return $strReturn;
    }

    /**
     * This is the most dynamically way to build a form.
     * Using this method, the current object is analyzed regarding its
     * methods and annotation. As soon as a matching property is found, the field
     * is added to the current list of form-entries.
     * Therefore the internal method addDynamicField is used.
     * In order to identify a field as relevant, the getter has to be marked with a fieldType annotation.
     *
     * @return void
     */
    public function generateFieldsFromObject() {

        //load all methods
        $objReflection = new ReflectionClass($this->objSourceobject);
        $objAnnotations = new class_reflection($this->objSourceobject);

        $arrMethods = $objReflection->getMethods();
        foreach($arrMethods as $objOneMethod) {
            if($objAnnotations->hasMethodAnnotation($objOneMethod->name, "@fieldType")) {
                $this->addDynamicField(uniStrtolower(uniStrReplace(array("getStr", "getInt", "getBit", "getLong"), array(), $objOneMethod->name)));
            }
        }

        return;
    }

    /**
     * Adds a new field to the current form.
     * Therefore, the current source-object is inspected regarding the passed propertyname.
     * So it is essential to provide the matching getters and setters in order to have all
     * set up dynamically.
     *
     * @param $strPropertyName
     * @return class_formentry_base|interface_formentry
     * @throws class_exception
     */
    public function addDynamicField($strPropertyName) {

        //try to get the matching getter
        $objReflection = new class_reflection($this->objSourceobject);
        $strGetter = $objReflection->getGetter($strPropertyName);
        if($strGetter === null)
            throw new class_exception("unable to find getter for property ".$strPropertyName."@".get_class($this->objSourceobject), class_exception::$level_ERROR);

        $strType      = $objReflection->getMethodAnnotationValue($strGetter, self::STR_TYPE_ANNOTATION);
        $strValidator = $objReflection->getMethodAnnotationValue($strGetter, self::STR_VALIDATOR_ANNOTATION);
        $strMandatory = $objReflection->getMethodAnnotationValue($strGetter, self::STR_MANDATORY_ANNOTATION);
        $strLabel     = $objReflection->getMethodAnnotationValue($strGetter, self::STR_LABEL_ANNOTATION);

        if($strType === false)
            $strType = "text";

        $objField = $this->getFormEntryInstance($strType, $strPropertyName);
        if($strLabel !== false) {
            $objField->updateLabel($strLabel);
        }

        $bitMandatory = false;
        if($strMandatory !== false && $strMandatory !== "false")
            $bitMandatory = true;

        $objField->setBitMandatory($bitMandatory);

        if($strValidator !== false) {
            $objField->setObjValidator($this->getValidatorInstance($strValidator));
        }

        $this->addField($objField, $strPropertyName);

        return $objField;
    }


    /**
     * Loads the field-entry identified by the passed name.
     *
     * @param $strName
     * @param $strPropertyname
     * @return class_formentry_base|interface_formentry
     * @throws class_exception
     */
    private function getFormEntryInstance($strName, $strPropertyname) {

        $strClassname = "class_formentry_".$strName;
        if(class_resourceloader::getInstance()->getPathForFile("/admin/formentries/".$strClassname.".php")) {
            return new $strClassname($this->strFormname, $strPropertyname, $this->objSourceobject);
        }
        else
            throw new class_exception("failed to load form-entry of type ".$strClassname, class_exception::$level_ERROR);

    }

    /**
     * Loads the validator identified by the passed name.
     *
     * @param $strName
     * @return interface_validator
     * @throws class_exception
     */
    private function getValidatorInstance($strName) {
        $strClassname = "class_".$strName."_validator";
        if(class_resourceloader::getInstance()->getPathForFile("/system/validators/".$strClassname.".php")) {
            return new $strClassname();
        }
        else
            throw new class_exception("failed to load validator of type ".$strClassname, class_exception::$level_ERROR);
    }

    /**
     * Returns an array of validation-errors
     * @return array
     */
    public function getArrValidationErrors() {
        return $this->arrValidationErrors;
    }

    /**
     * Returns an array of validation-errors.
     * Alias for getArrValidationErrors due to backwards compatibility.
     * @return array
     * @see getArrValidationErrors
     */
    public function getValidationErrors() {
        return $this->getArrValidationErrors();
    }

    /**
     * Adds an additional, user-specific validation-error to the current list of errors.
     *
     * @param $strEntry
     * @param $strMessage
     */
    public function addValidationError($strEntry, $strMessage) {
        $this->arrValidationErrors[$strEntry] = $strMessage;
    }

    /**
     * Adds a single field to the current form, the hard, manual way.
     * Use this method if you want to add custom fields to the current form.
     *
     * @param class_formentry_base $objField
     * @param string $strKey
     * @return class_formentry_base|interface_formentry
     */
    public function addField(class_formentry_base $objField, $strKey = "") {
        if($strKey == "")
            $strKey = $objField->getStrEntryName();

        $this->arrFields[$strKey] = $objField;

        return $objField;
    }

    /**
     * Returns a single entry form the fields, identified by its form-entry-name.
     * @param $strName
     * @return class_formentry_base|interface_formentry
     */
    public function getField($strName) {
        if(isset($this->arrFields[$strName]))
            return $this->arrFields[$strName];
        else
            return null;
    }

}
