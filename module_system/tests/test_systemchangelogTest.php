<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");
require_once (__DIR__."/../../module_system/system/interface_versionable.php");

/**
 * Tests a few aspects of the changelog-component
 *
 * @author sidler@mulchprod.de
 * @package module_system
 */
class test_systemchangelogTest extends class_testbase {

    public function testChangelog() {

        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objChanges = new class_module_system_changelog();

        $strSystemid = generateSystemid();
        $arrOldValues = $objChanges->readOldValues(new dummyObject($strSystemid));

        $this->assertEquals($arrOldValues["strTest"], "old");
        $this->assertEquals($arrOldValues["second"], "second old");

        $arrOldValues = $objChanges->getOldValuesForSystemid($strSystemid);
        $this->assertEquals($arrOldValues["strTest"], "old");
        $this->assertEquals($arrOldValues["second"], "second old");


        $objChanges->createLogEntry(new dummyObject($strSystemid), "1");
        $this->assertEquals(0, class_module_system_changelog::getLogEntriesCount($strSystemid));

        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objDummy = new dummyObject($strSystemid);
        $objDummy->setStrTest("new test 1");
        $objDummy->setStrSecondTest("new val 2");

        $objChanges->createLogEntry($objDummy, "2");
        $this->assertEquals(2, class_module_system_changelog::getLogEntriesCount($strSystemid));
        $this->assertEquals(2, count(class_module_system_changelog::getLogEntries($strSystemid)));

        $arrLogs = class_module_system_changelog::getLogEntries($strSystemid);
        foreach($arrLogs as $objOneChangelog) {
            if($objOneChangelog->getStrProperty() == "strTest") {
                $this->assertEquals($objOneChangelog->getStrOldValue(), "old");
                $this->assertEquals($objOneChangelog->getStrNewValue(), "new test 1");
            }

            if($objOneChangelog->getStrProperty() == "second") {
                $this->assertEquals($objOneChangelog->getStrOldValue(), "second old");
                $this->assertEquals($objOneChangelog->getStrNewValue(), "new val 2");
            }
        }

        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objChanges->createLogEntry(new dummyObject($strSystemid), "2", true);
        $this->assertEquals(4, class_module_system_changelog::getLogEntriesCount($strSystemid));
        $this->assertEquals(4, count(class_module_system_changelog::getLogEntries($strSystemid)));


    }
}


class dummyObject implements interface_versionable {

    /**
     * @var
     * @versionable
     */
    private $strTest = "old";

    /**
     * @var
     * @versionable second
     */
    private $strSecondTest = "second old";

    private $strSystemid;
    function __construct($strSystemid) {
        $this->strSystemid = $strSystemid;
    }

    public function getSystemid() {
        return $this->strSystemid;
    }

    public function getPrevid() {
        return "";
    }

    public function setStrSystemid($strSystemid) {
        $this->strSystemid = $strSystemid;
    }

    public function renderVersionValue($strProperty, $strValue) {
        return $strValue;
    }

    public function getVersionActionName($strAction) {
        return "dummy";
    }

    public function getArrModule($strKey) {
        return "dummy";
    }

    public function getVersionPropertyName($strProperty) {
        return $strProperty;
    }

    public function getVersionRecordName() {
        return "dummy";
    }

    public function setStrSecondTest($strSecondTest) {
        $this->strSecondTest = $strSecondTest;
    }

    public function getStrSecondTest() {
        return $this->strSecondTest;
    }

    public function setStrTest($strTest) {
        $this->strTest = $strTest;
    }

    public function getStrTest() {
        return $this->strTest;
    }

}
