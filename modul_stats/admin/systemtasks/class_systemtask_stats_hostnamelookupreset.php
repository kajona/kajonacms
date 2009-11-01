<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * Resets erroneous hostnames
 *
 * @package modul_stats
 */
class class_systemtask_stats_hostnamelookupreset extends class_systemtask_base implements interface_admin_systemtask {


	/**
	 * contructor to call the base constructor
	 */
	public function __construct() {
		parent::__construct();
        $this->setStrTextBase("stats");
    }
    
    /**
     * @see interface_admin_systemtast::getGroupIdenitfier()
     * @return string 
     */
    public function getGroupIdentifier() {
        return "stats";
    }

    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
    	return "statshostnamelookupreset";
    }
    
    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
    	return $this->getText("systemtask_hostnamelookupreset_name");
    }
    
    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
        $strReturn = "";
        $objWorker = new class_modul_stats_worker("");
        $objWorker->hostnameLookupResetHostnames();

        $strReturn .= $this->objToolkit->getTextRow($this->getText("worker_lookupReset_end"));

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