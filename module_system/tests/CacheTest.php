<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Cache;
use Kajona\System\System\Testbase;

class CacheTest extends Testbase
{

    private static $strTestId = "";
    private static $strCacheSource = "autotest";

    protected function setUp()
    {
        self::$strTestId = generateSystemid();
        parent::setUp();

        echo "test cache...\n";
    }


    public function testCacheSources()
    {

        $objCache = Cache::createNewInstance(self::$strCacheSource);

        $objCache->setStrHash1("test");
        $objCache->setStrContent("test");
        $objCache->updateObjectToDb();

        $this->flushDBCache();

        $arrSources = Cache::getCacheSources();

        $this->assertTrue(in_array(self::$strCacheSource, $arrSources));
    }


    public function testClean()
    {

        $objCache = Cache::createNewInstance(self::$strCacheSource);
        $objCache->setStrHash1("testFlush");
        $objCache->setStrContent("test");
        $objCache->setIntLeasetime(time() + 2);
        $objCache->updateObjectToDb();
        $this->flushDBCache();
        sleep(3);

        Cache::cleanCache();

        $objEntries = Cache::getCachedEntry(self::$strCacheSource, "testFlush");

        $this->assertNull($objEntries);

    }


    public function testCacheing()
    {

        $objCache = Cache::createNewInstance(self::$strCacheSource);
        $objCache->setStrHash1("testCache");
        $objCache->setStrContent("testContent");
        $objCache->setIntLeasetime(time() + 100);
        $objCache->updateObjectToDb();
        $this->flushDBCache();

        Cache::cleanCache();

        $objEntries = Cache::getCachedEntry(self::$strCacheSource, "testCache");
        $this->assertNotNull($objEntries);

        $this->assertEquals("testContent", $objEntries->getStrContent());

    }

    public function testCacheFlushing()
    {

        $objCache = Cache::createNewInstance(self::$strCacheSource);
        $objCache->setStrHash1("testClean");
        $objCache->setStrContent("test");
        $objCache->setIntLeasetime(time() + 2);
        $objCache->updateObjectToDb();
        $this->flushDBCache();

        $objEntry = Cache::getCachedEntry(self::$strCacheSource, "testClean");
        $this->assertNotNull($objEntry);

        $this->flushDBCache();

        Cache::flushCache(self::$strCacheSource);

        $this->flushDBCache();

        $objEntry = Cache::getCachedEntry(self::$strCacheSource, "testClean");
        $this->assertNull($objEntry);
    }

}

