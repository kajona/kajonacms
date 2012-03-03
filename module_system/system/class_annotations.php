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
class class_annotations {

    /**
     *
     * @var ReflectionClass
     */
    private $objReflectionClass;

	/**
	 * Creates an instance of the annotations-class, parametrized with the class to inspect
     *
     * @param string|object $strSourceClass
	 */
	public function __construct($strSourceClass) {

        if(is_object($strSourceClass))
            $strSourceClass = get_class($strSourceClass);

        if(!class_exists($strSourceClass))
            throw new class_exception("class ".$strSourceClass." not found", class_exception::$level_ERROR);

        $this->objReflectionClass = new ReflectionClass($strSourceClass);
	}

    /**
     * searches an annotation (e.g. @version) in the doccomment of a passed method and validates
     * the existence of this method
     *
     * @param string $strMethodName
     * @param string $strAnnotation
     * @return bool
     */
    public function hasMethodAnnotation($strMethodName, $strAnnotation) {
        $objReflectionMethod = $this->objReflectionClass->getMethod($strMethodName);
        return $this->searchAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
    }


    /**
     * searches an annotation (e.g. @version) in the doccomment of a passed property and validates
     * the existence of this method
     *
     * @param string $strPropertyName
     * @param string $strAnnotation
     * @return bool
     */
    public function hasPropertyAnnotation($strPropertyName, $strAnnotation) {
        $objReflectionMethod = $this->objReflectionClass->getProperty($strPropertyName);
        return $this->searchAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
    }

    /**
     * Searches an annotation (e.g. @version) in the doccomment of a passed method
     * and passes the value, so anything behind the @name part.
     * E.g., if the annotation is written like
     *   @test value1, value2
     * then only
     *   value1, value2
     * are returned.
     * If the annotation could not be found, false is returned instead.
     *
     * @param string $strMethodName
     * @param string $strAnnotation
     * @return string|false
     */
    public function getMethodAnnotationValue($strMethodName, $strAnnotation) {
        $objReflectionMethod = $this->objReflectionClass->getMethod($strMethodName);
        $strLine = $this->searchAnnotationInDoc($objReflectionMethod->getDocComment(), $strAnnotation);
        if($strLine === false)
            return false;

        //strip the annotation parts
        return trim(uniSubstr($strLine, uniStrpos($strLine, $strAnnotation)+uniStrlen($strAnnotation)));
    }

    /**
     * Searches the current class for properties marked with a given annotation.
     * If found, the name of the propery plus the (optional) value of the property is returned.
     *
     * @param $strAnnotation
     * @return array ["propertyname" => "annotationvalue"]
     */
    public function getPropertiesWithAnnotation($strAnnotation) {
        $arrProperties = $this->objReflectionClass->getProperties();

        $arrReturn = array();

        foreach($arrProperties as $objOneProperty) {
            $strLine = $this->searchAnnotationInDoc($objOneProperty->getDocComment(), $strAnnotation);
            if($strLine !== false) {
                $arrReturn[$objOneProperty->getName()] = trim(uniSubstr($strLine, uniStrpos($strLine, $strAnnotation)+uniStrlen($strAnnotation)));
            }
        }

        return $arrReturn;
    }

    /**
     * Internal helper, does the parsing of the comment
     *
     * @param string $strDoc
     * @param string $strAnnotation
     * @return bool
     */
    private function searchAnnotationInDoc($strDoc, $strAnnotation) {
        $arrLines = explode("\n", $strDoc);
        foreach($arrLines as $strOneLine) {
            if(uniStrpos($strOneLine, $strAnnotation) !== false)
                return $strOneLine;
        }

        return false;
    }

}

