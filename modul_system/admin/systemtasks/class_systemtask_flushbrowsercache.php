<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_systemtask_flushcache.php 3387 2010-08-27 19:32:51Z sidler $                                        *
********************************************************************************************************/

/**
 * Increases the system constant _system_browser_cachebuster_ which will be added as a GET parameter
 * to all JS and CSS file requests. By changing the param, browsers are forced to reload the JS and CSS files
 * regardless of the browsers cache configuration and sent HTTP headers.
 *
 * @package modul_system
 */
class class_systemtask_flushbrowsercache extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();
    }

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
        return "flushbrowsercache";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getText("systemtask_flushbrowsercache_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {
        //increase the cachebuster, so browsers are forced to reload JS and CSS files
        $objCachebuster = class_modul_system_setting::getConfigByName("_system_browser_cachebuster_");
        $objCachebuster->setStrValue((int)$objCachebuster->getStrValue()+1);
        $objCachebuster->updateObjectToDb();

        return $this->objToolkit->getTextRow($this->getText("systemtask_flushbrowsercache_success"));
    }

}
?>