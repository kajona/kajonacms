<?php

require_once (dirname(__FILE__)."/../system/class_testbase.php");

class class_test_zip extends class_testbase  {



    public function testZipFiles() {
        $objFileSystem = new class_filesystem();

        echo "\ttesting class_zip...\n";
        
        $objZip = new class_zip();
        echo "\topening "._realpath_."/test.zip\n";
        $this->assertTrue($objZip->openArchiveForWriting("/test.zip"), __FILE__." openArchive");
        
        $this->assertTrue($objZip->addFile("/system/tables.txt"), __FILE__." addFile");
        $this->assertTrue($objZip->addFile("/system/tables.txt", "/tables_plain.txt"), __FILE__." addFileRenamed");
        
        $this->assertTrue($objZip->addFolder("/system/config"), __FILE__." addFolder");
        
        $this->assertTrue($objZip->closeArchive(), __FILE__." closeArchive");
        
        $this->assertFileExists(_realpath_."/test.zip", __FILE__." checkFileExists");
        
        echo "\textracting files\n";
        $objFileSystem->folderCreate("/zipextract");
        $this->assertFileExists(_realpath_."/zipextract", __FILE__." checkFileExists");
        
        $objZip = new class_zip();
        $this->assertTrue($objZip->extractArchive("/test.zip", "/zipextract"), __FILE__." extractArchive");
        
        $this->assertFileExists(_realpath_."/zipextract/system/tables.txt", __FILE__." extractArchive");
        $this->assertFileExists(_realpath_."/zipextract/tables_plain.txt", __FILE__." extractArchive");
        
        $this->assertFileExists(_realpath_."/zipextract/system/config/config.php", __FILE__." extractArchive");
        

        echo "\tremoving testfile";
        $this->assertTrue($objFileSystem->fileDelete("/test.zip"), __FILE__." deleteFile");
        $this->assertFileNotExists(_realpath_."/test.zip", __FILE__." checkFileNotExists");
        
        $objFileSystem->folderDeleteRecursive("/zipextract");
        $this->assertFileNotExists(_realpath_."/zipextract", __FILE__." checkFileNotExists");
    }
    
    
    public function testZipDirectory() {
        $objFileSystem = new class_filesystem();

        
        echo "\t creating test structure\n";
        $objFileSystem->folderCreate("/ziptest");
        $this->assertFileExists(_realpath_."/ziptest", __FILE__." checkFileNotExists");
        
        $objFileSystem->folderCreate("/ziptest/subdir");
        $objFileSystem->fileCopy("/system/tables.txt", "/ziptest/tables1.txt");
        $objFileSystem->fileCopy("/system/tables.txt", "/ziptest/tables2.txt");
        $objFileSystem->fileCopy("/system/tables.txt", "/ziptest/subdir/tables.txt");
        
        echo "\ttesting class_zip...\n";
        
        $objZip = new class_zip();
        echo "\topening "._realpath_."/test.zip\n";
        $this->assertTrue($objZip->openArchiveForWriting("/test.zip"), __FILE__." openArchive");
        $this->assertTrue($objZip->addFolder("/ziptest"), __FILE__." addFolder");
        $this->assertTrue($objZip->closeArchive(), __FILE__." closeArchive");
        $this->assertFileExists(_realpath_."/test.zip", __FILE__." checkFileExists");
        
        echo "\textracting files\n";
        $objFileSystem->folderCreate("/zipextract");
        $this->assertFileExists(_realpath_."/zipextract", __FILE__." checkFileExists");
        
        $objZip = new class_zip();
        $this->assertTrue($objZip->extractArchive("/test.zip", "/zipextract"), __FILE__." extractArchive");
        
        $this->assertFileExists(_realpath_."/zipextract/ziptest/tables1.txt", __FILE__." extractArchive");
        $this->assertFileExists(_realpath_."/zipextract/ziptest/tables2.txt", __FILE__." extractArchive");
        $this->assertFileExists(_realpath_."/zipextract/ziptest/subdir/tables.txt", __FILE__." extractArchive");
        
        

        echo "\tremoving testfile";
        $this->assertTrue($objFileSystem->fileDelete("/test.zip"), __FILE__." deleteFile");
        $this->assertFileNotExists(_realpath_."/test.zip", __FILE__." checkFileNotExists");
        
        $objFileSystem->folderDeleteRecursive("/ziptest");
        $this->assertFileNotExists(_realpath_."/ziptest", __FILE__." checkFileNotExists");
        
        $objFileSystem->folderDeleteRecursive("/zipextract");
        $this->assertFileNotExists(_realpath_."/zipextract", __FILE__." checkFileNotExists");
    }

}

?>