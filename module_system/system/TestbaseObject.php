<?php

namespace Kajona\System\System;

use DOMDocument;
use DOMElement;
use RuntimeException;


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
abstract class TestbaseObject extends Testbase
{

    private $arrStructure = array();

    protected function setUp()
    {

        parent::setUp();

        $strFile = $this->getFixtureFile();
        if (!is_file($strFile)) {
            throw new RuntimeException('Could not find fixture file ' . $strFile);
        }

        $objDom = new DOMDocument();
        $objDom->load($strFile);

        $this->createStructure($objDom->documentElement);

        $this->flushDBCache();

    }

    protected function tearDown()
    {

        parent::tearDown();

        $this->cleanStructure();

    }

    /**
     * Creates the database structure like defined in the xml element
     *
     * @param DOMElement $objElement
     *
     * @return void
     */
    protected function createStructure(DOMElement $objElement)
    {
        foreach ($objElement->childNodes as $objChild) {
            if ($objChild instanceof DOMElement && $objChild->nodeName == 'object') {
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
    protected function cleanStructure()
    {
        /** @var Model $objOneModel */
        foreach (array_reverse($this->arrStructure, true) as $objOneModel) {
            $strSystemId = $objOneModel->getStrSystemid();
            $objOneModel->deleteObjectFromDatabase();

            //if it is a user also delete the user from the database completeley
            if ($objOneModel instanceof UserUser) {
                $strQuery = "DELETE FROM " . _dbprefix_ . "user WHERE user_id=?";
                //call other models that may be interested
                $bitDelete = Database::getInstance()->_pQuery($strQuery, array($strSystemId));
            }
        }
    }

    /**
     * Returns the default root id for an given class name
     *
     * @param string $strClassName
     *
     * @return string
     */
    abstract protected function getDefaultRootId($strClassName);

    /**
     * Is called if an object gets created can be used to store the reference which can be used later in the test
     *
     * @param string $strName
     * @param Model $objObject
     */
    protected function addObject($strName, Model $objObject)
    {
        $this->arrStructure[$strName] = $objObject;
    }

    /**
     * Returns an object for an given reference name
     *
     * @param $strName
     *
     * @return Model
     */
    protected function getObject($strName)
    {
        if (isset($this->arrStructure[$strName])) {
            return $this->arrStructure[$strName];
        }

        return null;
    }

    /**
     * Assigns an reference to an object
     *
     * @param Model $objSource
     * @param Model $objReference
     */
    abstract protected function assignReferenceToObject(Model $objSource, Model $objReference);

    /**
     * Creates recursively the data structure for the given DOMElement. This is the main method which calls essentially
     * all abstract methods which must be implemented by an test case
     *
     * @param DOMElement $objElement
     * @param Model $objParent
     */
    protected function createDataStructure(DOMElement $objElement, $objParent = null)
    {
        $arrParameters = array();
        foreach ($objElement->attributes as $objAttr) {
            if (!in_array($objAttr->nodeName, array('class', 'name'))) {
                $arrParameters[$objAttr->nodeName] = $objAttr->nodeValue;
            }
        }

        $strName = $objElement->getAttribute('name');

        $strClassName = $objElement->getAttribute('class');
        if (empty($strClassName)) {
            throw new RuntimeException('No class name given for object "' . $strName . '" (' . $objElement->getNodePath() . ')');
        }

        if ($strClassName == "class_module_user_user") {
            $objObject = $this->createFixtureUser($objElement, $objParent, $strClassName, $arrParameters, $strName);
        } else {
            $objObject = $this->createFixtureObject($objElement, $objParent, $strClassName, $arrParameters, $strName);
        }

        $this->addObject($strName, $objObject);

        // handle assignments
        $arrAssignments = explode(',', $objElement->getAttribute('assignments'));

        foreach ($arrAssignments as $strName) {
            $strName = trim($strName);
            if (!empty($strName)) {
                $objReference = $this->getObject($strName);
                if ($objReference instanceof Model) {
                    $this->assignReferenceToObject($objObject, $objReference);
                }
            }
        }

        $objObject->updateObjectToDb();

        // walk through child elements
        foreach ($objElement->childNodes as $objNode) {
            if ($objNode instanceof DOMElement) {
                $this->createDataStructure($objNode, $objObject);
            }
        }
    }

    /**
     * @param DOMElement $objElement
     * @param $objParent
     * @param $strClassName
     * @param $arrParameters
     * @param $strName
     *
     * @return Model
     */
    protected function createFixtureObject(DOMElement $objElement, $objParent, $strClassName, $arrParameters, $strName)
    {
        $strParentId = $objParent === null ? $this->getDefaultRootId($strClassName) : $objParent->getStrSystemid();

        // resolve references
        foreach ($arrParameters as $strKey => $strValue) {
            if (substr($strValue, 0, 11) == 'objectlist:') {
                $strRef = trim(substr($strValue, 11));
                $arrRefs = explode(",", $strRef);

                $arrParameters[$strKey] = array();
                foreach ($arrRefs as $strRefKey) {
                    $objRef = $this->getObject($strRefKey);
                    if ($objRef instanceof Model) {
                        $arrParameters[$strKey][] = $objRef;
                    } else {
                        throw new RuntimeException('Object "' . $strName . '" refers to an non existing object (' . $objElement->getNodePath() . ')');
                    }
                }
            } elseif (substr($strValue, 0, 4) == 'ref:') {
                $strRef = trim(substr($strValue, 4));
                $objRef = $this->getObject($strRef);
                if ($objRef instanceof Model) {
                    $arrParameters[$strKey] = $objRef->getStrSystemid();
                } else {
                    throw new RuntimeException('Object "' . $strName . '" refers to an non existing object (' . $objElement->getNodePath() . ')');
                }
            }
        }

        $objObject = $this->createObject($strClassName, $strParentId, array(), $arrParameters, false);

        return $objObject;
    }


    /**
     * @param DOMElement $objElement
     * @param $objParent
     * @param $strClassName
     * @param $arrParameters
     * @param $strName
     *
     * @return Model
     */
    private function createFixtureUser(DOMElement $objElement, $objParent, $strClassName, $arrParameters, $strName)
    {
        $strUserName = $arrParameters["strUsername"];

        $objUser = new UserUser();
        $objUser->setIntActive(1);
        $objUser->setIntAdmin(1);
        $objUser->setStrUsername($strUserName);
        $objUser->updateObjectToDb();
        $objSourceUser = $objUser->getObjSourceUser();
        $objSourceUser->setStrPass($strUserName);
        $objSourceUser->setStrEmail("{$strUserName}@example");
        $objSourceUser->setStrForename($strUserName . "_Forname");
        $objSourceUser->setStrName($strUserName . "Lastname");
        $objSourceUser->updateObjectToDb();

        return $objUser;
    }

}
