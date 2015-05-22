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

    public function setStrAddLink($strAddLink) {
        $this->strAddLink = $strAddLink;
    }

    protected function updateValue() {
        $arrParams = class_carrier::getAllParams();

        $strEntryName = $this->getStrEntryName();
        $strEntryNameEmpty = $strEntryName."_empty";

        if(isset($arrParams[$strEntryName])) {
            $this->setStrValue($arrParams[$strEntryName]);
        }
        else if(isset($arrParams[$strEntryNameEmpty])) {
            $this->setStrValue("");
        }
        else {
            $this->setStrValue($this->getValueFromObject());
        }
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
        if($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        // filter objects
        $arrObjects = array_values(array_filter($this->arrKeyValues, function($objObject){
            return $objObject->rightView();
        }));

        $strReturn.= $objToolkit->formInputObjectList($this->getStrEntryName(), $this->getStrLabel(), $arrObjects, $this->strAddLink);
        return $strReturn;
    }

    public function setStrValue($strValue) {
        $arrValuesIds = array();
        if(is_array($strValue) || $strValue instanceof Traversable) {
            foreach($strValue as $objValue) {
                if($objValue instanceof class_model) {
                    $arrValuesIds[] = $objValue->getStrSystemid();
                }
                else {
                    $arrValuesIds[] = $objValue;
                }
            }
        }
        $strValue = implode(",", $arrValuesIds);

        $objReturn = parent::setStrValue($strValue);
        $this->setArrKeyValues($this->toObjectArray());

        return $objReturn;
    }

    public function setValueToObject() {
        $objSourceObject = $this->getObjSourceObject();
        if($objSourceObject == null)
            return "";

        $objReflection = new class_reflection($objSourceObject);

        // get database object which we can not change
        $strGetter = $objReflection->getGetter($this->getStrSourceProperty());
        if($strGetter === null)
            throw new class_exception("unable to find getter for value-property ".$this->getStrSourceProperty()."@".get_class($objSourceObject), class_exception::$level_ERROR);


        $arrObjects = call_user_func(array($objSourceObject, $strGetter));
        $arrNotObjects = array_values(array_filter($arrObjects->getArrayCopy(), function(class_model $objObject){
            return !$objObject->rightView();
        }));

        // merge objects
        $arrNewObjects = array_merge($this->toObjectArray(), $arrNotObjects);

        // filter double object ids
        $arrObjects = array();
        foreach($arrNewObjects as $objObject) {
            $arrObjects[$objObject->getStrSystemid()] = $objObject;
        }
        $arrObjects = array_values($arrObjects);

        // set value
        $strSetter = $objReflection->getSetter($this->getStrSourceProperty());
        if($strSetter === null)
            throw new class_exception("unable to find setter for value-property ".$this->getStrSourceProperty()."@".get_class($objSourceObject), class_exception::$level_ERROR);

        return call_user_func(array($objSourceObject, $strSetter), $arrObjects);
    }

    public function validateValue() {
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

        if(!empty($this->arrKeyValues)) {
            $strHtml = "<ul>";
            foreach($this->arrKeyValues as $objObject) {
                if($objObject instanceof interface_model) {
                    if($objObject->rightView()) {
                        $strHtml.= "<li>" . $objObject->getStrDisplayName() . "</li>";
                    }
                }
                else {
                    throw new class_exception("Array must contain objects", class_exception::$level_ERROR);
                }
            }
            $strHtml.= "</ul>";

            return $strHtml;
        }

        return "-";
    }

    private function toObjectArray() {
        $strValue = $this->getStrValue();
        if(!empty($strValue)) {
            $arrIds = explode(",", $strValue);
            $arrObjects = array_map(function($strId){return class_objectfactory::getInstance()->getObject($strId);}, $arrIds);
            return $arrObjects;
        }

        return array();
    }

}
