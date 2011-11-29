<?php

require_once (dirname(__FILE__)."/../../module_system/system/class_testbase.php");

class class_test_zip extends class_testbase  {



    public function testZipFiles() {
        $objFileSystem = new class_filesystem();

        echo "\ttesting class_zip...\n";
        
        $objZip = new class_zip();
        echo "\topening "._realpath_."/test.zip\n";
        $this->assertTrue($objZip->openArchiveForWriting("/files/cache/test.zip"), __FILE__." openArchive");
        
        $this->assertTrue($objZip->addFile("/core/module_system/system/tables.txt"), __FILE__." addFile");
        $this->assertTrue($objZip->addFile("/core/module_system/system/tables.txt", "/tables_plain.txt"), __FILE__." addFileRenamed");
        
        $this->assertTrue($objZip->addFolder("/core/module_system/system/config"), __FILE__." addFolder");
        
        $this->assertTrue($objZip->closeArchive(), __FILE__." closeArchive");
        
        $this->assertFileExists(_realpath_."/files/cache/test.zip", __FILE__." checkFileExists");
        
        echo "\textracting files\n";
        $objFileSystem->folderCreate("/files/cache/zipextract");
        $this->assertFileExists(_realpath_."/files/cache/zipextract", __FILE__." checkFileExists");
        
        $objZip = new class_zip();
        $this->assertTrue($objZip->extractArchive("/files/cache/test.zip", "/files/cache/zipextract"), __FILE__." extractArchive");
        
        $this->assertFileExists(_realpath_."/files/cache/zipextract/system/tables.txt", __FILE__." extractArchive");
        $this->assertFileExists(_realpath_."/files/cache/zipextract/tables_plain.txt", __FILE__." extractArchive");
        
        $this->assertFileExists(_realpath_."/files/cache/zipextract/system/config/config.php", __FILE__." extractArchive");
        

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
        $objFileSystem->fileCopy("/core/module_system/system/tables.txt", "/files/cache/ziptest/tables1.txt");
        $objFileSystem->fileCopy("/core/module_system/system/tables.txt", "/files/cache/ziptest/tables2.txt");
        $objFileSystem->fileCopy("/core/module_system/system/tables.txt", "/files/cache/ziptest/subdir/tables.txt");
        
        echo "\ttesting class_zip...\n";
        
        $objZip = new class_zip();
        echo "\topening "._realpath_."/test.zip\n";
        $this->assertTrue($objZip->openArchiveForWriting("/files/cache/test.zip"), __FILE__." openArchive");
        $this->assertTrue($objZip->addFolder("/files/cache/ziptest"), __FILE__." addFolder");
        $this->assertTrue($objZip->closeArchive(), __FILE__." closeArchive");
        $this->assertFileExists(_realpath_."/files/cache/test.zip", __FILE__." checkFileExists");
        
        echo "\textracting files\n";
        $objFileSystem->folderCreate("/files/cache/zipextract");
        $this->assertFileExists(_realpath_."/files/cache/zipextract", __FILE__." checkFileExists");
        
        $objZip = new class_zip();
        $this->assertTrue($objZip->extractArchive("/files/cache/test.zip", "/files/cache/zipextract"), __FILE__." extractArchive");
        
        $this->assertFileExists(_realpath_."/files/cache/zipextract/ziptest/tables1.txt", __FILE__." extractArchive");
        $this->assertFileExists(_realpath_."/files/cache/zipextract/ziptest/tables2.txt", __FILE__." extractArchive");
        $this->assertFileExists(_realpath_."/files/cache/zipextract/ziptest/subdir/tables.txt", __FILE__." extractArchive");
        
        

        echo "\tremoving testfile";
        $this->assertTrue($objFileSystem->fileDelete("/files/cache/test.zip"), __FILE__." deleteFile");
        $this->assertFileNotExists(_realpath_."/files/cache/test.zip", __FILE__." checkFileNotExists");
        
        $objFileSystem->folderDeleteRecursive("/files/cache/ziptest");
        $this->assertFileNotExists(_realpath_."/files/cache/ziptest", __FILE__." checkFileNotExists");
        
        $objFileSystem->folderDeleteRecursive("/files/cache/zipextract");
        $this->assertFileNotExists(_realpath_."/files/cache/zipextract", __FILE__." checkFileNotExists");
    }

}

?>