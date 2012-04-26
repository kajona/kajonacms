<?php
require_once (__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_packagemanager extends class_testbase  {



    public function testMetadataReader() {

        $objReader = new class_module_packagemanager_metadata();
        $objReader->autoInit("/core/module_packagemanager");

        echo $objReader."\n\n";
    }


    public function testInstalledPackageList() {

        $objManager = new class_module_packagemanager_manager();
        $arrModules = $objManager->getAvailablePackages();

        foreach($arrModules as $intKey => $objOneModule) {
            echo "#".$intKey.": ".$objOneModule."\n";
        }
    }


    public function testExtractAndMove() {

        $objFilesystem = new class_filesystem();

        $objFilesystem->folderCreate(_projectpath_."/temp/moduletest");

        file_put_contents(_realpath_._projectpath_."/temp/moduletest/metadata.xml", $this->getStrMetadata());

        $objFilesystem->folderCreate(_projectpath_."/temp/moduletest/system");
        file_put_contents(_realpath_._projectpath_."/temp/moduletest/system/test.txt", $this->getStrMetadata());

        $objZip = new class_zip();
        $objZip->openArchiveForWriting(_projectpath_."/temp/autotest.zip");
        $objZip->addFile(_projectpath_."/temp/moduletest/metadata.xml", "/metadata.xml");
        $objZip->addFile(_projectpath_."/temp/moduletest/system/test.txt", "/system/test.txt");
        $objZip->closeArchive();

        $objFilesystem->folderDeleteRecursive(_projectpath_."/temp/moduletest/");


        $objManager = new class_module_packagemanager_manager();
        $objPackageManager = $objManager->getPackageManagerForPath(_projectpath_."/temp/autotest.zip");
        $this->assertEquals(get_class($objPackageManager), "class_module_packagemanager_packagemanager_module");

        $objPackageManager = $objManager->extractPackage(_projectpath_."/temp/autotest.zip");
        $this->assertEquals(get_class($objPackageManager), "class_module_packagemanager_packagemanager_module");

        $objPackageManager->move2Filesystem();

        $this->assertFileExists(_realpath_."/core/module_autotest/metadata.xml");
        $this->assertFileExists(_realpath_."/core/module_autotest/system/test.txt");

        $objMetadata = new class_module_packagemanager_metadata();
        $objMetadata->autoInit("/core/module_autotest/");

        $this->assertEquals("Autotest", $objMetadata->getStrTitle());
        $this->assertEquals("", $objMetadata->getStrDescription());
        $this->assertEquals("3.9.1", $objMetadata->getStrVersion());
        $this->assertEquals("Kajona Team", $objMetadata->getStrAuthor());
        $this->assertEquals("module_autotest", $objMetadata->getStrTarget());
        $this->assertEquals(class_module_packagemanager_manager::$STR_TYPE_MODULE, $objMetadata->getStrType());
        $this->assertEquals(false, $objMetadata->getBitProvidesInstaller());
        $this->assertEquals("system,pages", $objMetadata->getStrRequiredModules());
        $this->assertEquals("3.4.1", $objMetadata->getStrMinVersion());

        $objFilesystem->folderDeleteRecursive("/core/module_autotest");
        $objFilesystem->fileDelete(_projectpath_."/temp/autotest.zip");

    }


    public function testProviderConfig() {
        $objManager = new class_module_packagemanager_manager();
        $arrProviders = $objManager->getContentproviders();
        $this->assertEquals(2, count($arrProviders));


        $this->assertEquals("class_module_packagemanager_contentprovider_local", get_class($arrProviders[0]));
    }


    public function testUpdateOrInstall() {
        $objManager = new class_module_packagemanager_manager();
        $objHandler = $objManager->getPackageManagerForPath("/core/module_packagemanager");
        $this->assertTrue(!$objHandler->isInstallable());
    }


    private function getStrMetadata() {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<package>
    <title>Autotest</title>
    <description></description>
    <version>3.9.1</version>
    <author>Kajona Team</author>
    <target>module_autotest</target>
    <type>MODULE</type>
    <providesInstaller>FALSE</providesInstaller>
    <requiredModules>system,pages</requiredModules>
    <minSystemVersion>3.4.1</minSystemVersion>
</package>
XML;
    }

}
