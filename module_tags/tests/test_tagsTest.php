<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_tags extends class_testbase  {

    public function testCopyRecordWithTag() {

        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("autotest");
        $objAspect->updateObjectToDb();

        $objTag = new class_module_tags_tag();
        $objTag->setStrName("demo tag");
        $objTag->updateObjectToDb();

        $objTag->assignToSystemrecord($objAspect->getStrSystemid());

        $objFirstAspect = new class_module_system_aspect($objAspect->getSystemid());

        $objAspect->copyObject();

        $this->assertNotEquals($objFirstAspect->getSystemid(), $objAspect->getSystemid());

        $this->assertEquals(count(class_module_tags_tag::getTagsForSystemid($objFirstAspect->getSystemid())), count(class_module_tags_tag::getTagsForSystemid($objAspect->getSystemid())));

        $arrTagsFirst = class_module_tags_tag::getTagsForSystemid($objFirstAspect->getSystemid());
        $objFirstTag = $arrTagsFirst[0];
        $arrTagsCopy = class_module_tags_tag::getTagsForSystemid($objAspect->getSystemid());
        $objSecondTag = $arrTagsCopy[0];

        $this->assertEquals($objFirstTag->getSystemid(), $objSecondTag->getSystemid());

        $objFirstAspect->deleteObjectFromDatabase();
        $objAspect->deleteObjectFromDatabase();
        $objSecondTag->deleteObjectFromDatabase();

    }



    public function testTagAssignmentRemoval() {
        //related to checkin #6111

        $objTag = new class_module_tags_tag();
        $objTag->setStrName(generateSystemid());
        $objTag->updateObjectToDb();

        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName(generateSystemid());
        $objAspect->updateObjectToDb();

        $objTag->assignToSystemrecord($objAspect->getSystemid());

        $this->flushDBCache();

        $this->assertEquals(count($objTag->getArrAssignedRecords()), 1);
        $this->assertEquals(count(class_module_tags_tag::getTagsForSystemid($objAspect->getSystemid())), 1);

        $objTag->removeFromSystemrecord($objAspect->getSystemid(), "");

        $this->flushDBCache();

        $this->assertEquals(count($objTag->getArrAssignedRecords()), 0);
        $this->assertEquals(count(class_module_tags_tag::getTagsForSystemid($objAspect->getSystemid())), 0);

        $objTag->deleteObjectFromDatabase();
        $objAspect->deleteObjectFromDatabase();
    }


    public function testTagAssignment() {

        if(class_module_system_module::getModuleByName("pages") === null)
            return true;

        $strName = generateSystemid();
        $arrPages = class_module_pages_page::getAllPages();

        if(count($arrPages) == 0)
            return;

        $objTag = new class_module_tags_tag();
        $objTag->setStrName($strName);
        $objTag->updateObjectToDb();


        foreach($arrPages as $objOnePage) {
            $objTag->assignToSystemrecord($objOnePage->getSystemid());
            break;
        }

        $arrFolder = class_module_pages_folder::getFolderList();
        foreach($arrFolder as $objOneFolder) {
            $objTag->assignToSystemrecord($objOneFolder->getSystemid());
            break;
        }


        $this->flushDBCache();

        $objTag = class_module_tags_tag::getTagByName($strName);
        $this->assertEquals($objTag->getIntAssignments(), 2);

        $arrPlainAssignments = $objTag->getListOfAssignments();
        $this->assertEquals(count($arrPlainAssignments), 2);

        $arrAssignment = $objTag->getArrAssignedRecords();
        $this->assertEquals(count($arrAssignment), 2);

        $this->assertTrue($arrAssignment[0] instanceof class_module_pages_page || $arrAssignment[0] instanceof class_module_pages_folder);
        $this->assertTrue($arrAssignment[1] instanceof class_module_pages_page || $arrAssignment[1] instanceof class_module_pages_folder);


        $strOldSysid = $objTag->getSystemid();
        $objTag->copyObject();

        $this->assertNotEquals($strOldSysid, $objTag->getSystemid());
        $this->assertEquals($objTag->getStrName(), $strName."_1");

        $this->assertEquals($objTag->getIntAssignments(), 2);
        $arrAssignment = $objTag->getArrAssignedRecords();

        $this->assertEquals(count($arrAssignment), 2);

        $this->assertTrue($arrAssignment[0] instanceof class_module_pages_page || $arrAssignment[0] instanceof class_module_pages_folder);
        $this->assertTrue($arrAssignment[1] instanceof class_module_pages_page || $arrAssignment[1] instanceof class_module_pages_folder);

        $objTag->deleteObjectFromDatabase();
    }


}

