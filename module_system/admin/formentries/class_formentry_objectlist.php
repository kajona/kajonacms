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
//TODO why does this formentry is subclass of multiselect. Does not make any sense to me.
class class_formentry_objectlist extends class_formentry_base implements interface_formentry_printable
{

    protected $strAddLink;
    protected $arrKeyValues = array();

    public function setStrAddLink($strAddLink)
    {
        $this->strAddLink = $strAddLink;
    }

    /**
     * @return mixed
     */
    public function getStrAddLink()
    {
        return $this->strAddLink;
    }

    protected function updateValue()
    {
        $arrParams = class_carrier::getAllParams();

        $strEntryName = $this->getStrEntryName();
        $strEntryNameEmpty = $strEntryName."_empty";

        if (isset($arrParams[$strEntryName])) {
            $this->setStrValue($arrParams[$strEntryName]);
        }
        elseif (isset($arrParams[$strEntryNameEmpty])) {
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
    public function renderField()
    {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if ($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        // filter objects
        $arrObjects = array_values(array_filter($this->arrKeyValues, function ($objObject) {
            return $objObject->rightView();
        }));

        $strReturn .= $objToolkit->formInputObjectList($this->getStrEntryName(), $this->getStrLabel(), $arrObjects, $this->strAddLink, $this->getBitReadonly());
        return $strReturn;
    }

    public function setStrValue($strValue)
    {
        $arrValuesIds = array();
        if (is_array($strValue) || $strValue instanceof Traversable) {
            foreach ($strValue as $objValue) {
                if ($objValue instanceof class_model) {
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

    public function setValueToObject()
    {
        $objSourceObject = $this->getObjSourceObject();
        if ($objSourceObject == null) {
            return "";
        }

        $objReflection = new class_reflection($objSourceObject);

        // get database object which we can not change
        $strGetter = $objReflection->getGetter($this->getStrSourceProperty());
        if ($strGetter === null) {
            throw new class_exception("unable to find getter for value-property ".$this->getStrSourceProperty()."@".get_class($objSourceObject), class_exception::$level_ERROR);
        }


        $arrObjects = call_user_func(array($objSourceObject, $strGetter));
        $arrNotObjects = array_values(array_filter((array)$arrObjects, function (class_model $objObject) {
            return !$objObject->rightView();
        }));

        // merge objects
        $arrNewObjects = array_merge($this->toObjectArray(), $arrNotObjects);

        // filter double object ids
        $arrObjects = array();
        foreach ($arrNewObjects as $objObject) {
            $arrObjects[$objObject->getStrSystemid()] = $objObject;
        }
        $arrObjects = array_values($arrObjects);

        // set value
        $strSetter = $objReflection->getSetter($this->getStrSourceProperty());
        if ($strSetter === null) {
            throw new class_exception("unable to find setter for value-property ".$this->getStrSourceProperty()."@".get_class($objSourceObject), class_exception::$level_ERROR);
        }

        return call_user_func(array($objSourceObject, $strSetter), $arrObjects);
    }

    public function validateValue()
    {
        $arrIds = explode(",", $this->getStrValue());
        foreach ($arrIds as $strId) {
            if (!validateSystemid($strId)) {
                return false;
            }
        }

        return true;
    }

    public function getValueAsText()
    {
        $objSourceObject = $this->getObjSourceObject();
        if ($objSourceObject == null) {
            return "";
        }


        if (!empty($this->arrKeyValues)) {
            $strHtml = "";
            foreach ($this->arrKeyValues as $objObject) {
                if ($objObject instanceof interface_model && $objObject->rightView()) {
                    $strTitle = self::getDisplayName($objObject);

                    //see, if the matching target-module provides a showSummary method
                    $objModule = class_module_system_module::getModuleByName($objObject->getArrModule("modul"));
                    if ($objModule != null) {
                        $objAdmin = $objModule->getAdminInstanceOfConcreteModule($objObject->getSystemid());

                        if ($objAdmin !== null && method_exists($objAdmin, "actionShowSummary")) {
                            $strTitle = class_link::getLinkAdmin($objObject->getArrModule("modul"), "showSummary", "&systemid=".$objObject->getSystemid(), $strTitle);
                        }
                    }


                    $strHtml .= $strTitle."<br/>\n";
                }
                else {
                    throw new class_exception("Array must contain objects", class_exception::$level_ERROR);
                }
            }
            $strHtml .= "";

            return $strHtml;
        }

        return "-";
    }




    private function toObjectArray()
    {
        $strValue = $this->getStrValue();
        if (!empty($strValue)) {
            $arrIds = explode(",", $strValue);
            $arrObjects = array_map(function ($strId) {
                return class_objectfactory::getInstance()->getObject($strId);
            }, $arrIds);
            return $arrObjects;
        }

        return array();
    }

    /**
     * Renders the display name for the object and, if possible, also the object type
     *
     * @param interface_model $objObject
     *
     * @return string
     */
    public static function getDisplayName(interface_model $objObject)
    {
        $strObjectName = "";

        $objClass = new ReflectionClass(get_class($objObject));
        if ($objClass->implementsInterface('interface_aufgaben_taskable')) {
            $strObjectName .= "[".$objObject->getStrTaskCategory()."] ";
        }
        else if ($objClass->implementsInterface('interface_aufgaben_taskable')) {
            $strObjectName .= "[".$objObject->getVersionRecordName()."] ";
        }

        $strObjectName .= $objObject->getStrDisplayName();

        return $strObjectName;
    }


    /**
     * @param interface_model $objOneElement
     * @param string $intAllowedLevel
     * @return string
     */
    public static function getPathName(interface_model $objOneElement)
    {
        //fetch the process-path, at least two levels
        $arrParents = $objOneElement->getPathArray();

        // check if oe is on the allowed level if parameter is available
        if (count($arrParents) <= 3) {
            return "";
        }

        array_pop($arrParents);

        //Only return three levels
        $arrPath = array();
        for ($intI = 0; $intI < 3; $intI++) {
            $strPathId = array_pop($arrParents);
            if (!validateSystemid($strPathId)) {
                break;
            }

            $objObject = class_objectfactory::getInstance()->getObject($strPathId);
            $arrPath[] = $objObject->getStrDisplayName();
        }

        if(count($arrPath) == 0) {
            return "";
        }

        $strPath = implode(" &gt; ", array_reverse($arrPath));
        return $strPath;
    }

    /**
     * @param $arrKeyValues
     *
     * @return class_formentry_dropdown
     */
    public function setArrKeyValues($arrKeyValues) {
        $this->arrKeyValues = $arrKeyValues;
        return $this;
    }

    public function getArrKeyValues() {
        return $this->arrKeyValues;
    }


}
