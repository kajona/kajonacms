<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_zip extends class_testbase  {



    public function testZipFiles() {
        $objFileSystem = new class_filesystem();

        echo "\ttesting class_zip...\n";

        $objZip = new class_zip();
        echo "\topening "._realpath_."/test.zip\n";
        $this->assertTrue($objZip->openArchiveForWriting("/files/cache/test.zip"), __FILE__." openArchive");

        $this->assertTrue($objZip->addFile(class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/metadata.xml"), __FILE__." addFile");
        $this->assertTrue($objZip->addFile(class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/metadata.xml", "/metadata_plain.xml"), __FILE__." addFileRenamed");

        $this->assertTrue($objZip->addFolder(class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/system/config"), __FILE__." addFolder");

        $this->assertTrue($objZip->closeArchive(), __FILE__." closeArchive");

        $this->assertFileExists(_realpath_."/files/cache/test.zip", __FILE__." checkFileExists");

        echo "\textracting files\n";
        $objFileSystem->folderCreate("/files/cache/zipextract");
        $this->assertFileExists(_realpath_."/files/cache/zipextract", __FILE__." checkFileExists");

        $objZip = new class_zip();
        $this->assertTrue($objZip->extractArchive("/files/cache/test.zip", "/files/cache/zipextract"), __FILE__." extractArchive");

        $this->assertFileExists(_realpath_."/files/cache/zipextract".class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/metadata.xml", __FILE__." extractArchive");
        $this->assertFileExists(_realpath_."/files/cache/zipextract/metadata_plain.xml", __FILE__." extractArchive");

        $this->assertFileExists(_realpath_."/files/cache/zipextract".class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/system/config/config.php", __FILE__." extractArchive");


        echo "\tremoving testfile";
        $this->assertTrue($objFileSystem->fileDelete("/files/cache/test.zip"), __FILE__." deleteFile");
        $this->assertFileNotExists(_realpath_."/files/cache/test.zip", __FILE__." checkFileNotExists");

        $objFileSystem->folderDeleteRecursive("/files/cache/zipextract");
        $this->assertFileNotExists(_realpath_."/files/cache/zipextract", __FILE__." checkFileNotExists");
    }


    public function testZipDirectory() {
        $objFileSystem = new class_filesystem();


        echo "\t creating test structure\n";
        $objFileSystem->folderCreate("/files/cache/ziptest");
        $this->assertFileExists(_realpath_."/files/cache/ziptest", __FILE__." checkFileNotExists");

        $objFileSystem->folderCreate("/files/cache/ziptest/subdir");
        $objFileSystem->fileCopy(class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/metadata.xml", "/files/cache/ziptest/licence_lgpl1.txt");
        $objFileSystem->fileCopy(class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/metadata.xml", "/files/cache/ziptest/licence_lgpl2.txt");
        $objFileSystem->fileCopy(class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/metadata.xml", "/files/cache/ziptest/subdir/licence_lgpl.txt");

        echo "\ttesting class_zip...\n";

        $objZip = new class_zip();
        echo "\topening test.zip\n";
        $this->assertTrue($objZip->openArchiveForWriting("/files/cache/test.zip"), __FILE__." openArchive");
        $this->assertTrue($objZip->addFolder("/files/cache/ziptest"), __FILE__." addFolder");
        $this->assertTrue($objZip->closeArchive(), __FILE__." closeArchive");
        $this->assertFileExists(_realpath_."/files/cache/test.zip", __FILE__." checkFileExists");

        echo "\textracting files\n";
        $objFileSystem->folderCreate("/files/cache/zipextract");
        $this->assertFileExists(_realpath_."/files/cache/zipextract", __FILE__." checkFileExists");

        $objZip = new class_zip();
        $this->assertTrue($objZip->extractArchive("/files/cache/test.zip", "/files/cache/zipextract"), __FILE__." extractArchive");

        $this->assertFileExists(_realpath_."/files/cache/zipextract/files/cache/ziptest/licence_lgpl1.txt", __FILE__." extractArchive");
        $this->assertFileExists(_realpath_."/files/cache/zipextract/files/cache/ziptest/licence_lgpl2.txt", __FILE__." extractArchive");
        $this->assertFileExists(_realpath_."/files/cache/zipextract/files/cache/ziptest/subdir/licence_lgpl.txt", __FILE__." extractArchive");



        echo "\tremoving testfile\n";
        $this->assertTrue($objFileSystem->fileDelete("/files/cache/test.zip"), __FILE__." deleteFile");
        $this->assertFileNotExists(_realpath_."/files/cache/test.zip", __FILE__." checkFileNotExists");

        $objFileSystem->folderDeleteRecursive("/files/cache/ziptest");
        $this->assertFileNotExists(_realpath_."/files/cache/ziptest", __FILE__." checkFileNotExists");

        $objFileSystem->folderDeleteRecursive("/files/cache/zipextract");
        $this->assertFileNotExists(_realpath_."/files/cache/zipextract", __FILE__." checkFileNotExists");
    }


    public function testZipFileread() {
        $objFileSystem = new class_filesystem();

        echo "\ttesting class_zip file-reading...\n";

        $objZip = new class_zip();
        echo "\topening "._realpath_."/test.zip\n";
        $this->assertTrue($objZip->openArchiveForWriting("/files/cache/test.zip"), __FILE__." openArchive");
        $this->assertTrue($objZip->addFile(class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/metadata.xml"), __FILE__." addFile");
        $this->assertTrue($objZip->closeArchive(), __FILE__." closeArchive");

        $this->assertFileExists(_realpath_."/files/cache/test.zip", __FILE__." checkFileExists");

        echo "\treading files\n";
        $strContent = $objZip->getFileFromArchive("/files/cache/test.zip", class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/metadata.xml");
        $this->assertTrue(uniStrpos($strContent, "xsi:noNamespaceSchemaLocation=\"http://apidocs.kajona.de/xsd/package.xsd\"") !== false);

        echo "\tremoving testfile\n";
        $this->assertTrue($objFileSystem->fileDelete("/files/cache/test.zip"), __FILE__." deleteFile");
        $this->assertFileNotExists(_realpath_."/files/cache/test.zip", __FILE__." checkFileNotExists");

        $objFileSystem->folderDeleteRecursive("/files/cache/zipextract");
        $this->assertFileNotExists(_realpath_."/files/cache/zipextract", __FILE__." checkFileNotExists");
    }
}

