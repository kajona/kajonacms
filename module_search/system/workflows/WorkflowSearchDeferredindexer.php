<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\System\Workflows;

use Kajona\Search\System\SearchEnumIndexaction;
use Kajona\Search\System\SearchIndexqueue;
use Kajona\Search\System\SearchIndexwriter;
use Kajona\System\System\Carrier;
use Kajona\System\System\Date;
use Kajona\System\System\Objectfactory;
use Kajona\Workflows\System\WorkflowsHandlerInterface;
use Kajona\Workflows\System\WorkflowsWorkflow;

/**
 * A workflow used to index objects decoupled from their changes. This reduces the workload when creating and changing objects.
 *
 * @package module_search
 * @since 4.6
 */
class WorkflowSearchDeferredindexer implements WorkflowsHandlerInterface
{

    private $intIntervall = 300;
    private $intMaxObjectsPerRun = 1000;

    /**
     * @var WorkflowsWorkflow
     */
    private $objWorkflow = null;

    /**
     * @inheritdoc
     */
    public function getConfigValueNames()
    {
        return array(
            Carrier::getInstance()->getObjLang()->getLang("workflow_deferredindexer_cfg_val1", "search"),
            Carrier::getInstance()->getObjLang()->getLang("workflow_deferredindexer_cfg_val2", "search")
        );
    }

    /**
     * @inheritdoc
     */
    public function setConfigValues($strVal1, $strVal2, $strVal3)
    {
        $this->intIntervall = $strVal1;
        if ($strVal2 > 0) {
            $this->intMaxObjectsPerRun = $strVal2;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDefaultValues()
    {
        return array(300, 1000);
    }

    /**
     * @param WorkflowsWorkflow $objWorkflow
     *
     * @return void
     */
    public function setObjWorkflow($objWorkflow)
    {
        $this->objWorkflow = $objWorkflow;
    }

    /**
     * @return string
     */
    public function getStrName()
    {
        return Carrier::getInstance()->getObjLang()->getLang("workflow_deferredindexer_title", "search");
    }

    /**
     * @return bool
     */
    public function execute()
    {
        $objIndex = new SearchIndexwriter();

        //start with deletions
        $objQueue = new SearchIndexqueue();

        Carrier::getInstance()->getObjRights()->setBitTestMode(true);

        foreach ($objQueue->getRows(SearchEnumIndexaction::DELETE()) as $arrRow) {
            $objIndex->removeRecordFromIndex($arrRow["search_queue_systemid"]);
            $objQueue->deleteBySystemid($arrRow["search_queue_systemid"]);
        }

        //index objects
        foreach ($objQueue->getRows(SearchEnumIndexaction::INDEX(), 0, $this->intMaxObjectsPerRun) as $arrRow) {
            $objIndex->indexObject(Objectfactory::getInstance()->getObject($arrRow["search_queue_systemid"]));
            $objQueue->deleteBySystemidAndAction($arrRow["search_queue_systemid"], SearchEnumIndexaction::INDEX());
        }

        Carrier::getInstance()->getObjRights()->setBitTestMode(false);

        //reschedule for the next run
        return false;
    }


    /**
     * @return void
     */
    public function onDelete()
    {
    }


    /**
     * @return void
     */
    public function schedule()
    {
        $this->objWorkflow->setObjTriggerdate(new Date(time() + $this->intIntervall));
    }

    /**
     * @return void
     */
    public function getUserInterface()
    {

    }

    /**
     * @param array $arrParams
     *
     * @return void
     */
    public function processUserInput($arrParams)
    {
        return;
    }

    /**
     * @return bool
     */
    public function providesUserInterface()
    {
        return false;
    }


}
