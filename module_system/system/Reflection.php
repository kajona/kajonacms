<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\System\System;

use ReflectionClass;
use ReflectionException;

/**
 * Annotations are a common way to enrich classes and methods with metainformation and documentation.
 * This class can be used to parse the phpdocs of a given class in order to get and read annotations.
 * In most cases, the docs for methods are the only one mattering, so the class' focus is to parse those
 * comment blocks
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.4.1
 */
class Reflection
{

    private static $STR_HASCLASS_CACHE = "hasclass";
    private static $STR_CLASS_PROPERTIES_CACHE = "classproperties";

    private static $STR_METHOD_CACHE = "methods";
    private static $STR_HASMETHOD_CACHE = "hasmethods";

    private static $STR_PROPERTIES_CACHE = "properties";
    private static $STR_PROPERTIES_ANNOTATION_VALUE_CACHE = "properties_annotation_value";
    private static $STR_HASPROPERTY_CACHE = "hasproperty";
    private static $STR_GETTER_CACHE = "getters";
    private static $STR_SETTER_CACHE = "setters";

    private static $STR_DOC_COMMENT_PROPERTIES_CACHE = "doccomment";

    private $arrCurrentCache;
    private $strSourceClass;

    /**
     *
     * @var ReflectionClass
     */
    private $objReflectionClass;

    private $bitCacheSaveRequired = false;


    /**
     * Creates an instance of the annotations-class, parametrized with the class to inspect
     *
     * @param string|object $strSourceClass
     *
     * @throws Exception
     */
    public function __construct($strSourceClass)
    {

        if (is_object($strSourceClass)) {
            $this->strSourceClass = get_class($strSourceClass);
        }
        else {
            $this->strSourceClass = $strSourceClass;
        }

        if (!class_exists($this->strSourceClass)) {
            throw new Exception("class ".$this->strSourceClass." not found", Exception::$level_ERROR);
        }


        $this->arrCurrentCache = BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_REFLECTION, $this->strSourceClass);
        if ($this->arrCurrentCache === false) {
            $this->arrCurrentCache = array(
                self::$STR_CLASS_PROPERTIES_CACHE => array(),
                self::$STR_METHOD_CACHE => array(),
                self::$STR_HASMETHOD_CACHE => array(),
                self::$STR_PROPERTIES_CACHE => array(),
                self::$STR_PROPERTIES_ANNOTATION_VALUE_CACHE => array(),
                self::$STR_HASPROPERTY_CACHE => array(),
                self::$STR_DOC_COMMENT_PROPERTIES_CACHE => array(),
                self::$STR_GETTER_CACHE => array(),
                self::$STR_SETTER_CACHE => array()
            );
            $this->bitCacheSaveRequired = true;
        }

        $this->objReflectionClass = new ReflectionClass($this->strSourceClass);
    }

    /**
     * internal destructor
     */
    function __destruct()
    {
        if ($this->bitCacheSaveRequired) {
            BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_REFLECTION, $this->strSourceClass, $this->arrCurrentCache);
            $this->bitCacheSaveRequired = false;
        }
    }

    /**
     * Flushes the cache-files.
     * Use this method if you added new modules / classes.
     *
     * @return void
     * @deprecated
     */
    public static function flushCache()
    {
        Classloader::getInstance()->flushCache();
    }

    /**
     * Fetches a list of annotations from the class-doc-comment.
     * Please be aware that this method returns an array and not only a single line.
     * Parent classes are evaluated, too.
     *
     * @param $strAnnotation
     * @param integer $intEnum - whether to return annotation values or parameters, default is values
     *
     * @return array|string|string[]
     */
    public function getAnnotationValuesFromClass($strAnnotation, $intEnum = ReflectionEnum::VALUES)
    {

        $strCacheKey = $strAnnotation . "_" . $intEnum;
        if (isset($this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strCacheKey])) {
            return $this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strCacheKey];
        }

        $strClassDoc = $this->objReflectionClass->getDocComment();
        $arrReturn = $this->searchAnnotationInDoc($strClassDoc, $strAnnotation);

        if (count($arrReturn) == 2) {
            if ($intEnum === ReflectionEnum::PARAMS) {
                $arrReturn = $arrReturn["params"];
            }

            if ($intEnum === ReflectionEnum::VALUES) {
                $arrReturn = $arrReturn["values"];
            }
        }

        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if ($objBaseClass !== false) {
            $objBaseAnnotations = new Reflection($objBaseClass->getName());
            $arrReturn = array_merge($arrReturn, $objBaseAnnotations->getAnnotationValuesFromClass($strAnnotation, $intEnum));
        }

        $this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strCacheKey] = $arrReturn;
        $this->bitCacheSaveRequired = true;
        return $arrReturn;
    }

    /**
     * Returns a list of all annotation names with a given value.
     *
     * @param string $strValue Annotation value
     * @param integer $intEnum - whether to return annotation values or parameters, default is values
     *
     * @return array List of annotation names
     */
    public function getAnnotationsWithValueFromClass($strValue, $intEnum = ReflectionEnum::VALUES)
    {

        $arrReturn = array();

        $strClassDoc = $this->objReflectionClass->getDocComment();
        $arrProperties = $this->searchAllAnnotationsInDoc($strClassDoc);

        if ($intEnum === ReflectionEnum::VALUES) {
            foreach ($arrProperties as $strName => $arrValues) {
                if (in_array($strValue, $arrValues["values"])) {
                    $arrReturn[] = $strName;
                }
            }
        }
        elseif ($intEnum === ReflectionEnum::PARAMS) {
            foreach ($arrProperties as $strName => $arrValues) {
                $arrParameters = $arrValues["params"];

                foreach ($arrParameters as $arrParams) {
                    foreach ($arrParams as $strParamName => $objParamValue) {
                        if (is_array($objParamValue)) {
                            if (in_array($strValue, $objParamValue)) {
                                $arrReturn[$strName] = $strName;
                            }
                        }
                        elseif ($objParamValue == $strValue) {
                            $arrReturn[$strName] = $strName;
                        }
                    }
                }
            }
            $arrReturn = array_keys($arrReturn);
        }

        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if ($objBaseClass !== false) {
            $objBaseAnnotations = new Reflection($objBaseClass->getName());
            $arrReturn = array_merge($arrReturn, $objBaseAnnotations->getAnnotationsWithValueFromClass($strValue, $intEnum));
        }

        return $arrReturn;
    }

    /**
     * searches an annotation (e.g. @version) in the doccomment of a passed method and validates
     * the existence of this annotation.
     *
     * @param string $strMethodName
     * @param string $strAnnotation
     *
     * @return bool
     */
    public function hasMethodAnnotation($strMethodName, $strAnnotation)
    {
        $strCacheKey = $strMethodName . "_" . $strAnnotation;
        if (isset($this->arrCurrentCache[self::$STR_HASMETHOD_CACHE][$strCacheKey])) {
            return $this->arrCurrentCache[self::$STR_HASMETHOD_CACHE][$strCacheKey];
        }

        try {
            $objReflectionMethod = $this->objReflectionClass->getMethod($strMethodName);
            $bitReturn = false !== $this->searchFirstAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
        }
        catch (ReflectionException $objEx) {
            $bitReturn = false;
        }

        $this->arrCurrentCache[self::$STR_HASMETHOD_CACHE][$strCacheKey] = $bitReturn;
        $this->bitCacheSaveRequired = true;
        return $bitReturn;
    }

    /**
     * searches an annotation (e.g. @version) in the doccomment of the current class and validates
     * the existence of this annotation.
     *
     * @param string $strAnnotation
     *
     * @return bool
     */
    public function hasClassAnnotation($strAnnotation)
    {
        if (isset($this->arrCurrentCache[self::$STR_HASCLASS_CACHE][$strAnnotation])) {
            return $this->arrCurrentCache[self::$STR_HASCLASS_CACHE][$strAnnotation];
        }

        try {
            $bitReturn = false !== $this->searchFirstAnnotationInDoc($this->objReflectionClass->getDocComment(), $strAnnotation);
        }
        catch (ReflectionException $objEx) {
            $bitReturn = false;
        }

        $this->arrCurrentCache[self::$STR_HASCLASS_CACHE][$strAnnotation] = $bitReturn;
        $this->bitCacheSaveRequired = true;
        return $bitReturn;
    }


    /**
     * searches an annotation (e.g. @version) in the doccomment of a passed property and validates
     * the existence of this annotation
     *
     * @param string $strPropertyName
     * @param string $strAnnotation
     *
     * @return bool
     */
    public function hasPropertyAnnotation($strPropertyName, $strAnnotation)
    {
        $strCacheKey = $strPropertyName . "_" . $strAnnotation;
        if (isset($this->arrCurrentCache[self::$STR_HASPROPERTY_CACHE][$strCacheKey])) {
            return $this->arrCurrentCache[self::$STR_HASPROPERTY_CACHE][$strCacheKey];
        }

        try {
            $objReflectionMethod = $this->objReflectionClass->getProperty($strPropertyName);
            $bitReturn = false !== $this->searchFirstAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
        }
        catch (ReflectionException $objEx) {
            $bitReturn = false;

            //not found in current class, maybe a base-class is existing?
            $objBaseClass = $this->objReflectionClass->getParentClass();
            if ($objBaseClass !== false) {
                $objBaseAnnotations = new Reflection($objBaseClass->getName());
                $bitReturn = $objBaseAnnotations->hasPropertyAnnotation($strPropertyName, $strAnnotation);
            }
        }

        $this->arrCurrentCache[self::$STR_HASPROPERTY_CACHE][$strCacheKey] = $bitReturn;
        $this->bitCacheSaveRequired = true;
        return $bitReturn;
    }

    /**
     * Searches an annotation (e.g. @version) in the doccomment of a passed method
     * and passes the value, so anything behind the @name part .
     * E.g., if the annotation is written like
     *
     * @test value1, value2
     * then only
     *   value1, value2
     * are returned.
     * If the annotation could not be found, false is returned instead. If there are multiple annotations
     * matching the passed pattern, only the first one is returned.
     *
     * @param string $strMethodName
     * @param string $strAnnotation
     * @param integer $intEnum - whether to return annotation values or parameters, default is values
     *
     * @return string|bool
     */
    public function getMethodAnnotationValue($strMethodName, $strAnnotation, $intEnum = ReflectionEnum::VALUES)
    {

        $strCacheKey = $strMethodName . "_" . $strAnnotation . "_" . $intEnum;
        if (isset($this->arrCurrentCache[self::$STR_METHOD_CACHE][$strCacheKey])) {
            return $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strCacheKey];
        }

        $objReflectionMethod = $this->objReflectionClass->getMethod($strMethodName);

        $strReturn = false;
        $arrReturn = $this->searchFirstAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
        if ($intEnum === ReflectionEnum::VALUES) {
            $strReturn = $arrReturn["values"][0];
        }
        elseif ($intEnum === ReflectionEnum::PARAMS) {
            $strReturn = $arrReturn["params"][0];
        }

        if ($arrReturn === false) {
            $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strCacheKey] = false;
            return false;
        }

        //strip the annotation parts
        $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strCacheKey] = $strReturn;
        $this->bitCacheSaveRequired = true;
        return $strReturn;
    }

    /**
     * Searches the current class for properties marked with a given annotation.
     * If found, the name of the property plus the (optional) value of the property is returned.
     * The base classes are queried, too.
     *
     * @param string $strAnnotation
     * @param integer $intEnum - whether to return annotation values or parameters, default is values
     *
     * @return string[] ["propertyname" => "annotationvalue"]
     */
    public function getPropertiesWithAnnotation($strAnnotation, $intEnum = ReflectionEnum::VALUES)
    {
        $strCacheKey = $strAnnotation . "_" . $intEnum;
        if (isset($this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strCacheKey])) {
            return $this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strCacheKey];
        }

        $arrReturn = array();

        $arrProperties = $this->objReflectionClass->getProperties();

        //check if there's a base-class -> inheritance, so base class before extending class
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if ($objBaseClass !== false) {
            $objBaseAnnotations = new Reflection($objBaseClass->getName());
            $arrReturn = array_merge($arrReturn, $objBaseAnnotations->getPropertiesWithAnnotation($strAnnotation, $intEnum));
        }


        foreach ($arrProperties as $objOneProperty) {
            $arrFirstAnnotation = $this->searchFirstAnnotationInDoc($objOneProperty->getDocComment(), $strAnnotation);
            if ($arrFirstAnnotation !== false) {
                if ($intEnum === ReflectionEnum::VALUES) {
                    $arrFirstAnnotation = $arrFirstAnnotation["values"][0];
                }
                elseif ($intEnum === ReflectionEnum::PARAMS) {
                    $arrFirstAnnotation = $arrFirstAnnotation["params"][0];
                }

                $arrReturn[$objOneProperty->getName()] = $arrFirstAnnotation;
            }
        }

        $this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strCacheKey] = $arrReturn;
        $this->bitCacheSaveRequired = true;
        return $arrReturn;
    }

    /**
     * Searches a given annotation for a specified property. If given, the value is returned, otherwise (when not found) null is returned.
     *
     * @param string $strProperty
     * @param string $strAnnotation
     * @param integer $intEnum - whether to return annotation values or parameters, default is values
     *
     * @return null|string
     */
    public function getAnnotationValueForProperty($strProperty, $strAnnotation, $intEnum = ReflectionEnum::VALUES)
    {
        $strCacheKey = $strProperty."_".$strAnnotation."_".$intEnum;
        if (array_key_exists($strCacheKey, $this->arrCurrentCache[self::$STR_PROPERTIES_ANNOTATION_VALUE_CACHE])) {
            return $this->arrCurrentCache[self::$STR_PROPERTIES_ANNOTATION_VALUE_CACHE][$strCacheKey];
        }

        $strValue = null;
        $arrProperties = $this->objReflectionClass->getProperties();

        foreach ($arrProperties as $objOneProperty) {
            if ($objOneProperty->getName() == $strProperty) {
                $strFirstAnnotation = $this->searchFirstAnnotationInDoc($objOneProperty->getDocComment(), $strAnnotation);

                if ($intEnum === ReflectionEnum::VALUES) {
                    $strFirstAnnotation = $strFirstAnnotation["values"][0];
                }
                elseif ($intEnum === ReflectionEnum::PARAMS) {
                    $strFirstAnnotation = $strFirstAnnotation["params"][0];
                }

                if ($strFirstAnnotation !== false) {
                    $this->arrCurrentCache[self::$STR_PROPERTIES_ANNOTATION_VALUE_CACHE][$strCacheKey] = $strFirstAnnotation;
                    $this->bitCacheSaveRequired = true;
                    return $strFirstAnnotation;
                }
            }
        }

        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if ($objBaseClass !== false) {
            $objBaseAnnotations = new Reflection($objBaseClass->getName());
            $strValue = $objBaseAnnotations->getAnnotationValueForProperty($strProperty, $strAnnotation, $intEnum);
        }

        $this->arrCurrentCache[self::$STR_PROPERTIES_ANNOTATION_VALUE_CACHE][$strCacheKey] = $strValue;
        $this->bitCacheSaveRequired = true;
        return $strValue;
    }


    /**
     * Gets the param value for the given property, annotation and param name
     *
     * @param $strProperty
     * @param $strAnnotation
     * @param $strParamName
     *
     * @return mixed|null
     */
    public function getParamValueForPropertyAndAnnotation($strProperty, $strAnnotation, $strParamName) {
        $arrParams = $this->getAnnotationValueForProperty($strProperty, $strAnnotation, ReflectionEnum::PARAMS);

        if(is_array($arrParams) && array_key_exists($strParamName, $arrParams)) {
            return $arrParams[$strParamName];
        }

        return null;
    }

    /**
     * Searches an object for a given properties' setter method.
     * If not found, null is returned instead.
     *
     * @param string $strPropertyName
     *
     * @return null|string
     * @static
     */
    public function getSetter($strPropertyName)
    {

        if (array_key_exists($strPropertyName, $this->arrCurrentCache[self::$STR_SETTER_CACHE])) {
            return $this->arrCurrentCache[self::$STR_SETTER_CACHE][$strPropertyName];
        }

        $strSetter = null;

        $arrSetters = array(
            "setStr".$strPropertyName,
            "setInt".$strPropertyName,
            "setFloat".$strPropertyName,
            "setBit".$strPropertyName,
            "setObj".$strPropertyName,
            "setArr".$strPropertyName,
            "setLong".$strPropertyName,
            "set".$strPropertyName
        );

        foreach ($arrSetters as $strOneSetter) {
            if (method_exists($this->strSourceClass, $strOneSetter)) {
                $strSetter = $strOneSetter;
                break;
            }
        }

        $this->arrCurrentCache[self::$STR_SETTER_CACHE][$strPropertyName] = $strSetter;
        $this->bitCacheSaveRequired = true;

        return $strSetter;
    }


    /**
     * Searches an object for a given properties' getter method.
     * If not found, null is returned instead.
     *
     * @param string $strPropertyName
     *
     * @return null|string
     * @static
     */
    public function getGetter($strPropertyName)
    {

        if (array_key_exists($strPropertyName, $this->arrCurrentCache[self::$STR_GETTER_CACHE])) {
            return $this->arrCurrentCache[self::$STR_GETTER_CACHE][$strPropertyName];
        }

        $strGetter = null;

        $arrGetters = array(
            "getStr".$strPropertyName,
            "getInt".$strPropertyName,
            "getFloat".$strPropertyName,
            "getBit".$strPropertyName,
            "getObj".$strPropertyName,
            "getArr".$strPropertyName,
            "getLong".$strPropertyName,
            "get".$strPropertyName
        );


        foreach ($arrGetters as $strOneGetter) {
            if (method_exists($this->strSourceClass, $strOneGetter)) {
                $strGetter = $strOneGetter;
                break;
            }
        }

        $this->arrCurrentCache[self::$STR_GETTER_CACHE][$strPropertyName] = $strGetter;
        $this->bitCacheSaveRequired = $strGetter;

        return $strGetter;
    }

    /**
     * @param object $objObject
     * @param string $strPropertyName
     * @param mixed $strValue
     */
    public function setObjectProperty($objObject, $strPropertyName, $strValue)
    {
        $objProperty = $this->objReflectionClass->getProperty($strPropertyName);
        $objProperty->setAccessible(true);
        $objProperty->setValue($objObject, $strValue);
    }

    /**
     * Returns a new object instance
     *
     * @param array $arrArguments
     *
     * @return object
     */
    public function newInstance(array $arrArguments = array())
    {
        return $this->objReflectionClass->newInstanceArgs($arrArguments);
    }

    /**
     * Returns a new object instance without calling the constructor
     *
     * @param array $arrArguments
     *
     * @return object
     */
    public function newInstanceWithoutConstructor()
    {
        return $this->objReflectionClass->newInstanceWithoutConstructor();
    }

    /**
     * Internal helper, does the parsing of the comment.
     * Returns the first annotation matching the passed name.
     *
     * @param string $strDoc
     * @param string $strAnnotation
     *
     * @return bool
     */
    private function searchFirstAnnotationInDoc($strDoc, $strAnnotation)
    {
        $arrAnnotations = $this->searchAnnotationInDoc($strDoc, $strAnnotation);

        if (count($arrAnnotations) == 2) {
            return $arrAnnotations;
        }

        return false;
    }


    /**
     * Internal helper, does the parsing of the comment.
     * Returns an array of all matching annotations.
     *
     * @param string $strDoc
     * @param string $strAnnotation
     *
     * @return string[] or ["values" => array(), "params" => array()]
     */
    private function searchAnnotationInDoc($strDoc, $strAnnotation)
    {
        $arrAllAnnotations = $this->searchAllAnnotationsInDoc($strDoc);

        if (isset($arrAllAnnotations[$strAnnotation])) {
            return $arrAllAnnotations[$strAnnotation];
        }
        else {
            return array();
        }
    }


    /**
     * Internal helper, does the parsing of the comment.
     * Returns an array of all annotations.
     *
     * @param string $strDoc
     *
     * @return array ["annotation_name" => array("values" => values, "params" => params)]
     */
    private function searchAllAnnotationsInDoc($strDoc)
    {
        $strDoc = StringUtil::replace(array("\r\n", "\r"), "\n", $strDoc); //replace needed as regex on windows or mac won't work properly

        $arrReturn = array();

        $strCacheKey = md5($strDoc);

        if (isset($this->arrCurrentCache[self::$STR_DOC_COMMENT_PROPERTIES_CACHE][$strCacheKey])) {
            return $this->arrCurrentCache[self::$STR_DOC_COMMENT_PROPERTIES_CACHE][$strCacheKey];
        }

        $arrMatches = array();
        if (preg_match_all("/(@[a-zA-Z0-9]+)(\s+.*)?(\s+\(.*\))?$/Um", $strDoc, $arrMatches, PREG_SET_ORDER) !== false) {
            foreach ($arrMatches as $arrOneMatch) {
                $strName = $arrOneMatch[1];
                $strValue = isset($arrOneMatch[2]) ? $arrOneMatch[2] : "";
                $strParams = isset($arrOneMatch[3]) ? $arrOneMatch[3] : "";

                if (!isset($arrReturn[$strName])) {
                    $arrReturn[$strName] = array("values" => array(), "params" => array());
                }

                $arrReturn[$strName]["values"][] = trim($strValue);
                $arrReturn[$strName]["params"][] = $this->params2Array(trim($strParams));
            }
        }

        $this->arrCurrentCache[self::$STR_DOC_COMMENT_PROPERTIES_CACHE][$strCacheKey] = $arrReturn;
        $this->bitCacheSaveRequired = true;
        return $arrReturn;
    }

    /**
     * Converts the string of params into an associative array e.g.
     * the string (param1=0, param2="abc", param3={"0", 123, 456}, param4=999, param5="hans im glück") is converted into an array of
     *
     * array(
     *   "param1" => "0",
     *   "param2" => "abc",
     *   "param3" => array("0", "123", "456"),
     *   "param4" => "999",
     *   "param5" => "hans im glück",
     * )
     *
     *
     * @param $strParams
     *
     * @return array ["paramname" => "value"]
     */
    private function params2Array($strParams)
    {
        $arrParams = array();

        if ($strParams == "") {
            return $arrParams;
        }

        $strPatternParams = "/(\w+)=(\d+?)|(\w+)=\"(.*)\"|(\w+)=(\{.*\})/U";
        if (preg_match_all($strPatternParams, $strParams, $arrMatches, PREG_SET_ORDER) !== false) {
            foreach ($arrMatches as $arrOneMatch) {
                //GetParam name
                $strParamName = "";
                if (isset($arrOneMatch[1]) && $arrOneMatch[1] != "") {
                    $strParamName = $arrOneMatch[1];
                }
                elseif (isset($arrOneMatch[3]) && $arrOneMatch[3] != "") {
                    $strParamName = $arrOneMatch[3];
                }
                elseif (isset($arrOneMatch[5]) && $arrOneMatch[5] != "") {
                    $strParamName = $arrOneMatch[5];
                }

                //Get param value(s)
                $strParamValue = "";
                if (isset($arrOneMatch[2]) && $arrOneMatch[2] != "") {
                    $strParamValue = $arrOneMatch[2];
                }
                elseif (isset($arrOneMatch[4]) && $arrOneMatch[4] != "") {
                    $strParamValue = $arrOneMatch[4];
                }
                elseif (isset($arrOneMatch[6]) && $arrOneMatch[6] != "") {
                    $strParamValue = $arrOneMatch[6];
                    $strParamValue = StringUtil::replace(array("{"), "[", $strParamValue);
                    $strParamValue = StringUtil::replace(array("}"), "]", $strParamValue);
                    $strParamValue = json_decode($strParamValue);
                }
                $arrParams[$strParamName] = $strParamValue;
            }
        }
        return $arrParams;
    }

    /**
     * Checks if a property exists for a class
     *
     * @param $strProperty
     *
     * @return bool
     */
    public function hasProperty($strProperty)
    {
        $bitReturn = $this->objReflectionClass->hasProperty($strProperty);

        if (!$bitReturn) {
            $objBaseClass = $this->objReflectionClass->getParentClass();
            if ($objBaseClass !== false) {
                $objBaseAnnotations = new Reflection($objBaseClass->getName());
                return $objBaseAnnotations->hasProperty($strProperty);
            }
        }

        return $bitReturn;
    }

}
