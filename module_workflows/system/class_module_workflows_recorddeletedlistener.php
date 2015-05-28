<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Deletes workflows assigned to the record currently being deleted
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 *
 */
class class_module_workflows_recorddeletedlistener implements interface_genericevent_listener {


    /**
     * Searches for tags assigned to the systemid to be deleted.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        $bitReturn = true;


        $arrWorkflows = class_module_workflows_workflow::getWorkflowsForSystemid($strSystemid, false);
        foreach($arrWorkflows as $objOneWorkflow) {

            if($strEventName == class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY)
                $bitReturn = $bitReturn && $objOneWorkflow->deleteObject();

            if($strEventName == class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED) {
                $bitReturn = $bitReturn && $objOneWorkflow->deleteObjectFromDatabase();
            }

        }

        return $bitReturn;

    }



    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, new class_module_workflows_recorddeletedlistener());
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY, new class_module_workflows_recorddeletedlistener());
    }


}

class_module_workflows_recorddeletedlistener::staticConstruct();