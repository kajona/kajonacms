<?php

require_once __DIR__."/class_testbase.php";

/**
 * The class_testbase is the common baseclass for all testcases which need to test against an object tree
 *
 * @package module_system
 * @since 3.4
 * @author sidler@mulchprod.de
 */
abstract class class_testbase_object extends class_testbase {

    protected $arrObjects = array();
    protected $arrOes = array();
    protected $arrDimensions = array();

    protected function setUp() {

        parent::setUp();

        $file = $this->getFixtureFile();
        if(!is_file($file)) {
            throw new RuntimeException('Could not find fixture file ' . $file);
        }

        $dom = new DOMDocument();
        $dom->load($file);

        $objObject = null;
        $objOe = null;
        $objDimension = null;
        foreach($dom->documentElement->childNodes as $objChild) {
            if($objChild instanceof DOMElement) {
                if($objChild->nodeName == 'object') {
                    $objObject = $objChild;
                }
                else if($objChild->nodeName == 'oe') {
                    $objOe = $objChild;
                }
                else if($objChild->nodeName == 'dimension') {
                    $objDimension = $objChild;
                }
            }
        }

        // we must create first oes and dimensions so that objects can refer to them
        if(!empty($objDimension)) {
            $this->createDimensionStructure($objDimension);
        }

        if(!empty($objOe)) {
            $this->createOeStructure($objOe);
        }

        if(!empty($objObject)) {
            $this->createObjectStructure($objObject);
        }

        $this->flushDBCache();
        class_module_prozessverwaltung_dimension::resetCache();
    }

    protected function tearDown() {

        parent::tearDown();

        $this->cleanStructure();

    }

    protected function createObjectStructure(DOMElement $objElement, $objParent = null)
    {
        $arrParameters = array();
        foreach($objElement->attributes as $objAttr) {
            if(!in_array($objAttr->nodeName, array('class', 'name', 'assignments'))) {
                $arrParameters[$objAttr->nodeName] = (string) $objAttr->nodeValue;
            }
        }

        $strParentId = $objParent === null ? class_module_prozessverwaltung_prozess::getProcessRootId() : $objParent->getStrSystemid();
        $strName = $objElement->getAttribute('name');
        $arrAssignments = explode(',', $objElement->getAttribute('assignments'));

        $className = $objElement->getAttribute('class');
        if(empty($className)) {
            throw new RuntimeException('No class name given for object "' . $strName . '" (' . $objElement->getNodePath() . ')');
        }

        // resolve references
        foreach($arrParameters as $strKey => $strValue) {
            if(substr($strValue, 0, 4) == 'ref:') {
                $strRef = trim(substr($strValue, 4));
                if(isset($this->arrObjects[$strRef])) {
                    $arrParameters[$strKey] = $this->arrObjects[$strRef]->getStrSystemid();
                }
                else {
                    throw new RuntimeException('Object "' . $strName . '" refers to an non existing object (' . $objElement->getNodePath() . ')');
                }
            }
        }

        $objObject = $this->createObject($className, $strParentId, array(), $arrParameters, false);

        if(isset($this->arrObjects[$strName])) {
            throw new RuntimeException('Object name "' . $strName . '" already exists (' . $objElement->getNodePath() . ')');
        }

        $this->arrObjects[$strName] = $objObject;

        foreach($objElement->childNodes as $objNode) {
            if($objNode instanceof DOMElement) {
                $this->createObjectStructure($objNode, $objObject);
            }
        }

        foreach($arrAssignments as $strName) {
            $strName = trim($strName);
            if(!empty($strName)) {
                if(isset($this->arrOes[$strName])) {
                    $objObject->setStrOeId($this->arrOes[$strName]->getStrSystemid());
                }
                else if(isset($this->arrDimensions[$strName])) {
                    $objObject->addAssignedUnterdimension($this->arrDimensions[$strName]->getStrSystemid());
                }
                else {
                    throw new RuntimeException('Object "' . $objObject->getStrSystemid() . '" refers to an non existing object "' . $strName . '" (' . $objElement->getNodePath() . ')');
                }
            }
        }

        $objObject->updateObjectToDb();
    }

    protected function createOeStructure(DOMElement $objElement, $objParent = null)
    {
        $arrParameters = array();
        foreach($objElement->attributes as $objAttr) {
            if(!in_array($objAttr->nodeName, array('class', 'name'))) {
                $arrParameters[$objAttr->nodeName] = $objAttr->nodeValue;
            }
        }

        $strParentId = $objParent === null ? class_module_prozessverwaltung_oe::getOeRootId() : $objParent->getStrSystemid();
        $strName = $objElement->getAttribute('name');

        $className = $objElement->getAttribute('class');
        if(empty($className)) {
            throw new RuntimeException('No class name given for oe "' . $strName . '" (' . $objElement->getNodePath() . ')');
        }

        $objObject = $this->createObject($className, $strParentId, array(), $arrParameters, false);

        if(isset($this->arrOes[$strName])) {
            throw new RuntimeException('Oe name "' . $strName . '" already exists (' . $objElement->getNodePath() . ')');
        }

        $this->arrOes[$strName] = $objObject;

        foreach($objElement->childNodes as $objNode) {
            if($objNode instanceof DOMElement) {
                $this->createOeStructure($objNode, $objObject);
            }
        }
    }

    protected function createDimensionStructure(DOMElement $objElement, $objParent = null)
    {
        $arrParameters = array();
        foreach($objElement->attributes as $objAttr) {
            if(!in_array($objAttr->nodeName, array('class', 'name'))) {
                $arrParameters[$objAttr->nodeName] = $objAttr->nodeValue;
            }
        }

        $strParentId = $objParent === null ? class_module_prozessverwaltung_dimension::getDimensionRootId() : $objParent->getStrSystemid();
        $strName = $objElement->getAttribute('name');

        $className = $objElement->getAttribute('class');
        if(empty($className)) {
            throw new RuntimeException('No class name given for dimension "' . $strName . '" (' . $objElement->getNodePath() . ')');
        }

        $objObject = $this->createObject($className, $strParentId, array(), $arrParameters, false);

        if(isset($this->arrDimensions[$strName])) {
            throw new RuntimeException('Dimension name "' . $strName . '" already exists (' . $objElement->getNodePath() . ')');
        }

        $this->arrDimensions[$strName] = $objObject;

        foreach($objElement->childNodes as $objNode) {
            if($objNode instanceof DOMElement) {
                $this->createDimensionStructure($objNode, $objObject);
            }
        }
    }

    protected function cleanStructure()
    {
        $arrDimensions = array_reverse($this->arrDimensions);
        foreach($arrDimensions as $objObject) {
            /** @var $objObject class_module_prozessverwaltung_dimension */
            $objObject->deleteObject();
        }

        $arrOes = array_reverse($this->arrOes);
        foreach($arrOes as $objObject) {
            /** @var $objObject class_module_prozessverwaltung_oe */
            $objObject->deleteObjectFromDatabase();
        }

        $arrObjects = array_reverse($this->arrObjects);
        foreach($arrObjects as $objObject) {
            /** @var $objObject class_module_prozessverwaltung_prozess */
            $objObject->deleteObjectFromDatabase();
        }
    }

    abstract protected function getFixtureFile();

    protected function assertTreeEqualsTree($expect, array $actual)
    {
        $this->assertEquals($expect, $this->getTreeAsStringArray($actual));
    }

    protected function getTreeAsStringArray(array $data, $deep = 0)
    {
        $arrResult = array();
        $arrResult[] = str_repeat('-', $deep) . ' ' . (isset($data['attributes']['strTitel']) ? $data['attributes']['strTitel'] : '.');

        if(isset($data['children']) && is_array($data['children'])) {
            foreach($data['children'] as $child) {
                $arrResult = array_merge($arrResult, $this->getTreeAsStringArray($child, $deep + 1));
            }
        }

        return $arrResult;
    }

}
