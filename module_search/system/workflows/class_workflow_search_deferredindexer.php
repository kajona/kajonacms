<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A workflow used to index objects decoupled from their changes. This reduces the workload when creating and changing objects.
 *
 * @package module_search
 * @since 4.6
 */
class class_workflow_search_deferredindexer implements interface_workflows_handler  {

    private $intIntervall = 300;
    private $intMaxObjectsPerRun = 1000;

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
            class_carrier::getInstance()->getObjLang()->getLang("workflow_deferredindexer_cfg_val1", "search"),
            class_carrier::getInstance()->getObjLang()->getLang("workflow_deferredindexer_cfg_val2", "search")
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
        $this->intIntervall = $strVal1;
        if($strVal2 > 0)
            $this->intMaxObjectsPerRun = $strVal2;
    }

    /**
     * @see interface_workflows_handler::getDefaultValues()
     * @return string[]
     */
    public function getDefaultValues() {
        return array(300, 1000);
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
        return class_carrier::getInstance()->getObjLang()->getLang("workflow_deferredindexer_title", "search");
    }

    /**
     * @return bool
     */
    public function execute() {
        $objIndex = new class_module_search_indexwriter();

        //start with deletions
        $objQueue = new class_search_indexqueue();

        class_carrier::getInstance()->getObjRights()->setBitTestMode(true);

        foreach($objQueue->getRows(class_search_enum_indexaction::DELETE()) as $arrRow) {
            $objIndex->removeRecordFromIndex($arrRow["search_queue_systemid"]);
            $objQueue->deleteBySystemid($arrRow["search_queue_systemid"]);
        }

        //index objects
        foreach($objQueue->getRows(class_search_enum_indexaction::INDEX(), 0, $this->intMaxObjectsPerRun) as $arrRow) {
            $objIndex->indexObject(class_objectfactory::getInstance()->getObject($arrRow["search_queue_systemid"]));
            $objQueue->deleteBySystemidAndAction($arrRow["search_queue_systemid"], class_search_enum_indexaction::INDEX());
        }

        class_carrier::getInstance()->getObjRights()->setBitTestMode(false);

        //reschedule for the next run
        return false;
    }




    /**
     * @return void
     */
    public function onDelete() {
    }


    /**
     * @return void
     */
    public function schedule() {
        $this->objWorkflow->setObjTriggerdate(new class_date(time()+$this->intIntervall));
    }

    /**
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
