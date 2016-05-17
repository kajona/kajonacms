<?php

namespace Kajona\Packagemanager\Tests;

use Kajona\Packagemanager\System\PackagemanagerManager;
use Kajona\Packagemanager\System\PackagemanagerMetadata;
use Kajona\System\System\Filesystem;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Zip;
use Kajona\System\Tests\Testbase;

class PackagemanagerTest extends Testbase
{


    public function testMetadataReader()
    {

        $objReader = new PackagemanagerMetadata();
        $objReader->autoInit(Resourceloader::getInstance()->getCorePathForModule("module_packagemanager") . "/module_packagemanager");

        echo $objReader . "\n\n";
    }


    public function testInstalledPackageList()
    {

        $objManager = new PackagemanagerManager();
        $arrModules = $objManager->getAvailablePackages();

        foreach ($arrModules as $intKey => $objOneModule) {
            //echo "#" . $intKey . ": " . $objOneModule . "\n";
        }
    }


    public function testExtractAndMove()
    {

        $objFilesystem = new Filesystem();

        $objFilesystem->folderCreate(_projectpath_ . "/temp/moduletest");

        file_put_contents(_realpath_ . _projectpath_ . "/temp/moduletest/metadata.xml", $this->getStrMetadata());

        $objFilesystem->folderCreate(_projectpath_ . "/temp/moduletest/system");
        file_put_contents(_realpath_ . _projectpath_ . "/temp/moduletest/system/test.txt", $this->getStrMetadata());

        $objZip = new Zip();
        $objZip->openArchiveForWriting(_projectpath_ . "/temp/autotest.zip");
        $objZip->addFile(_projectpath_ . "/temp/moduletest/metadata.xml", "/metadata.xml");
        $objZip->addFile(_projectpath_ . "/temp/moduletest/system/test.txt", "/system/test.txt");
        $objZip->closeArchive();

        $objFilesystem->folderDeleteRecursive(_projectpath_ . "/temp/moduletest/");


        $objManager = new PackagemanagerManager();
        $objPackageManager = $objManager->getPackageManagerForPath(_projectpath_ . "/temp/autotest.zip");
        $this->assertEquals(get_class($objPackageManager), "Kajona\\Packagemanager\\System\\PackagemanagerPackagemanagerModule");

        $objPackageManager = $objManager->extractPackage(_projectpath_ . "/temp/autotest.zip");
        $this->assertEquals(get_class($objPackageManager), "Kajona\\Packagemanager\\System\\PackagemanagerPackagemanagerModule");

        $objPackageManager->move2Filesystem();

        $this->assertFileExists(_realpath_ . "/core/module_autotest/metadata.xml");
        $this->assertFileExists(_realpath_ . "/core/module_autotest/system/test.txt");

        $objMetadata = new PackagemanagerMetadata();
        $objMetadata->autoInit("/core/module_autotest/");

        $this->assertEquals("Autotest", $objMetadata->getStrTitle());
        $this->assertEquals("", $objMetadata->getStrDescription());
        $this->assertEquals("3.9.1", $objMetadata->getStrVersion());
        $this->assertEquals("Kajona Team", $objMetadata->getStrAuthor());
        $this->assertEquals("module_autotest", $objMetadata->getStrTarget());
        $this->assertEquals(PackagemanagerManager::STR_TYPE_MODULE, $objMetadata->getStrType());
        $this->assertEquals(false, $objMetadata->getBitProvidesInstaller());

        $arrRequired = $objMetadata->getArrRequiredModules();
        $arrModules = array_keys($arrRequired);
        $arrVersion = array_values($arrRequired);

        $this->assertEquals("system", $arrModules[0]);
        $this->assertEquals("3.4.1", $arrVersion[0]);

        $this->assertEquals("pages", $arrModules[1]);
        $this->assertEquals("3.4.2", $arrVersion[1]);

        $arrImages = $objMetadata->getArrScreenshots();
        $this->assertEquals(1, count($arrImages));
        $this->assertEquals("/test.jpg", $arrImages[0]);

        $objFilesystem->folderDeleteRecursive("/core/module_autotest");
        $objFilesystem->fileDelete(_projectpath_ . "/temp/autotest.zip");

    }


    public function testProviderConfig()
    {
        $objManager = new PackagemanagerManager();
        $arrProviders = $objManager->getContentproviders();
        $this->assertEquals(2, count($arrProviders));
        $this->assertEquals("Kajona\\Packagemanager\\System\\PackagemanagerContentproviderLocal", get_class($arrProviders[1]));
    }


    public function testUpdateOrInstall()
    {
        $objManager = new PackagemanagerManager();
        $objHandler = $objManager->getPackageManagerForPath(Resourceloader::getInstance()->getCorePathForModule("module_packagemanager") . "/module_packagemanager");
        $this->assertTrue(!$objHandler->isInstallable());
    }

    public function testRequiredBy()
    {
        $objManager = new PackagemanagerManager();
        $objSystem = $objManager->getPackage("system");

        $arrRequiredBy = $objManager->getArrRequiredBy($objSystem);
        $this->assertTrue(in_array("packagemanager", $arrRequiredBy));
        $this->assertFalse(in_array("system", $arrRequiredBy));
    }


    private function getStrMetadata()
    {
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
    <requiredModules>
        <module name="system" version="3.4.1" />
        <module name="pages" version="3.4.2" />
    </requiredModules>
    <screenshots>
        <screenshot path="/test.jpg" />
        <screenshot path="/test2.jpsg" />
    </screenshots>
</package>
XML;
    }

}
