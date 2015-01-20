<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * The class_object_serializer is based on the class_template_mapper. Returns 
 * all properties of an class which has an @jsonExport annotation. These 
 * properties are exposed through the json api. Currently the @jsonMapper 
 * annotation uses the same mapper classes as the @templateMapper
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @author christoph.kappestein@gmail.com
 * @since 4.5
 *
 * @todo this class requires some cleanup, e.g. the removal of unused methods
 */
class class_object_serializer {

    const STR_ANNOTATION_JSONEXPORT = "@jsonExport";
    const STR_ANNOTATION_JSONMAPPER = "@jsonMapper";

    /** @var class_root */
    private $objObject = null;

    private $arrMapping = array();


    /**
     * @param null $objObject
     */
    function __construct($objObject = null) {
        $this->objObject = $objObject;

        if($objObject !== null)
            $this->readPropertiesFromObject();
    }

    /**
     * Returns an array with all property names
     *
     * @return array
     */
    public function getPropertyNames() {
        $objReflection = new class_reflection($this->objObject);
        $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_ANNOTATION_JSONEXPORT);

        return array_keys($arrProperties);
    }

    /**
     * Adds a single entry to the current set of mapped values
     *
     * @param string $strName
     * @param string $strValue
     * @param string $strTemplateMapper
     *
     * @return void
     */
    public function addPlaceholder($strName, $strValue, $strTemplateMapper = "default") {
        try {
            $objMapper = $this->getMapperInstance($strTemplateMapper);
            $strValue = $objMapper->format($strValue);
        }
        catch(class_exception $objException) {
            $strValue = $objException->getMessage();
        }
        $this->arrMapping[$strName] = $strValue;
    }

    /**
     * Reads the properties marked with templateExport from the current object
     *
     * @return void
     */
    private function readPropertiesFromObject() {
        $objReflection = new class_reflection($this->objObject);
        $properties = $this->getPropertyNames();

        foreach($properties as $strOneProperty) {
            $strGetter = $objReflection->getGetter($strOneProperty);

            //get the jsonmapper
            $strMapper = $objReflection->getAnnotationValueForProperty($strOneProperty, self::STR_ANNOTATION_JSONMAPPER);
            if($strMapper == null)
                $strMapper = "default";

            $this->addPlaceholder($strOneProperty, call_user_func(array($this->objObject, $strGetter)), $strMapper);
        }
    }

    /**
     * Loads the validator identified by the passed name.
     *
     * @param string $strName
     * @return interface_templatemapper
     * @throws class_exception
     */
    private function getMapperInstance($strName) {
        $strClassname = "class_".$strName."_templatemapper";
        if(class_resourceloader::getInstance()->getPathForFile("/portal/templatemapper/".$strClassname.".php")) {
            return new $strClassname();
        }
        else
            throw new class_exception("failed to load validator of type ".$strClassname, class_exception::$level_ERROR);
    }

    /**
     * @param array $arrMapping
     * @return void
     */
    public function setArrMapping($arrMapping) {
        $this->arrMapping = $arrMapping;
    }

    /**
     * @return array
     */
    public function getArrMapping() {
        return $this->arrMapping;
    }
}
