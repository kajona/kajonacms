<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Exception;
use Kajona\System\System\OrmCondition;
use Kajona\System\System\OrmPropertyInCondition;

class OrmConditionTest extends Testbase
{

    /**
     * Test OrmCondition
     */
    public function testGetStrWhere_Condition()
    {
        $objCondition = new OrmCondition("");
        $this->assertEquals("", $objCondition->getStrWhere());

        $objCondition = new OrmCondition("   ");
        $this->assertEquals("", $objCondition->getStrWhere());

        $objCondition = new OrmCondition("    1=1     ");
        $this->assertEquals("1=1", $objCondition->getStrWhere());

        $objCondition = new OrmCondition("foo");
        $this->assertEquals("foo", $objCondition->getStrWhere());

        $objCondition = new OrmCondition("foo = ?", array(1));
        $this->assertEquals("foo = ?", $objCondition->getStrWhere());

        $objCondition = new OrmCondition(" foo = ? ", array(1));
        $this->assertEquals("foo = ?", $objCondition->getStrWhere());
    }

    /**
     * Test OrmPropertyInCondition
     *
     * @throws \Kajona\System\System\OrmException
     */
    public function testGetStrWhere_PropertyCondition()
    {
        $objCondition = new OrmPropertyInCondition("intRecordStatus", array(1));
        $objCondition->setStrTargetClass("Kajona\\System\\System\\Root");
        $this->assertEquals("system.system_status IN (?)", $objCondition->getStrWhere());

        $objCondition = new OrmPropertyInCondition("intRecordStatus", array());
        $objCondition->setStrTargetClass("Kajona\\System\\System\\Root");
        $this->assertEquals("", $objCondition->getStrWhere());

        $objCondition = new OrmPropertyInCondition("", array(1));
        $objCondition->setStrTargetClass("Kajona\\System\\System\\Root");
    }

    /**
     * Test OrmPropertyInCondition
     *
     * @expectedException Exception
     */
    public function testGetStrWhere_PropertyCondition_Exception()
    {
        $objCondition = new OrmPropertyInCondition("", array(1));
        $objCondition->setStrTargetClass("Kajona\\System\\System\\Root");
        $objCondition->getStrWhere();

    }
}


