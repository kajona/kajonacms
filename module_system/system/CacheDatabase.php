<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Cache provider which uses the Cache model
 *
 * @author christoph.kappestein@gmail.com
 * @since 5.0
 * @internal Do not use this class instead use \Kajona\System\System\CacheManager::getCache() to obtain a cache
 */
class CacheDatabase extends CacheProvider
{
    const CACHE_SOURCE = "internal_cache";

    /**
     * Internal cache to make only one database request in case someone calls contains() and fetch()
     *
     * @var array
     */
    protected $arrCache = array();

    public function doFetch($id)
    {
        $id = $this->getCacheKey($id);

        if (isset($this->arrCache[$id])) {
            return $this->arrCache[$id];
        }

        $objCache = Cache::getCachedEntry(self::CACHE_SOURCE, $id);
        if ($objCache instanceof Cache) {
            return unserialize($objCache->getStrContent());
        }

        return false;
    }

    public function doContains($id)
    {
        $id = $this->getCacheKey($id);

        return $this->arrCache[$id] = $this->fetch($id) !== false;
    }

    public function doSave($id, $data, $lifeTime = 0)
    {
        $objCache = Cache::createNewInstance(self::CACHE_SOURCE);
        $objCache->setStrContent(serialize($data));
        $objCache->setStrHash1($this->getCacheKey($id));
        $objCache->setIntLeasetime(time() + $lifeTime);
        $objCache->updateObjectToDb();
    }

    public function doDelete($id)
    {
        Cache::flushCache(self::CACHE_SOURCE, $this->getCacheKey($id));
    }

    public function doFlush()
    {
        Cache::flushCache(self::CACHE_SOURCE);
    }

    public function doGetStats()
    {
        return array();
    }

    protected function getCacheKey($id)
    {
        return md5($id);
    }
}
