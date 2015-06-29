<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/

/**
 * Triggers the execution of the workflow-engine
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 */
class class_systemtask_workflows extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();

        $this->setStrTextBase("workflows");
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
        return "runworkflows";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_runworkflows_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {

        $objWorkflowController = new class_workflows_controller();

        $objWorkflowController->scheduleWorkflows();
        $objWorkflowController->runWorkflows();
        

        return "";
    }

}
