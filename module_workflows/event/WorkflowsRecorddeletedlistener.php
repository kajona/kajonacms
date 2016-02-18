<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

namespace Kajona\Workflows\Event;

use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\OrmBase;
use Kajona\System\System\OrmDeletedhandlingEnum;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistRestriction;
use Kajona\System\System\SystemEventidentifier;
use Kajona\Workflows\System\WorkflowsWorkflow;


/**
 * Deletes workflows assigned to the record currently being deleted
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 *
 */
class WorkflowsRecorddeletedlistener implements GenericeventListenerInterface
{


    /**
     * Searches for workflows assigned to the systemid to be deleted.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments)
    {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        $bitReturn = true;


        $objORM = new OrmObjectlist();
        $objORM->setObjHandleLogicalDeleted(OrmDeletedhandlingEnum::INCLUDED);
        $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND workflows_systemid = ?", $strSystemid));
        if ($objORM->getObjectCount("Kajona\\Workflows\\System\\WorkflowWorkflow") == 0) {
            return true;
        }

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);
        $arrWorkflows = WorkflowsWorkflow::getWorkflowsForSystemid($strSystemid, false);
        foreach ($arrWorkflows as $objOneWorkflow) {

            if ($strEventName == SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY) {
                $bitReturn = $bitReturn && $objOneWorkflow->deleteObject();
            }

            if ($strEventName == SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED) {
                $bitReturn = $bitReturn && $objOneWorkflow->deleteObjectFromDatabase();
            }

        }

        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::EXCLUDED);
        return $bitReturn;
    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     *
     * @return void
     */
    public static function staticConstruct()
    {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, new WorkflowsRecorddeletedlistener());
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY, new WorkflowsRecorddeletedlistener());
    }


}

WorkflowsRecorddeletedlistener::staticConstruct();