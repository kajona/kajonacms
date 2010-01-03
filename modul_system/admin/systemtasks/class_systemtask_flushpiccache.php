<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                          *
********************************************************************************************************/


/**
 * Flushes all images saved to the cache
 *
 * @package modul_system
 */
class class_systemtask_flushpiccache extends class_systemtask_base implements interface_admin_systemtask {


	/**
	 * contructor to call the base constructor
	 */
	public function __construct() {
		parent::__construct();
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
    	return "flushpiccache";
    }
    
    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getText("systemtask_flushpiccache_name");
    }
    
    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
    	$strReturn = "";
    	//fetch the number of images to be deleted
    	$objFilesystem = new class_filesystem();
    	$arrFiles = $objFilesystem->getFilelist(_images_cachepath_, array());
    	$intFilesDeleted = 0;
    	$intTotalFiles = count($arrFiles);
    	foreach($arrFiles as $strOneFile) {
    		if($objFilesystem->fileDelete(_images_cachepath_."/".$strOneFile))
    		  $intFilesDeleted++;
    	}
    	
    	//build the return string
    	$strReturn .= $this->getText("systemtask_flushpiccache_done");
    	$strReturn .= $this->getText("systemtask_flushpiccache_deleted").$intFilesDeleted;
    	$strReturn .= $this->getText("systemtask_flushpiccache_skipped").($intTotalFiles - $intFilesDeleted);
    	return $strReturn;
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