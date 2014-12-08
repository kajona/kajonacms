<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
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

    protected function setUp() {

        //echo "\n\nlogging test-setUp on ".get_class($this)." @ ".timeToString(time())."...\n";

        if(!defined("_block_config_db_loading_")) {
            define("_block_config_db_loading_", true);
        }

        if(!defined("_autotesting_")) {
            define("_autotesting_", true);
        }

        $objCarrier = class_carrier::getInstance();

        $strSQL = "UPDATE "._dbprefix_."system_config SET system_config_value = ?
                    WHERE system_config_name = ?";

        $objCarrier->getObjDB()->_pQuery($strSQL, array("true", "_system_changehistory_enabled_"));
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_APC | class_carrier::INT_CACHE_TYPE_DBQUERIES);

        class_config::getInstance()->loadConfigsDatabase(class_db::getInstance());

        //flush garbage collection, should avoid some segfaults on php 5.3.
        gc_collect_cycles();
        gc_disable();

        parent::setUp();
    }


    protected function tearDown() {

        //reenable garbage collection
        gc_enable();


        $objChangelog = new class_module_system_changelog();
        $objChangelog->processCachedInserts();

        parent::tearDown();
    }

    protected function flushDBCache() {
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES);
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
        //create the object
        $objReflector = new ReflectionClass($strClassType);
        $obj = $objReflector->newInstance();
        //TODO: why is this required here? could lead to wrong onInsertToDb triggers
//        $obj->updateObjectToDb($strParentId);

        $objReflectorAnnotated = new class_reflection($strClassType);

        //get properties which are annotated with @var and have a setter method
        $arrReflectionProperties = $objReflector->getProperties();
        foreach($arrReflectionProperties as $objReflectionProperty) {
            $strPropName = $objReflectionProperty->getName();

            //Exclude properties to be set
            if(in_array($strPropName, $arrExcludeFillProperty)) {
                continue;
            }

            //Set properties from array $arrPropertyValues
            if(array_key_exists($strPropName, $arrPropertyValues)) {
                $strSetterMethod = $objReflectorAnnotated->getSetter($strPropName);
                if($objReflector->hasMethod($strSetterMethod)) {
                    $objValue = $arrPropertyValues[$strPropName];
                    $objReflectionMethod = $objReflector->getMethod($strSetterMethod);
                    $objReflectionMethod->invoke($obj, $objValue);
                    continue;
                }
            }

            //check if the property is annotated with @tablecolumn
            if($bitAutofillProperties) {
                if($objReflectorAnnotated->hasPropertyAnnotation($strPropName, class_orm_base::STR_ANNOTATION_TABLECOLUMN)) {
                    $strSetterMethod = $objReflectorAnnotated->getSetter($strPropName);

                    if($objReflector->hasMethod($strSetterMethod)) {
                        $objReflectionMethod = $objReflector->getMethod($strSetterMethod);

                        //determine the field type
                        $strDataType = $objReflectorAnnotated->getAnnotationValueForProperty($strPropName, "@var");
                        $strFieldType = $objReflectorAnnotated->getAnnotationValueForProperty($strPropName, "@fieldType");
                        $objMethodValue = null;

                        if($strDataType == "string") {
                            if($strFieldType == "text" || $strFieldType == "textarea") {
                                $objMethodValue = $strPropName."_".$obj->getStrSystemid();

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

                        $objReflectionMethod->invoke($obj, $objMethodValue);
                    }
                }
            }
        }

        //save it
        $obj->updateObjectToDb($strParentId);
        return $obj;
    }


    /**
     * Resets all relevant caches
     */
    protected function resetCaches() {
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE | class_carrier::INT_CACHE_TYPE_OBJECTFACTORY | class_carrier::INT_CACHE_TYPE_APC);
    }

}


