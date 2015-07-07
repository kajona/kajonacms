<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * The admin-form generator is used to create, validate and manage forms for the backend.
 * Those forms are created as automatically as possible, so the setup of the field-types, validators
 * and more is done by reflection and code-inspection. Therefore, especially the annotations on the extending
 * class_model-objects are analyzed.
 *
 * There are three ways of adding entries to the current form, each representing a different level of
 * automation.
 * 1. generateFieldsFromObject(), everything is rendered automatically
 * 2. addDynamicField(), adds a single field based on its name
 * 3. addField(), pass a field to add it explicitly
 *
 * @author sidler@mulchprod.de
 * @since  4.0
 * @module module_formgenerator
 */
class class_admin_formgenerator {

    const  STR_TYPE_ANNOTATION      = "@fieldType";
    const  STR_VALIDATOR_ANNOTATION = "@fieldValidator";
    const  STR_MANDATORY_ANNOTATION = "@fieldMandatory";
    const  STR_LABEL_ANNOTATION     = "@fieldLabel";
    const  STR_HIDDEN_ANNOTATION    = "@fieldHidden";
    const  STR_READONLY_ANNOTATION  = "@fieldReadonly";
    const  STR_OBJECTVALIDATOR_ANNOTATION  = "@objectValidator";

    const  BIT_BUTTON_SAVE   = 2;
    const  BIT_BUTTON_CLOSE  = 4;
    const  BIT_BUTTON_CANCEL = 8;
    const  BIT_BUTTON_SUBMIT = 16;
    const  BIT_BUTTON_DELETE = 32;
    const  BIT_BUTTON_RESET  = 64;
    const  BIT_BUTTON_CONTINUE  = 128;
    const  BIT_BUTTON_BACK = 256;

    const FORM_ENCTYPE_MULTIPART = "multipart/form-data";
    const FORM_ENCTYPE_TEXTPLAIN = "text/plain";

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

    private $arrHiddenElements = array();
    private $strHiddenGroupTitle = "additional fields";
    private $bitHiddenElementsVisible = false;

    private $strFormEncoding = "";

    private $strOnSubmit = "";
    private $objLang;

    /**
     * Creates a new instance of the form-generator.
     *
     * @param string $strFormname
     * @param class_model $objSourceobject
     */
    public function __construct($strFormname,  $objSourceobject) {
        $this->strFormname = $strFormname;
        $this->objSourceobject = $objSourceobject;

        $this->strOnSubmit = "$(this).on('submit', function() { return false; }); return true;";
        $this->objLang = class_lang::getInstance();
    }

    /**
     * Stores the values saved with the params-array back to the currently associated object.
     * Afterwards, the object may be persisted.
     *
     * @return void
     */
    public function updateSourceObject() {
        foreach($this->arrFields as $objOneField) {
            if($objOneField->getObjSourceObject() != null) {
                $objOneField->setValueToObject();
            }
        }
    }

    /**
     * Returns an array of required fields.
     * @return string[] where string[fielName] = type
     */
    public function getRequiredFields() {
        $arrReturn = array();
        foreach($this->arrFields as $objOneField)
            if($objOneField->getBitMandatory())
                $arrReturn[$objOneField->getStrEntryName()] = get_class($objOneField->getObjValidator());

        return $arrReturn;
    }

    /**
     * Validates the current form.
     *
     * @throws class_exception
     * @return bool
     */
    public function validateForm() {
        $objLang = class_carrier::getInstance()->getObjLang();

        //1. Validate fields
        foreach($this->arrFields as $objOneField) {

            $bitFieldIsEmpty
                = (!is_array($objOneField->getStrValue()) && trim($objOneField->getStrValue()) === "")
                || is_null($objOneField->getStrValue())
                || (is_array($objOneField->getStrValue()) && count($objOneField->getStrValue()) == 0); //if it is an array with no entries

            //mandatory field
            if($objOneField->getBitMandatory()) {
                //if field is mandatory and empty -> validation error
                if($bitFieldIsEmpty) {
                    $this->addValidationError($objOneField->getStrEntryName(), $objLang->getLang("commons_validator_field_empty", "system", array($objOneField->getStrLabel())));
                }
            }

            //if field is not empty -> validate
            if(!$bitFieldIsEmpty) {
                if(!$objOneField->validateValue()) {
                    $this->addValidationError($objOneField->getStrEntryName(), $objOneField->getStrValidationErrorMsg());
                }
            }
        }

        //2. Validate complete object
        if($this->getObjSourceobject() != null) {
            $objReflection = new class_reflection($this->getObjSourceobject());
            $arrObjectValidator = $objReflection->getAnnotationValuesFromClass(self::STR_OBJECTVALIDATOR_ANNOTATION);
            if(count($arrObjectValidator) == 1) {

                $strObjectValidator = $arrObjectValidator[0];
                if(!class_exists($strObjectValidator)) {
                    throw new class_exception("object validator ".$strObjectValidator." not existing", class_exception::$level_ERROR);
                }

                /** @var class_objectvalidator_base $objValidator */
                $objValidator = new $strObjectValidator();

                //Keep the reference of the current object
                $objSourceObjectTemp = $this->getObjSourceobject();

                //Create a new instance of the source object and set it as source object in the formgenerator
                //Each existing field will also reference the new created source object
                $strClassName = get_class($this->objSourceobject);
                $this->objSourceobject = new $strClassName($this->objSourceobject->getStrSystemid());
                foreach($this->arrFields as $objOneField) {
                    if($objOneField->getObjSourceObject() != null) {
                        $objOneField->setObjSourceObject($this->objSourceobject);
                    }
                }

                //if we are in new-mode, we should fix the prev-id to the lateron matching one
                if(($this->getField("mode") != null && $this->getField("mode")->getStrValue() == "new") || class_carrier::getInstance()->getParam("mode") == "new") {
                    $this->objSourceobject->setStrPrevId(class_carrier::getInstance()->getParam("systemid"));
                }

                //Update the new source object values from the fields and validate the object
                $this->updateSourceObject();
                $objValidator->validateObject($this->getObjSourceobject());

                foreach($objValidator->getArrValidationMessages() as $strKey => $arrMessages) {
                    if(!is_array($arrMessages)) {
                        throw new class_exception("method validateObject must return an array of format array(\"<messageKey>\" => array())", class_exception::$level_ERROR);
                    }

                    foreach($arrMessages as $strMessage) {
                        $this->addValidationError($strKey, $strMessage);
                    }
                }

                //Set back kept reference to the formgenerator and all it's fields
                $this->objSourceobject = $objSourceObjectTemp;
                foreach($this->arrFields as $objOneField) {
                    if($objOneField->getObjSourceObject() != null) {
                        $objOneField->setObjSourceObject($objSourceObjectTemp);
                    }
                }
            }
        }

        return count($this->arrValidationErrors) == 0;
    }

    /**
     * @param string $strTargetURI If you pass null, no form-tags will be rendered.
     * @param int $intButtonConfig a list of buttons to attach to the end of the form. if you need more then the obligatory save-button,
     *                             pass them combined by a bitwise or, e.g. class_admin_formgenerator::BIT_BUTTON_SAVE | class_admin_formgenerator::$BIT_BUTTON_CANCEL
     *
     * @throws class_exception
     * @return string
     */
    public function renderForm($strTargetURI, $intButtonConfig = 2) {
        $strReturn = "";

        //add a hidden systemid-field
        if($this->objSourceobject != null) {
            $objField = new class_formentry_hidden($this->strFormname, "systemid");
            $objField->setStrEntryName("systemid")->setStrValue($this->objSourceobject->getSystemid())->setObjValidator(new class_systemid_validator());
            $this->addField($objField);
        }

        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        if($strTargetURI !== null)
            $strReturn .= $objToolkit->formHeader($strTargetURI, "", $this->strFormEncoding, $this->strOnSubmit);
        $strReturn .= $objToolkit->getValidationErrors($this);

        $strHidden = "";
        foreach($this->arrFields as $objOneField) {
            if(in_array($objOneField->getStrEntryName(), $this->arrHiddenElements))
                $strHidden .= $objOneField->renderField();
            else
                $strReturn .= $objOneField->renderField();
        }

        if($strHidden != "")
            $strReturn .= $objToolkit->formOptionalElementsWrapper($strHidden, $this->strHiddenGroupTitle, $this->bitHiddenElementsVisible);

        if($intButtonConfig & self::BIT_BUTTON_SUBMIT)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_submit", "system"), "submitbtn");

        if($intButtonConfig & self::BIT_BUTTON_SAVE)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_save", "system"), "submitbtn");

        if($intButtonConfig & self::BIT_BUTTON_CANCEL)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_cancel", "system"), "cancelbtn");

        if($intButtonConfig & self::BIT_BUTTON_CLOSE)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_close", "system"), "submitbtn");

        if($intButtonConfig & self::BIT_BUTTON_DELETE)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_delete", "system"), "submitbtn");

        if($intButtonConfig & self::BIT_BUTTON_RESET)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_reset", "system"), "reset", "", "cancelbutton");

        if($intButtonConfig & self::BIT_BUTTON_CONTINUE)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_continue", "system"), "submitbtn");

        if($intButtonConfig & self::BIT_BUTTON_BACK)
            $strReturn .= $objToolkit->formInputSubmit(class_lang::getInstance()->getLang("commons_back", "system"), "backbtn");


        if($strTargetURI !== null)
            $strReturn .= $objToolkit->formClose();

        if(count($this->arrFields) > 0) {
            reset($this->arrFields);

            do {
                $objField = current($this->arrFields);
                if(!$objField instanceof class_formentry_hidden
                    && !$objField instanceof class_formentry_plaintext
                    && !$objField instanceof class_formentry_headline
                    && !$objField instanceof class_formentry_divider
                ) {
                    $strReturn .= $objToolkit->setBrowserFocus($objField->getStrEntryName());
                    break;
                }
            }
            while(next($this->arrFields) !== false);

        }

        //lock the record to avoid multiple edit-sessions - if in edit mode
        if($this->objSourceobject != null && method_exists($this->objSourceobject, "getLockManager")) {

            $bitSkip = false;
            if($this->getField("mode") != null && $this->getField("mode")->getStrValue() == "new")
                $bitSkip = true;

            if(!$bitSkip && !validateSystemid($this->objSourceobject->getSystemid()))
                $bitSkip = true;

            if(!$bitSkip) {
                if($this->objSourceobject->getLockManager()->isAccessibleForCurrentUser()) {
                    $this->objSourceobject->getLockManager()->lockRecord();
                }
                else {
                    $objUser = new class_module_user_user($this->objSourceobject->getLockManager()->getLockId());
                    throw new class_exception("Current record is already locked by user '".$objUser->getStrDisplayName()."'.\nCannot be locked for the current user", class_exception::$level_ERROR);
                }
            }
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
        $objAnnotations = new class_reflection($this->objSourceobject);

        $arrProperties = $objAnnotations->getPropertiesWithAnnotation("@fieldType");
        foreach($arrProperties as $strPropertyName => $strDataType) {
            $this->addDynamicField($strPropertyName);
        }

        return;
    }

    /**
     * Adds a new field to the current form.
     * Therefore, the current source-object is inspected regarding the passed propertyname.
     * So it is essential to provide the matching getters and setters in order to have all
     * set up dynamically.
     *
     * @param string $strPropertyName
     * @return class_formentry_base|interface_formentry
     * @throws class_exception
     */
    public function addDynamicField($strPropertyName) {

        //try to get the matching getter
        $objReflection = new class_reflection($this->objSourceobject);
        $strGetter = $objReflection->getGetter($strPropertyName);
        if($strGetter === null)
            throw new class_exception("unable to find getter for property ".$strPropertyName."@".get_class($this->objSourceobject), class_exception::$level_ERROR);

        //load detailed properties

        $strType      = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_TYPE_ANNOTATION);
        $strValidator = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_VALIDATOR_ANNOTATION);
        $strMandatory = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_MANDATORY_ANNOTATION);
        $strLabel     = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_LABEL_ANNOTATION);
        $strHidden    = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_HIDDEN_ANNOTATION);
        $strReadonly  = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_READONLY_ANNOTATION);

        if($strType === null)
            $strType = "text";

        $strStart = uniSubstr($strPropertyName, 0, 3);
        if(in_array($strStart, array("int", "bit", "str", "arr", "obj")))
            $strPropertyName = uniStrtolower(uniSubstr($strPropertyName, 3));

        $strStart = uniSubstr($strPropertyName, 0, 4);
        if(in_array($strStart, array("long")))
            $strPropertyName = uniStrtolower(uniSubstr($strPropertyName, 4));

        $strStart = uniSubstr($strPropertyName, 0, 5);
        if(in_array($strStart, array("float")))
            $strPropertyName = uniStrtolower(uniSubstr($strPropertyName, 5));

        $objField = $this->getFormEntryInstance($strType, $strPropertyName);
        if($strLabel !== null) {
            $objField->updateLabel($strLabel);
        }

        $bitMandatory = false;
        if($strMandatory !== null && $strMandatory !== "false")
            $bitMandatory = true;

        $objField->setBitMandatory($bitMandatory);

        $bitReadonly = false;
        if($strReadonly !== null && $strReadonly !== "false")
            $bitReadonly = true;

        $objField->setBitReadonly($bitReadonly);


        if($strValidator !== null)
            $objField->setObjValidator($this->getValidatorInstance($strValidator));

        $this->addField($objField, $strPropertyName);

        if($strHidden !== null)
            $this->addFieldToHiddenGroup($objField);

        return $objField;
    }

    /**
     * Set the position of a single field in the list of fields, so
     * the position inside the form.
     * The position is set human-readable, so the first element uses
     * the index 1.
     *
     * @param string $strField
     * @param int $intPos
     *
     * @throws class_exception
     * @return void
     */
    public function setFieldToPosition($strField, $intPos) {

        if(!isset($this->arrFields[$strField]))
            throw new class_exception("field ".$strField." not found in list ".implode(", ", array_keys($this->arrFields)), class_exception::$level_ERROR);

        $objField = $this->arrFields[$strField];

        $arrNewOrder = array();

        $intI = 1;
        foreach($this->arrFields as $strKey => $objValue) {
            //skip the same field, is inserted somewhere else
            if($strKey == $strField)
                continue;

            if($intI == $intPos) {
                $arrNewOrder[$strField] = $objField;
                $objField = null;
            }


            $arrNewOrder[$strKey] = $objValue;

            $intI++;
        }

        if($objField !== null)
            $arrNewOrder[$strField] = $objField;


        $this->arrFields = $arrNewOrder;
    }



    /**
     * Loads the field-entry identified by the passed name.
     *
     * @param string $strName
     * @param string $strPropertyname
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
     * @param string $strClassname
     *
     * @throws class_exception
     * @return interface_validator
     */
    private function getValidatorInstance($strClassname) {
        if(uniStrpos($strClassname, "class_") === false)
            $strClassname = "class_".$strClassname."_validator";

        if(class_resourceloader::getInstance()->getPathForFile("/system/validators/".$strClassname.".php")) {
            return new $strClassname();
        }
        else
            throw new class_exception("failed to load validator of type ".$strClassname, class_exception::$level_ERROR);
    }

    /**
     * Returns an language text. By default the formname is used as module name
     *
     * @param string $strText
     * @return string
     */
    protected function getLang($strText, $strModule = null) {
        return $this->objLang->getLang($strText, $strModule === null ? $this->strFormname : $strModule);
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
     * @param string $strEntry
     * @param string $strMessage
     * @return void
     */
    public function addValidationError($strEntry, $strMessage) {
        if(!array_key_exists($strEntry, $this->arrValidationErrors)) {
            $this->arrValidationErrors[$strEntry] = array();
        }
        $this->arrValidationErrors[$strEntry][] = $strMessage;
    }

    /**
     * Removes a single validation error
     * @param string $strEntry
     * @return void
     */
    public function removeValidationError($strEntry) {
        if(isset($this->arrValidationErrors[$strEntry]))
            unset($this->arrValidationErrors[$strEntry]);
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
     * @param string $strName
     * @return class_formentry_base|interface_formentry
     */
    public function getField($strName) {
        if(isset($this->arrFields[$strName]))
            return $this->arrFields[$strName];
        else
            return null;
    }

    /**
     * Removes a single entry form the fields, identified by its form-entry-name.
     *
     * @param string $strName
     * @return $this
     */
    public function removeField($strName) {
        unset($this->arrFields[$strName]);
        return $this;
    }

    /**
     * Sets the name of the group of hidden elements
     *
     * @param string $strHiddenGroupTitle
     * @return $this
     */
    public function setStrHiddenGroupTitle($strHiddenGroupTitle) {
        $this->strHiddenGroupTitle = $strHiddenGroupTitle;
        return $this;
    }

    /**
     * Moves a single field to the list of hidden elements
     *
     * @param class_formentry_base $objField
     * @return class_formentry_base
     */
    public function addFieldToHiddenGroup(class_formentry_base $objField) {
        $this->arrHiddenElements[] = $objField->getStrEntryName();
        if(!isset($this->arrFields[$objField->getStrEntryName()]) && !isset($this->arrFields[$objField->getStrSourceProperty()]))
            $this->addField($objField);
        return $objField;
    }

    /**
     * Makes the group of hidden elements visible or hides the content on page-load
     *
     * @param bool $bitHiddenElementsVisible
     * @return void
     */
    public function setBitHiddenElementsVisible($bitHiddenElementsVisible) {
        $this->bitHiddenElementsVisible = $bitHiddenElementsVisible;
    }

    /**
     * @return \class_model|interface_model
     */
    public function getObjSourceobject() {
        return $this->objSourceobject;
    }

    /**
     * @return class_formentry_base[]|interface_formentry[]
     */
    public function getArrFields() {
        return $this->arrFields;
    }

    /**
     * Allows to inject an onsubmit handler
     *
     * @param string $strOnSubmit
     * @return void
     */
    public function setStrOnSubmit($strOnSubmit) {
        $this->strOnSubmit = $strOnSubmit;
    }

    /**
     * @return string
     */
    public function getStrOnSubmit() {
        return $this->strOnSubmit;
    }

    /**
     * @param string $strFormEncoding
     * @return void
     */
    public function setStrFormEncoding($strFormEncoding) {
        $this->strFormEncoding = $strFormEncoding;
    }

    /**
     * @return string
     */
    public function getStrFormEncoding() {
        return $this->strFormEncoding;
    }

    /**
     * @return string
     */
    public function getStrFormname() {
        return $this->strFormname;
    }


}
