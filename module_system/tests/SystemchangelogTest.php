<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\Date;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\VersionableInterface;

/**
 * Tests a few aspects of the changelog-component
 *
 * @author sidler@mulchprod.de
 * @package module_system
 */
class SystemchangelogTest extends Testbase
{

    protected function setUp()
    {
        parent::setUp();
        SystemChangelog::$bitChangelogEnabled = true;
    }

    protected function tearDown()
    {
        parent::tearDown();
        SystemChangelog::$bitChangelogEnabled = null;
    }


    public function testChangelogArrayHandling()
    {

        $arrOld = array(1, 2, 3, 4, 5);
        $arrNew = array(3, 4, 5, 6, 7);

        $arrChanges = array(
            array("property" => "testArray", "oldvalue" => $arrOld, "newvalue" => $arrNew)
        );

        $strSystemid = generateSystemid();
        $objDummy = new DummyObject($strSystemid);

        $objChanges = new SystemChangelog();

        $this->assertEquals(0, SystemChangelog::getLogEntriesCount($strSystemid));
        $objChanges->processChanges($objDummy, "arrayTest", $arrChanges);
        $objChanges->processCachedInserts();
        $this->flushDBCache();
        $this->assertEquals(SystemChangelog::getLogEntriesCount($strSystemid), 4);

        $arrChanges = SystemChangelog::getSpecificEntries($strSystemid, "arrayTest", "testArray");
        $this->assertEquals(4, count($arrChanges));

        foreach ($arrChanges as $objOneChangeSet) {
            if ($objOneChangeSet->getStrOldValue() != "") {
                $this->assertTrue(in_array($objOneChangeSet->getStrOldValue(), array(1, 2)));
            }

            if ($objOneChangeSet->getStrNewValue() != "") {
                $this->assertTrue(in_array($objOneChangeSet->getStrNewValue(), array(6, 7)));
            }
        }
    }

    public function testChangelog()
    {

        $this->flushDBCache();

        $objChanges = new SystemChangelog();

        $strSystemid = generateSystemid();
        $objChanges->readOldValues(new DummyObject($strSystemid));
        $arrOldValues = $objChanges->getOldValuesForSystemid($strSystemid);

        $this->assertEquals($arrOldValues["strTest"], "old");
        $this->assertEquals($arrOldValues["strSecondTest"], "second old");

        $arrOldValues = $objChanges->getOldValuesForSystemid($strSystemid);
        $this->assertEquals($arrOldValues["strTest"], "old");
        $this->assertEquals($arrOldValues["strSecondTest"], "second old");


        $objChanges->createLogEntry(new DummyObject($strSystemid), "1");
        $objChanges->processCachedInserts();
        $this->assertEquals(0, SystemChangelog::getLogEntriesCount($strSystemid));

        Carrier::getInstance()->getObjDB()->flushQueryCache();

        $objDummy = new DummyObject($strSystemid);
        $objDummy->setStrTest("new test 1");
        $objDummy->setStrSecondTest("new val 2");

        $objChanges->createLogEntry($objDummy, "2");
        $objChanges->processCachedInserts();
        $this->assertEquals(2, SystemChangelog::getLogEntriesCount($strSystemid));
        $this->assertEquals(2, count(SystemChangelog::getLogEntries($strSystemid)));

        $arrLogs = SystemChangelog::getLogEntries($strSystemid);
        foreach ($arrLogs as $objOneChangelog) {
            if ($objOneChangelog->getStrProperty() == "strTest") {
                $this->assertEquals($objOneChangelog->getStrOldValue(), "old");
                $this->assertEquals($objOneChangelog->getStrNewValue(), "new test 1");
            }

            if ($objOneChangelog->getStrProperty() == "strSecondTest") {
                $this->assertEquals($objOneChangelog->getStrOldValue(), "second old");
                $this->assertEquals($objOneChangelog->getStrNewValue(), "new val 2");
            }
        }

        Carrier::getInstance()->getObjDB()->flushQueryCache();

        $objChanges->createLogEntry(new DummyObject($strSystemid), "2", true);
        $objChanges->processCachedInserts();
        $this->assertEquals(4, SystemChangelog::getLogEntriesCount($strSystemid));
        $this->assertEquals(4, count(SystemChangelog::getLogEntries($strSystemid)));


    }

    public function testChangelogIntervalChanges()
    {
        $strSystemid = generateSystemid();

        $objStartDate = new Date();
        $objEndDate = new Date();
        $objMiddleDate = new Date();
        $objStartDate->setIntYear(2012)->setIntMonth(10)->setIntDay(1)->setIntHour(10)->setIntMin(0)->setIntSec(0);
        $objMiddleDate->setIntYear(2012)->setIntMonth(11)->setIntDay(1)->setIntHour(10)->setIntMin(0)->setIntSec(0);
        $objEndDate->setIntYear(2012)->setIntMonth(12)->setIntDay(1)->setIntHour(10)->setIntMin(0)->setIntSec(0);

        $objChanges = new SystemChangelog();
        $objChanges->createLogEntry(new DummyObject($strSystemid), 1);
        $objChanges->processCachedInserts();

        $strQuery = "INSERT INTO " . _dbprefix_ . "changelog
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

        Carrier::getInstance()->getObjDB()->_pQuery(
            $strQuery,
            array(
                generateSystemid(),
                $objStartDate->getLongTimestamp(),
                $strSystemid,
                "", "", "DummyObject", "edit", "test2", "", "1"
            )
        );

        Carrier::getInstance()->getObjDB()->_pQuery(
            $strQuery,
            array(
                generateSystemid(),
                $objMiddleDate->getLongTimestamp(),
                $strSystemid,
                "", "", "DummyObject", "edit", "test2", "1", "2"
            )
        );

        Carrier::getInstance()->getObjDB()->_pQuery(
            $strQuery,
            array(
                generateSystemid(),
                $objEndDate->getLongTimestamp(),
                $strSystemid,
                "", "", "DummyObject", "edit", "test2", "2", "3"
            )
        );

        //start middle  end
        //  1      2     3

        $objStartDate->setIntDay(2);
        $objEndDate->setIntHour(9);

        SystemChangelog::changeValueForInterval($strSystemid, "edit", "test2", "", "DummyObject", "", "a", $objStartDate, $objEndDate);

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


    public function testChangeDetection()
    {
        $objChangelog = new SystemChangelog();

        $objOne = new DummyObject(generateSystemid());
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


    public function testPerformance()
    {

        $objChanges = new SystemChangelog();
        $objChanges->processCachedInserts();
        $intFired = (Carrier::getInstance()->getObjDB()->getNumber() - Carrier::getInstance()->getObjDB()->getNumberCache());

        for ($intI = 0; $intI < 100; $intI++) {
            $objChanges->createLogEntry(new DummyObject(generateSystemid()), "1");
            $objChanges->processCachedInserts();
        }
        $intFiredAfter = (Carrier::getInstance()->getObjDB()->getNumber() - Carrier::getInstance()->getObjDB()->getNumberCache());

        $this->assertTrue(($intFiredAfter - $intFired) >= 100);

//        echo "Queries: " . ($intFiredAfter - $intFired) . "\n";


        $intFired = (Carrier::getInstance()->getObjDB()->getNumber() - Carrier::getInstance()->getObjDB()->getNumberCache());

        $objChanges = new SystemChangelog();
        for ($intI = 0; $intI < 100; $intI++) {
            $objChanges->createLogEntry(new DummyObject(generateSystemid()), "1");
        }
        $objChanges->processCachedInserts();
        $intFiredAfter = (Carrier::getInstance()->getObjDB()->getNumber() - Carrier::getInstance()->getObjDB()->getNumberCache());

        $this->assertTrue(($intFiredAfter - $intFired) < 10);

//        echo "Queries: " . ($intFiredAfter - $intFired) . "\n";


    }

    public function testArrayHandling()
    {
        $objChangelog = new SystemChangelog();

        $objOne = new DummyObject2(generateSystemid());
        $objChangelog->readOldValues($objOne);

        $arrChanges = array();
        $this->assertTrue(!$objChangelog->isObjectChanged($objOne, $arrChanges));
        $this->assertTrue(count($arrChanges) == 0);

        $objOne->setArrValues(array("a", "c", "d"));
        $arrChanges = array();
        $this->assertTrue($objChangelog->isObjectChanged($objOne, $arrChanges));
        $this->assertTrue(count($arrChanges) == 1);
        $this->assertEquals($arrChanges[0]["property"], "arrValues");
        $this->assertEquals($arrChanges[0]["oldvalue"], "b,c,d");
        $this->assertEquals($arrChanges[0]["newvalue"], "a,c,d");

        $objChangelog->readOldValues($objOne);
        $objOne->setArrValues(array("a", "d", "c"));
        $arrChanges = array();
        $this->assertTrue(!$objChangelog->isObjectChanged($objOne, $arrChanges));
        $this->assertTrue(count($arrChanges) == 0);

    }
}


class DummyObject implements VersionableInterface
{

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

    function __construct($strSystemid)
    {
        $this->strSystemid = $strSystemid;
    }

    public function getSystemid()
    {
        return $this->strSystemid;
    }

    public function getPrevid()
    {
        return "";
    }

    public function setStrSystemid($strSystemid)
    {
        $this->strSystemid = $strSystemid;
    }

    public function renderVersionValue($strProperty, $strValue)
    {
        return $strValue;
    }

    public function getVersionActionName($strAction)
    {
        return "dummy";
    }

    public function getArrModule($strKey)
    {
        return "dummy";
    }

    public function getVersionPropertyName($strProperty)
    {
        return $strProperty;
    }

    public function getVersionRecordName()
    {
        return "dummy";
    }

    public function setStrSecondTest($strSecondTest)
    {
        $this->strSecondTest = $strSecondTest;
    }

    public function getStrSecondTest()
    {
        return $this->strSecondTest;
    }

    public function setStrTest($strTest)
    {
        $this->strTest = $strTest;
    }

    public function getStrTest()
    {
        return $this->strTest;
    }

}


class DummyObject2 implements VersionableInterface
{

    /**
     * @var
     * @versionable
     */
    private $arrValues = array("b", "c", "d");

    private $strSystemid;

    function __construct($strSystemid)
    {
        $this->strSystemid = $strSystemid;
    }

    public function getSystemid()
    {
        return $this->strSystemid;
    }

    public function getPrevid()
    {
        return "";
    }

    public function setStrSystemid($strSystemid)
    {
        $this->strSystemid = $strSystemid;
    }

    public function renderVersionValue($strProperty, $strValue)
    {
        return $strValue;
    }

    public function getVersionActionName($strAction)
    {
        return "dummy";
    }

    public function getArrModule($strKey)
    {
        return "dummy";
    }

    public function getVersionPropertyName($strProperty)
    {
        return $strProperty;
    }

    public function getVersionRecordName()
    {
        return "dummy";
    }


    public function getArrValues()
    {
        return $this->arrValues;
    }

    public function setArrValues($arrValues)
    {
        $this->arrValues = $arrValues;
    }


}