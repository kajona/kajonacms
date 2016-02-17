<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Ldap\System\Workflows;

use class_carrier;
use \Kajona\System\System\Date;
use class_module_workflows_workflow;
use class_usersources_source_ldap;
use interface_workflows_handler;


/**
 * triggers the internal sync of the ldap-userbase
 *
 * @package module_ldap
 */
class WorkflowLdapSync implements interface_workflows_handler {


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

    }

    /**
     * @see interface_workflows_handler::getDefaultValues()
     * @return string[]
     */
    public function getDefaultValues() {
        return array(); // by default there are 24h between each sync
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
        $objDate = new \Kajona\System\System\Date();
        $objDate->setNextDay();
        $objDate->setIntHour(3);
        $objDate->setIntMin(20);
        $this->objWorkflow->setObjTriggerdate($objDate);
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
