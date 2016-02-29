<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;

use Kajona\System\System\Cache;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;


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

        if (Cache::flushCache($this->getParam("cacheSource"))) {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_flushcache_success"));
        }
        else {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_flushcache_error"));
        }
    }

    /**
     * @inheritdoc
     */
    public function getAdminForm()
    {
        $strReturn = "";
        //show dropdown to select cache-source
        $arrSources = Cache::getCacheSources();
        $arrOptions = array();
        $arrOptions[""] = $this->getLang("systemtask_flushcache_all");
        foreach ($arrSources as $strOneSource) {
            $arrOptions[$strOneSource] = $strOneSource;
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
