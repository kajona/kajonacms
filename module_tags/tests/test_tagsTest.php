<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_tags extends class_testbase  {

    public function testTagAssignment() {

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
    }


}

