<?php
namespace Kajona\Packageserver\Tests;

use class_filesystem;
use class_module_mediamanager_file;
use class_module_mediamanager_repo;
use class_module_system_module;
use class_testbase;
use FilesystemIterator;
use Kajona\Mediamanager\System\MediamanagerFile;
use Kajona\Mediamanager\System\MediamanagerRepo;
use Kajona\Packageserver\Portal\PackageserverPortal;
use Kajona\System\System\Filesystem;
use Kajona\System\System\SystemModule;
use Kajona\System\System\Testbase;
use Phar;
require_once __DIR__."/../../../core/module_system/system/Testbase.php";
class PackageserverTest extends Testbase  {


    public function testJsonList() {

        $objFilesystem = new Filesystem();

        $objFilesystem->folderCreate("/files/packageservertest");
        $objFilesystem->folderCreate("/files/packageservertest/t");

        file_put_contents(_realpath_."/files/packageservertest/t/metadata.xml", $this->getStrMetadata());
        $objFilesystem->folderCreate("/files/packageservertest/t/system");
        file_put_contents(_realpath_."/files/packageservertest/t/system/test.txt", $this->getStrMetadata());



        $objPhar = new Phar(
            _realpath_."/files/packageservertest/autotest.phar",
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
            "autotest.phar"
        );
        $objPhar->buildFromDirectory(_realpath_."/files/packageservertest/t");
        $objPhar->setStub($objPhar->createDefaultStub());

        $this->assertFileExists(_realpath_."/files/packageservertest/autotest.phar");

        $objFilesystem->folderDeleteRecursive("/files/packageservertest/t");

        $objMediamanagerRepo = new MediamanagerRepo();
        $objMediamanagerRepo->setStrPath("/files/packageservertest");
        $objMediamanagerRepo->setStrTitle("autotest packages");
        $objMediamanagerRepo->updateObjectToDb();

        MediamanagerFile::syncRecursive($objMediamanagerRepo->getSystemid(), $objMediamanagerRepo->getStrPath());

        /** @var $objPortalServer PackageserverPortal */
        $objPortalServer = SystemModule::getModuleByName("packageserver")->getPortalInstanceOfConcreteModule();

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
        Phar::unlinkArchive(_realpath_."/files/packageservertest/autotest.phar");

        $this->assertFileNotExists(_realpath_."/files/packageservertest/autotest.phar");

    }





    private function getStrMetadata() {
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
