<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Cache provider which uses the class_cache model
 *
 * @author christoph.kappestein@gmail.com
 * @since 5.0
 */
class CacheDatabase extends CacheProvider
{
    const CACHE_SOURCE = "internal_cache";

    /**
     * Internal cache in case someone calls the method contains() we fetch the data from the database. After that a call
     * to the fetch() method will not query the database again instead the cache is used
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

        $objCache = \class_cache::getCachedEntry(self::CACHE_SOURCE, $id);
        if ($objCache instanceof \class_cache) {
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
        $objCache = \class_cache::createNewInstance(self::CACHE_SOURCE);
        $objCache->setStrContent(serialize($data));
        $objCache->setStrHash1($this->getCacheKey($id));
        $objCache->setIntLeasetime(time() + $lifeTime);
        $objCache->updateObjectToDb();
    }

    public function doDelete($id)
    {
        \class_cache::flushCache(self::CACHE_SOURCE, $this->getCacheKey($id));
    }

    public function doFlush()
    {
        \class_cache::flushCache(self::CACHE_SOURCE);
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
