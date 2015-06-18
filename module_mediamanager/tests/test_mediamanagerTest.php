<?php
require_once (__DIR__ . "/../../module_system/system/class_testbase.php");

class class_mediamanagerTest extends class_testbase  {


    public function testFileSync() {

        echo "test mediamanager...\n";

        if(class_module_system_module::getModuleByName("samplecontent") == null || !is_file(_realpath_."/files/images/samples/IMG_3000.JPG"))
            return;

        $objFilesystem = new class_filesystem();
        $objFilesystem->folderCreate(_filespath_."/images/autotest");

        $objFilesystem->fileCopy(_filespath_."/images/samples/IMG_3000.JPG", _filespath_."/images/autotest/IMG_3000.jpg");
        $objFilesystem->fileCopy(_filespath_."/images/samples/IMG_3000.JPG", _filespath_."/images/autotest/IMG_3000.png");
        $objFilesystem->fileCopy(_filespath_."/images/samples/IMG_3000.JPG", _filespath_."/images/autotest/PA021805.JPG");
        $objFilesystem->fileCopy(_filespath_."/images/samples/IMG_3000.JPG", _filespath_."/images/autotest/test.txt");

        $objRepo = new class_module_mediamanager_repo();
        $objRepo->setStrPath(_filespath_."/images/autotest");
        $objRepo->setStrTitle("autotest repo");
        $objRepo->setStrViewFilter(".jpg,.png");
        $objRepo->updateObjectToDb();
        $objRepo->syncRepo();

        $arrFiles = class_module_mediamanager_file::loadFilesDB($objRepo->getSystemid());

        $this->assertEquals(3, count($arrFiles));
        foreach($arrFiles as $objOneFile)
            $objOneFile->deleteObjectFromDatabase();


        $objRepo->deleteObjectFromDatabase();

        $arrFiles = $objFilesystem->getFilelist(_filespath_."/images/autotest");

        $this->assertEquals(1, count($arrFiles));
        $this->assertEquals("test.txt", array_values($arrFiles)[0]);

    }



}

