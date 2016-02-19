<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Workflows\Test;
require_once __DIR__."/../../../core/module_system/system/Testbase.php";
use Kajona\System\System\Testbase;
use Kajona\Workflows\System\WorkflowsWorkflow;

class WorkflowTest  extends Testbase {

    /**
     * Tests method getWorkflowsForSystemid with existing workflow objects
     */
    public function test_getWorkflowsForSystemid_1() {
        $arrWorkflows = WorkflowsWorkflow::getAllworkflows();
        $arrMap = array();

        //1. Collect all workflows for all objects
        /** @var  WorkflowsWorkflow */
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
            $arrWorkflows = WorkflowsWorkflow::getWorkflowsForSystemid($strSystemId, false, array_keys($arrClasses));
            $this->assertEquals(count($arrWorkflows), array_sum($arrClasses));

            $arrWorkflows = WorkflowsWorkflow::getWorkflowsForSystemid($strSystemId, false);
            $this->assertEquals(count($arrWorkflows), array_sum($arrClasses));

            foreach($arrMap[$strSystemId] as $strClass => $intCount) {
                $arrWorkflows = WorkflowsWorkflow::getWorkflowsForSystemid($strSystemId, false, $strClass);
                $this->assertEquals(count($arrWorkflows), $intCount);

                $arrWorkflows = WorkflowsWorkflow::getWorkflowsForSystemid($strSystemId, false, array($strClass));
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
                array("class" => "Kajona\\Workflows\\System\\Workflows\\WorkflowWorkflowsMessagesummary", "systemid" => $strSystemId1, "amount" => 5),
                array("class" => "Kajona\\Workflows\\System\\Workflows\\WorkflowWorkflowsMessagesummary","systemid" => $strSystemId2, "amount" => 5),
                array("class" => "Kajona\\Workflows\\System\\Workflows\\WorkflowWorkflowsDbdump","systemid" => $strSystemId2, "amount" => 23)
        );


        //2. Create the workflow objects
        $arrCreatedWorkflows = array();
        foreach($arrWorkflowsClasses as $arrInfo) {
            for($intI = 0; $intI < $arrInfo["amount"]; $intI++) {
                $objWorkflow = new WorkflowsWorkflow();
                $objWorkflow->setStrClass($arrInfo["class"]);
                $objWorkflow->setStrAffectedSystemid($arrInfo["systemid"]);
                $objWorkflow->updateObjectToDb();
                $arrCreatedWorkflows[] = $objWorkflow;
            }
        }


        $this->flushDBCache();

        //3. Assert number of workflows
        foreach($arrWorkflowsClasses as $arrInfo) {
            $arrWorkflows = WorkflowsWorkflow::getWorkflowsForSystemid($arrInfo["systemid"], false, $arrInfo["class"]);
            $this->assertEquals(count($arrWorkflows), $arrInfo["amount"]);

            $arrWorkflows = WorkflowsWorkflow::getWorkflowsForSystemid($arrInfo["systemid"], false, array($arrInfo["class"]));
            $this->assertEquals(count($arrWorkflows), $arrInfo["amount"]);
        }

        $arrWorkflows = WorkflowsWorkflow::getWorkflowsForSystemid($strSystemId1, false, array("Kajona\\Workflows\\System\\Workflows\\WorkflowWorkflowsMessagesummary"));
        $this->assertEquals(count($arrWorkflows), 5);
        $arrWorkflows = WorkflowsWorkflow::getWorkflowsForSystemid($strSystemId2, false, array("Kajona\\Workflows\\System\\Workflows\\WorkflowWorkflowsDbdump", "Kajona\\Workflows\\System\\Workflows\\WorkflowWorkflowsMessagesummary"));
        $this->assertEquals(count($arrWorkflows), 28);


        //4. Delete created workflow objects
        /** @var WorkflowsWorkflow $objWorkflow*/
        foreach($arrCreatedWorkflows as $objWorkflow) {
            $objWorkflow->deleteObjectFromDatabase();
        }

        $this->resetCaches();
    }


    public function test_getWorkflowsForSystemid() {
        //execute test case with invalid systemid
        $arrReturn = WorkflowsWorkflow::getWorkflowsForSystemid("ddd", false, array("Kajona\\Workflows\\System\\Workflows\\WorkflowWorkflowsMessagesummary"));
        $this->assertEquals(0, count($arrReturn));
    }
}