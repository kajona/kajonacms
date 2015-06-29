<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * Flushes the entries from the systemwide cache
 *
 * @package module_system
 */
class class_systemtask_flushcache extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "cache";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "flushcache";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_flushcache_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {

        if(!class_module_system_module::getModuleByName("system")->rightRight2())
            return $this->getLang("commons_error_permissions");

        //increase the cachebuster, so browsers are forced to reload JS and CSS files
        $objCachebuster = class_module_system_setting::getConfigByName("_system_browser_cachebuster_");
        $objCachebuster->setStrValue((int)$objCachebuster->getStrValue() + 1);
        $objCachebuster->updateObjectToDb();

        if(class_cache::flushCache($this->getParam("cacheSource"))) {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_flushcache_success"));
        }
        else {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_flushcache_error"));
        }
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
        $strReturn = "";
        //show dropdown to select cache-source
        $arrSources = class_cache::getCacheSources();
        $arrOptions = array();
        $arrOptions[""] = $this->getLang("systemtask_flushcache_all");
        foreach($arrSources as $strOneSource) {
            $arrOptions[$strOneSource] = $strOneSource;
        }

        $strReturn .= $this->objToolkit->formInputDropdown("cacheSource", $arrOptions, $this->getLang("systemtask_cacheSource_source"));

        return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getSubmitParams()
     * @return string
     */
    public function getSubmitParams() {
        return "&cacheSource=" . $this->getParam("cacheSource");
    }
}
