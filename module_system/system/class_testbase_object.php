<?php

require_once __DIR__."/class_testbase.php";

/**
 * Base class to create object structures which can be used to test against an object tree
 *
 * @package module_system
 * @since 3.4
 * @author sidler@mulchprod.de
 */
abstract class class_testbase_object extends class_testbase {

    protected function setUp() {

        parent::setUp();

        $strFile = $this->getFixtureFile();
        if(!is_file($strFile)) {
            throw new RuntimeException('Could not find fixture file ' . $strFile);
        }

        $objDom = new DOMDocument();
        $objDom->load($strFile);

        $this->createStructure($objDom->documentElement);

        $this->flushDBCache();

    }

    protected function tearDown() {

        parent::tearDown();

        $this->cleanStructure();

    }

    /**
     * Returns an path to an xml fixture file which can be used to create and delete database structures
     *
     * @return string
     */
    abstract protected function getFixtureFile();

    /**
     * Creates the database structure like defined in the xml element
     *
     * @param DOMElement $objElement
     * @return void
     */
    abstract protected function createStructure(DOMElement $objElement);

    /**
     * Cleans up the database structure
     *
     * @return void
     */
    abstract protected function cleanStructure();

    /**
     * Returns the default root id for an given class name
     *
     * @param string $strClassName
     * @return string
     */
    abstract protected function getDefaultRootId($strClassName);

    /**
     * Is called if an object gets created can be used to store the reference which can be used later in the test
     *
     * @param string $strName
     * @param string $objObject
     */
    abstract protected function addObject($strName, class_model $objObject);

    /**
     * Returns an object for an given reference name
     *
     * @param $strName
     * @return class_model
     */
    abstract protected function getObject($strName);

    /**
     * Assigns an reference to an object
     *
     * @param $objSource
     * @param $objReference
     * @return mixed
     */
    abstract protected function assignReferenceToObject(class_model $objSource, class_model $objReference);

    protected function createDataStructure(DOMElement $objElement, $objParent = null)
    {
        $arrParameters = array();
        foreach($objElement->attributes as $objAttr) {
            if(!in_array($objAttr->nodeName, array('class', 'name'))) {
                $arrParameters[$objAttr->nodeName] = $objAttr->nodeValue;
            }
        }

        $strName = $objElement->getAttribute('name');

        $strClassName = $objElement->getAttribute('class');
        if(empty($strClassName)) {
            throw new RuntimeException('No class name given for object "' . $strName . '" (' . $objElement->getNodePath() . ')');
        }

        $strParentId = $objParent === null ? $this->getDefaultRootId($strClassName) : $objParent->getStrSystemid();

        // resolve references
        foreach($arrParameters as $strKey => $strValue) {
            if(substr($strValue, 0, 4) == 'ref:') {
                $strRef = trim(substr($strValue, 4));
                $objRef = $this->getObject($strRef);
                if($objRef instanceof class_model) {
                    $arrParameters[$strKey] = $objRef->getStrSystemid();
                }
                else {
                    throw new RuntimeException('Object "' . $strName . '" refers to an non existing object (' . $objElement->getNodePath() . ')');
                }
            }
        }

        $objObject = $this->createObject($strClassName, $strParentId, array(), $arrParameters, false);

        $this->addObject($strName, $objObject);

        // handle assignments
        $arrAssignments = explode(',', $objElement->getAttribute('assignments'));

        foreach($arrAssignments as $strName) {
            $strName = trim($strName);
            if(!empty($strName)) {
                $objReference = $this->getObject($strName);
                if($objReference instanceof class_model) {
                    $this->assignReferenceToObject($objObject, $objReference);
                }
            }
        }

        $objObject->updateObjectToDb();

        // walk through child elements
        foreach($objElement->childNodes as $objNode) {
            if($objNode instanceof DOMElement) {
                $this->createDataStructure($objNode, $objObject);
            }
        }
    }
}
