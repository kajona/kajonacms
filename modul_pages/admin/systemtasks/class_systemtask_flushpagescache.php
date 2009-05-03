<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                        *
********************************************************************************************************/

//base class and interface
include_once(_adminpath_."/systemtasks/class_systemtask_base.php");
include_once(_adminpath_."/systemtasks/interface_admin_systemtask.php");

/**
 * Flushes the cache holding generated pages
 *
 * @package modul_system
 */
class class_systemtask_flushpagescache extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setStrTextBase("pages");
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
        return "flushpagescache";
    }
    
    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getText("systemtask_flushpagescache_name");
    }
    
    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
        include_once(_systempath_."/class_modul_pages_pagecache.php");
        $objPagecache = new class_modul_pages_pagecache();
        $objPagecache->flushCompletePagesCache();
        return $this->getText("systemtask_flushpagescache_done");
    }

    /**
     * @see interface_admin_systemtast::getAdminForm()
     * @return string 
     */
    public function getAdminForm() {
        return "";
    }
    
}
?>