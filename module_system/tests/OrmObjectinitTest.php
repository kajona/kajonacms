<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\Date;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmAssignmentArray;
use Kajona\System\System\OrmBase;
use Kajona\System\System\OrmDeletedhandlingEnum;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\SystemModule;

/**
 * Class class_test_orm_schemamanagerTest
 *
 */
class OrmObjectinitTest extends Testbase
{



    protected function setUp()
    {
        $objSchema = new OrmSchemamanager();
        $objSchema->createTable(OrmObjectinitTestclass::class);
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $objDb = Carrier::getInstance()->getObjDB();
        $objDb->_pQuery("DROP TABLE "._dbprefix_."inittestclass", array());
        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBTABLES);
    }


    public function testObjectInit()
    {
        $objObject = new OrmObjectinitTestclass();

        $objObject->setStrChar20("char20");
        $objObject->setStrChar254("char254");
        $objObject->setStrText("text");
        $objObject->setStrLongtext("longtext");
        $objObject->setIntInteger(12345);
        $objObject->setIntBigint(20161223120000);
        $objObject->setFloatDouble(123.45);
        $objObject->setBitBoolean(false);

        $longDate = 20160827011525;
        $objObject->setObjDate(new Date($longDate));
        $objObject->updateObjectToDb();

        Objectfactory::getInstance()->flushCache();

        /** @var OrmObjectinitTestclass $objObj */
        $objObj = Objectfactory::getInstance()->getObject($objObject->getSystemid());

        $this->assertTrue($objObj->getObjDate() instanceof Date);
        $this->assertTrue($objObj->getObjDate()->isSameDay(new Date($longDate)));

        $this->assertSame("char20", $objObj->getStrChar20());
        $this->assertSame("char254", $objObj->getStrChar254());
        $this->assertSame("text", $objObj->getStrText());
        $this->assertSame("longtext", $objObj->getStrLongtext());
        $this->assertSame(12345, $objObj->getIntInteger());
        $this->assertSame(20161223120000, $objObj->getIntBigint());
        $this->assertSame(123.45, $objObj->getFloatDouble());
        $this->assertEquals(false, $objObj->getBitBoolean());

        $objObj->setBitBoolean(true);
        $objObj->updateObjectToDb();

        $objObj = Objectfactory::getInstance()->getObject($objObject->getSystemid());
        $this->assertEquals(true, $objObj->getBitBoolean());
    }


    public function testObjectInitNull()
    {
        $objObject = new OrmObjectinitTestclass();
        $objObject->updateObjectToDb();
        Objectfactory::getInstance()->flushCache();

        /** @var OrmObjectinitTestclass $objObj */
        $objObj = Objectfactory::getInstance()->getObject($objObject->getSystemid());

        $this->assertNull($objObj->getStrChar20());
        $this->assertNull($objObj->getStrChar254());
        $this->assertNull($objObj->getStrText());
        $this->assertNull($objObj->getStrLongtext());
        $this->assertNull($objObj->getIntInteger());
        $this->assertNull($objObj->getIntBigint());
        $this->assertNull($objObj->getFloatDouble());
        $this->assertNull($objObj->getObjDate());
        $this->assertNull($objObj->getBitBoolean());
    }



}



/**
 * Class orm_schematest_testclass
 *
 * @targetTable inittestclass.testclass_id
 * @module system
 * @moduleId _system_modul_id_
 */
class OrmObjectinitTestclass extends Model implements ModelInterface
{

    /**
     * @var Date
     * @tableColumn inittestclass.col1
     * @tableColumnDatatype long
     */
    private $objDate = null;

    /**
     * @var string
     * @tableColumn inittestclass.col2
     * @tableColumnDatatype char20
     */
    private $strChar20 = null;

    /**
     * @var string
     * @tableColumn inittestclass.col3
     * @tableColumnDatatype char254
     */
    private $strChar254 = null;

    /**
     * @var string
     * @tableColumn inittestclass.col4
     * @tableColumnDatatype text
     */
    private $strText = null;

    /**
     * @var string
     * @tableColumn inittestclass.col5
     * @tableColumnDatatype longtext
     */
    private $strLongtext = null;

    /**
     * @var int
     * @tableColumn inittestclass.col6
     * @tableColumnDatatype int
     */
    private $intInteger = null;

    /**
     * @var bool
     * @tableColumn inittestclass.col7
     * @tableColumnDatatype int
     */
    private $bitBoolean = null;

    /**
     * @var int
     * @tableColumn inittestclass.col8
     * @tableColumnDatatype long
     */
    private $intBigint = null;

    /**
     * @var float
     * @tableColumn inittestclass.col9
     * @tableColumnDatatype double
     */
    private $floatDouble = null;

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return "testing";
    }

    /**
     * @return Date
     */
    public function getObjDate()
    {
        return $this->objDate;
    }

    /**
     * @param Date $objDate
     */
    public function setObjDate($objDate)
    {
        $this->objDate = $objDate;
    }

    /**
     * @return string
     */
    public function getStrChar20()
    {
        return $this->strChar20;
    }

    /**
     * @param string $strChar20
     */
    public function setStrChar20($strChar20)
    {
        $this->strChar20 = $strChar20;
    }

    /**
     * @return string
     */
    public function getStrChar254()
    {
        return $this->strChar254;
    }

    /**
     * @param string $strChar254
     */
    public function setStrChar254($strChar254)
    {
        $this->strChar254 = $strChar254;
    }

    /**
     * @return string
     */
    public function getStrText()
    {
        return $this->strText;
    }

    /**
     * @param string $strText
     */
    public function setStrText($strText)
    {
        $this->strText = $strText;
    }

    /**
     * @return string
     */
    public function getStrLongtext()
    {
        return $this->strLongtext;
    }

    /**
     * @param string $strLongtext
     */
    public function setStrLongtext($strLongtext)
    {
        $this->strLongtext = $strLongtext;
    }

    /**
     * @return int
     */
    public function getIntInteger()
    {
        return $this->intInteger;
    }

    /**
     * @param int $intInteger
     */
    public function setIntInteger($intInteger)
    {
        $this->intInteger = $intInteger;
    }

    /**
     * @return int
     */
    public function getIntBigint()
    {
        return $this->intBigint;
    }

    /**
     * @param int $intBigint
     */
    public function setIntBigint($intBigint)
    {
        $this->intBigint = $intBigint;
    }

    /**
     * @return float
     */
    public function getFloatDouble()
    {
        return $this->floatDouble;
    }

    /**
     * @param float $floatDouble
     */
    public function setFloatDouble($floatDouble)
    {
        $this->floatDouble = $floatDouble;
    }

    /**
     * @return bool
     */
    public function getBitBoolean()
    {
        return $this->bitBoolean;
    }

    /**
     * @param bool $bitBoolean
     */
    public function setBitBoolean($bitBoolean)
    {
        $this->bitBoolean = $bitBoolean;
    }

}


