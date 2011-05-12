<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_systemtask_filedump.php 3714 2011-04-01 13:21:01Z sidler $                                        *
********************************************************************************************************/


/**
 * Creates a zip-archive of all relevant folders in the system
 *
 * @package modul_system
 */
class class_systemtask_filedump extends class_systemtask_base implements interface_admin_systemtask {

    private $arrFoldersToInclude = array(
        "/system/config",
        "/portal/pics/upload",
        "/portal/downloads",
        "/portal/css",
        "/templates"
    );

    
    private $arrFilesToInclude = array(
        "/portal/global_includes.php",
        "/.htaccess"
    );
    
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
        return "";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
    	return "filedump";
    }
    
    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getText("systemtask_filedump_name");
    }
    
    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {

        $strFilename = "/backup_".time().".zip";
        
        $objZip = new class_zip();
        $objZip->openArchiveForWriting($strFilename);
        foreach($this->arrFoldersToInclude as $strOneFolder) {
            $objZip->addFolder($strOneFolder);
        } 
        
        foreach($this->arrFilesToInclude as $strOneFile) {
            $objZip->addFile($strOneFile);
        }
        
        if($objZip->closeArchive())
            return $this->getText("systemtask_filedump_success").$strFilename;    
        else
            return $this->getText("systemtask_filedump_error");    
    }

    
}
?>