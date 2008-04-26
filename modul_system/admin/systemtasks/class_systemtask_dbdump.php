<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_systemtask_dbdump.php                                                                         *
*   Dumps the database using the current db-driver                                                      *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_login_admin.php 1884 2007-12-26 15:04:48Z sidler $                                        *
********************************************************************************************************/

//base class and interface
include_once(_adminpath_."/systemtasks/class_systemtask_base.php");
include_once(_adminpath_."/systemtasks/interface_admin_systemtask.php");

/**
 * Dumps the database to the filesystem using the current db-driver
 *
 * @package modul_system
 */
class class_systemtask_dbdump extends class_systemtask_base implements interface_admin_systemtask {


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
    	return "dbdump";
    }
    
    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getText("systemtask_dbexport_name");
    }
    
    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
    	if(class_carrier::getInstance()->getObjDB()->dumpDb())
            return $this->objToolkit->getTextRow($this->getText("systemtask_dbexport_success"));
        else
            return $this->objToolkit->getTextRow($this->getText("systemtask_dbexport_error"));
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