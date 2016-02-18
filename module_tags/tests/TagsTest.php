<?php

namespace Kajona\Tags\Tests;

use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\Testbase;
use Kajona\Tags\System\TagsTag;

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class TagsTest extends Testbase {

    public function testCopyRecordWithTag() {

        $objAspect = new SystemAspect();
        $objAspect->setStrName("autotest");
        $objAspect->updateObjectToDb();

        $objTag = new TagsTag();
        $objTag->setStrName("demo tag");
        $objTag->updateObjectToDb();

        $objTag->assignToSystemrecord($objAspect->getStrSystemid());

        $objFirstAspect = new SystemAspect($objAspect->getSystemid());

        $objAspect->copyObject();

        $this->assertNotEquals($objFirstAspect->getSystemid(), $objAspect->getSystemid());

        $this->assertEquals(count(TagsTag::getTagsForSystemid($objFirstAspect->getSystemid())), count(TagsTag::getTagsForSystemid($objAspect->getSystemid())));

        $arrTagsFirst = TagsTag::getTagsForSystemid($objFirstAspect->getSystemid());
        $objFirstTag = $arrTagsFirst[0];
        $arrTagsCopy = TagsTag::getTagsForSystemid($objAspect->getSystemid());
        $objSecondTag = $arrTagsCopy[0];

        $this->assertEquals($objFirstTag->getSystemid(), $objSecondTag->getSystemid());

        $objFirstAspect->deleteObjectFromDatabase();
        $objAspect->deleteObjectFromDatabase();
        $objSecondTag->deleteObjectFromDatabase();

    }



    public function testTagAssignmentRemoval() {
        //related to checkin #6111

        $objTag = new TagsTag();
        $objTag->setStrName(generateSystemid());
        $objTag->updateObjectToDb();

        $objAspect = new SystemAspect();
        $objAspect->setStrName(generateSystemid());
        $objAspect->updateObjectToDb();

        $objTag->assignToSystemrecord($objAspect->getSystemid());

        $this->flushDBCache();

        $this->assertEquals(count($objTag->getArrAssignedRecords()), 1);
        $this->assertEquals(count(TagsTag::getTagsForSystemid($objAspect->getSystemid())), 1);

        $objTag->removeFromSystemrecord($objAspect->getSystemid(), "");

        $this->flushDBCache();

        $this->assertEquals(count($objTag->getArrAssignedRecords()), 0);
        $this->assertEquals(count(TagsTag::getTagsForSystemid($objAspect->getSystemid())), 0);

        $objTag->deleteObjectFromDatabase();
        $objAspect->deleteObjectFromDatabase();
    }


    public function testTagAssignment() {

        if(SystemModule::getModuleByName("pages") === null)
            return true;

        $strName = generateSystemid();
        $arrPages = PagesPage::getAllPages();

        if(count($arrPages) == 0)
            return;

        $objTag = new TagsTag();
        $objTag->setStrName($strName);
        $objTag->updateObjectToDb();


        foreach($arrPages as $objOnePage) {
            $objTag->assignToSystemrecord($objOnePage->getSystemid());
            break;
        }

        $arrFolder = PagesFolder::getFolderList();
        foreach($arrFolder as $objOneFolder) {
            $objTag->assignToSystemrecord($objOneFolder->getSystemid());
            break;
        }


        $this->flushDBCache();

        $objTag = TagsTag::getTagByName($strName);
        $this->assertEquals($objTag->getIntAssignments(), 2);

        $arrPlainAssignments = $objTag->getListOfAssignments();
        $this->assertEquals(count($arrPlainAssignments), 2);

        $arrAssignment = $objTag->getArrAssignedRecords();
        $this->assertEquals(count($arrAssignment), 2);

        $this->assertTrue($arrAssignment[0] instanceof PagesPage || $arrAssignment[0] instanceof PagesFolder);
        $this->assertTrue($arrAssignment[1] instanceof PagesPage || $arrAssignment[1] instanceof PagesFolder);


        $strOldSysid = $objTag->getSystemid();
        $objTag->copyObject();

        $this->assertNotEquals($strOldSysid, $objTag->getSystemid());
        $this->assertEquals($objTag->getStrName(), $strName."_1");

        $this->assertEquals($objTag->getIntAssignments(), 2);
        $arrAssignment = $objTag->getArrAssignedRecords();

        $this->assertEquals(count($arrAssignment), 2);

        $this->assertTrue($arrAssignment[0] instanceof PagesPage || $arrAssignment[0] instanceof PagesFolder);
        $this->assertTrue($arrAssignment[1] instanceof PagesPage || $arrAssignment[1] instanceof PagesFolder);

        $objTag->deleteObjectFromDatabase();
    }


}

