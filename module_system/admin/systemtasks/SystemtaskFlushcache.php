<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;

use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\CacheManager;

/**
 * Flushes the entries from the systemwide cache
 *
 * @package module_system
 */
class SystemtaskFlushcache extends SystemtaskBase implements AdminSystemtaskInterface
{


    /**
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "cache";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "flushcache";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_flushcache_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("system")->rightRight2()) {
            return $this->getLang("commons_error_permissions");
        }

        //increase the cachebuster, so browsers are forced to reload JS and CSS files
        $objCachebuster = SystemSetting::getConfigByName("_system_browser_cachebuster_");
        $objCachebuster->setStrValue((int)$objCachebuster->getStrValue() + 1);
        $objCachebuster->updateObjectToDb();

        $intType = (int) $this->getParam("cacheSource");
        if ($intType > 0) {
            CacheManager::getInstance()->flushCache($intType);

            return $this->objToolkit->getTextRow($this->getLang("systemtask_flushcache_success"));
        }

        return $this->objToolkit->getTextRow($this->getLang("systemtask_flushcache_error"));
    }

    /**
     * @inheritdoc
     */
    public function getAdminForm()
    {
        $strReturn = "";
        //show dropdown to select cache-source
        $arrSources = CacheManager::getAvailableDriver();
        $arrOptions = array();
        $arrOptions[CacheManager::TYPE_APC | CacheManager::TYPE_FILESYSTEM | CacheManager::TYPE_DATABASE | CacheManager::TYPE_PHPFILE] = $this->getLang("systemtask_flushcache_all");
        foreach($arrSources as $intValue => $strLabel) {
            $arrOptions[$intValue] = $strLabel;
        }

        $strReturn .= $this->objToolkit->formInputDropdown("cacheSource", $arrOptions, $this->getLang("systemtask_cacheSource_source"));

        return $strReturn;
    }

    /**
     * @inheritdoc
     */
    public function getSubmitParams()
    {
        return "&cacheSource=".$this->getParam("cacheSource");
    }
}
