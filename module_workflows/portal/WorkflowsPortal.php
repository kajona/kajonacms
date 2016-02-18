<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

namespace Kajona\Workflows\Portal;

use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\SystemSetting;
use Kajona\Workflows\System\WorkflowsController;


/**
 * Portal-class of the workflows module. Used to provide an access point to trigger the workflow-engine.
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 *
 * @module workflows
 * @moduleId _workflows_module_id_
 */
class WorkflowsPortal extends PortalController implements PortalInterface
{

    /**
     * Default implementation to avoid mail-spamming.
     *
     * @return void
     */
    protected function actionList()
    {

    }


    /**
     * Triggers the workflow engine
     *
     * @xml
     * @return string
     */
    protected function actionTrigger()
    {
        Carrier::getInstance()->getObjSession()->setBitBlockDbUpdate(true);
        if ($this->getParam("authkey") == SystemSetting::getConfigValue("_workflows_trigger_authkey_")) {
            $objWorkflowController = new WorkflowsController();
            $objWorkflowController->scheduleWorkflows();
            $objWorkflowController->runWorkflows();

            return "<message>Execution successful</message>";
        }


        ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
        return "<message><error>Not authorized</error></message>";
    }


}
