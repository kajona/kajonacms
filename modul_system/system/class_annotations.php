<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
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
 * @package modul_system
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
     * @param string $strSourceClass
	 */
	public function __construct($strSourceClass) {

        if(!class_exists($strSourceClass))
            throw new class_exception("class ".$strSourceClass." not found", class_exception::$level_ERROR);
        
        $this->objReflectionClass = new ReflectionClass($strSourceClass);
	}

    /**
     * searches an annotation (e.g. @version) in the doccomment of a passed method.
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
                return true;
        }
        
        return false;
    }
	
} 

?>