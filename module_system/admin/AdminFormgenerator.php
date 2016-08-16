<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\Admin\Formentries\FormentryBase;
use Kajona\System\Admin\Formentries\FormentryDivider;
use Kajona\System\Admin\Formentries\FormentryHeadline;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\Formentries\FormentryPlaintext;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\Lang;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Reflection;
use Kajona\System\System\ReflectionEnum;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\UserUser;
use Kajona\System\System\ValidatorInterface;
use Kajona\System\System\Validators\ObjectvalidatorBase;
use Kajona\System\System\Validators\SystemidValidator;


/**
 * The admin-form generator is used to create, validate and manage forms for the backend.
 * Those forms are created as automatically as possible, so the setup of the field-types, validators
 * and more is done by reflection and code-inspection. Therefore, especially the annotations on the extending
 * \Kajona\System\System\Model-objects are analyzed.
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
class AdminFormgenerator
{

    const STR_METHOD_POST = "POST";
    const STR_METHOD_GET = "GET";

    const  STR_TYPE_ANNOTATION = "@fieldType";
    const  STR_VALIDATOR_ANNOTATION = "@fieldValidator";
    const  STR_MANDATORY_ANNOTATION = "@fieldMandatory";
    const  STR_LABEL_ANNOTATION = "@fieldLabel";
    const  STR_HIDDEN_ANNOTATION = "@fieldHidden";
    const  STR_READONLY_ANNOTATION = "@fieldReadonly";
    const  STR_OBJECTVALIDATOR_ANNOTATION = "@objectValidator";

    const  BIT_BUTTON_SAVE = 2;
    const  BIT_BUTTON_CLOSE = 4;
    const  BIT_BUTTON_CANCEL = 8;
    const  BIT_BUTTON_SUBMIT = 16;
    const  BIT_BUTTON_DELETE = 32;
    const  BIT_BUTTON_RESET = 64;
    const  BIT_BUTTON_CONTINUE = 128;
    const  BIT_BUTTON_BACK = 256;
    const  BIT_BUTTON_SAVENEXT = 512;

    const FORM_ENCTYPE_MULTIPART = "multipart/form-data";
    const FORM_ENCTYPE_TEXTPLAIN = "text/plain";

    const STR_FORM_ON_SAVE_RELOAD_PARAM = "onsavereloadaction";

    /**
     * The list of form-entries
     *
     * @var FormentryBase[]|FormentryInterface[]
     */
    private $arrFields = array();

    /**
     * The internal name of the form. Used to generate the field-identifiers and more.
     *
     * @var string
     */
    private $strFormname = "";

    /**
     * The source-object to be rendered by the form
     *
     * @var Model
     */
    private $objSourceobject = null;

    private $arrValidationErrors = array();

    private $arrHiddenElements = array();
    private $strHiddenGroupTitle = "additional fields";
    private $bitHiddenElementsVisible = false;

    private $strFormEncoding = "";

    private $strOnSubmit = "";
    private $strMethod = "POST";
    private $objLang;


    /**
     * After save action is being called, this URL will used for the reload URL
     *
     * @var null
     */
    private $strOnSaveRedirectUrl = null;//

    /**
     * A list of buttons to attach to the end of the form.
     * pass them single or combined by a bitwise OR, e.g. AdminFormgenerator::BIT_BUTTON_SAVE | AdminFormgenerator::$BIT_BUTTON_CANCEL
     *
     * @var null
     */
    private $intButtonConfig = null;

    /**
     * Creates a new instance of the form-generator.
     *
     * @param string $strFormname
     * @param Model $objSourceobject
     */
    public function __construct($strFormname, $objSourceobject)
    {
        $this->strFormname = $strFormname;
        $this->objSourceobject = $objSourceobject;

        $this->strOnSubmit = "$(this).on('submit', function() { return false; }); $(window).off('unload'); KAJONA.admin.forms.animateSubmit(this); return true;";
        $this->objLang = Lang::getInstance();
    }

    /**
     * Stores the values saved with the params-array back to the currently associated object.
     * Afterwards, the object may be persisted.
     *
     * @return void
     */
    public function updateSourceObject()
    {
        foreach ($this->arrFields as $objOneField) {
            if ($objOneField->getObjSourceObject() != null) {
                $objOneField->setValueToObject();
            }
        }
    }

    /**
     * Returns an array of required fields.
     *
     * @return string[] where string[fielName] = type
     */
    public function getRequiredFields()
    {
        $arrReturn = array();
        foreach ($this->arrFields as $objOneField) {
            if ($objOneField->getBitMandatory()) {
                $arrReturn[$objOneField->getStrEntryName()] = get_class($objOneField->getObjValidator());
            }
        }

        return $arrReturn;
    }

    /**
     * Validates the current form.
     *
     * @throws Exception
     * @return bool
     */
    public function validateForm()
    {
        $objLang = Carrier::getInstance()->getObjLang();

        //1. Validate fields
        foreach ($this->arrFields as $objOneField) {

            $bitFieldIsEmpty = $objOneField->isFieldEmpty();

            //mandatory field
            if ($objOneField->getBitMandatory()) {
                //if field is mandatory and empty -> validation error
                if ($bitFieldIsEmpty) {
                    $strErrorMesage = $objOneField->getStrLabel() != "" ? $objLang->getLang("commons_validator_field_empty", "system", array($objOneField->getStrLabel())) : "";
                    $this->addValidationError($objOneField->getStrEntryName(), $strErrorMesage);
                }
            }

            //if field is not empty -> validate
            if (!$bitFieldIsEmpty) {
                if (!$objOneField->validateValue()) {
                    $this->addValidationError($objOneField->getStrEntryName(), $objOneField->getStrValidationErrorMsg());
                }
            }
        }

        //2. Validate complete object
        if ($this->getObjSourceobject() != null) {
            $objReflection = new Reflection($this->getObjSourceobject());
            $arrObjectValidator = $objReflection->getAnnotationValuesFromClass(self::STR_OBJECTVALIDATOR_ANNOTATION);
            if (count($arrObjectValidator) == 1) {

                $strObjectValidator = $arrObjectValidator[0];
                if (!class_exists($strObjectValidator)) {
                    throw new Exception("object validator " . $strObjectValidator . " not existing", Exception::$level_ERROR);
                }

                /** @var ObjectvalidatorBase $objValidator */
                $objValidator = new $strObjectValidator();

                //Keep the reference of the current object
                $objSourceObjectTemp = $this->getObjSourceobject();

                //Create a new instance of the source object and set it as source object in the formgenerator
                //Each existing field will also reference the new created source object
                $strClassName = get_class($this->objSourceobject);
                $this->objSourceobject = new $strClassName($this->objSourceobject->getStrSystemid());
                foreach ($this->arrFields as $objOneField) {
                    if ($objOneField->getObjSourceObject() != null) {
                        $objOneField->setObjSourceObject($this->objSourceobject);
                    }
                }

                //if we are in new-mode, we should fix the prev-id to the lateron matching one
                if (($this->getField("mode") != null && $this->getField("mode")->getStrValue() == "new") || Carrier::getInstance()->getParam("mode") == "new") {
                    $this->objSourceobject->setStrPrevId(Carrier::getInstance()->getParam("systemid"));
                }

                //Update the new source object values from the fields and validate the object
                $this->updateSourceObject();
                $objValidator->validateObject($this->getObjSourceobject());

                foreach ($objValidator->getArrValidationMessages() as $strKey => $arrMessages) {
                    if (!is_array($arrMessages)) {
                        throw new Exception("method validateObject must return an array of format array(\"<messageKey>\" => array())", Exception::$level_ERROR);
                    }

                    foreach ($arrMessages as $strMessage) {
                        $this->addValidationError($strKey, $strMessage);
                    }
                }

                //Set back kept reference to the formgenerator and all it's fields
                $this->objSourceobject = $objSourceObjectTemp;
                foreach ($this->arrFields as $objOneField) {
                    if ($objOneField->getObjSourceObject() != null) {
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
     *                             pass them combined by a bitwise or, e.g. AdminFormgenerator::BIT_BUTTON_SAVE | AdminFormgenerator::$BIT_BUTTON_CANCEL
     *
     * @throws Exception
     * @return string
     */
    public function renderForm($strTargetURI, $intButtonConfig = 2)
    {
        $strReturn = "";
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");

        /*add a hidden systemid-field*/
        if ($this->objSourceobject != null && $this->objSourceobject instanceof Model) {
            $objField = new FormentryHidden($this->strFormname, "systemid");
            $objField->setStrEntryName("systemid")->setStrValue($this->objSourceobject->getSystemid())->setObjValidator(new SystemidValidator());
            $this->addField($objField);
        }

        /*add reload URL param*/
        if ($this->strOnSaveRedirectUrl != "") {
            $objField = new FormentryHidden($this->strFormname, self::STR_FORM_ON_SAVE_RELOAD_PARAM);
            $objField->setStrEntryName(self::STR_FORM_ON_SAVE_RELOAD_PARAM)->setStrValue($this->strOnSaveRedirectUrl);
            $this->addField($objField);
        }

        // we add a info field if the user can not access the record
        if ($this->shouldAcquireLock() && !$this->objSourceobject->getLockManager()->isAccessibleForCurrentUser()) {
            $objUser = new UserUser($this->objSourceobject->getLockManager()->getLockId());
            $strMessage = Lang::getInstance()->getLang("generic_record_locked", "system", array($objUser->getStrDisplayName()));

            // add info box field
            $objField = new FormentryPlaintext($this->strFormname);
            $objField->setStrValue($objToolkit->warningBox($strMessage, "alert-info"));
            $this->addField($objField, "lock_info");
            $this->setFieldToPosition("lock_info", 1);

            // set all fields to readonly
            $arrFields = $this->getArrFields();
            foreach ($arrFields as $objField) {
                $objField->setBitReadonly(true);
            }

            // we overwrite the button config to 0 so that no button is displayed
            $this->intButtonConfig = 0;
        }

        /*generate form name*/
        $strGeneratedFormname = $this->strFormname;
        if ($strGeneratedFormname == null) {
            $strGeneratedFormname = "form" . generateSystemid();
        }

        if ($strTargetURI !== null) {
            $strReturn .= $objToolkit->formHeader($strTargetURI, $strGeneratedFormname, $this->strFormEncoding, $this->strOnSubmit, $this->strMethod);
        }
        $strReturn .= $objToolkit->getValidationErrors($this);

        $strHidden = "";
        foreach ($this->arrFields as $objOneField) {
            if (in_array($objOneField->getStrEntryName(), $this->arrHiddenElements)) {
                $strHidden .= $objOneField->renderField();
            } else {
                $strReturn .= $objOneField->renderField();
            }
        }

        if ($strHidden != "") {
            $strReturn .= $objToolkit->formOptionalElementsWrapper($strHidden, $this->strHiddenGroupTitle, $this->bitHiddenElementsVisible);
        }

        /*Render form buttons*/
        $strButtons = "";

        //Check if class property is set
        if($this->intButtonConfig !== null) {
            $intButtonConfig = $this->intButtonConfig;
        }

        if ($intButtonConfig & self::BIT_BUTTON_SUBMIT) {
            $strButtons .= $objToolkit->formInputSubmit(Lang::getInstance()->getLang("commons_submit", "system"), "submitbtn", "", "", true, false);
        }

        if ($intButtonConfig & self::BIT_BUTTON_SAVE) {
            $strButtons .= $objToolkit->formInputSubmit(Lang::getInstance()->getLang("commons_save", "system"), "submitbtn", "", "", true, false);
        }

        if ($intButtonConfig & self::BIT_BUTTON_CANCEL) {
            $strButtons .= $objToolkit->formInputSubmit(Lang::getInstance()->getLang("commons_cancel", "system"), "cancelbtn", "", "", true, false);
        }

        if ($intButtonConfig & self::BIT_BUTTON_CLOSE) {
            $strButtons .= $objToolkit->formInputSubmit(Lang::getInstance()->getLang("commons_close", "system"), "closebtn", "", "", true, false);
        }

        if ($intButtonConfig & self::BIT_BUTTON_DELETE) {
            $strButtons .= $objToolkit->formInputSubmit(Lang::getInstance()->getLang("commons_delete", "system"), "deletebtn", "", "", true, false);
        }

        if ($intButtonConfig & self::BIT_BUTTON_RESET) {
            $strButtons .= $objToolkit->formInputSubmit(Lang::getInstance()->getLang("commons_reset", "system"), "reset", "", "cancelbutton", true, false);
        }

        if ($intButtonConfig & self::BIT_BUTTON_CONTINUE) {
            $strButtons .= $objToolkit->formInputSubmit(Lang::getInstance()->getLang("commons_continue", "system"), "continuebtn", "", "", true, false);
        }

        if ($intButtonConfig & self::BIT_BUTTON_BACK) {
            $strButtons .= $objToolkit->formInputSubmit(Lang::getInstance()->getLang("commons_back", "system"), "backbtn", "", "", true, false);
        }

        if ($intButtonConfig & self::BIT_BUTTON_SAVENEXT) {
            $strButtons .= $objToolkit->formInputSubmit(Lang::getInstance()->getLang("commons_savenext", "system"), "savenextbtn", "", "", true, false);
        }

        $strReturn .= $objToolkit->formInputButtonWrapper($strButtons);

        if ($strTargetURI !== null) {
            $strReturn .= $objToolkit->formClose();
        }

        if (count($this->arrFields) > 0) {
            reset($this->arrFields);

            do {
                $objField = current($this->arrFields);
                if (!$objField instanceof FormentryHidden
                    && !$objField instanceof FormentryPlaintext
                    && !$objField instanceof FormentryHeadline
                    && !$objField instanceof FormentryDivider
                ) {
                    $strReturn .= $objToolkit->setBrowserFocus($objField->getStrEntryName());
                    break;
                }
            } while (next($this->arrFields) !== false);

        }

        //lock the record to avoid multiple edit-sessions - if in edit mode
        if ($this->shouldAcquireLock()) {
            if ($this->objSourceobject->getLockManager()->isAccessibleForCurrentUser()) {
                $this->objSourceobject->getLockManager()->lockRecord();

                //register a new unlock-handler
                // KAJONA.admin.ajax.genericAjaxCall('system', 'unlockRecord', '".$this->objSourceobject->getSystemid()."');
                $strReturn .= "<script type='text/javascript'>
                        $(window).on('unload', function() { $.ajax({url: KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=unlockRecord&systemid=" . $this->objSourceobject->getSystemid() . "', async:false}) ; });
//                        $('#{$strGeneratedFormname}').on('submit', function() { $(window).off('unload'); return true;});
                    </script>";
            }
        }

        return $strReturn;
    }

    /**
     * Returns whether we want to acquire a lock for the source object
     *
     * @return boolean
     */
    private function shouldAcquireLock()
    {
        if ($this->objSourceobject != null && method_exists($this->objSourceobject, "getLockManager")) {
            $bitSkip = false;
            if ($this->getField("mode") != null && $this->getField("mode")->getStrValue() == "new") {
                $bitSkip = true;
            }

            if (!$bitSkip && !validateSystemid($this->objSourceobject->getSystemid())) {
                $bitSkip = true;
            }

            if (!$bitSkip) {
                return true;
            }
        }

        return false;
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
    public function generateFieldsFromObject()
    {

        //load all methods
        $objAnnotations = new Reflection($this->objSourceobject);

        $arrProperties = $objAnnotations->getPropertiesWithAnnotation("@fieldType");
        foreach ($arrProperties as $strPropertyName => $strDataType) {
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
     *
     * @return FormentryBase|FormentryInterface
     * @throws Exception
     */
    public function addDynamicField($strPropertyName)
    {

        //try to get the matching getter
        $objReflection = new Reflection($this->objSourceobject);
        $strGetter = $objReflection->getGetter($strPropertyName);
        if ($strGetter === null) {
            throw new Exception("unable to find getter for property " . $strPropertyName . "@" . get_class($this->objSourceobject), Exception::$level_ERROR);
        }

        //load detailed properties

        $strType = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_TYPE_ANNOTATION);
        $strValidator = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_VALIDATOR_ANNOTATION);
        $strMandatory = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_MANDATORY_ANNOTATION);
        $strLabel = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_LABEL_ANNOTATION);
        $strHidden = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_HIDDEN_ANNOTATION);
        $strReadonly = $objReflection->getAnnotationValueForProperty($strPropertyName, self::STR_READONLY_ANNOTATION);

        if ($strType === null) {
            $strType = "text";
        }


        $strPropertyName = Lang::getInstance()->propertyWithoutPrefix($strPropertyName);

        $objField = $this->getFormEntryInstance($strType, $strPropertyName);
        if ($strLabel !== null) {
            $objField->updateLabel($strLabel);
        }

        $bitMandatory = false;
        if ($strMandatory !== null && $strMandatory !== "false") {
            $bitMandatory = true;
        }

        $objField->setBitMandatory($bitMandatory);

        $bitReadonly = false;
        if ($strReadonly !== null && $strReadonly !== "false") {
            $bitReadonly = true;
        }

        $objField->setBitReadonly($bitReadonly);


        if ($strValidator !== null) {
            $objField->setObjValidator($this->getValidatorInstance($strValidator));
        }

        $this->addField($objField, $strPropertyName);

        if ($strHidden !== null) {
            $this->addFieldToHiddenGroup($objField);
        }

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
     * @throws Exception
     * @return void
     */
    public function setFieldToPosition($strField, $intPos)
    {

        if (!isset($this->arrFields[$strField])) {
            throw new Exception("field " . $strField . " not found in list " . implode(", ", array_keys($this->arrFields)), Exception::$level_ERROR);
        }

        $objField = $this->arrFields[$strField];

        $arrNewOrder = array();

        $intI = 1;
        foreach ($this->arrFields as $strKey => $objValue) {
            //skip the same field, is inserted somewhere else
            if ($strKey == $strField) {
                continue;
            }

            if ($intI == $intPos) {
                $arrNewOrder[$strField] = $objField;
                $objField = null;
            }


            $arrNewOrder[$strKey] = $objValue;

            $intI++;
        }

        if ($objField !== null) {
            $arrNewOrder[$strField] = $objField;
        }


        $this->arrFields = $arrNewOrder;
    }


    /**
     * Loads the field-entry identified by the passed name.
     *
     * @param string $strName
     * @param string $strPropertyname
     *
     * @return FormentryBase|FormentryInterface
     * @throws Exception
     */
    private function getFormEntryInstance($strName, $strPropertyname)
    {

        //backslash given?
        //the V5 way: namespaces
        if (uniStrpos($strName, "\\") !== false) {
            $strClassname = $strName;
        } else {
            //backwards support for v4
            $strClassname = "class_formentry_" . $strName;
            $strPath = Resourceloader::getInstance()->getPathForFile("/admin/formentries/" . $strClassname . ".php");

            if (!$strPath) {
                $strPath = Resourceloader::getInstance()->getPathForFile("/legacy/" . $strClassname . ".php");

                if ($strPath == null) {
                    $strClassname = null;
                }
            }

        }

        if ($strClassname !== null) {
            return new $strClassname($this->strFormname, $strPropertyname, $this->objSourceobject);
        } else {
            throw new Exception("failed to load form-entry of type " . $strClassname, Exception::$level_ERROR);
        }

    }

    /**
     * Loads the validator identified by the passed name.
     *
     * @param string $strClassname
     *
     * @throws Exception
     * @return ValidatorInterface
     */
    private function getValidatorInstance($strClassname)
    {
        if (class_exists($strClassname)) {
            return new $strClassname();
        }

        if (uniStrpos($strClassname, "class_") === false) {
            $strClassname = "class_" . $strClassname . "_validator";
        }

        if (Resourceloader::getInstance()->getPathForFile("/system/validators/" . $strClassname . ".php")) {
            return new $strClassname();
        } else {
            throw new Exception("failed to load validator of type " . $strClassname, Exception::$level_ERROR);
        }
    }

    /**
     * Returns an language text. By default the formname is used as module name
     *
     * @param string $strText
     *
     * @param null $strModule
     *
     * @return string
     */
    protected function getLang($strText, $strModule = null, array $arrParameters = array())
    {
        return $this->objLang->getLang($strText, $strModule === null ? $this->strFormname : $strModule, $arrParameters);
    }

    /**
     * Returns an array of validation-errors
     *
     * @return array
     */
    public function getArrValidationErrors()
    {
        return $this->arrValidationErrors;
    }

    /**
     * Returns an array of validation-errors.
     * Alias for getArrValidationErrors due to backwards compatibility.
     *
     * @return array
     * @see getArrValidationErrors
     */
    public function getValidationErrors()
    {
        return $this->getArrValidationErrors();
    }

    /**
     * Adds an additional, user-specific validation-error to the current list of errors.
     *
     * @param string $strEntry
     * @param string $strMessage
     *
     * @return void
     */
    public function addValidationError($strEntry, $strMessage)
    {
        if (!array_key_exists($strEntry, $this->arrValidationErrors)) {
            $this->arrValidationErrors[$strEntry] = array();
        }
        $this->arrValidationErrors[$strEntry][] = $strMessage;
    }

    /**
     * Removes a single validation error
     *
     * @param string $strEntry
     *
     * @return void
     */
    public function removeValidationError($strEntry)
    {
        if (isset($this->arrValidationErrors[$strEntry])) {
            unset($this->arrValidationErrors[$strEntry]);
        }
    }

    /**
     * Clear all validation errors in the form
     */
    public function removeAllValidationError()
    {
        $this->arrValidationErrors = array();
    }

    /**
     * Adds a single field to the current form, the hard, manual way.
     * Use this method if you want to add custom fields to the current form.
     *
     * @param FormentryBase $objField
     * @param string $strKey
     *
     * @return FormentryBase|FormentryInterface
     */
    public function addField(FormentryBase $objField, $strKey = "")
    {
        if ($strKey == "") {
            $strKey = $objField->getStrEntryName();
        }

        $this->arrFields[$strKey] = $objField;

        return $objField;
    }

    /**
     * Returns a single entry form the fields, identified by its form-entry-name.
     *
     * @param string $strName
     *
     * @return FormentryBase|FormentryInterface
     */
    public function getField($strName)
    {
        if (isset($this->arrFields[$strName])) {
            return $this->arrFields[$strName];
        } else {
            return null;
        }
    }


    /**
     * Orders the fields by the given array.
     * The array must contain as values the keys of the form fields
     *
     * @param $arrFieldOrder
     *
     * @return int
     * @throws Exception
     */
    public function orderFields($arrFieldOrder)
    {
        $intPosition = 1;

        foreach ($arrFieldOrder as $strFieldName) {
            if ($this->getField($strFieldName) != null) {
                $this->setFieldToPosition($strFieldName, $intPosition);
                $intPosition++;
            }
        }
        return $intPosition;
    }

    /**
     * Removes a single entry form the fields, identified by its form-entry-name.
     *
     * @param string $strName
     *
     * @return $this
     */
    public function removeField($strName)
    {
        unset($this->arrFields[$strName]);
        return $this;
    }

    /**
     * Sets the name of the group of hidden elements
     *
     * @param string $strHiddenGroupTitle
     *
     * @return $this
     */
    public function setStrHiddenGroupTitle($strHiddenGroupTitle)
    {
        $this->strHiddenGroupTitle = $strHiddenGroupTitle;
        return $this;
    }

    /**
     * Moves a single field to the list of hidden elements
     *
     * @param FormentryBase $objField
     *
     * @return FormentryBase
     */
    public function addFieldToHiddenGroup(FormentryBase $objField)
    {
        $this->arrHiddenElements[] = $objField->getStrEntryName();
        if (!isset($this->arrFields[$objField->getStrEntryName()]) && !isset($this->arrFields[$objField->getStrSourceProperty()])) {
            $this->addField($objField);
        }
        return $objField;
    }

    /**
     * Makes the group of hidden elements visible or hides the content on page-load
     *
     * @param bool $bitHiddenElementsVisible
     *
     * @return void
     */
    public function setBitHiddenElementsVisible($bitHiddenElementsVisible)
    {
        $this->bitHiddenElementsVisible = $bitHiddenElementsVisible;
    }

    /**
     * @return Model|ModelInterface
     */
    public function getObjSourceobject()
    {
        return $this->objSourceobject;
    }

    /**
     * @param Model|ModelInterface $objSource
     */
    protected function setObjSourceobject($objSource)
    {
        $this->objSourceobject = $objSource;
    }

    /**
     * @return FormentryBase[]|FormentryInterface[]
     */
    public function getArrFields()
    {
        return $this->arrFields;
    }

    /**
     * Allows to inject an onsubmit handler
     *
     * @param string $strOnSubmit
     *
     * @return void
     */
    public function setStrOnSubmit($strOnSubmit)
    {
        $this->strOnSubmit = $strOnSubmit;
    }

    /**
     * @return string
     */
    public function getStrOnSubmit()
    {
        return $this->strOnSubmit;
    }

    /**
     * @param string $strFormEncoding
     *
     * @return void
     */
    public function setStrFormEncoding($strFormEncoding)
    {
        $this->strFormEncoding = $strFormEncoding;
    }

    /**
     * @return string
     */
    public function getStrFormEncoding()
    {
        return $this->strFormEncoding;
    }

    /**
     * @return string
     */
    public function getStrFormname()
    {
        return $this->strFormname;
    }

    /**
     * @param string $strMethod
     *
     * @throws Exception
     */
    public function setStrMethod($strMethod)
    {
        if (in_array($strMethod, array(self::STR_METHOD_GET, self::STR_METHOD_POST))) {
            $this->strMethod = $strMethod;
        } else {
            throw new Exception("Invalid form method", Exception::$level_ERROR);
        }
    }

    /**
     * @return string
     */
    public function getStrMethod()
    {
        return $this->strMethod;
    }

    /**
     * @return null
     */
    public function getStrOnSaveRedirectUrl()
    {
        return $this->strOnSaveRedirectUrl;
    }

    /**
     * @param null $strOnSaveRedirectUrl
     */
    public function setStrOnSaveRedirectUrl($strOnSaveRedirectUrl)
    {
        $this->strOnSaveRedirectUrl = $strOnSaveRedirectUrl;
    }

    /**
     * @return null
     */
    public function getIntButtonConfig()
    {
        return $this->intButtonConfig;
    }

    /**
     * @param null $intButtonConfig
     */
    public function setIntButtonConfig($intButtonConfig)
    {
        $this->intButtonConfig = $intButtonConfig;
    }
}
