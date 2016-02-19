<?php

namespace Kajona\Search\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\Search\Event\SearchObjectdeletedlistener;
use Kajona\Search\Event\SearchRecordupdatedlistener;
use Kajona\Search\System\SearchCommons;
use Kajona\Search\System\SearchSearch;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\Testbase;
use Kajona\Tags\System\TagsTag;

class SearchIndexEventTest extends Testbase
{

    protected function setUp()
    {
        parent::setUp();
        SearchObjectdeletedlistener::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST = false;
        SearchRecordupdatedlistener::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST = false;
    }

    protected function tearDown()
    {
        parent::tearDown();
        SearchObjectdeletedlistener::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST = true;
        SearchRecordupdatedlistener::$BIT_UPDATE_INDEX_ON_END_OF_REQUEST = true;
    }


    public function testIndexEvent()
    {

        if (SystemModule::getModuleByName("tags") == null || SystemModule::getModuleByName("system") == null) {
            return;
        }


        $strSearchKey1 = generateSystemid();

        $objAspect = new SystemAspect();
        $objAspect->setStrName($strSearchKey1);
        $objAspect->updateObjectToDb();

        $objSearchCommons = new SearchCommons();

        $objSearchParams = new SearchSearch();
        $objSearchParams->setStrQuery($strSearchKey1);
        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]->getObjObject()->getStrSystemid(), $objAspect->getStrSystemid());


        $strSearchKey2 = generateSystemid();
        $objTag = new TagsTag();
        $objTag->setStrName($strSearchKey2);
        $objTag->updateObjectToDb();


        $objSearchParams = new SearchSearch();
        $objSearchParams->setStrQuery($strSearchKey2);
        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]->getObjObject()->getStrSystemid(), $objTag->getStrSystemid());


        $objTag->assignToSystemrecord($objAspect->getStrSystemid());

        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 2);

        $objSearchParams->setStrInternalFilterModules(_system_modul_id_);
        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]->getObjObject()->getStrSystemid(), $objAspect->getStrSystemid());


        $objTag->removeFromSystemrecord($objAspect->getStrSystemid());

        //the aspect itself should not be found any more
        $objSearchParams = new SearchSearch();
        $objSearchParams->setStrQuery($strSearchKey2);
        $arrResult = $objSearchCommons->doIndexedSearch($objSearchParams, null);
        $this->assertEquals(count($arrResult), 1);
        $this->assertEquals($arrResult[0]->getObjObject()->getStrSystemid(), $objTag->getStrSystemid());


        $objAspect->deleteObjectFromDatabase();
        $objTag->deleteObjectFromDatabase();

    }

}

