<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                          *
********************************************************************************************************/


/**
 * Flushes all images saved to the cache
 *
 * @package module_system
 */
class class_systemtask_flushpiccache extends class_systemtask_base implements interface_admin_systemtask {


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
    	return "flushpiccache";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getLang("systemtask_flushpiccache_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
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
    	$strReturn .= $this->getLang("systemtask_flushpiccache_done");
    	$strReturn .= $this->getLang("systemtask_flushpiccache_deleted").$intFilesDeleted;
    	$strReturn .= $this->getLang("systemtask_flushpiccache_skipped").($intTotalFiles - $intFilesDeleted);
    	return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
    	return "";
    }

}
