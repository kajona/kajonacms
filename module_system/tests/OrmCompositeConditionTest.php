<?php

namespace Kajona\System\Tests;

use Kajona\System\System\OrmCompositeCondition;
use Kajona\System\System\OrmCondition;

class OrmCompositeConditionTest extends Testbase
{

    public function testGetStrWhereOrmComposite()
    {
        $objCompositeCondition = new OrmCompositeCondition();
        $objCompositeCondition->addCondition(new OrmCondition(""));
        $objCompositeCondition->addCondition(new OrmCondition("   "));
        $objCompositeCondition->addCondition(new OrmCondition("         "));
        $this->assertEquals("", $objCompositeCondition->getStrWhere());


        $objCompositeCondition = new OrmCompositeCondition();
        $objCompositeCondition->addCondition(new OrmCondition(""));
        $objCompositeCondition->addCondition(new OrmCondition("   "));
        $objCompositeCondition->addCondition(new OrmCondition("foo = ?", array(1)));
        $this->assertEquals("(foo = ?)", $objCompositeCondition->getStrWhere());

        $objCompositeCondition = new OrmCompositeCondition();
        $objCompositeCondition->addCondition(new OrmCondition(" 1=1 "));
        $objCompositeCondition->addCondition(new OrmCondition("   "));
        $objCompositeCondition->addCondition(new OrmCondition("foo = ?", array(1)));
        $objCompositeCondition->addCondition(new OrmCondition(" bar = ? ", array(1)));
        $this->assertEquals("( (1=1) AND (foo = ?) AND (bar = ?) )", $objCompositeCondition->getStrWhere());

        $objCompositeCondition = new OrmCompositeCondition();
        $objCompositeCondition->setStrConditionConnect(OrmCompositeCondition::STR_CONDITION_OR);
        $objCompositeCondition->addCondition(new OrmCondition(" 1=1 "));
        $objCompositeCondition->addCondition(new OrmCondition("   "));
        $objCompositeCondition->addCondition(new OrmCondition("foo = ?", array(1)));
        $objCompositeCondition->addCondition(new OrmCondition(" bar = ? ", array(1)));
        $this->assertEquals("( (1=1) OR (foo = ?) OR (bar = ?) )", $objCompositeCondition->getStrWhere());
    }

    public function testGetStrWhereTwoOrmComposite_Empty_1()
    {
        $objCompositeConditionOuter = new OrmCompositeCondition();
        $objCompositeConditionInner1 = new OrmCompositeCondition();
        $objCompositeConditionInner2 = new OrmCompositeCondition();

        $objCompositeConditionOuter->addCondition($objCompositeConditionInner1);
        $objCompositeConditionOuter->addCondition($objCompositeConditionInner2);
        $this->assertEquals("", $objCompositeConditionOuter->getStrWhere());
    }

    public function testGetStrWhereTwoOrmComposite_Empty_2()
    {

        $objCompositeConditionOuter = new OrmCompositeCondition();

        $objCompositeConditionInner1 = new OrmCompositeCondition();
        $objCompositeConditionInner1->addCondition(new OrmCondition("", array(1)));

        $objCompositeConditionInner2 = new OrmCompositeCondition();
        $objCompositeConditionInner2->addCondition(new OrmCondition("    ", array(1)));

        $objCompositeConditionOuter->addCondition($objCompositeConditionInner1);
        $objCompositeConditionOuter->addCondition($objCompositeConditionInner2);

        $this->assertEquals("", $objCompositeConditionOuter->getStrWhere());
    }

    public function testGetStrWhereTwoOrmComposite_2()
    {
        $objCompositeConditionOuter = new OrmCompositeCondition();

        $objCompositeConditionInner1 = new OrmCompositeCondition();
        $objCompositeConditionInner1->addCondition(new OrmCondition("", array(1)));

        $objCompositeConditionInner2 = new OrmCompositeCondition();
        $objCompositeConditionInner2->addCondition(new OrmCondition("foo = ?", array(1)));

        $objCompositeConditionOuter->addCondition($objCompositeConditionInner1);
        $objCompositeConditionOuter->addCondition($objCompositeConditionInner2);

        $this->assertEquals("((foo = ?))", $objCompositeConditionOuter->getStrWhere());
    }

    public function testGetStrWhereTwoOrmComposite_3()
    {
        $objCompositeConditionOuter = new OrmCompositeCondition();

        $objCompositeConditionInner1 = new OrmCompositeCondition();
        $objCompositeConditionInner1->addCondition(new OrmCondition("foo = ?", array(1)));
        $this->assertEquals("(foo = ?)", $objCompositeConditionInner1->getStrWhere());

        $objCompositeConditionInner2 = new OrmCompositeCondition();
        $objCompositeConditionInner2->addCondition(new OrmCondition("bar = ?", array(1)));
        $this->assertEquals("(bar = ?)", $objCompositeConditionInner2->getStrWhere());

        $objCompositeConditionOuter->addCondition($objCompositeConditionInner1);
        $objCompositeConditionOuter->addCondition($objCompositeConditionInner2);
        $this->assertEquals("( ((foo = ?)) AND ((bar = ?)) )", $objCompositeConditionOuter->getStrWhere());
    }

    public function testGetStrWhereTwoOrmComposite_4()
    {
        $objCompositeConditionOuter = new OrmCompositeCondition();

        $objCompositeConditionInner1 = new OrmCompositeCondition();
        $objCompositeConditionInner1->addCondition(new OrmCondition("foo = ?", array(1)));
        $objCompositeConditionInner1->addCondition(new OrmCondition("bar = ?", array(1)));
        $this->assertEquals("( (foo = ?) AND (bar = ?) )", $objCompositeConditionInner1->getStrWhere());

        $objCompositeConditionInner2 = new OrmCompositeCondition();
        $this->assertEquals("", $objCompositeConditionInner2->getStrWhere());

        $objCompositeConditionOuter->addCondition($objCompositeConditionInner1);
        $objCompositeConditionOuter->addCondition($objCompositeConditionInner2);

        $this->assertEquals("(( (foo = ?) AND (bar = ?) ))", $objCompositeConditionOuter->getStrWhere());
    }

    public function testGetStrWhereTwoOrmComposite_5()
    {
        $objCompositeConditionOuter = new OrmCompositeCondition();

        $objCompositeConditionInner1 = new OrmCompositeCondition();
        $objCompositeConditionInner1->addCondition(new OrmCondition("foo = ?", array(1)));
        $objCompositeConditionInner1->addCondition(new OrmCondition("bar = ?", array(1)));
        $this->assertEquals("( (foo = ?) AND (bar = ?) )", $objCompositeConditionInner1->getStrWhere());

        $objCompositeConditionInner2 = new OrmCompositeCondition();
        $objCompositeConditionInner2->addCondition(new OrmCondition("one = ?", array(1)));
        $this->assertEquals("(one = ?)", $objCompositeConditionInner2->getStrWhere());

        $objCompositeConditionOuter->addCondition($objCompositeConditionInner1);
        $objCompositeConditionOuter->addCondition($objCompositeConditionInner2);

        $this->assertEquals("( (( (foo = ?) AND (bar = ?) )) AND ((one = ?)) )", $objCompositeConditionOuter->getStrWhere());
    }


}


