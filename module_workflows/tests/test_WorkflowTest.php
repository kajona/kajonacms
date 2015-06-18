<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


require_once (__DIR__."/../../../core/module_system/system/class_testbase.php");

class test_Workflow  extends class_testbase {

    /**
     * Tests method getWorkflowsForSystemid with existing workflow objects
     */
    public function test_getWorkflowsForSystemid_1() {
        $arrWorkflows = class_module_workflows_workflow::getAllworkflows();
        $arrMap = array();

        //1. Collect all workflows for all objects
        /** @var  class_module_workflows_workflow */
        foreach($arrWorkflows as $objWorkflow) {
            $strAffectedSystemId = $objWorkflow->getStrAffectedSystemid();
            if(!validateSystemid($strAffectedSystemId)) {
                continue;
            }

            $strWorkflowClass = $objWorkflow->getStrClass();

            if(!array_key_exists($strAffectedSystemId, $arrMap)) {
                $arrMap[$strAffectedSystemId] = array();
            }

            if(!array_key_exists($strWorkflowClass, $arrMap[$strAffectedSystemId])) {
                $arrMap[$strAffectedSystemId][$strWorkflowClass] = 1;
            }
            else {
                $arrMap[$strAffectedSystemId][$strWorkflowClass]++;
            }
        }

        //2. Now assert
        foreach($arrMap as $strSystemId => $arrClasses) {
            $arrWorkflows = class_module_workflows_workflow::getWorkflowsForSystemid($strSystemId, false, array_keys($arrClasses));
            $this->assertEquals(count($arrWorkflows), array_sum($arrClasses));

            $arrWorkflows = class_module_workflows_workflow::getWorkflowsForSystemid($strSystemId, false);
            $this->assertEquals(count($arrWorkflows), array_sum($arrClasses));

            foreach($arrMap[$strSystemId] as $strClass => $intCount) {
                $arrWorkflows = class_module_workflows_workflow::getWorkflowsForSystemid($strSystemId, false, $strClass);
                $this->assertEquals(count($arrWorkflows), $intCount);

                $arrWorkflows = class_module_workflows_workflow::getWorkflowsForSystemid($strSystemId, false, array($strClass));
                $this->assertEquals(count($arrWorkflows), $intCount);
            }
        }
    }


    /**
     * Tests method getWorkflowsForSystemid with newly created workflow objects
     */
    public function test_getWorkflowsForSystemid_2() {

        //1 Init settings
        $strSystemId1 = generateSystemid();
        $strSystemId2 = generateSystemid();
        $arrWorkflowsClasses =
            array(
                array("class" => "class_workflow_workflows_messagesummary", "systemid" => $strSystemId1, "amount" => 5),
                array("class" => "class_workflow_workflows_messagesummary","systemid" => $strSystemId2, "amount" => 5),
                array("class" => "class_workflow_workflows_dbdump","systemid" => $strSystemId2, "amount" => 23)
        );


        //2. Create the workflow objects
        $arrCreatedWorkflows = array();
        foreach($arrWorkflowsClasses as $arrInfo) {
            for($intI = 0; $intI < $arrInfo["amount"]; $intI++) {
                $objWorkflow = new class_module_workflows_workflow();
                $objWorkflow->setStrClass($arrInfo["class"]);
                $objWorkflow->setStrAffectedSystemid($arrInfo["systemid"]);
                $objWorkflow->updateObjectToDb();
                $arrCreatedWorkflows[] = $objWorkflow;
            }
        }


        $this->flushDBCache();

        //3. Assert number of workflows
        foreach($arrWorkflowsClasses as $arrInfo) {
            $arrWorkflows = class_module_workflows_workflow::getWorkflowsForSystemid($arrInfo["systemid"], false, $arrInfo["class"]);
            $this->assertEquals(count($arrWorkflows), $arrInfo["amount"]);

            $arrWorkflows = class_module_workflows_workflow::getWorkflowsForSystemid($arrInfo["systemid"], false, array($arrInfo["class"]));
            $this->assertEquals(count($arrWorkflows), $arrInfo["amount"]);
        }

        $arrWorkflows = class_module_workflows_workflow::getWorkflowsForSystemid($strSystemId1, false, array("class_workflow_workflows_messagesummary"));
        $this->assertEquals(count($arrWorkflows), 5);
        $arrWorkflows = class_module_workflows_workflow::getWorkflowsForSystemid($strSystemId2, false, array("class_workflow_workflows_dbdump", "class_workflow_workflows_messagesummary"));
        $this->assertEquals(count($arrWorkflows), 28);


        //4. Delete created workflow objects
        /** @var class_module_workflows_workflow $objWorkflow*/
        foreach($arrCreatedWorkflows as $objWorkflow) {
            $objWorkflow->deleteObjectFromDatabase();
        }

        $this->resetCaches();
    }


    public function test_getWorkflowsForSystemid() {
        //execute test case with invlaid systemid
        $arrReturn = class_module_workflows_workflow::getWorkflowsForSystemid("ddd", false, array("clas_workflow_workflows_messagesummary"));
        $this->assertEquals(0, count($arrReturn));
    }
}