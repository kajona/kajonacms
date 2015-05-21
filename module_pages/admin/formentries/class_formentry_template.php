<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A yes-no field renders a dropdown containing a list of entries.
 * Make sure to pass the list of possible entries before rendering the form.
 *
 * @author sidler@mulchprod.de
 * @since 4.3
 * @package module_pages
 */
class class_formentry_template extends class_formentry_base implements interface_formentry {

    /**
     * the path to the folder of matching templates
     */
    const STR_TEMPLATEDIR_ANNOTATION = "@fieldTemplateDir";


    private $arrKeyValues = array();
    private $strAddons = "";

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new class_text_validator());
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

        if(count($this->arrKeyValues) == 1 && $this->getStrValue() == "") {
            $arrKeys = array_keys($this->arrKeyValues);
            $this->setStrValue($arrKeys[0]);
        }


        $strReturn .=  $objToolkit->formInputDropdown($this->getStrEntryName(), $this->arrKeyValues, $this->getStrLabel(), $this->getStrValue(), "", !$this->getBitReadonly(), $this->getStrAddons());
        return $strReturn;
    }

    /**
     * Overwritten in order to load key-value pairs declared by annotations
     */
    protected function updateValue() {
        parent::updateValue();

        if($this->getObjSourceObject() != null && $this->getStrSourceProperty() != "") {
            $objReflection = new class_reflection($this->getObjSourceObject());

            //try to find the matching source property
            $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_TEMPLATEDIR_ANNOTATION);
            $strSourceProperty = null;

            foreach($arrProperties as $strPropertyName => $strValue) {
                if(uniSubstr(uniStrtolower($strPropertyName), (uniStrlen($this->getStrSourceProperty()))*-1) == $this->getStrSourceProperty())
                    $strSourceProperty = $strPropertyName;
            }

            if($strSourceProperty == null)
                return;

            $strTemplateDir = $objReflection->getAnnotationValueForProperty($strSourceProperty, self::STR_TEMPLATEDIR_ANNOTATION);

            //load templates
            $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder($strTemplateDir);
            $arrTemplatesDD = array();
            if(count($arrTemplates) > 0) {
                foreach($arrTemplates as $strTemplate) {
                    $arrTemplatesDD[$strTemplate] = $strTemplate;
                }
            }
            $this->setArrKeyValues($arrTemplatesDD);

        }
    }


    /**
     * @param $arrKeyValues
     * @return class_formentry_dropdown
     */
    public function setArrKeyValues($arrKeyValues) {
        $this->arrKeyValues = $arrKeyValues;
        return $this;
    }

    public function getArrKeyValues() {
        return $this->arrKeyValues;
    }

    /**
     * @param string $strAddons
     * @return $this
     */
    public function setStrAddons($strAddons) {
        $this->strAddons = $strAddons;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrAddons() {
        return $this->strAddons;
    }



}
