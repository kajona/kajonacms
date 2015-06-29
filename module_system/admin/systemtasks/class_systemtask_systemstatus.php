<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * A systemtask to set the status of a given record
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_systemtask_systemstatus extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "database";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "systemstatus";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_systemstatus_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {

        if(!class_module_system_module::getModuleByName("system")->rightRight2())
            return $this->getLang("commons_error_permissions");

        //try to load and update the systemrecord
        if(validateSystemid($this->getParam("systemstatus_systemid"))) {
            $objRecord = new class_module_system_common($this->getParam("systemstatus_systemid"));
            $objRecord->setIntRecordStatus($this->getParam("systemstatus_status"));
            $objRecord->updateObjectToDb();

            return $this->objToolkit->getTextRow($this->getLang("systemtask_status_success"));
        }

        return $this->objToolkit->getTextRow($this->getLang("systemtask_status_error"));
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
    	$strReturn = "";

        $arrDropdown = array(
            1 => $this->getLang("systemtask_systemstatus_active"),
            0 => $this->getLang("systemtask_systemstatus_inactive")
        );

        $strReturn .= $this->objToolkit->formInputText("systemstatus_systemid", $this->getLang("systemtask_systemstatus_systemid"));
        $strReturn .= $this->objToolkit->formInputDropdown("systemstatus_status", $arrDropdown, $this->getLang("systemtask_systemstatus_status"));

        return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getSubmitParams()
     * @return string
     */
    public function getSubmitParams() {
        return "&systemstatus_systemid=".$this->getParam("systemstatus_systemid")."&systemstatus_status=".$this->getParam("systemstatus_status");
    }
}
