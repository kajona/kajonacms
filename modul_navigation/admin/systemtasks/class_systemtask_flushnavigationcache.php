<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_systemtask_flushpagescache.php 2353 2008-12-31 15:22:01Z sidler $                        *
********************************************************************************************************/

//base class and interface
include_once(_adminpath_."/systemtasks/class_systemtask_base.php");
include_once(_adminpath_."/systemtasks/interface_admin_systemtask.php");

/**
 * Flushes the cache holding generated pages
 *
 * @package modul_navigation
 */
class class_systemtask_flushnavigationcache extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setStrTextBase("navigation");
    }

    /**
     * @see interface_admin_systemtast::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "cache";
    }
    
    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "flushnavigationcache";
    }
    
    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getText("systemtask_flushnavigationcache_name");
    }
    
    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
        include_once(_systempath_."/class_modul_navigation_cache.php");
        class_modul_navigation_cache::flushCache();
        return $this->getText("systemtask_flushnavigationcache_done");
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string 
     */
    public function getAdminForm() {
        return "";
    }
    
}
?>