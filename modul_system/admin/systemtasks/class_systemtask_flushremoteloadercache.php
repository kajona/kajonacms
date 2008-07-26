<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_systemtask_flushremoteloadercache.php                                                         *
*   Deletes all cached remote-responses                                                                 *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_systemtask_flushremoteloadercache.php 2076 2008-06-17 11:27:21Z sidler $                 *
********************************************************************************************************/

//base class and interface
include_once(_adminpath_."/systemtasks/class_systemtask_base.php");
include_once(_adminpath_."/systemtasks/interface_admin_systemtask.php");
include_once(_systempath_."/class_remoteloader.php");

/**
 * Flushes all images saved to the cache
 *
 * @package modul_system
 */
class class_systemtask_flushremoteloadercache extends class_systemtask_base implements interface_admin_systemtask {


	/**
	 * contructor to call the base constructor
	 */
	public function __construct() {
		parent::__construct();
    }
    
    
    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
    	return "flushremoteloadercache";
    }
    
    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getText("systemtask_flushremoteloadercache_name");
    }
    
    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
    	$objCache = new class_remoteloader();
    	$objCache->flushCache();
    	return $this->getText("systemtask_flushremoteloadercache_done");
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