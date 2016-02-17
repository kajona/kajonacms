<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                          *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;



/**
 * Flushes all images saved to the cache
 *
 * @package module_system
 */
class SystemtaskFlushpiccache extends class_systemtask_base implements interface_admin_systemtask {


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

        if(!class_module_system_module::getModuleByName("system")->rightRight2())
            return $this->getLang("commons_error_permissions");

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
