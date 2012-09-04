<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_workflow_ldap_sync.php 4743 2012-06-28 11:31:38Z sidler $                               *
********************************************************************************************************/

/**
 * triggers the internal sync of the ldap-userbase
 *
 * @package module_ldap
 */
class class_workflow_ldap_sync implements interface_workflows_handler  {

    private $intIntervalHours = 24;
    
    /**
     * @var class_module_workflows_workflow
     */
    private $objWorkflow = null;

    /**
     * @see interface_workflows_handler::getConfigValueNames()
     */
    public function getConfigValueNames() {
        return array(
            class_carrier::getInstance()->getObjLang()->getLang("workflow_ldapsync_val1", "ldap", "admin")
        );
    }

    /**
     * @see interface_workflows_handler::setConfigValues()
     */
    public function setConfigValues($strVal1, $strVal2, $strVal3) {
        if($strVal1 != "" && is_numeric($strVal1))
            $this->intIntervalHours = $strVal1;

    }

    /**
     * @see interface_workflows_handler::getDefaultValues()
     */
    public function getDefaultValues() {
        return array(24); // by default there are 24h between each sync
    }
    
    public function setObjWorkflow($objWorkflow) {
        $this->objWorkflow = $objWorkflow;
    }

    public function getStrName() {
        return class_carrier::getInstance()->getObjLang()->getLang("workflow_ldapsync_title", "ldap", "admin");
    }
    

    public function execute() {

        $objUsersources = new class_usersources_source_ldap();
        $objUsersources->updateUserData();

        //trigger again
        return false;

    }

    public function onDelete() {

    }


    public function schedule() {
        $this->objWorkflow->setObjTriggerdate(new class_date(time() - 30 + $this->intIntervalHours * 36000));
    }

    public function getUserInterface() {
       
    }

    public function processUserInput($arrParams) {
        return;

    }

    public function providesUserInterface() {
        return false;
    }


    
}
