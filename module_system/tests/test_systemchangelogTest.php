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
    protected function setUp() {
        parent::setUp();
        class_module_system_changelog::$bitChangelogEnabled = true;
    }

    protected function tearDown() {
        parent::tearDown();
        class_module_system_changelog::$bitChangelogEnabled = null;
    }


    public function testChangelogArrayHandling() {

        $arrOld = array(1, 2, 3, 4, 5);
        $arrNew = array(      3, 4, 5, 6, 7);

        $arrChanges = array(
            array("property" => "testArray", "oldvalue" => $arrOld, "newvalue" => $arrNew)
        );

        $strSystemid = generateSystemid();
        $objDummy = new dummyObject($strSystemid);

        $objChanges = new class_module_system_changelog();

        $this->assertEquals(0, class_module_system_changelog::getLogEntriesCount($strSystemid));
        $objChanges->processChanges($objDummy, "arrayTest", $arrChanges);
        $objChanges->processCachedInserts();
        $this->flushDBCache();
        $this->assertEquals(class_module_system_changelog::getLogEntriesCount($strSystemid), 4);

        $arrChanges = class_module_system_changelog::getSpecificEntries($strSystemid, "arrayTest", "testArray");
        $this->assertEquals(4, count($arrChanges));

        foreach($arrChanges as $objOneChangeSet) {
            if($objOneChangeSet->getStrOldValue() != "") {
                $this->assertTrue(in_array($objOneChangeSet->getStrOldValue(), array(1, 2)));
            }

            if($objOneChangeSet->getStrNewValue() != "") {
                $this->assertTrue(in_array($objOneChangeSet->getStrNewValue(), array(6, 7)));
            }
        }
    }

    public function testChangelog() {

        $this->flushDBCache();

        $objChanges = new class_module_system_changelog();

        $strSystemid = generateSystemid();
        $arrOldValues = $objChanges->readOldValues(new dummyObject($strSystemid));

        $this->assertEquals($arrOldValues["strTest"], "old");
        $this->assertEquals($arrOldValues["strSecondTest"], "second old");

        $arrOldValues = $objChanges->getOldValuesForSystemid($strSystemid);
        $this->assertEquals($arrOldValues["strTest"], "old");
        $this->assertEquals($arrOldValues["strSecondTest"], "second old");


        $objChanges->createLogEntry(new dummyObject($strSystemid), "1");
        $objChanges->processCachedInserts();
        $this->assertEquals(0, class_module_system_changelog::getLogEntriesCount($strSystemid));

        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objDummy = new dummyObject($strSystemid);
        $objDummy->setStrTest("new test 1");
        $objDummy->setStrSecondTest("new val 2");

        $objChanges->createLogEntry($objDummy, "2");
        $objChanges->processCachedInserts();
        $this->assertEquals(2, class_module_system_changelog::getLogEntriesCount($strSystemid));
        $this->assertEquals(2, count(class_module_system_changelog::getLogEntries($strSystemid)));

        $arrLogs = class_module_system_changelog::getLogEntries($strSystemid);
        foreach($arrLogs as $objOneChangelog) {
            if($objOneChangelog->getStrProperty() == "strTest") {
                $this->assertEquals($objOneChangelog->getStrOldValue(), "old");
                $this->assertEquals($objOneChangelog->getStrNewValue(), "new test 1");
            }

            if($objOneChangelog->getStrProperty() == "strSecondTest") {
                $this->assertEquals($objOneChangelog->getStrOldValue(), "second old");
                $this->assertEquals($objOneChangelog->getStrNewValue(), "new val 2");
            }
        }

        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objChanges->createLogEntry(new dummyObject($strSystemid), "2", true);
        $objChanges->processCachedInserts();
        $this->assertEquals(4, class_module_system_changelog::getLogEntriesCount($strSystemid));
        $this->assertEquals(4, count(class_module_system_changelog::getLogEntries($strSystemid)));


    }

    public function testChangelogIntervalChanges() {
        $strSystemid = generateSystemid();

        $objStartDate = new class_date();
        $objEndDate = new class_date();
        $objMiddleDate = new class_date();
        $objStartDate->setIntYear(2012)->setIntMonth(10)->setIntDay(1)->setIntHour(10)->setIntMin(0)->setIntSec(0);
        $objMiddleDate->setIntYear(2012)->setIntMonth(11)->setIntDay(1)->setIntHour(10)->setIntMin(0)->setIntSec(0);
        $objEndDate->setIntYear(2012)->setIntMonth(12)->setIntDay(1)->setIntHour(10)->setIntMin(0)->setIntSec(0);

        $objChanges = new class_module_system_changelog();
        $objChanges->createLogEntry(new dummyObject($strSystemid), 1);
        $objChanges->processCachedInserts();

        $strQuery = "INSERT INTO "._dbprefix_."changelog
                     (change_id,
                      change_date,
                      change_systemid,
                      change_system_previd,
                      change_user,
                      change_class,
                      change_action,
                      change_property,
                      change_oldvalue,
                      change_newvalue) VALUES
                     (?,?,?,?,?,?,?,?,?,?)";

        class_carrier::getInstance()->getObjDB()->_pQuery(
            $strQuery,
            array(
                generateSystemid(),
                $objStartDate->getLongTimestamp(),
                $strSystemid,
                "", "", "dummyObject", "edit", "test2", "", "1"
            )
        );

        class_carrier::getInstance()->getObjDB()->_pQuery(
            $strQuery,
            array(
                generateSystemid(),
                $objMiddleDate->getLongTimestamp(),
                $strSystemid,
                "", "", "dummyObject", "edit", "test2", "1", "2"
            )
        );

        class_carrier::getInstance()->getObjDB()->_pQuery(
            $strQuery,
            array(
                generateSystemid(),
                $objEndDate->getLongTimestamp(),
                $strSystemid,
                "", "", "dummyObject", "edit", "test2", "2", "3"
            )
        );

        //start middle  end
        //  1      2     3

        $objStartDate->setIntDay(2);
        $objEndDate->setIntHour(9);

        class_module_system_changelog::changeValueForInterval($strSystemid, "edit", "test2", "", "dummyObject", "", "a", $objStartDate, $objEndDate);

        $objStartDate->setIntDay(1);
        $this->assertEquals("1", $objChanges->getValueForDate($strSystemid, "test2", $objStartDate));
        $objStartDate->setIntDay(2);
        $this->assertEquals("a", $objChanges->getValueForDate($strSystemid, "test2", $objStartDate));
        $this->assertEquals("a", $objChanges->getValueForDate($strSystemid, "test2", $objMiddleDate));
        $objEndDate->setIntHour(8);
        $this->assertEquals("a", $objChanges->getValueForDate($strSystemid, "test2", $objEndDate));
        $objEndDate->setIntHour(9);
        $this->assertEquals("2", $objChanges->getValueForDate($strSystemid, "test2", $objEndDate));
        $objEndDate->setIntHour(11);
        $this->assertEquals("3", $objChanges->getValueForDate($strSystemid, "test2", $objEndDate));
    }


    public function testChangeDetection() {
        $objChangelog = new class_module_system_changelog();

        $objOne = new dummyObject(generateSystemid());
        $objChangelog->readOldValues($objOne);

        $arrChanges = array();
        $this->assertTrue(!$objChangelog->isObjectChanged($objOne, $arrChanges));
        $this->assertTrue(count($arrChanges) == 0);

        $objOne->setStrTest("changed");
        $arrChanges = array();
        $this->assertTrue($objChangelog->isObjectChanged($objOne, $arrChanges));
        $this->assertTrue(count($arrChanges) == 1);
        $this->assertEquals($arrChanges[0]["property"], "strTest");

        $objOne->setStrTest("old");
        $arrChanges = array();
        $this->assertTrue(!$objChangelog->isObjectChanged($objOne, $arrChanges));
        $this->assertTrue(count($arrChanges) == 0);
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
     * @versionable
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
