<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

/**
 * Portal-class of the workflows module. Used to provide an access point to trigger the workflow-engine.
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 *
 * @module workflows
 * @moduleId _workflows_module_id_
 */
class class_module_workflows_portal extends class_portal_controller implements interface_portal {

    /**
     * Default implementation to avoid mail-spamming.
     * @return void
     */
    protected function actionList() {

    }



    /**
     * Triggers the workflow engine
     *
     * @xml
     * @return string
     */
    protected function actionTrigger() {
        class_carrier::getInstance()->getObjSession()->setBitBlockDbUpdate(true);
        if($this->getParam("authkey") == class_module_system_setting::getConfigValue("_workflows_trigger_authkey_")) {
            $objWorkflowController = new class_workflows_controller();
            $objWorkflowController->scheduleWorkflows();
            $objWorkflowController->runWorkflows();

            return "<message>Execution successful</message>";
        }


        class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
        return "<message><error>Not authorized</error></message>";
    }


}
