<?php

/**
 * Base class to test object structures. These structures can be defined in an XML file. The XML fixture file could look
 * like:
 * <code>
 * <?xml version="1.0"?>
 * <objects>
 *  <object name="page_1" class="class_module_pages_page" strName="Page 1">
 *   <object name="page_2" class="class_module_pages_page" strName="Page 2" />
 *  </object>
 * </objects>
 * </code>
 *
 * @package module_system
 * @since 4.7
 * @author christoph.kappestein@gmail.com
 */
abstract class class_testbase_object extends class_testbase {

    private $arrStructure = array();

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
     * Creates the database structure like defined in the xml element
     *
     * @param DOMElement $objElement
     * @return void
     */
    protected function createStructure(DOMElement $objElement)
    {
        foreach($objElement->childNodes as $objChild) {
            if($objChild instanceof DOMElement && $objChild->nodeName == 'object') {
                $this->createDataStructure($objChild);
            }
        }

        $this->flushDBCache();
    }

    /**
     * Returns an path to an xml fixture file which can be used to create and delete database structures
     *
     * @return string
     */
    abstract protected function getFixtureFile();

    /**
     * Cleans up the database structure. This method should delete all database entries which were previously created
     *
     * @return void
     */
    protected function cleanStructure() {
        /** @var class_model $objOneModel */
        foreach(array_reverse($this->arrStructure, true) as $objOneModel) {
            $objOneModel->deleteObject();
        }

    }

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
     * @param class_model $objObject
     */
    protected function addObject($strName, class_model $objObject) {
        $this->arrStructure[$strName] = $objObject;
    }

    /**
     * Returns an object for an given reference name
     *
     * @param $strName
     * @return class_model
     */
    protected function getObject($strName) {
        if(isset($this->arrStructure[$strName]))
            return $this->arrStructure[$strName];

        return null;
    }

    /**
     * Assigns an reference to an object
     *
     * @param class_model $objSource
     * @param class_model $objReference
     */
    abstract protected function assignReferenceToObject(class_model $objSource, class_model $objReference);

    /**
     * Creates recursively the data structure for the given DOMElement. This is the main method which calls essentially
     * all abstract methods which must be implemented by an test case
     *
     * @param DOMElement $objElement
     * @param class_model $objParent
     */
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
