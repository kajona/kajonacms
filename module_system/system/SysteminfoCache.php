<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Doctrine\Common\Cache\Cache as DoctrineCache;


/**
 * General information regarding the current timezone environment
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class SysteminfoCache implements SysteminfoInterface
{
    /**
     * Returns the title of the info-block
     *
     * @return string
     */
    public function getStrTitle()
    {
        return Carrier::getInstance()->getObjLang()->getLang("cache", "system");
    }

    /**
     * Returns the contents of the info-block
     *
     * @return mixed
     */
    public function getArrContent()
    {
        $objLang = Carrier::getInstance()->getObjLang();
        $arrReturn = array();

        $arrTypes = array(
            CacheManager::TYPE_APC => $objLang->getLang("cache_apc", "system"),
            CacheManager::TYPE_FILESYSTEM => $objLang->getLang("cache_filesystem", "system"),
        );

        $arrKeys = array(
            DoctrineCache::STATS_HITS => $objLang->getLang("cache_hits", "system"),
            DoctrineCache::STATS_MISSES => $objLang->getLang("cache_misses", "system"),
            DoctrineCache::STATS_UPTIME => $objLang->getLang("cache_uptime", "system"),
            DoctrineCache::STATS_MEMORY_USAGE => $objLang->getLang("cache_usage", "system"),
            DoctrineCache::STATS_MEMORY_AVAILABLE => $objLang->getLang("cache_available", "system"),
        );

        foreach ($arrTypes as $intType => $strType) {
            $arrStats = CacheManager::getInstance()->getStats($intType);
            if (!empty($arrStats)) {
                $arrReturn[] = array("<b>" . $strType . "</b>", "");
                foreach ($arrKeys as $intKey => $strDescription) {
                    if (isset($arrStats[$intKey])) {
                        if ($intKey == DoctrineCache::STATS_MEMORY_USAGE || $intKey == DoctrineCache::STATS_MEMORY_AVAILABLE) {
                            $strValue = bytesToString($arrStats[$intKey]);
                        } elseif ($intKey == DoctrineCache::STATS_UPTIME) {
                            $strValue = dateToString(new Date($arrStats[$intKey]));
                        } else {
                            $strValue = $arrStats[$intKey];
                        }
                        $arrReturn[] = array($strDescription, $strValue);
                    }
                }
            }
        }

        return $arrReturn;
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public static function getExtensionName()
    {
        return SysteminfoInterface::STR_EXTENSION_POINT;
    }

}
