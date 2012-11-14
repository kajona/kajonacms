<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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

    private $arrCurrentCache;
    private $strSourceClass;

    /**
     *
     * @var ReflectionClass
     */
    private $objReflectionClass;

    public static function static_construct() {
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
                self::$STR_HASPROPERTY_CACHE
            );

        $this->arrCurrentCache = &self::$arrAnnotationsCache[$this->strSourceClass];
        $this->objReflectionClass = new ReflectionClass($this->strSourceClass);
    }

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
     * @return array
     */
    public function getAnnotationValuesFromClass($strAnnotation) {
        if(isset($this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strAnnotation]))
            return $this->arrCurrentCache[self::$STR_CLASS_PROPERTIES_CACHE][$strAnnotation];

        $strClassDoc = $this->objReflectionClass->getDocComment();
        $arrValues = $this->searchAllAnnotationsInDoc($strClassDoc, $strAnnotation);

        $arrReturn = array();
        foreach($arrValues as $strOneProperty) {
            $arrReturn[] = trim(uniSubstr($strOneProperty, uniStrpos($strOneProperty, $strAnnotation)+uniStrlen($strAnnotation)));
        }

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
            $bitReturn = false !== $this->searchAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
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

        $objReflectionMethod = $this->objReflectionClass->getProperty($strPropertyName);
        $bitReturn = false !== $this->searchAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);

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
        $strLine = $this->searchAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
        if($strLine === false) {
            $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation] = false;
            return false;
        }

        //strip the annotation parts
        $strReturn = trim(uniSubstr($strLine, uniStrpos($strLine, $strAnnotation)+uniStrlen($strAnnotation)));
        $this->arrCurrentCache[self::$STR_METHOD_CACHE][$strMethodName."_".$strAnnotation] = $strReturn;
        self::$bitCacheSaveRequired = true;
        return $strReturn;
    }

    /**
     * Searches the current class for properties marked with a given annotation.
     * If found, the name of the property plus the (optional) value of the property is returned.
     * The base classes are queried, too.
     *
     * @param $strAnnotation
     * @return array ["propertyname" => "annotationvalue"]
     */
    public function getPropertiesWithAnnotation($strAnnotation) {

        if(isset($this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strAnnotation]))
            return $this->arrCurrentCache[self::$STR_PROPERTIES_CACHE][$strAnnotation];

        $arrProperties = $this->objReflectionClass->getProperties();

        $arrReturn = array();

        foreach($arrProperties as $objOneProperty) {
            $strLine = $this->searchAnnotationInDoc($objOneProperty->getDocComment(), $strAnnotation);
            if($strLine !== false) {
                $arrReturn[$objOneProperty->getName()] = trim(uniSubstr($strLine, uniStrpos($strLine, $strAnnotation)+uniStrlen($strAnnotation)));
            }
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
     * @param $strProperty
     * @param $strAnnotation
     *
     * @return null|string
     */
    public function getAnnotationValueForProperty($strProperty, $strAnnotation) {
        $arrProperties = $this->objReflectionClass->getProperties();

        foreach($arrProperties as $objOneProperty) {
            if($objOneProperty->getName() == $strProperty) {
                $strLine = $this->searchAnnotationInDoc($objOneProperty->getDocComment(), $strAnnotation);
                if($strLine !== false) {
                    return trim(uniSubstr($strLine, uniStrpos($strLine, $strAnnotation)+uniStrlen($strAnnotation)));
                }
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
     * @static
     * @param string $strPropertyName
     * @return null|string
     */
    public function getSetter($strPropertyName) {

        $strSetter = "setStr".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "setInt".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "setFloat".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "setBit".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "setLong".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "setArr".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "set".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        return null;
    }


    /**
     * Searches an object for a given properties' getter method.
     * If not found, null is returned instead.
     * @static
     * @param string $strPropertyName
     * @return null|string
     */
    public function getGetter($strPropertyName) {

        $strSetter = "getStr".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "getInt".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "getFloat".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "getBit".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "getLong".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "getArr".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

        $strSetter = "get".$strPropertyName;
        if(method_exists($this->strSourceClass, $strSetter))
            return $strSetter;

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
    private function searchAnnotationInDoc($strDoc, $strAnnotation) {
        $arrLines = explode("\n", $strDoc);
        foreach($arrLines as $strOneLine) {
            if(uniStrpos($strOneLine, $strAnnotation) !== false) {
                return $strOneLine;
            }
        }

        return false;
    }


    /**
     * Internal helper, does the parsing of the comment.
     * Returns an array of all matching annotations.
     *
     * @param string $strDoc
     * @param string $strAnnotation
     * @return array
     */
    private function searchAllAnnotationsInDoc($strDoc, $strAnnotation) {
        $arrReturn = array();
        $arrLines = explode("\n", $strDoc);
        foreach($arrLines as $strOneLine) {
            if(uniStrpos($strOneLine, $strAnnotation) !== false) {
                $arrReturn[] = $strOneLine;
            }
        }

        return $arrReturn;
    }
}
class_reflection::static_construct();
