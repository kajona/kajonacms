<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Reflection;
use Kajona\System\System\SystemModule;
use ReflectionClass;
use Traversable;


/**
 * An list of objects which can be added or removed.
 *
 * @author christoph.kappestein@gmail.com
 * @since 4.7
 * @package module_formgenerator
 */
class FormentryObjectlist extends FormentryBase implements FormentryPrintableInterface
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
        $arrParams = Carrier::getAllParams();

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
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if ($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        // filter objects
        $arrObjects = array_values(array_filter($this->arrKeyValues, function ($objObject) {
            return $objObject->rightView();
        }));

        $strReturn .= $objToolkit->formInputObjectList($this->getStrEntryName(), $this->getStrLabel(), $arrObjects, $this->strAddLink, $this->getBitReadonly());
        $strReturn .= $objToolkit->formInputHidden($this->getStrEntryName()."_empty", "1");
        return $strReturn;
    }

    public function setStrValue($strValue)
    {
        $arrValuesIds = array();
        if (is_array($strValue) || $strValue instanceof Traversable) {
            foreach ($strValue as $objValue) {
                if ($objValue instanceof Model) {
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

        $objReflection = new Reflection($objSourceObject);

        // get database object which we can not change
        $strGetter = $objReflection->getGetter($this->getStrSourceProperty());
        if ($strGetter === null) {
            throw new Exception("unable to find getter for value-property ".$this->getStrSourceProperty()."@".get_class($objSourceObject), Exception::$level_ERROR);
        }


        $arrObjects = $objSourceObject->{$strGetter}();
        $arrNotObjects = array_values(array_filter((array)$arrObjects, function (Model $objObject) {
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
            throw new Exception("unable to find setter for value-property ".$this->getStrSourceProperty()."@".get_class($objSourceObject), Exception::$level_ERROR);
        }

        return $objSourceObject->{$strSetter}($arrObjects);
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
                if ($objObject instanceof Model && $objObject instanceof ModelInterface) {

                    if($objObject->rightView()) {
                        $strTitle = self::getDisplayName($objObject);

                        //see, if the matching target-module provides a showSummary method
                        $objModule = SystemModule::getModuleByName($objObject->getArrModule("modul"));
                        if ($objModule != null) {
                            $objAdmin = $objModule->getAdminInstanceOfConcreteModule($objObject->getSystemid());

                            if ($objAdmin !== null && method_exists($objAdmin, "actionShowSummary")) {
                                $strTitle = Link::getLinkAdmin($objObject->getArrModule("modul"), "showSummary", "&systemid=" . $objObject->getSystemid(), $strTitle);
                            }
                        }
                        $strHtml .= $strTitle."<br/>\n";
                    }
                    else {
                        $strHtml .= AdminskinHelper::getAdminImage("icon_lockerClosed")." ".Carrier::getInstance()->getObjLang()->getLang("commons_error_permissions", "system")."<br/>\n";
                    }
                }
                else {
                    throw new Exception("Array must contain objects", Exception::$level_ERROR);
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
                return Objectfactory::getInstance()->getObject($strId);
            }, $arrIds);
            return $arrObjects;
        }

        return array();
    }

    /**
     * Renders the display name for the object and, if possible, also the object type
     *
     * @param ModelInterface $objObject
     *
     * @return string
     */
    public static function getDisplayName(ModelInterface $objObject)
    {
        $strObjectName = "";

        $objClass = new ReflectionClass(get_class($objObject)); //TODO remove hardcoded cross-module dependencies
        if ($objClass->implementsInterface('AGP\Aufgaben\System\AufgabenTaskableInterface')) {
            $strObjectName .= "[".$objObject->getStrTaskCategory()."] ";
        }
        elseif ($objClass->implementsInterface('Kajona\System\System\VersionableInterface')) {
            $strObjectName .= "[".$objObject->getVersionRecordName()."] ";
        }

        $strObjectName .= $objObject->getStrDisplayName();

        return $strObjectName;
    }


    /**
     * @param ModelInterface $objOneElement
     * @param string $intAllowedLevel
     * @return string
     */
    public static function getPathName(ModelInterface $objOneElement)
    {
        //fetch the process-path, at least two levels
        $arrParents = $objOneElement->getPathArray();

        // remove first two nodes
        if (count($arrParents) >= 2) {
            array_shift($arrParents);
            array_shift($arrParents);
        }

        //remove current element
        array_pop($arrParents);



        //Only return three levels
        $arrPath = array();
        for ($intI = 0; $intI < 3; $intI++) {
            $strPathId = array_pop($arrParents);
            if (!validateSystemid($strPathId)) {
                break;
            }

            $objObject = Objectfactory::getInstance()->getObject($strPathId);
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
     * @return FormentryObjectlist
     */
    public function setArrKeyValues($arrKeyValues) {
        $this->arrKeyValues = $arrKeyValues;
        return $this;
    }

    public function getArrKeyValues() {
        return $this->arrKeyValues;
    }


}
