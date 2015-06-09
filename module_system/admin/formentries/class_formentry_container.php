<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A formelement which provides an div container. The container can optional contain other formentry elements.
 *
 * @author  christoph.kappestein@gmail.com
 * @since   4.8
 * @package module_formgenerator
 */
class class_formentry_container extends class_formentry_base {

    protected $arrFields = array();

    public function __construct($strFormName, $strSourceProperty)
    {
        parent::__construct($strFormName, $strSourceProperty);
    }

    /**
     * @param interface_formentry $formentry
     * @return class_formentry_base|interface_formentry
     */
    public function addField(class_formentry_base $objField, $strKey = "")
    {
        if($strKey == "")
            $strKey = $objField->getStrEntryName();

        $this->arrFields[$strKey] = $objField;

        return $objField;
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

        $arrFields = array();
        foreach($this->arrFields as $objField) {
            /** @var interface_formentry $objField */
            $arrFields[] = $objField->renderField();
        }

        $strReturn.= $objToolkit->formInputContainer($this->getStrEntryName(), $this->getStrLabel(), $arrFields);

        return $strReturn;
    }

    public function setValueToObject()
    {
        $objSourceObject = $this->getObjSourceObject();
        if($objSourceObject == null)
            return "";

        $objReflection = new class_reflection($objSourceObject);
        $strSetter = $objReflection->getSetter($this->getStrSourceProperty());
        if($strSetter === null)
            throw new class_exception("unable to find setter for value-property ".$this->getStrSourceProperty()."@".get_class($objSourceObject), class_exception::$level_ERROR);

        return call_user_func(array($objSourceObject, $strSetter), json_encode($this->getStrValue()));
    }

    public function validateValue() {
        return true;
    }

}
