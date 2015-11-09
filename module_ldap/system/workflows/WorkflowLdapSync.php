<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Ldap\System\Workflows;

use class_carrier;
use class_date;
use class_module_workflows_workflow;
use class_usersources_source_ldap;
use interface_workflows_handler;


/**
 * triggers the internal sync of the ldap-userbase
 *
 * @package module_ldap
 */
class WorkflowLdapSync implements interface_workflows_handler {

    private $intIntervalHours = 24;
    
    /**
     * @var class_module_workflows_workflow
     */
    private $objWorkflow = null;

    /**
     * @see interface_workflows_handler::getConfigValueNames()
     * @return string[]
     */
    public function getConfigValueNames() {
        return array(
            class_carrier::getInstance()->getObjLang()->getLang("workflow_ldapsync_val1", "ldap")
        );
    }

    /**
     * @param string $strVal1
     * @param string $strVal2
     * @param string $strVal3
     *
     * @see interface_workflows_handler::setConfigValues()
     * @return void
     */
    public function setConfigValues($strVal1, $strVal2, $strVal3) {
        if($strVal1 != "" && is_numeric($strVal1))
            $this->intIntervalHours = $strVal1;

    }

    /**
     * @see interface_workflows_handler::getDefaultValues()
     * @return string[]
     */
    public function getDefaultValues() {
        return array(24); // by default there are 24h between each sync
    }

    /**
     * @param class_module_workflows_workflow $objWorkflow
     * @return void
     */
    public function setObjWorkflow($objWorkflow) {
        $this->objWorkflow = $objWorkflow;
    }

    /**
     * @return string
     */
    public function getStrName() {
        return class_carrier::getInstance()->getObjLang()->getLang("workflow_ldapsync_title", "ldap");
    }


    /**
     * @return bool
     */
    public function execute() {
        $objUsersources = new class_usersources_source_ldap();
        $objUsersources->updateUserData();

        //trigger again
        return false;
    }

    /**
     * void implementation
     * @return void
     */
    public function onDelete() {

    }


    /**
     * schedule the workflow
     * @return void
     */
    public function schedule() {
        $this->objWorkflow->setObjTriggerdate(new class_date(time() - 30 + $this->intIntervalHours * 36000));
    }

    /**
     * void implementation
     * @return void
     */
    public function getUserInterface() {
       
    }

    /**
     * @param array $arrParams
     * @return void
     */
    public function processUserInput($arrParams) {
        return;

    }

    /**
     * @return bool
     */
    public function providesUserInterface() {
        return false;
    }


    
}
