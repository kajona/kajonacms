<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * An list of objects which can be added or removed.
 *
 * @author christoph.kappestein@gmail.com
 * @since 4.7
 * @package module_formgenerator
 */
class class_formentry_objectlist extends class_formentry_multiselect {

    protected $strAddLink;

    public function setStrAddLink($strAddLink)
    {
        $this->strAddLink = $strAddLink;
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField()
    {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        $strReturn.= $objToolkit->formInputObjectList($this->getStrEntryName(), $this->getStrLabel(), $this->arrKeyValues, $this->strAddLink);
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

        return call_user_func(array($objSourceObject, $strSetter), explode(",", $this->getStrValue()));
    }

    public function validateValue()
    {
        $arrIds = explode(",", $this->getStrValue());
        foreach($arrIds as $strId) {
            if(!validateSystemid($strId)) {
                return false;
            }
        }

        return true;
    }

    public function getValueAsText()
    {
        $objSourceObject = $this->getObjSourceObject();
        if($objSourceObject == null)
            return "-";

        $strMethodName = "getAssigned" . ucfirst($this->getStrSourceProperty());
        $arrObjects = array();
        if(method_exists($objSourceObject, $strMethodName)) {
            $arrObjects = $objSourceObject->$strMethodName();
        }

        if(!empty($arrObjects) && is_array($arrObjects)) {
            $strHtml = "<ul>";
            foreach($arrObjects as $objObject) {
                if($objObject instanceof interface_model) {
                    $strHtml.= "<li>" . htmlspecialchars($objObject->getStrDisplayName()) . "</li>";
                }
            }
            $strHtml.= "</ul>";

            return $strHtml;
        }
        else {
            return "-";
        }
    }
}
