<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
     * @param string $strAnnotation
     * @return string[]
     */
    public function getAnnotationValuesFromClass($strAnnotation) {
        if(isset($this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strAnnotation]))
            return $this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strAnnotation];

        $strClassDoc = $this->objReflectionClass->getDocComment();
        $arrReturn = $this->searchAnnotationInDoc($strClassDoc, $strAnnotation);

        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if($objBaseClass !== false) {
            $objBaseAnnotations = new class_reflection($objBaseClass->getName());
            $arrReturn = array_merge($arrReturn, $objBaseAnnotations->getAnnotationValuesFromClass($strAnnotation));
        }

        $this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strAnnotation] = $arrReturn;
        self::$bitCacheSaveRequired = true;
        return $arrReturn;
    }
    
    /**
     * Returns a list of all annotation names with a given value.
     * 
     * @param string $strValue Annotation value
     * @return array List of annotation names
     */
    public function getAnnotationsWithValueFromClass($strValue) {
        $arrReturn = array();
        
        $strClassDoc = $this->objReflectionClass->getDocComment();
        $arrProperties = $this->searchAllAnnotationsInDoc($strClassDoc);
        
        foreach ($arrProperties as $strName => $arrValues) {
            if (in_array($strValue, $arrValues))
                $arrReturn[] = $strName;
        }
        
        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if($objBaseClass !== false) {
            $objBaseAnnotations = new class_reflection($objBaseClass->getName());
            $arrReturn = array_merge($arrReturn, $objBaseAnnotations->getAnnotationsWithValueFromClass($strValue));
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
     * @return string|bool
     */
    public function getMethodAnnotationValue($strMethodName, $strAnnotation) {

        if(isset($this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation]))
            return $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation];

        $objReflectionMethod = $this->objReflectionClass->getMethod($strMethodName);
        $strReturn = $this->searchFirstAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
        if($strReturn === false) {
            $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation] = false;
            return false;
        }

        //strip the annotation parts
        $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation] = $strReturn;
        self::$bitCacheSaveRequired = true;
        return $strReturn;
    }

    /**
     * Searches the current class for properties marked with a given annotation.
     * If found, the name of the property plus the (optional) value of the property is returned.
     * The base classes are queried, too.
     *
     * @param string $strAnnotation
     * @return string[] ["propertyname" => "annotationvalue"]
     */
    public function getPropertiesWithAnnotation($strAnnotation) {

        if(isset($this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strAnnotation]))
            return $this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strAnnotation];

        $arrProperties = $this->objReflectionClass->getProperties();

        $arrReturn = array();

        foreach($arrProperties as $objOneProperty) {
            $strFirstAnnotation = $this->searchFirstAnnotationInDoc($objOneProperty->getDocComment(), $strAnnotation);
            if ($strFirstAnnotation !== false)
                $arrReturn[$objOneProperty->getName()] = $strFirstAnnotation;
        }


        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if($objBaseClass !== false) {
            $objBaseAnnotations = new class_reflection($objBaseClass->getName());
            $arrReturn = array_merge($arrReturn, $objBaseAnnotations->getPropertiesWithAnnotation($strAnnotation));
        }

        $this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strAnnotation] = $arrReturn;
        self::$bitCacheSaveRequired = true;
        return $arrReturn;
    }

    /**
     * Searches a given annotation for a specified property. If given, the value is returned, otherwise (when not found) null is returned.
     *
     * @param string $strProperty
     * @param string $strAnnotation
     *
     * @return null|string
     */
    public function getAnnotationValueForProperty($strProperty, $strAnnotation) {
        $arrProperties = $this->objReflectionClass->getProperties();

        foreach($arrProperties as $objOneProperty) {
            if($objOneProperty->getName() == $strProperty) {
                $strFirstAnnotation = $this->searchFirstAnnotationInDoc($objOneProperty->getDocComment(), $strAnnotation);
                if ($strFirstAnnotation !== false)
                    return $strFirstAnnotation;
            }
        }

        //check if there's a base-class -> inheritance
        $objBaseClass = $this->objReflectionClass->getParentClass();
        if($objBaseClass !== false) {
            $objBaseAnnotations = new class_reflection($objBaseClass->getName());
            return $objBaseAnnotations->getAnnotationValueForProperty($strProperty, $strAnnotation);
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
            "setLong".$strPropertyName,
            "setArr".$strPropertyName,
            "setObj".$strPropertyName,
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
            "getLong".$strPropertyName,
            "getArr".$strPropertyName,
            "getObj".$strPropertyName,
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
        
        if (count($arrAnnotations) > 0)
            return $arrAnnotations[0];
        
        return false;
    }


    /**
     * Internal helper, does the parsing of the comment.
     * Returns an array of all matching annotations.
     *
     * @param string $strDoc
     * @param string $strAnnotation
     * @return string[]
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
     * @return array
     */
    private function searchAllAnnotationsInDoc($strDoc) {
        $arrReturn = array();
        
        $strCacheKey = md5($strDoc);
        
        if (isset($this->arrCurrentCache[self::$STR_DOC_COMMENT_PROPERTIES_CACHE][$strCacheKey]))
            return $this->arrCurrentCache[self::$STR_DOC_COMMENT_PROPERTIES_CACHE][$strCacheKey];
        
        $arrMatches = array();
        if (preg_match_all("/(@[a-zA-Z0-9]+)(\s+.*)?$/Um", $strDoc, $arrMatches, PREG_SET_ORDER) !== false) {
            foreach ($arrMatches as $arrOneMatch) {
                $strName = $arrOneMatch[1];
                $strValue = isset($arrOneMatch[2]) ? $arrOneMatch[2] : "";
                
                if (!isset($arrReturn[$strName]))
                    $arrReturn[$strName] = array();
                
                $arrReturn[$strName][] = trim($strValue);
            }
        }
        
        $this->arrCurrentCache[self::$STR_DOC_COMMENT_PROPERTIES_CACHE][$strCacheKey] = $arrReturn;
        self::$bitCacheSaveRequired = true;
        return $arrReturn;
    }
}
class_reflection::staticConstruct();
