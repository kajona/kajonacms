<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/


/**
 * Syncs the ldap-userbase with the data from the directory
 *
 * @package module_ldap
 * @author sidler@mulchprod.de
 */
class class_systemtask_ldapsync extends class_systemtask_base implements interface_admin_systemtask {

	/**
	 * contructor to call the base constructor
	 */
	public function __construct() {
		parent::__construct();
        
        $this->setStrTextBase("ldap");
    }
    
    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string 
     */
    public function getGroupIdentifier() {
        return "ldap";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
    	return "ldapsync";
    }
    
    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getLang("systemtask_ldapsync_name");
    }
    
    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {

        if(!class_module_system_module::getModuleByName("ldap")->rightEdit())
            return $this->getLang("commons_error_permissions");

        $objUsersources = new class_usersources_source_ldap();
        $bitSync = $objUsersources->updateUserData();
        
        
    	if($bitSync)
            return $this->objToolkit->getTextRow($this->getLang("systemtask_ldapsync_success"));
        else
            return $this->objToolkit->getTextRow($this->getLang("systemtask_ldapsync_error"));
    }

    
}
