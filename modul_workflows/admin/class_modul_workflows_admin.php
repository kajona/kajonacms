<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/


/**
 * Admin class of the workflows-module. Responsible for editing workflows and organizing them.
 *
 * @package modul_workflows
 */
class class_modul_workflows_admin extends class_admin implements interface_admin {


	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 				= "modul_workflows";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _workflows_modul_id_;
		$arrModul["modul"]				= "workflows";

		//Base class
		parent::__construct($arrModul);

        //set default action
        if($this->getParam("action") == "")
            $this->setAction("myList");
	}



	protected function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
    	$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "myList", "", $this->getText("module_mylist"), "", "", true, "adminnavi"));
    	$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("commons_list"), "", "", true, "adminnavi"));
    	$arrReturn[] = array("", "");
        $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "listHandlers", "", $this->getText("module_list_handlers"), "", "", true, "adminnavi"));
    	//$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "triggerWorkflows", "", $this->getText("module_trigger"), "", "", true, "adminnavi"));
		return $arrReturn;
	}

// --- ListenFunktionen ---------------------------------------------------------------------------------


	/**
	 * Creates a list of all workflows-instances currently available.
	 * @return string
	 */
	protected function actionList() {
		$strReturn = "";
        if($this->getObjModule()->rightEdit()) {
            $strWorkflows = "";
            $intI = 0;

            $strReturn .= $this->objToolkit->formHeadline($this->getText("header_list_all"));

    		//Load all workflows
            $objArraySectionIterator = new class_array_section_iterator(class_modul_workflows_workflow::getAllworkflowsCount());
            $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_modul_workflows_workflow::getAllworkflows($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

    	    $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->arrModule["modul"], "list");
            $arrWorkflows = $arrPageViews["elements"];

            /** @var class_modul_workflows_workflow $objOneWorkflow */
			foreach($arrWorkflows as $objOneWorkflow) {
			    if($objOneWorkflow->rightView()) {

                    $strAction = "";

                    //check if there's a user-action to take. required the workflow to be scheduled
                    if($objOneWorkflow->getIntState() == class_modul_workflows_workflow::$INT_STATE_SCHEDULED && $objOneWorkflow->getObjWorkflowHandler()->providesUserInterface()) {
                        $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showUI", "&systemid=".$objOneWorkflow->getSystemid(), "", $this->getText("workflow_ui"), "icon_workflow_ui.gif"));
                    }

                    $strStatusIcon = "";
                    if($objOneWorkflow->getIntState() == class_modul_workflows_workflow::$INT_STATE_NEW)
                        $strStatusIcon = getImageAdmin("icon_workflowNew.gif", $this->getText("workflow_status_".$objOneWorkflow->getIntState()));
                    if($objOneWorkflow->getIntState() == class_modul_workflows_workflow::$INT_STATE_SCHEDULED)
                        $strStatusIcon = getImageAdmin("icon_workflowScheduled.gif", $this->getText("workflow_status_".$objOneWorkflow->getIntState()));
                    if($objOneWorkflow->getIntState() == class_modul_workflows_workflow::$INT_STATE_EXECUTED)
                        $strStatusIcon = getImageAdmin("icon_workflowExecuted.gif", $this->getText("workflow_status_".$objOneWorkflow->getIntState()));

                    if($strStatusIcon != "")
                        $strAction .= $this->objToolkit->listButton($strStatusIcon);

                    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showDetails", "&systemid=".$objOneWorkflow->getSystemid(), "", $this->getText("workflow_details"), "icon_lens.gif"));

    		   		if($objOneWorkflow->rightDelete())
    		   		    $strAction .= $this->objToolkit->listDeleteButton(
    		   		           $objOneWorkflow->getObjWorkflowHandler()->getStrName()." ".dateToString($objOneWorkflow->getObjTriggerDate()), $this->getText("workflow_delete_question"),
				               getLinkAdminHref($this->arrModule["modul"], "deleteWorkflow", "&systemid=".$objOneWorkflow->getSystemid())
    		   		    );

    				if($objOneWorkflow->rightRight())
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneWorkflow->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOneWorkflow->getSystemid())));

    		   		$strWorkflows .= $this->objToolkit->listRow2Image(getImageAdmin("icon_workflow.gif"), $objOneWorkflow->getObjWorkflowHandler()->getStrName()." ".dateToString($objOneWorkflow->getObjTriggerDate()), $strAction, $intI++);

			    }

			}

			if(uniStrlen($strWorkflows) != 0)
			    $strReturn .= $this->objToolkit->listHeader().$strWorkflows.$this->objToolkit->listFooter().$arrPageViews["pageview"];

            if(count($arrWorkflows) == 0)
    			$strReturn.= $this->getText("list_empty");

        }
        else
            $strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
	}


    /**
	 * Creates a list of workflow-instances available for the current user
	 * @return string
	 */
	protected function actionMyList() {
		$strReturn = "";
        if($this->getObjModule()->rightView()) {
            $strWorkflows = "";
            $intI = 0;

            $strReturn .= $this->objToolkit->formHeadline($this->getText("header_list_my"));

    		//Load all workflows
            $objArraySectionIterator = new class_array_section_iterator(class_modul_workflows_workflow::getPendingWorkflowsForUserCount( array_merge(array( $this->objSession->getUserID() ), $this->objSession->getGroupIdsAsArray()) ));
            $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_modul_workflows_workflow::getPendingWorkflowsForUser( array_merge(array( $this->objSession->getUserID() ), $this->objSession->getGroupIdsAsArray()), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

    	    $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->arrModule["modul"], "myList");
            $arrWorkflows = $arrPageViews["elements"];

            /** @var class_modul_workflows_workflow $objOneWorkflow */
			foreach($arrWorkflows as $objOneWorkflow) {
			    if($objOneWorkflow->rightView() || count(array_diff(array_merge(array( $this->objSession->getUserID() ), $this->objSession->getGroupIdsAsArray()), explode(",", $objOneWorkflow->getStrResponsible()))) > 0  ) {

                    $strAction = "";

                    //check if there's a user-action to take. required the workflow to be scheduled
                    if($objOneWorkflow->getIntState() == class_modul_workflows_workflow::$INT_STATE_SCHEDULED && $objOneWorkflow->getObjWorkflowHandler()->providesUserInterface()) {
                        $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showUI", "&systemid=".$objOneWorkflow->getSystemid(), "", $this->getText("workflow_ui"), "icon_workflow_ui.gif"));
                    }

                    $strStatusIcon = "";
                    if($objOneWorkflow->getIntState() == class_modul_workflows_workflow::$INT_STATE_NEW)
                        $strStatusIcon = getImageAdmin("icon_workflowNew.gif", $this->getText("workflow_status_".$objOneWorkflow->getIntState()));
                    if($objOneWorkflow->getIntState() == class_modul_workflows_workflow::$INT_STATE_SCHEDULED)
                        $strStatusIcon = getImageAdmin("icon_workflowScheduled.gif", $this->getText("workflow_status_".$objOneWorkflow->getIntState()));
                    if($objOneWorkflow->getIntState() == class_modul_workflows_workflow::$INT_STATE_EXECUTED)
                        $strStatusIcon = getImageAdmin("icon_workflowExecuted.gif", $this->getText("workflow_status_".$objOneWorkflow->getIntState()));

                    if($strStatusIcon != "")
                        $strAction .= $this->objToolkit->listButton($strStatusIcon);



    		   		$strWorkflows .= $this->objToolkit->listRow2Image(getImageAdmin("icon_workflow.gif"), $objOneWorkflow->getObjWorkflowHandler()->getStrName()." ".dateToString($objOneWorkflow->getObjTriggerDate()), $strAction, $intI++);

			    }

			}

			if(uniStrlen($strWorkflows) != 0)
			    $strReturn .= $this->objToolkit->listHeader().$strWorkflows.$this->objToolkit->listFooter().$arrPageViews["pageview"];

            if(count($arrWorkflows) == 0)
    			$strReturn.= $this->getText("myList_empty");

        }
        else
            $strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
	}

	/**
	 * Deletes a workflow-instance from the database
	 * @return string "" in case of success
	 */
	protected function actionDeleteWorkflow() {
		$strReturn = "";
		//Rights
        $objWorkflow = new class_modul_workflows_workflow($this->getSystemid());
        if($objWorkflow->rightDelete()) {
            if(!$objWorkflow->deleteWorkflow())
                throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
		}
		else
			$strReturn .= $this->getText("commons_error_permissions");


		return $strReturn;
	}

    /**
     * Shows technical details of a workflow-instance
     * @return string
     */
    protected function actionShowDetails() {
        $strReturn = "";
		//Rights
        $objWorkflow = new class_modul_workflows_workflow($this->getSystemid());
        if($objWorkflow->rightEdit()) {

            $intI = 0;
            $strReturn .= $this->objToolkit->formHeadline($this->getText("workflow_general"));

    		$strReturn .= $this->objToolkit->listHeader();

            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_class"), $objWorkflow->getStrClass(), $intI++);
            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_systemid"), $objWorkflow->getStrAffectedSystemid(), $intI++);
            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_trigger"), dateToString($objWorkflow->getObjTriggerdate()), $intI++);
            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_runs"), $objWorkflow->getIntRuns(), $intI++);
            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_status"), $this->getText("workflow_status_".$objWorkflow->getIntState()), $intI++);

            $strResponsible = "";
            foreach(explode(",", $objWorkflow->getStrResponsible()) as $strOneId) {
                if(validateSystemid($strOneId)) {
                    if($strResponsible != "")
                        $strResponsible .= ", ";

                    $objUser = new class_modul_user_user($strOneId, false);
                    if($objUser->getStrUsername() != "")
                        $strResponsible .= $objUser->getStrUsername();
                    else {
                        $objGroup = new class_modul_user_group($strOneId);
                        $strResponsible .= $objGroup->getStrName();
                    }
                }
            }
            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_responsible"), $strResponsible, $intI++);

            $strCreator = "";
            if(validateSystemid($objWorkflow->getStrOwner())) {
                $objUser = new class_modul_user_user($objWorkflow->getStrOwner(), false);
                $strCreator .= $objUser->getStrUsername();
            }
            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_owner"), $strCreator, $intI++);


            $strReturn .= $this->objToolkit->listFooter();

            $strReturn .= $this->objToolkit->formHeadline($this->getText("workflow_params"));
            $strReturn .= $this->objToolkit->listHeader();

            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_int1"), $objWorkflow->getIntInt1(), $intI++);
            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_int2"), $objWorkflow->getIntInt2(), $intI++);

            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_char1"), $objWorkflow->getStrChar1(), $intI++);
            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_char2"), $objWorkflow->getStrChar2(), $intI++);

            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_date1"), $objWorkflow->getLongDate1(), $intI++);
            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_date2"), $objWorkflow->getLongDate2(), $intI++);

            $strReturn .= $this->objToolkit->listRow2($this->getText("workflow_text"), $objWorkflow->getStrText(), $intI++);


    		$strReturn .= $this->objToolkit->listFooter();

            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "list"));
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_back"));
            $strReturn .= $this->objToolkit->formClose();
		}
		else
			$strReturn .= $this->getText("commons_error_permissions");


		return $strReturn;
    }

    /**
     * Uses the workflow-manager to trigger the scheduling and execution of workflows
     * @return string
     */
    protected function actionTriggerWorkflows() {
        $strReturn = "";
        if($this->getObjModule()->rightEdit()) {
            $objWorkflowController = new class_modul_workflows_controller();
            $objWorkflowController->scheduleWorkflows();
            $objWorkflowController->runWorkflows();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
		}
		else
			$strReturn .= $this->getText("commons_error_permissions");


		return $strReturn;
    }


    /**
     * Creates the form to perform the current workflow-step
     * @return string
     */
    protected function actionShowUi() {
        $strReturn = "";
        $objWorkflow = new class_modul_workflows_workflow($this->getSystemid());
        if($objWorkflow->rightView()) {

            $arrIdsToCheck = array_merge(array( $this->objSession->getUserID() ), $this->objSession->getGroupIdsAsArray());
            $arrIdsOfTask = explode(",", $objWorkflow->getStrResponsible());

            //ui given? current user responsible?
            if($objWorkflow->getObjWorkflowHandler()->providesUserInterface() && ($objWorkflow->getStrResponsible() == "" ||
                    //magic: the difference of the tasks' ids and the users' ids should be less than the count of the task-ids - then at least one id matches
                    count(array_diff($arrIdsOfTask, $arrIdsToCheck)) < count($arrIdsOfTask)  ) ) {
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveUI"));

                $strReturn .= $objWorkflow->getObjWorkflowHandler()->getUserInterface();

                $strReturn .= $this->objToolkit->formInputHidden("systemid", $objWorkflow->getSystemid());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
                $strReturn .= $this->objToolkit->formClose();
            }
            else
                $strReturn .= $this->getText("commons_error_permissions");
        }
        else
			$strReturn .= $this->getText("commons_error_permissions");


		return $strReturn;
    }


    /**
     * Calls the handler to process the values collected by the ui before.
     * @return string
     */
    protected function actionSaveUi() {
        $strReturn = "";
        $objWorkflow = new class_modul_workflows_workflow($this->getSystemid());
        if($objWorkflow->rightView()) {

            $arrIdsToCheck = array_merge(array( $this->objSession->getUserID() ), $this->objSession->getGroupIdsAsArray());
            $arrIdsOfTask = explode(",", $objWorkflow->getStrResponsible());

            //ui given? current user responsible?
            if($objWorkflow->getObjWorkflowHandler()->providesUserInterface() && ($objWorkflow->getStrResponsible() == "" ||
                   //magic: the difference of the tasks' ids and the users' ids should be less than the count of the task-ids - then at least one id matches
                    count(array_diff($arrIdsOfTask, $arrIdsToCheck)) < count($arrIdsOfTask)  ) ) {
                $objHandler = $objWorkflow->getObjWorkflowHandler();
                $objHandler->processUserInput($this->getAllParams());

                if($objWorkflow->getBitSaved() == true)
                    throw new class_exception("Illegal state detected! Workflow was already saved before!", class_exception::$level_FATALERROR);

                $objWorkflow->updateObjectToDb();

                $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "myList"));
            }
            else
                $strReturn .= $this->getText("commons_error_permissions");
        }
        else
			$strReturn .= $this->getText("commons_error_permissions");


		return $strReturn;
    }




    /**
     * Lists all handlers available to the system
     * @return string
     */
    protected function actionListHandlers() {
		$strReturn = "";
        if($this->getObjModule()->rightRight1()) {

            class_modul_workflows_handler::synchronizeHandlerList();

            $strWorkflows = "";
            $intI = 0;

            $strReturn .= $this->objToolkit->formHeadline($this->getText("header_list_handlers"));

    		//Load all handlers
            $objArraySectionIterator = new class_array_section_iterator(class_modul_workflows_handler::getAllworkflowHandlersCount());
            $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_modul_workflows_handler::getAllworkflowHandlers($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

    	    $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->arrModule["modul"], "listHandlers");
            $arrWorkflowsHandler = $arrPageViews["elements"];

            /** @var class_modul_workflows_handler $objOneWorkflowHandler */
			foreach($arrWorkflowsHandler as $objOneWorkflowHandler) {
			    if($this->getObjModule()->rightRight1()) {

                    $strAction = "";

                    if($objOneWorkflowHandler->rightRight1()) {
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editHandler", "&systemid=".$objOneWorkflowHandler->getSystemid(), "", $this->getText("workflow_handler_edit"), "icon_pencil.gif"));
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "instantiateHandler", "&systemid=".$objOneWorkflowHandler->getSystemid(), "", $this->getText("workflow_handler_instantiate"), "icon_workflowNew.gif"));
                    }

    		   		$strWorkflows .= $this->objToolkit->listRow3($objOneWorkflowHandler->getObjInstanceOfHandler()->getStrName(), $objOneWorkflowHandler->getStrHandlerClass(), $strAction, getImageAdmin("icon_workflow.gif"), $intI++);

			    }

			}

			if(uniStrlen($strWorkflows) != 0)
			    $strReturn .= $this->objToolkit->listHeader().$strWorkflows.$this->objToolkit->listFooter().$arrPageViews["pageview"];

            if(count($arrWorkflowsHandler) == 0)
    			$strReturn.= $this->getText("list_empty");

        }
        else
            $strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
	}

    /**
     * Creates the form to change the default-values of a workflow-handler
     */
    protected function actionEditHandler() {
        $strReturn = "";
        if($this->getObjModule()->rightRight1()) {

            $objHandler = new class_modul_workflows_handler($this->getSystemid());
            $arrNames = $objHandler->getObjInstanceOfHandler()->getConfigValueNames();

            $strReturn .= $this->objToolkit->formHeadline($objHandler->getObjInstanceOfHandler()->getStrName(). " (".$objHandler->getStrHandlerClass().")");

            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveHandler"));

            $strReturn .= $this->objToolkit->formInputText("handler_val1", (isset($arrNames[0]) ? $arrNames[0] : $this->getText("workflow_handler_val1")), $objHandler->getStrConfigVal1());
            $strReturn .= $this->objToolkit->formInputText("handler_val2", (isset($arrNames[1]) ? $arrNames[1] : $this->getText("workflow_handler_val2")), $objHandler->getStrConfigVal2());
            $strReturn .= $this->objToolkit->formInputText("handler_val3", (isset($arrNames[2]) ? $arrNames[2] : $this->getText("workflow_handler_val3")), $objHandler->getStrConfigVal3());


            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
        }
        else
            $strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
    }

    /**
     * Saves the values passed by the form back to the handler
     * @return string
     */
    protected function actionSaveHandler() {
        $strReturn = "";
        if($this->getObjModule()->rightRight1()) {

            $objHandler = new class_modul_workflows_handler($this->getSystemid());
            $objHandler->setStrConfigVal1($this->getParam("handler_val1"));
            $objHandler->setStrConfigVal2($this->getParam("handler_val2"));
            $objHandler->setStrConfigVal3($this->getParam("handler_val3"));

            if(!$objHandler->updateObjectToDb())
                throw new class_exception("error saving workflow-handler", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listHandlers"));
        }
        else
            $strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
    }



    protected function actionInstantiateHandler() {
        $strReturn = "";
        if($this->getObjModule()->rightRight1()) {

            $objHandler = new class_modul_workflows_handler($this->getSystemid());
            $strReturn .= $this->objToolkit->formHeadline($objHandler->getObjInstanceOfHandler()->getStrName(). " (".$objHandler->getStrHandlerClass().")");
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "startInstance"));
            $strReturn .= $this->objToolkit->formInputText("instance_systemid", $this->getText("instance_systemid"));
            $strReturn .= $this->objToolkit->formInputUserSelector("instance_responsible", $this->getText("instance_responsible"));
            $strReturn .= $this->objToolkit->formInputHidden("instance_responsible_id", "");
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));

        }
        else
            $strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
    }


    protected function actionStartInstance() {
        $strReturn = "";
        if($this->getObjModule()->rightRight1()) {

            $objHandler = new class_modul_workflows_handler($this->getSystemid());

            $objWorkflow = new class_modul_workflows_workflow();
            $objWorkflow->setStrClass($objHandler->getStrHandlerClass());
            $objWorkflow->setStrAffectedSystemid($this->getParam("instance_systemid"));
            $objWorkflow->setStrResponsible($this->getParam("instance_responsible_id"));

            $objWorkflow->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));


        }
        else
            $strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
    }



}

?>