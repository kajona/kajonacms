<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Filesystem;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\StringUtil;
use Kajona\System\System\Zip;

class ZipTest extends Testbase
{


    public function testZipFiles()
    {
        $objFileSystem = new Filesystem();

        $objZip = new Zip();
        $this->assertTrue($objZip->openArchiveForWriting("/files/cache/test.zip"), __FILE__ . " openArchive");

        $this->assertTrue($objZip->addFile(Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/metadata.xml"), __FILE__ . " addFile");
        $this->assertTrue($objZip->addFile(Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/metadata.xml", "/metadata_plain.xml"), __FILE__ . " addFileRenamed");

        $this->assertTrue($objZip->addFolder(Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/system/config"), __FILE__ . " addFolder");

        $this->assertTrue($objZip->closeArchive(), __FILE__ . " closeArchive");

        $this->assertFileExists(_realpath_ . "files/cache/test.zip", __FILE__ . " checkFileExists");

        $objFileSystem->folderCreate("/files/cache/zipextract");
        $this->assertFileExists(_realpath_ . "files/cache/zipextract", __FILE__ . " checkFileExists");

        $objZip = new Zip();
        $this->assertTrue($objZip->extractArchive("/files/cache/test.zip", "/files/cache/zipextract"), __FILE__ . " extractArchive");

        $this->assertFileExists(_realpath_ . "files/cache/zipextract" . Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/metadata.xml", __FILE__ . " extractArchive");
        $this->assertFileExists(_realpath_ . "files/cache/zipextract/metadata_plain.xml", __FILE__ . " extractArchive");

        $this->assertFileExists(_realpath_ . "files/cache/zipextract" . Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/system/config/config.php", __FILE__ . " extractArchive");


        $this->assertTrue($objFileSystem->fileDelete("/files/cache/test.zip"), __FILE__ . " deleteFile");
        $this->assertFileNotExists(_realpath_ . "files/cache/test.zip", __FILE__ . " checkFileNotExists");

        $objFileSystem->folderDeleteRecursive("/files/cache/zipextract");
        $this->assertFileNotExists(_realpath_ . "files/cache/zipextract", __FILE__ . " checkFileNotExists");
    }


    public function testZipDirectory()
    {
        $objFileSystem = new Filesystem();


        $objFileSystem->folderCreate("/files/cache/ziptest");
        $this->assertFileExists(_realpath_ . "files/cache/ziptest", __FILE__ . " checkFileNotExists");

        $objFileSystem->folderCreate("/files/cache/ziptest/subdir");
        $objFileSystem->fileCopy(Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/metadata.xml", "/files/cache/ziptest/licence_lgpl1.txt");
        $objFileSystem->fileCopy(Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/metadata.xml", "/files/cache/ziptest/licence_lgpl2.txt");
        $objFileSystem->fileCopy(Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/metadata.xml", "/files/cache/ziptest/subdir/licence_lgpl.txt");


        $objZip = new Zip();
        $this->assertTrue($objZip->openArchiveForWriting("/files/cache/test.zip"), __FILE__ . " openArchive");
        $this->assertTrue($objZip->addFolder("/files/cache/ziptest"), __FILE__ . " addFolder");
        $this->assertTrue($objZip->closeArchive(), __FILE__ . " closeArchive");
        $this->assertFileExists(_realpath_ . "files/cache/test.zip", __FILE__ . " checkFileExists");

        $objFileSystem->folderCreate("/files/cache/zipextract");
        $this->assertFileExists(_realpath_ . "files/cache/zipextract", __FILE__ . " checkFileExists");

        $objZip = new Zip();
        $this->assertTrue($objZip->extractArchive("/files/cache/test.zip", "/files/cache/zipextract"), __FILE__ . " extractArchive");

        $this->assertFileExists(_realpath_ . "files/cache/zipextract/files/cache/ziptest/licence_lgpl1.txt", __FILE__ . " extractArchive");
        $this->assertFileExists(_realpath_ . "files/cache/zipextract/files/cache/ziptest/licence_lgpl2.txt", __FILE__ . " extractArchive");
        $this->assertFileExists(_realpath_ . "files/cache/zipextract/files/cache/ziptest/subdir/licence_lgpl.txt", __FILE__ . " extractArchive");


        $this->assertTrue($objFileSystem->fileDelete("/files/cache/test.zip"), __FILE__ . " deleteFile");
        $this->assertFileNotExists(_realpath_ . "files/cache/test.zip", __FILE__ . " checkFileNotExists");

        $objFileSystem->folderDeleteRecursive("/files/cache/ziptest");
        $this->assertFileNotExists(_realpath_ . "files/cache/ziptest", __FILE__ . " checkFileNotExists");

        $objFileSystem->folderDeleteRecursive("/files/cache/zipextract");
        $this->assertFileNotExists(_realpath_ . "files/cache/zipextract", __FILE__ . " checkFileNotExists");
    }


    public function testZipFileread()
    {
        $objFileSystem = new Filesystem();


        $objZip = new Zip();
        $this->assertTrue($objZip->openArchiveForWriting("/files/cache/test.zip"), __FILE__ . " openArchive");
        $this->assertTrue($objZip->addFile(Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/metadata.xml"), __FILE__ . " addFile");
        $this->assertTrue($objZip->closeArchive(), __FILE__ . " closeArchive");

        $this->assertFileExists(_realpath_ . "files/cache/test.zip", __FILE__ . " checkFileExists");

        $strContent = $objZip->getFileFromArchive("/files/cache/test.zip", Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/metadata.xml");
        $this->assertTrue(StringUtil::indexOf($strContent, "xsi:noNamespaceSchemaLocation=\"https://apidocs.kajona.de/xsd/package.xsd\"") !== false);

        $this->assertTrue($objFileSystem->fileDelete("/files/cache/test.zip"), __FILE__ . " deleteFile");
        $this->assertFileNotExists(_realpath_ . "files/cache/test.zip", __FILE__ . " checkFileNotExists");

        $objFileSystem->folderDeleteRecursive("/files/cache/zipextract");
        $this->assertFileNotExists(_realpath_ . "files/cache/zipextract", __FILE__ . " checkFileNotExists");
    }
}

