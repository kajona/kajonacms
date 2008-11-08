<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                          *
********************************************************************************************************/

//base class and interface
include_once(_adminpath_."/systemtasks/class_systemtask_base.php");
include_once(_adminpath_."/systemtasks/interface_admin_systemtask.php");

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
    	include_once(_systempath_."/class_filesystem.php");
    	$objFilesystem = new class_filesystem();
    	$arrFiles = $objFilesystem->getFilelist(_bildergalerie_cachepfad_, array());
    	$intFilesDeleted = 0;
    	$intTotalFiles = count($arrFiles);
    	foreach($arrFiles as $strOneFile) {
    		if($objFilesystem->fileDelete(_bildergalerie_cachepfad_."/".$strOneFile))
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