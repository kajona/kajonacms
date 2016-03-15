<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/

namespace Kajona\Workflows\Admin\Systemtasks;

use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\SystemModule;
use Kajona\Workflows\System\WorkflowsController;


/**
 * Triggers the execution of the workflow-engine
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 */
class SystemtaskWorkflows extends SystemtaskBase implements AdminSystemtaskInterface
{


    /**
     * contructor to call the base constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setStrTextBase("workflows");
    }

    /**
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "runworkflows";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_runworkflows_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("workflows")->rightRight1()) {
            return $this->getLang("commons_error_permissions");
        }


        $objWorkflowController = new WorkflowsController();

        $objWorkflowController->scheduleWorkflows();
        $objWorkflowController->runWorkflows();


        return "";
    }

}
