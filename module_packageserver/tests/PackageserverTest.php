<?php
namespace Kajona\Packageserver\Tests;

use FilesystemIterator;
use Kajona\Mediamanager\System\MediamanagerFile;
use Kajona\Mediamanager\System\MediamanagerRepo;
use Kajona\Packageserver\Portal\PackageserverPortal;
use Kajona\System\System\Filesystem;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\Testbase;
use Phar;

class PackageserverTest extends Testbase
{


    public function testJsonList()
    {

        $objFilesystem = new Filesystem();

        $objFilesystem->folderCreate("/files/packagesv5");
        $objFilesystem->folderCreate("/files/packagesv5/t");

        file_put_contents(_realpath_ . "/files/packagesv5/t/metadata.xml", $this->getStrMetadata());
        $objFilesystem->folderCreate("/files/packagesv5/t/system");
        file_put_contents(_realpath_ . "/files/packagesv5/t/system/test.txt", $this->getStrMetadata());


        $objPhar = new Phar(
            _realpath_ . "/files/packagesv5/autotest.phar",
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
            "autotest.phar"
        );
        $objPhar->buildFromDirectory(_realpath_ . "/files/packagesv5/t");
        $objPhar->setStub($objPhar->createDefaultStub());

        $this->assertFileExists(_realpath_ . "/files/packagesv5/autotest.phar");

        $objFilesystem->folderDeleteRecursive("/files/packagesv5/t");

        $objMediamanagerRepo = new MediamanagerRepo(SystemSetting::getConfigValue("_packageserver_repo_v5_id_"));

        MediamanagerFile::syncRecursive($objMediamanagerRepo->getSystemid(), $objMediamanagerRepo->getStrPath());

        /** @var $objPortalServer PackageserverPortal */
        $objPortalServer = SystemModule::getModuleByName("packageserver")->getPortalInstanceOfConcreteModule();
        $objPortalServer->setParam("protocolversion", 5);

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
        $this->assertEquals("5.0", $arrItem["version"]);
        $this->assertEquals("demo", $arrItem["description"]);
        $this->assertEquals("MODULE", $arrItem["type"]);


        unset($objPhar);
        $objMediamanagerRepo->deleteObjectFromDatabase();
        Phar::unlinkArchive(_realpath_ . "/files/packagesv5/autotest.phar");

        $this->assertFileNotExists(_realpath_ . "/files/packagesv5/autotest.phar");

    }


    private function getStrMetadata()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<package>
    <title>Autotest</title>
    <description>demo</description>
    <version>5.0</version>
    <author>Kajona Team</author>
    <target>module_autotest</target>
    <type>MODULE</type>
    <providesInstaller>FALSE</providesInstaller>
    <requiredModules>
        <module name="system" version="5.0" />
        <module name="pages" version="5.1" />
    </requiredModules>
</package>
XML;
    }

}
