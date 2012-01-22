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


    public function getRequiredFields() {
        $arrReturn = array();
        foreach($this->arrFields as $objOneField)
            if($objOneField->getBitMandatory())
                $arrReturn[$objOneField->getStrEntryName()] = $objOneField->getObjValidator()->getStrName();

        return $arrReturn;
    }


    public function renderForm($strTargetURI) {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        $strReturn .= $objToolkit->formHeader($strTargetURI);
        $strReturn .= $objToolkit->getValidationErrors($this);

        foreach($this->arrFields as $objOneField)
            $strReturn .= $objOneField->renderField();

        $strReturn .= $objToolkit->formClose();
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

        $this->arrFields[] = $objField;

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
        return $this->arrValidationErrors;
    }

    public function getValidationErrors() {
        return $this->arrValidationErrors;
    }


}
