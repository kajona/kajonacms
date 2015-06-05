<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * @author  christoph.kappestein@gmail.com
 * @since   4.0
 * @package module_formgenerator
 */
class class_formentry_tableeditor extends class_formentry_base {

    const TYPE_TEXT = 0;
    const TYPE_CHECKBOX = 1;
    const TYPE_NUMBER = 2;

    protected $arrOptions;
    protected $arrTypes;
    protected $strAddLink;
    protected $strRemoveLink;

    public function setOptions(array $arrOptions)
    {
        $this->arrOptions = $arrOptions;

        return $this;
    }

    public function setTypes(array $arrTypes)
    {
        $this->arrTypes = $arrTypes;

        return $this;
    }

    public function setAddLink($strAddLink)
    {
        $this->strAddLink = $strAddLink;

        return $this;
    }

    public function setRemoveLink($strRemoveLink)
    {
        $this->strRemoveLink = $strRemoveLink;

        return $this;
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

        $strReturn .= $objToolkit->formInputTableEditor($this->getStrEntryName(), $this->getStrLabel(), $this->arrOptions, $this->arrTypes, $this->strAddLink, $this->strRemoveLink);

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
