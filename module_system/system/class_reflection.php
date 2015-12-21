<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

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
class class_reflection {

    private static $arrAnnotationsCache = array();
    private static $strAnnotationsCacheFile;
    private static $bitCacheSaveRequired = false;

    private static $STR_HASCLASS_CACHE = "hasclass";
    private static $STR_CLASS_PROPERTIES_CACHE = "classproperties";

    private static $STR_METHOD_CACHE = "methods";
    private static $STR_HASMETHOD_CACHE = "hasmethods";

    private static $STR_PROPERTIES_CACHE = "properties";
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

    /**
     * Internal init block, called on class-inclusion
     * @return void
     */
    public static function staticConstruct() {
        self::$strAnnotationsCacheFile = _realpath_."/project/temp/reflection.cache";

        self::$arrAnnotationsCache = class_apc_cache::getInstance()->getValue("reflection");

        if(self::$arrAnnotationsCache == false) {
            self::$arrAnnotationsCache = array();

            if(is_file(self::$strAnnotationsCacheFile))
                self::$arrAnnotationsCache = unserialize(file_get_contents(self::$strAnnotationsCacheFile));
        }
    }

    /**
     * Creates an instance of the annotations-class, parametrized with the class to inspect
     *
     * @param string|object $strSourceClass
     *
     * @throws class_exception
     */
    public function __construct($strSourceClass) {

        if(is_object($strSourceClass))
            $this->strSourceClass = get_class($strSourceClass);
        else
            $this->strSourceClass = $strSourceClass;

        if(!class_exists($this->strSourceClass))
            throw new class_exception("class ".$this->strSourceClass." not found", class_exception::$level_ERROR);

        if(!isset(self::$arrAnnotationsCache[$this->strSourceClass]))
            self::$arrAnnotationsCache[$this->strSourceClass] = array(
                self::$STR_CLASS_PROPERTIES_CACHE,
                self::$STR_METHOD_CACHE,
                self::$STR_HASMETHOD_CACHE,
                self::$STR_PROPERTIES_CACHE,
                self::$STR_HASPROPERTY_CACHE,
                self::$STR_DOC_COMMENT_PROPERTIES_CACHE,
                self::$STR_GETTER_CACHE,
                self::$STR_SETTER_CACHE
            );

        $this->arrCurrentCache = &self::$arrAnnotationsCache[$this->strSourceClass];
        $this->objReflectionClass = new ReflectionClass($this->strSourceClass);
    }

    /**
     * internal destructor
     */
    function __destruct() {
        if(self::$bitCacheSaveRequired && class_config::getInstance()->getConfig('resourcecaching') == true) {
            class_apc_cache::getInstance()->addValue("reflection", self::$arrAnnotationsCache);
            file_put_contents(self::$strAnnotationsCacheFile, serialize(self::$arrAnnotationsCache));
            self::$bitCacheSaveRequired = false;
        }
    }

    /**
     * Flushes the cache-files.
     * Use this method if you added new modules / classes.
     * @return void
     */
    public static function flushCache() {
        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete(self::$strAnnotationsCacheFile);
    }

    /**
     * Fetches a list of annotations from the class-doc-comment.
     * Please be aware that this method returns an array and not only a single line.
     * Parent classes are evaluated, too.
     *
     * @param $strAnnotation
     * @param integer $intEnum - whether to return annotation values or parameters, default is values
     * @return array|string|string[]
     */
    public function getAnnotationValuesFromClass($strAnnotation, $intEnum = class_reflection_enum::VALUES) {

        if(isset($this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strAnnotation."_".$intEnum]))
            return $this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strAnnotation."_".$intEnum];

        $strClassDoc = $this->objReflectionClass->getDocComment();
        $arrReturn = $this->searchAnnotationInDoc($strClassDoc, $strAnnotation);

        if(count($arrReturn) == 2) {
            if($intEnum === class_reflection_enum::PARAMS)
                $arrReturn = $arrReturn["params"];

            if($intEnum === class_reflection_enum::VALUES)
                $arrReturn = $arrReturn["values"];
        }

        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if($objBaseClass !== false) {
            $objBaseAnnotations = new class_reflection($objBaseClass->getName());
            $arrReturn = array_merge($arrReturn, $objBaseAnnotations->getAnnotationValuesFromClass($strAnnotation, $intEnum));
        }

        $this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strAnnotation."_".$intEnum] = $arrReturn;
        self::$bitCacheSaveRequired = true;
        return $arrReturn;
    }

    /**
     * Returns a list of all annotation names with a given value.
     * 
     * @param string $strValue Annotation value
     * @param integer $intEnum - whether to return annotation values or parameters, default is values
     * @return array List of annotation names
     */
    public function getAnnotationsWithValueFromClass($strValue, $intEnum = class_reflection_enum::VALUES) {

        $arrReturn = array();

        $strClassDoc = $this->objReflectionClass->getDocComment();
        $arrProperties = $this->searchAllAnnotationsInDoc($strClassDoc);

        if($intEnum === class_reflection_enum::VALUES) {
            foreach ($arrProperties as $strName => $arrValues) {
                if (in_array($strValue, $arrValues["values"]))
                    $arrReturn[] = $strName;
            }
        }
        else if($intEnum === class_reflection_enum::PARAMS) {
            foreach ($arrProperties as $strName => $arrValues) {
                $arrParameters = $arrValues["params"];

                foreach($arrParameters as $arrParams) {
                    foreach($arrParams as $strParamName => $objParamValue) {
                        if(is_array($objParamValue)) {
                            if (in_array($strValue, $objParamValue)) {
                                $arrReturn[$strName] = $strName;
                            }
                        }
                        else if ($objParamValue == $strValue){
                            $arrReturn[$strName] = $strName;
                        }
                    }
                }
            }
            $arrReturn = array_keys($arrReturn);
        }

        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if($objBaseClass !== false) {
            $objBaseAnnotations = new class_reflection($objBaseClass->getName());
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
     * @return bool
     */
    public function hasMethodAnnotation($strMethodName, $strAnnotation) {
        if(isset($this->arrCurrentCache[self::$STR_HASMETHOD_CACHE][$strMethodName."_".$strAnnotation]))
            return $this->arrCurrentCache[self::$STR_HASMETHOD_CACHE][$strMethodName."_".$strAnnotation];

        try {
            $objReflectionMethod = $this->objReflectionClass->getMethod($strMethodName);
            $bitReturn = false !== $this->searchFirstAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
        }
        catch(ReflectionException $objEx) {
            $bitReturn = false;
        }

        $this->arrCurrentCache[self::$STR_HASMETHOD_CACHE][$strMethodName."_".$strAnnotation] = $bitReturn;
        self::$bitCacheSaveRequired = true;
        return $bitReturn;
    }

    /**
     * searches an annotation (e.g. @version) in the doccomment of the current class and validates
     * the existence of this annotation.
     *
     * @param string $strAnnotation
     * @return bool
     */
    public function hasClassAnnotation($strAnnotation) {
        if(isset($this->arrCurrentCache[self::$STR_HASCLASS_CACHE][$strAnnotation]))
            return $this->arrCurrentCache[self::$STR_HASCLASS_CACHE][$strAnnotation];

        try {
            $bitReturn = false !== $this->searchFirstAnnotationInDoc($this->objReflectionClass->getDocComment(), $strAnnotation);
        }
        catch(ReflectionException $objEx) {
            $bitReturn = false;
        }

        $this->arrCurrentCache[self::$STR_HASCLASS_CACHE][$strAnnotation] = $bitReturn;
        self::$bitCacheSaveRequired = true;
        return $bitReturn;
    }


    /**
     * searches an annotation (e.g. @version) in the doccomment of a passed property and validates
     * the existence of this annotation
     *
     * @param string $strPropertyName
     * @param string $strAnnotation
     * @return bool
     */
    public function hasPropertyAnnotation($strPropertyName, $strAnnotation) {
        if(isset($this->arrCurrentCache[self::$STR_HASPROPERTY_CACHE][$strPropertyName."_".$strAnnotation]))
            return $this->arrCurrentCache[self::$STR_HASPROPERTY_CACHE][$strPropertyName."_".$strAnnotation];

        try {
            $objReflectionMethod = $this->objReflectionClass->getProperty($strPropertyName);
            $bitReturn = false !== $this->searchFirstAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
        }
        catch(ReflectionException $objEx) {
            //not found in current class, maybe a base-class is existing?
            $objBaseClass = $this->objReflectionClass->getParentClass();
            if($objBaseClass !== false) {
                $objBaseAnnotations = new class_reflection($objBaseClass->getName());
                $bitReturn = $objBaseAnnotations->hasPropertyAnnotation($strPropertyName, $strAnnotation);
            }
        }

        $this->arrCurrentCache[self::$STR_HASPROPERTY_CACHE][$strPropertyName."_".$strAnnotation] = $bitReturn;
        self::$bitCacheSaveRequired = true;
        return $bitReturn;
    }

    /**
     * Searches an annotation (e.g. @version) in the doccomment of a passed method
     * and passes the value, so anything behind the @name part.
     * E.g., if the annotation is written like
     *   @test value1, value2
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
    public function getMethodAnnotationValue($strMethodName, $strAnnotation, $intEnum = class_reflection_enum::VALUES) {

        if(isset($this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation."_".$intEnum]))
            return $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation."_".$intEnum];

        $objReflectionMethod = $this->objReflectionClass->getMethod($strMethodName);

        $strReturn = false;
        $arrReturn = $this->searchFirstAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
        if($intEnum === class_reflection_enum::VALUES) {
            $strReturn = $arrReturn["values"][0];
        }
        else if($intEnum === class_reflection_enum::PARAMS) {
            $strReturn = $arrReturn["params"][0];
        }

        if($arrReturn === false) {
            $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation."_".$intEnum] = false;
            return false;
        }

        //strip the annotation parts
        $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation."_".$intEnum] = $strReturn;
        self::$bitCacheSaveRequired = true;
        return $strReturn;
    }

    /**
     * Searches the current class for properties marked with a given annotation.
     * If found, the name of the property plus the (optional) value of the property is returned.
     * The base classes are queried, too.
     *
     * @param string $strAnnotation
     * @param integer $intEnum - whether to return annotation values or parameters, default is values
     * @return string[] ["propertyname" => "annotationvalue"]
     */
    public function getPropertiesWithAnnotation($strAnnotation , $intEnum = class_reflection_enum::VALUES) {

        if(isset($this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strAnnotation."_".$intEnum]))
            return $this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strAnnotation."_".$intEnum];

        $arrReturn = array();

        $arrProperties = $this->objReflectionClass->getProperties();

        //check if there's a base-class -> inheritance, so base class before extending class
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if($objBaseClass !== false) {
            $objBaseAnnotations = new class_reflection($objBaseClass->getName());
            $arrReturn = array_merge($arrReturn, $objBaseAnnotations->getPropertiesWithAnnotation($strAnnotation, $intEnum));
        }


        foreach($arrProperties as $objOneProperty) {
            $arrFirstAnnotation = $this->searchFirstAnnotationInDoc($objOneProperty->getDocComment(), $strAnnotation);
            if ($arrFirstAnnotation !== false) {
                if($intEnum === class_reflection_enum::VALUES) {
                    $arrFirstAnnotation = $arrFirstAnnotation["values"][0];
                }
                else if($intEnum === class_reflection_enum::PARAMS) {
                    $arrFirstAnnotation = $arrFirstAnnotation["params"][0];
                }

                $arrReturn[$objOneProperty->getName()] = $arrFirstAnnotation;
            }
        }

        $this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strAnnotation."_".$intEnum] = $arrReturn;
        self::$bitCacheSaveRequired = true;
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
    public function getAnnotationValueForProperty($strProperty, $strAnnotation, $intEnum = class_reflection_enum::VALUES) {

        $arrProperties = $this->objReflectionClass->getProperties();

        foreach($arrProperties as $objOneProperty) {
            if($objOneProperty->getName() == $strProperty) {
                $strFirstAnnotation = $this->searchFirstAnnotationInDoc($objOneProperty->getDocComment(), $strAnnotation);

                if($intEnum === class_reflection_enum::VALUES) {
                    $strFirstAnnotation = $strFirstAnnotation["values"][0];
                }
                else if($intEnum === class_reflection_enum::PARAMS) {
                    $strFirstAnnotation = $strFirstAnnotation["params"][0];
                }

                if ($strFirstAnnotation !== false)
                    return $strFirstAnnotation;
            }
        }

        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if($objBaseClass !== false) {
            $objBaseAnnotations = new class_reflection($objBaseClass->getName());
            return $objBaseAnnotations->getAnnotationValueForProperty($strProperty, $strAnnotation, $intEnum);
        }

        return null;
    }

    /**
     * Searches an object for a given properties' setter method.
     * If not found, null is returned instead.
     * @param string $strPropertyName
     * @return null|string
     * @static
     */
    public function getSetter($strPropertyName) {

        if(isset($this->arrCurrentCache[self::$STR_SETTER_CACHE][$strPropertyName]))
            return $this->arrCurrentCache[self::$STR_SETTER_CACHE][$strPropertyName];

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

        foreach($arrSetters as $strOneSetter) {
            if(method_exists($this->strSourceClass, $strOneSetter)) {
                $this->arrCurrentCache[self::$STR_SETTER_CACHE][$strPropertyName] = $strOneSetter;
                self::$bitCacheSaveRequired = true;
                return $strOneSetter;
            }
        }

        return null;
    }


    /**
     * Searches an object for a given properties' getter method.
     * If not found, null is returned instead.
     * @param string $strPropertyName
     * @return null|string
     * @static
     */
    public function getGetter($strPropertyName) {

        if(isset($this->arrCurrentCache[self::$STR_GETTER_CACHE][$strPropertyName]))
            return $this->arrCurrentCache[self::$STR_GETTER_CACHE][$strPropertyName];

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


        foreach($arrGetters as $strOneGetter) {
            if(method_exists($this->strSourceClass, $strOneGetter)) {
                $this->arrCurrentCache[self::$STR_GETTER_CACHE][$strPropertyName] = $strOneGetter;
                self::$bitCacheSaveRequired = true;
                return $strOneGetter;
            }
        }

        return null;
    }

    /**
     * Internal helper, does the parsing of the comment.
     * Returns the first annotation matching the passed name.
     *
     * @param string $strDoc
     * @param string $strAnnotation
     * @return bool
     */
    private function searchFirstAnnotationInDoc($strDoc, $strAnnotation) {
        $arrAnnotations = $this->searchAnnotationInDoc($strDoc, $strAnnotation);

            if(count($arrAnnotations) == 2) {
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
     * @return string[] or ["values" => array(), "params" => array()]
     */
    private function searchAnnotationInDoc($strDoc, $strAnnotation) { 
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
     * @return array ["annotation_name" => array("values" => values, "params" => params)]
     */
    private function searchAllAnnotationsInDoc($strDoc) {
        $strDoc = uniStrReplace(array("\r\n", "\r"), "\n", $strDoc); //replace needed as regex on windows or mac won't work properly

        $arrReturn = array();

        $strCacheKey = md5($strDoc);

        if (isset($this->arrCurrentCache[self::$STR_DOC_COMMENT_PROPERTIES_CACHE][$strCacheKey]))
            return $this->arrCurrentCache[self::$STR_DOC_COMMENT_PROPERTIES_CACHE][$strCacheKey];

        $arrMatches = array();
        if (preg_match_all("/(@[a-zA-Z0-9]+)(\s+.*)?(\s+\(.*\))?$/Um", $strDoc, $arrMatches, PREG_SET_ORDER) !== false) {
            foreach ($arrMatches as $arrOneMatch) {
                $strName = $arrOneMatch[1];
                $strValue = isset($arrOneMatch[2]) ? $arrOneMatch[2] : "";
                $strParams = isset($arrOneMatch[3]) ? $arrOneMatch[3] : "";

                if (!isset($arrReturn[$strName]))
                    $arrReturn[$strName] = array("values" => array(), "params" =>array());

                $arrReturn[$strName]["values"][] = trim($strValue);
                $arrReturn[$strName]["params"][] = $this->params2Array(trim($strParams));
            }
        }

        $this->arrCurrentCache[self::$STR_DOC_COMMENT_PROPERTIES_CACHE][$strCacheKey] = $arrReturn;
        self::$bitCacheSaveRequired = true;
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
    private function params2Array($strParams) {
        $arrParams = array();

        if($strParams == "") {
            return $arrParams;
        }

        $strPatternParams = "/(\w+)=(\d+?)|(\w+)=\"(.*)\"|(\w+)=(\{.*\})/U";
        if (preg_match_all($strPatternParams, $strParams, $arrMatches, PREG_SET_ORDER) !== false) {
            foreach ($arrMatches as $arrOneMatch) {
                //GetParam name
                $strParamName = "";
                if(isset($arrOneMatch[1]) && $arrOneMatch[1] != "") {
                    $strParamName = $arrOneMatch[1];
                }
                else if(isset($arrOneMatch[3]) && $arrOneMatch[3] != "") {
                    $strParamName = $arrOneMatch[3];
                }
                else if(isset($arrOneMatch[5]) && $arrOneMatch[5] != "") {
                    $strParamName = $arrOneMatch[5];
                }

                //Get param value(s)
                $strParamValue = "";
                if(isset($arrOneMatch[2]) && $arrOneMatch[2] != "") {
                    $strParamValue = $arrOneMatch[2];
                }
                else if(isset($arrOneMatch[4]) && $arrOneMatch[4] != "") {
                    $strParamValue = $arrOneMatch[4];
                }
                else if(isset($arrOneMatch[6]) && $arrOneMatch[6] != "") {
                    $strParamValue = $arrOneMatch[6];
                    $strParamValue = uniStrReplace(array("{"), "[", $strParamValue);
                    $strParamValue = uniStrReplace(array("}"), "]", $strParamValue);
                    $strParamValue = json_decode($strParamValue);
                }
                $arrParams[$strParamName] = $strParamValue;
            }
        }
        return $arrParams;
    }
}
class_reflection::staticConstruct();
