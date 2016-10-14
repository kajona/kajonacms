<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Reflection;
use Kajona\System\System\SystemModule;
use ReflectionClass;
use Traversable;


/**
 * A tag editor with autocomplete object selection
 *
 * @author christoph.kappestein@gmail.com
 * @since 4.7
 * @package module_formgenerator
 */
class FormentryObjecttags extends FormentryTageditor
{
    protected $strSource;

    /**
     * @param string $strSource
     */
    public function setStrSource($strSource)
    {
        $this->strSource = $strSource;

        return $this;
    }

    /**
     * @param integer $intType
     * @deprecated
     */
    public function setIntType($intType)
    {
        $this->intType = $intType;

        return $this;
    }

    public function renderField()
    {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if ($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        if ($this->getBitReadonly()) {
            $strReturn .= $objToolkit->formInputObjectList($this->getStrEntryName(), $this->getStrLabel(), $this->arrKeyValues, "", $this->getBitReadonly());
        } else {
            $strReturn .= $objToolkit->formInputObjectTags($this->getStrEntryName(), $this->getStrLabel(), $this->strSource, $this->arrKeyValues,
                $this->strOnChangeCallback);
        }

        return $strReturn;
    }

    /**
     * The normal field contains the actual display names which are shown in each tag. The _id field contains an array
     * of corresponding systemids
     *
     * @throws Exception
     */
    protected function updateValue()
    {
        $arrParams = Carrier::getAllParams();
        if (isset($arrParams[$this->getStrEntryName() . "_id"])) {
            $this->setStrValue($arrParams[$this->getStrEntryName() . "_id"]);
        } else {
            $this->setStrValue($this->getValueFromObject());
        }
    }

    /**
     * The value is either an array of objects or systemids. We normalize the value so that arrKeyValues always contains
     * an array of objects
     *
     * @param $strValue
     * @return FormentryBase
     */
    public function setStrValue($strValue)
    {
        $arrValuesIds = array();
        if (is_array($strValue) || $strValue instanceof Traversable) {
            foreach ($strValue as $objValue) {
                if ($objValue instanceof Model) {
                    $arrValuesIds[] = $objValue->getStrSystemid();
                } else {
                    $arrValuesIds[] = $objValue;
                }
            }
        }
        $strValue = implode(",", $arrValuesIds);

        $objReturn = parent::setStrValue($strValue);
        $this->setArrKeyValues($this->toObjectArray());

        return $objReturn;
    }

    /**
     * Converts an array of systemids to objects
     *
     * @return array
     */
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
     * Copied from FormentryObjectlist
     *
     * @return string
     * @throws Exception
     */
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
            throw new Exception("unable to find getter for value-property " . $this->getStrSourceProperty() . "@" . get_class($objSourceObject),
                Exception::$level_ERROR);
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
            throw new Exception("unable to find setter for value-property " . $this->getStrSourceProperty() . "@" . get_class($objSourceObject),
                Exception::$level_ERROR);
        }

        return $objSourceObject->{$strSetter}($arrObjects);
    }

    /**
     * Copied from FormentryObjectlist
     *
     * @inheritdoc
     */
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

                    $strTitle = self::getDisplayName($objObject);

                    if($objObject->rightView()) {

                        //see, if the matching target-module provides a showSummary method
                        $objModule = SystemModule::getModuleByName($objObject->getArrModule("modul"));
                        if ($objModule != null) {
                            $objAdmin = $objModule->getAdminInstanceOfConcreteModule($objObject->getSystemid());

                            if ($objAdmin !== null && method_exists($objAdmin, "actionShowSummary")) {
                                $strTitle = Link::getLinkAdmin($objObject->getArrModule("modul"), "showSummary", "&systemid=" . $objObject->getSystemid()."&folderview=".Carrier::getInstance()->getParam("folderview"), $strTitle);
                            }
                        }
                    }
                    $strHtml .= $strTitle."<br/>\n";
                }
                else {
                    throw new Exception("Array must contain objects", Exception::$level_ERROR);
                }
            }
            return $strHtml;
        }

        return "-";
    }

    /**
     * Copied from FormentryObjectlist
     *
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
}
