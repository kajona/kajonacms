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
        $longDate = 20160827011525;
        $objObject = new OrmObjectinitTestclass();
        $objObject->setObjDate(new Date($longDate));
        $objObject->updateObjectToDb();

        Objectfactory::getInstance()->flushCache();

        /** @var OrmObjectinitTestclass $objObj */
        $objObj = Objectfactory::getInstance()->getObject($objObject->getSystemid());

        $this->assertTrue($objObj->objDate instanceof Date);
        $this->assertTrue($objObj->objDate->isSameDay(new Date($longDate)));
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
     */
    public $objDate = null;

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





}


