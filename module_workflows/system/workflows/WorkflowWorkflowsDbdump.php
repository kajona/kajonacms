<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Workflows\System\Workflows;

use Kajona\System\System\Carrier;
use Kajona\Workflows\System\WorkflowsHandlerInterface;
use Kajona\Workflows\System\WorkflowsWorkflow;


/**
 * Workflow to create a dbdump in a regular interval, by default configured for 24h
 *
 * @package module_workflows
 */
class WorkflowWorkflowsDbdump implements WorkflowsHandlerInterface
{

    private $intIntervalHours = 24;

    /**
     * @var WorkflowsWorkflow
     */
    private $objWorkflow = null;

    /**
     * @see interface_workflows_handler::getConfigValueNames()
     */
    public function getConfigValueNames()
    {
        return array(
            Carrier::getInstance()->getObjLang()->getLang("workflow_dbdump_val1", "workflows")
        );
    }

    /**
     * @see interface_workflows_handler::setConfigValues()
     */
    public function setConfigValues($strVal1, $strVal2, $strVal3)
    {
        if ($strVal1 != "" && is_numeric($strVal1)) {
            $this->intIntervalHours = $strVal1;
        }

    }

    /**
     * @see interface_workflows_handler::getDefaultValues()
     */
    public function getDefaultValues()
    {
        return array(24); // by default there are 24h between each dbdump
    }

    public function setObjWorkflow($objWorkflow)
    {
        $this->objWorkflow = $objWorkflow;
    }

    public function getStrName()
    {
        return Carrier::getInstance()->getObjLang()->getLang("workflow_dbdumps_title", "workflows");
    }


    public function execute()
    {

        $objDB = Carrier::getInstance()->getObjDB();
        $objDB->dumpDb();

        //trigger again
        return false;

    }

    public function onDelete()
    {

    }


    public function schedule()
    {

        $newTriggerdate = $this->objWorkflow->getObjTriggerdate()->getTimeInOldStyle();
        $newTriggerdate = $newTriggerdate + $this->intIntervalHours * 3600;

        $this->objWorkflow->setObjTriggerdate(new \Kajona\System\System\Date($newTriggerdate));

    }

    public function getUserInterface()
    {

    }

    public function processUserInput($arrParams)
    {
        return;

    }

    public function providesUserInterface()
    {
        return false;
    }


}
