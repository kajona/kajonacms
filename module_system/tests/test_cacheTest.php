<?php

require_once (dirname(__FILE__)."/../system/class_testbase.php");

class class_test_cache extends class_testbase  {

    private static $strTestId = "";
    private static $strCacheSource = "autotest";

    protected function setUp() {
        self::$strTestId = generateSystemid();
        parent::setUp();
    }
    
    
    public function testCacheSources() {
        
        $objCache = class_cache::createNewInstance(self::$strCacheSource);
        
        $objCache->setStrHash1("test");
        $objCache->setStrContent("test");
        $objCache->updateObjectToDb();
        
        $this->flushDBCache();
        
        $arrSources = class_cache::getCacheSources();
        
        $this->assertTrue(in_array(self::$strCacheSource, $arrSources));
    }
    
    
    public function testClean() {
        
        $objCache = class_cache::createNewInstance(self::$strCacheSource);
        $objCache->setStrHash1("testFlush");
        $objCache->setStrContent("test");
        $objCache->setIntLeasetime(time()+2);
        $objCache->updateObjectToDb();
        $this->flushDBCache();
        sleep(3);
        
        class_cache::cleanCache();
        
        $objEntries = class_cache::getCachedEntry(self::$strCacheSource, "testFlush");
        
        $this->assertNull($objEntries);
        
    }
    
    
    public function testCacheing() {
        
        $objCache = class_cache::createNewInstance(self::$strCacheSource);
        $objCache->setStrHash1("testCache");
        $objCache->setStrContent("testContent");
        $objCache->setIntLeasetime(time()+100);
        $objCache->updateObjectToDb();
        $this->flushDBCache();
        
        class_cache::cleanCache();
        
        $objEntries = class_cache::getCachedEntry(self::$strCacheSource, "testCache");
        $this->assertNotNull($objEntries);
        
        $this->assertEquals("testContent", $objEntries->getStrContent());
        
    }
    
    public function testCacheFlushing() {
        
        $objCache = class_cache::createNewInstance(self::$strCacheSource);
        $objCache->setStrHash1("testClean");
        $objCache->setStrContent("test");
        $objCache->setIntLeasetime(time()+2);
        $objCache->updateObjectToDb();
        $this->flushDBCache();
        
        $objEntry = class_cache::getCachedEntry(self::$strCacheSource, "testClean");
        $this->assertNotNull($objEntry);
        
        $this->flushDBCache();
        
        class_cache::flushCache(self::$strCacheSource);
        
        $this->flushDBCache();
        
        $objEntry = class_cache::getCachedEntry(self::$strCacheSource, "testClean");
        $this->assertNull($objEntry);
    }
    
}

?>