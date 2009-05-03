<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

//base class and interface
include_once(_adminpath_."/systemtasks/class_systemtask_base.php");
include_once(_adminpath_."/systemtasks/interface_admin_systemtask.php");

/**
 * Restores the database from the filesystem using the current db-driver
 *
 * @package modul_system
 */
class class_systemtask_dbimport extends class_systemtask_base implements interface_admin_systemtask {


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
        return "database";
    }
    
    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "dbimport";
    }
    
    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getText("systemtask_dbimport_name");
    }
    
    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
        if(class_carrier::getInstance()->getObjDB()->importDb($this->getParam("dbImportFile")))
            return $this->objToolkit->getTextRow($this->getText("systemtask_dbimport_success"));
        else
            return $this->objToolkit->getTextRow($this->getText("systemtask_dbimport_error"));
    }

    /**
     * @see interface_admin_systemtast::getAdminForm()
     * @return string 
     */
    public function getAdminForm() {
    	$strReturn = "";
        //show dropdown to select db-dump
        include_once(_systempath_."/class_filesystem.php");
        $objFilesystem = new class_filesystem();
        $arrFiles = $objFilesystem->getFilelist("/system/dbdumps/", array(".sql", ".gz"));
        $arrOptions = array();
        foreach($arrFiles as $strOneFile)
            $arrOptions[$strOneFile] = $strOneFile;

        $strReturn .= $this->objToolkit->formInputDropdown("dbImportFile", $arrOptions, $this->getText("systemtask_dbimport_file"));
         
        return $strReturn;
    }
    
}
?>