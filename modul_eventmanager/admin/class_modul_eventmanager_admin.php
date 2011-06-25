<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * Admin class of the eventmanager-module. Responsible for editing events, participants and organizing them.
 *
 * @package modul_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_modul_eventmanager_admin extends class_admin implements interface_admin, interface_calendarsource_admin {

    public static $STR_CALENDAR_FILTER_EVENT = "STR_CALENDAR_FILTER_EVENT";

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_eventmanager";
		$arrModule["moduleId"] 			= _eventmanager_modul_id_;
		$arrModule["table"] 		    = _dbprefix_."eventmanager_event";
		$arrModule["table2"]			= _dbprefix_."eventmanager_participant";
		$arrModule["modul"]				= "eventmanager";

		//Base class
		parent::__construct($arrModule);

	}

	protected function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
    	$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("commons_list"), "", "", true, "adminnavi"));
    	$arrReturn[] = array("", "");
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newEvent", "", $this->getText("module_create"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


	public function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "saveEvent") {
            $arrReturn["event_title"] = "string";
            $arrReturn["event_registration"] = "character";
            $arrReturn["event_start"] = "date";
        }
        if($strAction == "saveParticipant") {
            $arrReturn["participant_email"] = "email";
            $arrReturn["participant_forename"] = "string";
            $arrReturn["participant_lastname"] = "string";
        }

        return $arrReturn;
    }

// --- ListenFunktionen ---------------------------------------------------------------------------------


	/**
	 * Returns a list of all events currently available
	 *
	 * @return string
	 */
	protected function actionList() {
		$strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

            $objArraySectionIterator = new class_array_section_iterator(class_modul_eventmanager_event::getAllEventsCount());
            $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_modul_eventmanager_event::getAllEvents($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
            $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->arrModule["modul"], "list");

            $arrEvents = $arrPageViews["elements"];

            $intI = 0;
			foreach($arrEvents as $objOneEvent) {
			    if($this->objRights->rightView($objOneEvent->getSystemid())) {

                    $strAction = "";
                    if($this->objRights->rightEdit($objOneEvent->getSystemid())) {
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editEvent", "&systemid=".$objOneEvent->getSystemid(), "", $this->getText("event_edit"), "icon_pencil.gif"));
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "listParticipants", "&systemid=".$objOneEvent->getSystemid(), "", $this->getText("event_listParticipants"), "icon_user.gif"));
                        if(_system_changehistory_enabled_ != "false")
                            $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showHistory", "&systemid=".$objOneEvent->getSystemid(), "", $this->getText("show_history"), "icon_history.gif"));
                    }
    		   		if($this->objRights->rightDelete($objOneEvent->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listDeleteButton(
    		   		           $objOneEvent->getStrTitle(), $this->getText("event_delete_question"),
				               getLinkAdminHref($this->arrModule["modul"], "deleteEvent", "&systemid=".$objOneEvent->getSystemid()."&peClose=".$this->getParam("pe"))
    		   		    );
    		   		if($this->objRights->rightEdit($objOneEvent->getSystemid()))
    				    $strAction .= $this->objToolkit->listStatusButton($objOneEvent->getSystemid());
    				if($this->objRights->rightRight($objOneEvent->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneEvent->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOneEvent->getSystemid())));

                    $strCenter = "(".dateToString($objOneEvent->getObjStartDate(), true);
                    if($objOneEvent->getObjEndDate() != null)
                        $strCenter .= " - ".dateToString($objOneEvent->getObjEndDate(), true);

                    if($objOneEvent->getIntRegistrationRequired()) {
                        $strCenter .= ", ". class_modul_eventmanager_participant::getAllParticipantsCount($objOneEvent->getSystemid())." ".$this->getText("event_participant");
                    }

                    $strCenter .= ")";

    		   		$strReturn .= $this->objToolkit->listRow3(uniStrTrim($objOneEvent->getStrTitle(), 80), $strCenter, $strAction, getImageAdmin("icon_event.gif"), $intI++);
			    }

			}
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
			    $strReturn .= $this->objToolkit->listRow3("", "", getLinkAdmin($this->arrModule["modul"], "newEvent", "", $this->getText("module_create"), $this->getText("module_create"), "icon_new.gif"), "", $intI++);

			if(uniStrlen($strReturn) != 0)
			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

            if(count($arrEvents) == 0)
    			$strReturn.= $this->getText("list_empty");
            else
                $strReturn .= $arrPageViews["pageview"];

        }
        else
            $strReturn = $this->getText("error_permissions");

		return $strReturn;
	}


    protected function actionEditEvent() {
        return $this->actionNewEvent("edit");
    }


	/**
	 * Shows the form to edit oder create an event
	 *
	 * @param string $strMode new || edit
	 * @return string
	 */
	protected function actionNewEvent($strMode = "new") {
		$strReturn = "";

        $arrDDYesNo = array(0 => $this->getText("event_yesno_0"), 1 => $this->getText("event_yesno_1"));

		if($strMode == "new") {
			//Form to create new events
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $strReturn .= $this->objToolkit->getValidationErrors($this, "saveEvent");
				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveEvent"));

                $strReturn .= $this->objToolkit->formInputText("event_title", $this->getText("event_title"), $this->getParam("event_title"));
                $strReturn .= $this->objToolkit->formWysiwygEditor("event_description", $this->getText("event_description"), $this->getParam("event_description"), "minimalimage");
                $strReturn .= $this->objToolkit->formInputText("event_location", $this->getText("event_location"), $this->getParam("event_location"));
                $strReturn .= $this->objToolkit->formInputDropdown("event_registration", $arrDDYesNo, $this->getText("event_registration"), $this->getParam("event_registration"));
                $strReturn .= $this->objToolkit->formTextRow($this->getText("event_limitparticipants_hint"));
                $strReturn .= $this->objToolkit->formInputDropdown("event_limitparticipants", $arrDDYesNo, $this->getText("event_limitparticipants"), $this->getParam("event_limitparticipants"));
                $strReturn .= $this->objToolkit->formTextRow($this->getText("event_maxparticipants_hint"));
                $strReturn .= $this->objToolkit->formInputText("event_maxparticipants", $this->getText("event_maxparticipants"), $this->getParam("event_maxparticipants"));
                $strReturn .= $this->objToolkit->formDateSingle("event_start",  $this->getText("event_start"), new class_date(), "inputDate", true);
                $strReturn .= $this->objToolkit->formDateSingle("event_end",  $this->getText("event_end"), null, "inputDate", true);

                $strReturn .= $this->objToolkit->formInputHidden("systemid", "");
                $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
                $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("button_save"));
				$strReturn .= $this->objToolkit->formClose();

				$strReturn .= $this->objToolkit->setBrowserFocus("event_title");
			}
			else
				$strReturn .= $this->getText("error_permissions");
		}
		elseif ($strMode == "edit") {
			//Rights
			if($this->objRights->rightEdit($this->getSystemid())) {
			    $objEvent = new class_modul_eventmanager_event($this->getSystemid());
			    $strReturn .= $this->objToolkit->getValidationErrors($this, "saveEvent");
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveEvent"));


                $strReturn .= $this->objToolkit->formInputText("event_title", $this->getText("event_title"), $objEvent->getStrTitle());
                $strReturn .= $this->objToolkit->formWysiwygEditor("event_description", $this->getText("event_description"), $objEvent->getStrDescription(), "minimalimage");
                $strReturn .= $this->objToolkit->formInputText("event_location", $this->getText("event_location"), $objEvent->getStrLocation());
                $strReturn .= $this->objToolkit->formInputDropdown("event_registration", $arrDDYesNo, $this->getText("event_registration"), $objEvent->getIntRegistrationRequired());
                $strReturn .= $this->objToolkit->formTextRow($this->getText("event_limitparticipants_hint"));
                $strReturn .= $this->objToolkit->formInputDropdown("event_limitparticipants", $arrDDYesNo, $this->getText("event_limitparticipants"), $objEvent->getIntLimitGiven());
                $strReturn .= $this->objToolkit->formTextRow($this->getText("event_maxparticipants_hint"));
                $strReturn .= $this->objToolkit->formInputText("event_maxparticipants", $this->getText("event_maxparticipants"), $objEvent->getIntParticipantsLimit());
                $strReturn .= $this->objToolkit->formDateSingle("event_start",  $this->getText("event_start"), $objEvent->getObjStartDate(), "inputDate", true);
                $strReturn .= $this->objToolkit->formDateSingle("event_end",  $this->getText("event_end"), $objEvent->getObjEndDate(), "inputDate", true);

                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("button_save"));
				$strReturn .= $this->objToolkit->formClose();

				$strReturn .= $this->objToolkit->setBrowserFocus("eventmanager_title");
			}
			else
				$strReturn .= $this->getText("error_permissions");
		}
		return $strReturn;
	}


	/**
	 * Saves or updates events
	 *
	 * @return string "" in case of success
	 */
	protected function actionSaveEvent() {
		$strReturn = "";

        if(!$this->validateForm())
            return $this->actionNewEvent($this->getParam("mode"));

        /**
         * @var class_modul_eventmanager_event
         */
        $objEvent = null;
		if($this->getParam("mode") == "new" && $this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            $objEvent = new class_modul_eventmanager_event();
		}
		elseif($this->getParam("mode") == "edit" && $this->objRights->rightEdit($this->getSystemid())) {
            $objEvent = new class_modul_eventmanager_event($this->getSystemid());
		}

        if($objEvent != null) {
            $objEvent->setStrTitle($this->getParam("event_title"));
            $objEvent->setStrDescription($this->getParam("event_description"));
            $objEvent->setStrLocation($this->getParam("event_location"));
            $objEvent->setIntRegistrationRequired($this->getParam("event_registration"));
            $objEvent->setIntLimitGiven($this->getParam("event_limitparticipants"));
            $objEvent->setIntParticipantsLimit($this->getParam("event_maxparticipants"));

            //parse passed dates
            $objStartDate = new class_date("0");
            $objStartDate->generateDateFromParams("event_start", $this->getAllParams());

            $objEndDate = null;
            if($this->getParam("event_end_day") != "") {
                $objEndDate = new class_date("0");
                $objEndDate->generateDateFromParams("event_end", $this->getAllParams());
            }
            
            $objEvent->setObjStartDate($objStartDate);
            $objEvent->setObjEndDate($objEndDate);

            if(!$objEvent->updateObjectToDb())
                throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
            
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]), "list");
            $this->flushCompletePagesCache();

        }
        else
            $strReturn .= $this->getText("error_permissions");

		return $strReturn;
	}

	/**
	 * Deletes eventmanager or shows the form to warn
	 *
	 * @return string "" in case of success
	 */
	protected function actionDeleteEvent() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {
            $objEvent = new class_modul_eventmanager_event($this->getSystemid());
            if(!$objEvent ->deleteEvent())
                throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list"));
		}
		else
			$strReturn .= $this->getText("error_permissions");


		return $strReturn;
	}


    /**
	 * Returns a list of all participants of the event selected before
	 *
	 * @return string
	 */
	protected function actionListParticipants() {
		$strReturn = "";
        if($this->objRights->rightView($this->getSystemid())) {

            $objEvent = new class_modul_eventmanager_event($this->getSystemid());


            $strParticipants = "";
            $arrLinks = array( getLinkAdmin($this->arrModule["modul"], "list", "", $objEvent->getStrTitle()) );
            $strReturn .= $this->objToolkit->getPathNavigation($arrLinks).$this->objToolkit->divider();

            if($objEvent->getIntRegistrationRequired() == "1" && $objEvent->getIntLimitGiven() == "1") {
                $strReturn .= $this->objToolkit->getTextRow($this->getText("participants_info_limit").$objEvent->getIntParticipantsLimit());
            }
            else {
                 $strReturn .= $this->objToolkit->getTextRow($this->getText("participants_info_nolimit"));
            }

            $strReturn .= $this->objToolkit->divider();

    		//Load all participants
            $objArraySectionIterator = new class_array_section_iterator(class_modul_eventmanager_participant::getAllParticipantsCount($this->getSystemid()));
            $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_modul_eventmanager_participant::getAllParticipants($this->getSystemid(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
            $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->arrModule["modul"], "listParticipants", "&systemid=".$this->getSystemid());

            $arrParticipants = $arrPageViews["elements"];

            $intI = 0;
			foreach($arrParticipants  as $objOneParticipant) {
			    if($this->objRights->rightView($objOneParticipant->getSystemid())) {

                    $strAction = "";
                    if($this->objRights->rightEdit($objOneParticipant->getSystemid())) {
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editParticipant", "&systemid=".$objOneParticipant->getSystemid(), "", $this->getText("participant_edit"), "icon_pencil.gif"));

                        if(checkEmailaddress($objOneParticipant->getStrEmail()) ) {
                            $strPreset  = "&mail_recipient=".$objOneParticipant->getStrEmail();
                            $strPreset .= "&mail_subject=".urlencode($this->getText("participant_mail_subject"));
                            $strPreset .= "&mail_body=".urlencode($this->getText("participant_mail_intro")."\n".
                                    $this->getText("event_title")." ".$objEvent->getStrTitle()."\n".
                                    $this->getText("event_location")." ".$objEvent->getStrLocation()."\n".
                                    $this->getText("event_start")." ".  dateToString($objEvent->getObjStartDate())
                            );
                            $strAction .= $this->objToolkit->listButton(getLinkAdminDialog("system", "mailForm", $strPreset, "", $this->getText("participant_mail"), "icon_mail.gif"));
                        }

                        if(_system_changehistory_enabled_ != "false")
                            $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "showHistory", "&systemid=".$objOneParticipant->getSystemid(), "", $this->getText("show_history"), "icon_history.gif"));
                    }
    		   		if($this->objRights->rightDelete($objOneParticipant->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listDeleteButton(
    		   		           $objOneParticipant->getStrEmail(), $this->getText("participant_delete_question"),
				               getLinkAdminHref($this->arrModule["modul"], "deleteParticipant", "&systemid=".$objOneParticipant->getSystemid()."&peClose=".$this->getParam("pe"))
    		   		    );
    		   		if($this->objRights->rightEdit($objOneParticipant->getSystemid()))
    				    $strAction .= $this->objToolkit->listStatusButton($objOneParticipant->getSystemid());
    				if($this->objRights->rightRight($objOneParticipant->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneParticipant->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOneParticipant->getSystemid())));

    		   		$strParticipants .= $this->objToolkit->listRow3($objOneParticipant->getStrLastname().", ".$objOneParticipant->getStrForename(), $objOneParticipant->getStrEmail(), $strAction, getImageAdmin("icon_user.gif"), $intI++);
			    }

			}
			if($this->objRights->rightEdit($this->getSystemid()))
            $strParticipants .= $this->objToolkit->listRow3("", "", getLinkAdmin($this->arrModule["modul"], "newParticipant", "&systemid=".$objEvent->getSystemid(), $this->getText("participant_create"), $this->getText("participant_create"), "icon_new.gif"), "", $intI++);

			if(uniStrlen($strParticipants) != 0)
			    $strParticipants = $this->objToolkit->listHeader().$strParticipants.$this->objToolkit->listFooter();

            if(count($arrParticipants ) == 0)
    			$strParticipants.= $this->getText("list_participants_empty");
            else
                $strParticipants .= $arrPageViews["pageview"];

    		$strReturn .= $strParticipants;
        }
        else
            $strReturn = $this->getText("error_permissions");

		return $strReturn;
	}


    protected function actionEditParticipant() {
        return $this->actionNewParticipant("edit");
    }


    /**
	 * Shows the form to edit oder create participants
	 *
	 * @param string $strMode new || edit
	 * @return string
	 */
	protected function actionNewParticipant($strMode = "new") {
		$strReturn = "";
		if($strMode == "new") {
			//Form to create new answer
			if($this->objRights->rightEdit($this->getSystemid())) {
			    $strReturn .= $this->objToolkit->getValidationErrors($this, "saveParticipant");

				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveParticipant"));

                $strReturn .= $this->objToolkit->formInputText("participant_email", $this->getText("participant_email"), $this->getParam("participant_email"));
                $strReturn .= $this->objToolkit->formInputText("participant_forename", $this->getText("participant_forename"), $this->getParam("participant_forename"));
                $strReturn .= $this->objToolkit->formInputText("participant_lastname", $this->getText("participant_lastname"), $this->getParam("participant_lastname"));
                $strReturn .= $this->objToolkit->formInputText("participant_phone", $this->getText("participant_phone"), $this->getParam("participant_phone"));
                $strReturn .= $this->objToolkit->formInputTextArea("participant_comment", $this->getText("participant_comment"), $this->getParam("participant_comment"));

                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("eventid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
                $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("button_save"));
				$strReturn .= $this->objToolkit->formClose();

				$strReturn .= $this->objToolkit->setBrowserFocus("answer_title");
			}
			else
				$strReturn .= $this->getText("error_permissions");
		}
		elseif ($strMode == "edit") {
			//Rights
			if($this->objRights->rightEdit($this->getSystemid())) {

			    $objParticpant = new class_modul_eventmanager_participant($this->getSystemid());
			    $strReturn .= $this->objToolkit->getValidationErrors($this, "saveParticipant");
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveParticipant"));
                $strReturn .= $this->objToolkit->formInputText("participant_email", $this->getText("participant_email"), $objParticpant->getStrEmail());
                $strReturn .= $this->objToolkit->formInputText("participant_forename", $this->getText("participant_forename"), $objParticpant->getStrForename());
                $strReturn .= $this->objToolkit->formInputText("participant_lastname", $this->getText("participant_lastname"), $objParticpant->getStrLastname());
                $strReturn .= $this->objToolkit->formInputText("participant_phone", $this->getText("participant_phone"), $objParticpant->getStrPhone());
                $strReturn .= $this->objToolkit->formInputTextArea("participant_comment", $this->getText("participant_comment"), $objParticpant->getStrComment());
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("eventid", $this->getPrevId());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("button_save"));
				$strReturn .= $this->objToolkit->formClose();

				$strReturn .= $this->objToolkit->setBrowserFocus("eventmanager_title");
			}
			else
				$strReturn .= $this->getText("error_permissions");
		}
		return $strReturn;
	}


    /**
	 * Saves or updates a single partcipant
	 *
	 * @return string "" in case of success
	 */
	protected function actionSaveParticipant() {
		$strReturn = "";

        if(!$this->validateForm())
            return $this->actionNewParticipant($this->getParam("mode"));
        /**
         * @var class_modul_eventmanager_participant
         */
        $objParticipant = null;

		if($this->getParam("mode") == "new" && $this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            $objParticipant  = new class_modul_eventmanager_participant();
		}
		elseif($this->getParam("mode") == "edit" && $this->objRights->rightEdit($this->getSystemid())) {
            $objParticipant  = new class_modul_eventmanager_participant($this->getSystemid());
		}

        if($objParticipant != null) {

            $objParticipant->setStrEmail($this->getParam("participant_email"));
            $objParticipant->setStrForename($this->getParam("participant_forename"));
            $objParticipant->setStrLastname($this->getParam("participant_lastname"));
            $objParticipant->setStrPhone($this->getParam("participant_phone"));
            $objParticipant->setStrComment($this->getParam("participant_comment"));

            if(!$objParticipant->updateObjectToDb( $this->getParam("eventid") ) )
                throw new class_exception("Error updating object to db", class_exception::$level_ERROR);

            $this->setSystemid($this->getParam("eventid"));

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listParticipants", "&systemid=".$this->getSystemid()));
            $this->flushCompletePagesCache();
        }
        else
            $strReturn .= $this->getText("error_permissions");

		return $strReturn;
	}

    /**
	 * Deletes a participant
	 *
	 * @return string "" in case of success
	 */
	protected function actionDeleteParticipant() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {
            $strPrev = $this->getPrevId();

            $objParticipant = new class_modul_eventmanager_participant($this->getSystemid());
            if(!$objParticipant->deleteParticipant())
                throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listParticipants", "&systemid=".$strPrev));
		}
		else
			$strReturn .= $this->getText("error_permissions");

		return $strReturn;
	}



    protected function actionShowHistory() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getSystemid())) {
            $objSystemAdmin = class_modul_system_module::getModuleByName("system")->getAdminInstanceOfConcreteModule();
            $strReturn .= $objSystemAdmin->actionGenericChangelog($this->getSystemid(), $this->arrModule["modul"], "showHistory");
        }
        else
            $strReturn = $this->getText("error_permissions");

        return $strReturn;
    }



    /**
     *
     * @see interface_calendarsource_admin::getArrCalendarEntries()
     */
    public function getArrCalendarEntries(class_date $objStartDate, class_date $objEndDate) {
        $arrEntries = array();


        if($this->objSession->getSession(self::$STR_CALENDAR_FILTER_EVENT) != "disabled") {
            $arrEvents = class_modul_eventmanager_event::getAllEvents(null, null, $objStartDate, $objEndDate);
            foreach($arrEvents as $objOneEvent) {

                $objEntry = new class_calendarentry();
                $strAlt = $this->getText("calendar_type_event");

                $strTitle = $objOneEvent->getStrTitle();
                if(uniStrlen($strTitle)> 15) {
                    $strAlt = $strTitle."<br />".$strAlt;
                    $strTitle = uniStrTrim($strTitle, 14);
                }

                $strName = getLinkAdmin($this->arrModule["modul"], "editEvent", "&systemid=".$objOneEvent->getSystemid(), $strTitle, $strAlt);
                $objEntry->setStrName($strName);
                $arrEntries[] = $objEntry;
            }
        }


        return $arrEntries;
    }

    /**
     *
     * @see interface_calendarsource_admin::getArrLegendEntries()
     */
    public function getArrLegendEntries() {
        return array($this->getText("calendar_type_event") => "calendarEvent");
    }

    /**
     *
     * @see interface_calendarsource_admin::getArrFilterEntries()
     */
    public function getArrFilterEntries() {
        return array(
            self::$STR_CALENDAR_FILTER_EVENT => $this->getText("calendar_filter_event"),
        );
    }

}

?>