<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_versionable.php 4413 2012-01-03 19:38:11Z sidler $                                   *
********************************************************************************************************/


/**
 * The admin-form generator is used to create, validate and mangage forms for the backend.
 * Those forms are created as automatically as possible, so the setup of the field-types, validators
 * and more is done by reflection and code-inspection. Therefore, especially the annotations on the extending
 * class_model-objects are analyzed.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @module module_system
 */
class class_admin_formgenerator {

    private static $STR_TYPE_ANNOTATION = "@fieldType";
    private static $STR_VALIDATOR_ANNOTATION = "@fieldValidator";
    private static $STR_MANDATORY_ANNOTATION = "@fieldMandatory";

    public static $BIT_BUTTON_SUBMIT = 2;
    public static $BIT_BUTTON_CLOSE = 4;
    public static $BIT_BUTTON_CANCEL = 8;


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


    private $arrValidationErrors = null;

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
        $this->arrValidationErrors = array();
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

        if($intButtonConfig & self::$BIT_BUTTON_SUBMIT)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_save", "system"), "submit");

        if($intButtonConfig & self::$BIT_BUTTON_CANCEL)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_cancel", "system"), "cancel");

        if($intButtonConfig & self::$BIT_BUTTON_CLOSE)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_close", "system"), "submit");



        $strReturn .= $objToolkit->formClose();

        if(count($this->arrFields) > 0) {
            $objField = $this->arrFields[0];
            $strReturn .= $objToolkit->setBrowserFocus($objField->getStrEntryName());
        }

        return $strReturn;
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
        $strGetter = "getStr".$strPropertyName;

        if(!method_exists($this->objSourceobject, $strGetter))
            $strGetter = "getInt".$strPropertyName;

        if(!method_exists($this->objSourceobject, $strGetter))
            $strGetter = "getFloat".$strPropertyName;

        if(!method_exists($this->objSourceobject, $strGetter))
            $strGetter = "getBit".$strPropertyName;

        if(!method_exists($this->objSourceobject, $strGetter))
            throw new class_exception("unable to find getter for property ".$strPropertyName."@".get_class($this->objSourceobject), class_exception::$level_ERROR);



        $objAnnotation = new class_annotations($this->objSourceobject);

        $strType      = $objAnnotation->getMethodAnnotationValue($strGetter, self::$STR_TYPE_ANNOTATION);
        $strValidator = $objAnnotation->getMethodAnnotationValue($strGetter, self::$STR_VALIDATOR_ANNOTATION);
        $strMandatory = $objAnnotation->getMethodAnnotationValue($strGetter, self::$STR_MANDATORY_ANNOTATION);

        if($strType === false)
            $strType = "text";

        $objField = $this->getFormEntryInstance($strType, $strPropertyName);

        $bitMandatory = false;
        if($strMandatory !== false && $strMandatory !== "false")
            $bitMandatory = true;

        $objField->setBitMandatory($bitMandatory);

        if($strValidator !== false) {
            $objField->setObjValidator($this->getValidatorInstance($strValidator));
        }

        $this->addField($objField);

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

    public function getArrValidationErrors() {
        if($this->arrValidationErrors == null)
            $this->validateForm();

        return $this->arrValidationErrors;
    }

    public function getValidationErrors() {
        return $this->getArrValidationErrors();
    }

    /**
     * @param class_formentry_base $objField
     * @return class_formentry_base
     */
    public function addField(class_formentry_base $objField) {
        $this->arrFields[] = $objField;

        return $objField;
    }

}
