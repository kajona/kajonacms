<?php

require_once (dirname(__FILE__)."/../system/class_testbase.php");

class class_test_downloads extends class_testbase  {



    public function testCreateDelete() {
        echo "creating an archive..\n";
        
        $objArchive = new class_modul_downloads_archive();
        $objArchive->setPath("/portal/pics/uploads");
        
        $this->assertTrue($objArchive->updateObjectToDb(),  __FILE__." create dl Archive");
        
        class_modul_downloads_file::syncRecursive($objArchive->getSystemid(), $objArchive->getPath());
        
        $strSystemid = $objArchive->getSystemid();
        
        $this->flushDBCache();
        
        echo "deleting archive...\n";
        $objArchive = new class_modul_downloads_archive($strSystemid);
        
        $this->assertTrue($objArchive->deleteArchiveRecursive(true),  __FILE__." delete dl Archive");
        $this->assertTrue($objArchive->deleteArchive(),  __FILE__." delete dl Archive");
    }
    

}



?>