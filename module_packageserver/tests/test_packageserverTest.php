<?php
require_once (__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_packageserver extends class_testbase  {


    public function testJsonList() {

        $objFilesystem = new class_filesystem();

        $objFilesystem->folderCreate("/files/packageservertest");
        $objFilesystem->folderCreate("/files/packageservertest/t");

        file_put_contents(_realpath_."/files/packageservertest/t/metadata.xml", $this->getStrMetadata());
        $objFilesystem->folderCreate("/files/packageservertest/t/system");
        file_put_contents(_realpath_."/files/packageservertest/t/system/test.txt", $this->getStrMetadata());

        $objZip = new class_zip();
        $objZip->openArchiveForWriting("/files/packageservertest/autotest.zip");
        $objZip->addFile("/files/packageservertest/t/metadata.xml", "/metadata.xml");
        $objZip->addFile("/files/packageservertest/t/system/test.txt", "/system/test.txt");
        $objZip->closeArchive();

        $objFilesystem->folderDeleteRecursive("/files/packageservertest/t");

        $objMediamanagerRepo = new class_module_mediamanager_repo();
        $objMediamanagerRepo->setStrPath("/files/packageservertest");
        $objMediamanagerRepo->setStrTitle("autotest packages");
        $objMediamanagerRepo->updateObjectToDb();

        class_module_mediamanager_file::syncRecursive($objMediamanagerRepo->getSystemid(), $objMediamanagerRepo->getStrPath());

        /** @var $objPortalServer class_module_packageserver_portal */
        $objPortalServer = class_module_system_module::getModuleByName("packageserver")->getPortalInstanceOfConcreteModule();

        $strJson = $objPortalServer->action("list");

        $this->assertNotNull($strJson);
        $arrData = json_decode($strJson, true);

        $this->assertTrue(isset($arrData["numberOfTotalItems"]));
        $this->assertTrue(isset($arrData["items"]));
        $this->assertTrue(isset($arrData["protocolVersion"]));

        $this->assertEquals(1, count($arrData["items"]));
        $arrItem = $arrData["items"][0];

        $this->assertTrue(isset($arrItem["systemid"]));
        $this->assertTrue(isset($arrItem["title"]));
        $this->assertTrue(isset($arrItem["version"]));
        $this->assertTrue(isset($arrItem["description"]));
        $this->assertTrue(isset($arrItem["type"]));

        $this->assertEquals("Autotest", $arrItem["title"]);
        $this->assertEquals("3.9.1", $arrItem["version"]);
        $this->assertEquals("demo", $arrItem["description"]);
        $this->assertEquals("MODULE", $arrItem["type"]);



        $objMediamanagerRepo->deleteObjectFromDatabase();
        $objFilesystem->fileDelete("/files/packageservertest/autotest.zip");

    }





    private function getStrMetadata() {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<package>
    <title>Autotest</title>
    <description>demo</description>
    <version>3.9.1</version>
    <author>Kajona Team</author>
    <target>module_autotest</target>
    <type>MODULE</type>
    <providesInstaller>FALSE</providesInstaller>
    <requiredModules>
        <module name="system" version="3.4.1" />
        <module name="pages" version="3.4.2" />
    </requiredModules>
</package>
XML;
    }

}
