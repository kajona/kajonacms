<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * @author christoph.kappestein@gmail.com
 * @since 5.0
 */
class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCacheGetAddRemove()
    {
        $objCacheManager = new CacheManager();
        $strValue = $objCacheManager->getValue("foo", CacheManager::TYPE_ARRAY);
        $this->assertFalse($strValue);

        $objCacheManager->addValue("foo", "bar", 180, CacheManager::TYPE_ARRAY);

        $strValue = $objCacheManager->getValue("foo", CacheManager::TYPE_ARRAY);
        $this->assertEquals("bar", $strValue);

        $objCacheManager->removeValue("foo", CacheManager::TYPE_ARRAY);

        $strValue = $objCacheManager->getValue("foo", CacheManager::TYPE_ARRAY);
        $this->assertFalse($strValue);
    }

    public function testFlushCache()
    {
        $objCacheManager = new CacheManager();
        $strValue = $objCacheManager->getValue("foo", CacheManager::TYPE_ARRAY);
        $this->assertFalse($strValue);

        $objCacheManager->addValue("foo", "bar", 180, CacheManager::TYPE_ARRAY);

        $strValue = $objCacheManager->getValue("foo", CacheManager::TYPE_ARRAY);
        $this->assertEquals("bar", $strValue);

        $objCacheManager->flushCache(CacheManager::TYPE_ARRAY);

        $strValue = $objCacheManager->getValue("foo", CacheManager::TYPE_ARRAY);
        $this->assertFalse($strValue);
    }

    /**
     * Test which simply calls the get value method with every possible type combination
     *
     * @dataProvider typeDataProvider
     */
    public function testTypes($intType)
    {
        $objCacheManager = new CacheManager();
        $strValue = $objCacheManager->getValue("foo", $intType);
        $this->assertFalse($strValue);

        // this call should com from the internal cache
        $strValue = $objCacheManager->getValue("foo", CacheManager::TYPE_ARRAY);
        $this->assertFalse($strValue);
    }

    /**
     * @expectedException \class_exception
     */
    public function testInvalidType()
    {
        $objCacheManager = new CacheManager();
        $objCacheManager->getValue("foo", 1 << 5);
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('Kajona\\System\\System\\CacheManager', CacheManager::getInstance());
    }

    public function testGetAvailableDriver()
    {
        $this->assertEquals(array(CacheManager::TYPE_APC, CacheManager::TYPE_DATABASE, CacheManager::TYPE_FILESYSTEM, CacheManager::TYPE_PHPFILE), array_keys(CacheManager::getAvailableDriver()));
    }

    public function typeDataProvider()
    {
        $intMax = CacheManager::TYPE_ARRAY | CacheManager::TYPE_APC | CacheManager::TYPE_DATABASE | CacheManager::TYPE_FILESYSTEM | CacheManager::TYPE_PHPFILE;
        $arrTypes = array();
        for ($intI = 0; $intI <= $intMax; $intI++) {
            $arrTypes[] = array($intI);
        }

        return $arrTypes;
    }
}
