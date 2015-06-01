<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


require_once __DIR__."../../../bootstrap.php";


/**
 * The class_testbase is the common baseclass for all testcases.
 * Triggers the methods required to run proper PHPUnit tests such as starting the system-kernel
 *
 * @package module_system
 * @since 3.4
 * @author sidler@mulchprod.de
 */
abstract class class_testbase extends PHPUnit_Framework_TestCase {

    private $arrTestStartDate = null;

    protected function printDebugValues() {
        $strDebug = "";
        $arrTimestampEnde = gettimeofday();
        $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($this->arrTestStartDate['sec'] * 1000000 + $this->arrTestStartDate['usec'])) / 1000000;

        $strDebug .= "PHP-Time:                            " . number_format($intTimeUsed, 6) . " sec \n";

        //Hows about the queries?
        $strDebug .= "Queries db/cachesize/cached/fired:   " . class_carrier::getInstance()->getObjDB()->getNumber() . "/" .
            class_carrier::getInstance()->getObjDB()->getCacheSize() . "/" .
            class_carrier::getInstance()->getObjDB()->getNumberCache() . "/" .
            (class_carrier::getInstance()->getObjDB()->getNumber() - class_carrier::getInstance()->getObjDB()->getNumberCache()) . " \n";

        //anything to say about the templates?
        $strDebug .= "Templates cached:                    " . class_carrier::getInstance()->getObjTemplate()->getNumberCacheSize() . " \n";

        //memory
        $strDebug .= "Memory/Max Memory:                   " . bytesToString(memory_get_usage()) . "/" . bytesToString(memory_get_peak_usage()) . " \n";
        $strDebug .= "Classes Loaded:                      " . class_classloader::getInstance()->getIntNumberOfClassesLoaded() . " \n";

        //and check the cache-stats
        $strDebug .= "Cache requests/hits/saves/cachesize: " .
            class_cache::getIntRequests() . "/" . class_cache::getIntHits() . "/" . class_cache::getIntSaves() . "/" . class_cache::getIntCachesize() . " \n";


        //echo get_called_class()."\n".$strDebug."\n";
    }

    protected function setUp() {

        $this->arrTestStartDate = gettimeofday();

        if(!defined("_block_config_db_loading_")) {
            define("_block_config_db_loading_", true);
        }

        if(!defined("_autotesting_")) {
            define("_autotesting_", true);
        }

        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_APC | class_carrier::INT_CACHE_TYPE_DBQUERIES);
        parent::setUp();
    }


    protected function tearDown() {
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_CHANGELOG);

        $this->printDebugValues();

        parent::tearDown();
    }

    protected function flushDBCache() {
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES |class_carrier::INT_CACHE_TYPE_DBTABLES);
    }


    /**
     * Crreates an object of type '$strClassType'.
     * Only properties which are annotated with @var will be considered
     *
     * @param string $strClassType - the name of the class as a string
     * @param string $strParentId - the parent id of the object to be created
     * @param array $arrExcludeFillProperty - array of poperty names which will not be set
     * @param array $arrPropertyValues - assoziative array which has as key the property name and as value the to be set for the property
     * @param boolean $bitAutofillProperties - if true all properties which have annotation @tablecolumn will be filled with random values
     *
     * @return class_model
     */
    protected function createObject($strClassType, $strParentId, array $arrExcludeFillProperty = array(),  array $arrPropertyValues = array(), $bitAutofillProperties = true) {
        //get properties with an tablecolumn annotation
        $objObject = new $strClassType();
        $objReflection = new class_reflection($strClassType);
        $arrProperties = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_TABLECOLUMN);

        foreach($arrProperties as $strPropName => $strValue) {

            //Exclude properties to be set
            if(in_array($strPropName, $arrExcludeFillProperty)) {
                continue;
            }

            //Set properties from array $arrPropertyValues
            if(array_key_exists($strPropName, $arrPropertyValues)) {
                $strSetterMethod = $objReflection->getSetter($strPropName);
                if($strSetterMethod !== null) {
                    $objValue = $arrPropertyValues[$strPropName];
                    $objObject->$strSetterMethod($objValue);
                    continue;
                }
            }

            //check if the property is annotated with @tablecolumn
            if($bitAutofillProperties) {
                if($objReflection->hasPropertyAnnotation($strPropName, class_orm_base::STR_ANNOTATION_TABLECOLUMN)) {
                    $strSetterMethod = $objReflection->getSetter($strPropName);
                    if($strSetterMethod !== null) {
                        //determine the field type
                        $strDataType = $objReflection->getAnnotationValueForProperty($strPropName, "@var");
                        $strFieldType = $objReflection->getAnnotationValueForProperty($strPropName, "@fieldType");
                        $objMethodValue = null;

                        if($strDataType == "string") {
                            if($strFieldType == "text" || $strFieldType == "textarea") {
                                $objMethodValue = $strPropName."_".$objObject->getStrSystemid();

                                if(uniStrlen($objMethodValue) > 10) {
                                    $objMethodValue = uniStrTrim($objMethodValue, 10, "");
                                }
                            }
                        }
                        else if($strDataType == "int" || $strDataType == "numeric") {
                            if($strFieldType != "dropdown") {
                                $objMethodValue = 1;
                            }
                        }
                        else if($strDataType == "class_date") {
                            $objMethodValue = new class_date();
                        }
                        else if($strDataType == "bool") {
                            $objMethodValue = false;
                        }
                        else {
                            continue;//continue with foreach
                        }

                        $objObject->$strSetterMethod($objMethodValue);
                    }
                }
            }
        }

        //save it
        $objObject->updateObjectToDb($strParentId);
        return $objObject;
    }


    /**
     * Resets all relevant caches
     */
    protected function resetCaches() {
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE | class_carrier::INT_CACHE_TYPE_OBJECTFACTORY | class_carrier::INT_CACHE_TYPE_APC);
    }

}


