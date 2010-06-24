<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/


/**
 * Dumps the database to the filesystem using the current db-driver
 *
 * @package modul_system
 */
class class_systemtask_dbdump extends class_systemtask_base implements interface_admin_systemtask {

    private $arrTablesToExlucde = array(
        "stats_data", "stats_ip2country", "cache"
    );

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

        $arrToExclude = array();
        if($this->getParam("excludeTables") == "1")
            $arrToExclude = $this->arrTablesToExlucde;

    	if(class_carrier::getInstance()->getObjDB()->dumpDb($arrToExclude))
            return $this->objToolkit->getTextRow($this->getText("systemtask_dbexport_success"));
        else
            return $this->objToolkit->getTextRow($this->getText("systemtask_dbexport_error"));
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string 
     */
    public function getAdminForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formTextRow($this->getText("systemtask_dbexport_exclude_intro"));
        $strReturn .= $this->objToolkit->formInputDropdown("dbExcludeTables", array(0 => $this->getText("systemtask_dbexport_include"), 1 => $this->getText("systemtask_dbexport_exclude")), $this->getText("systemtask_dbexport_excludetitle") );
    	return $strReturn;
    }

    /**
     * @see interface_admin_systemtast::getSubmitParams()
     * @return string
     */
    public function getSubmitParams() {
        return "&excludeTables=".$this->getParam("dbExcludeTables");
    }
    
}
?>