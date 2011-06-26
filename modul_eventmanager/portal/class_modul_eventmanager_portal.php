<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						*
********************************************************************************************************/

/**
 * Portal-class of the eventmanager. Handles the printing of eventmanager lists / detail
 *
 * @package modul_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_modul_eventmanager_portal extends class_portal implements interface_portal {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $arrModule = array();
		$arrModule["name"] 				= "modul_eventmanager";
		$arrModule["moduleId"] 			= _eventmanager_modul_id_;
		$arrModule["modul"]				= "eventmanager";

		parent::__construct($arrModule, $arrElementData);

	}

//--- Lists ---------------------------------------------------------------------------------------------

	/**
     * Creates the list of events available
     *
     * @return string
     */
	protected function actionList() {
		$strReturn = "";
        $strEvents = "";

        $arrEvents = class_modul_eventmanager_event::getAllEvents(null, null, null, null, true, $this->arrElementData["int1"]);
        $strEventTemplateID = $this->objTemplate->readTemplate("/modul_eventmanager/".$this->arrElementData["char1"], "event_list_entry");
        foreach($arrEvents as $objOneEvent) {
            if($objOneEvent->rightView()) {
                $arrTemplate = array();
                $arrTemplate["dateTimeFrom"] = dateToString($objOneEvent->getObjStartDate(), true);
                $arrTemplate["dateFrom"] = dateToString($objOneEvent->getObjStartDate(), false);
                $arrTemplate["dateTimeUntil"] = dateToString($objOneEvent->getObjEndDate(), true);
                $arrTemplate["dateUntil"] = dateToString($objOneEvent->getObjEndDate(), false);
                $arrTemplate["title"] = $objOneEvent->getStrTitle();
                $arrTemplate["description"] = $objOneEvent->getStrDescription();
                $arrTemplate["location"] = $objOneEvent->getStrLocation();
                $arrTemplate["detailsLinkHref"] = getLinkPortalHref($this->getPagename(), "", "eventDetails", "", $objOneEvent->getSystemid());

                if($objOneEvent->getIntRegistrationRequired() == "1" && $objOneEvent->rightRight1())
                    $arrTemplate["registerLinkHref"] = getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objOneEvent->getSystemid());

                $strEvents .= $this->fillTemplate($arrTemplate, $strEventTemplateID);
            }
        }

        $strWrapperID = $this->objTemplate->readTemplate("/modul_eventmanager/".$this->arrElementData["char1"], "event_list");
        $strReturn .= $this->fillTemplate(array("events" => $strEvents), $strWrapperID);
        
		return $strReturn;
	}

    /**
     * Creates a view of all event-details
     * @return string
     */
    protected function actionEventDetails() {
        $strReturn = "";
        $objEvent = new class_modul_eventmanager_event($this->getSystemid());
        if($objEvent->rightView()) {

            $arrTemplate = array();
            $arrTemplate["title"] = $objEvent->getStrTitle();
            $arrTemplate["description"] = $objEvent->getStrDescription();
            $arrTemplate["location"] = $objEvent->getStrLocation();
            $arrTemplate["dateTimeFrom"] = dateToString($objEvent->getObjStartDate(), true);
            $arrTemplate["dateFrom"] = dateToString($objEvent->getObjStartDate(), false);
            $arrTemplate["dateTimeUntil"] = dateToString($objEvent->getObjEndDate(), true);
            $arrTemplate["dateUntil"] = dateToString($objEvent->getObjEndDate(), false);

            $arrTemplate["maximumParticipants"] = $objEvent->getIntParticipantsLimit();
            $arrTemplate["currentParticipants"] = count(class_modul_eventmanager_participant::getAllParticipants($this->getSystemid()));

            if($objEvent->getIntRegistrationRequired() == "1" && $objEvent->rightRight1())
                $arrTemplate["registerLinkHref"] = getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objEvent->getSystemid());

            $strWrapperID = $this->objTemplate->readTemplate("/modul_eventmanager/".$this->arrElementData["char1"], "event_details");
            $strReturn .= $this->fillTemplate($arrTemplate, $strWrapperID);
        }
        else
            $strReturn = $this->getText("commons_error_permissions");

        return $strReturn;
    }

    protected function actionRegisterForEvent() {
        $strReturn = "";
        $objEvent = new class_modul_eventmanager_event($this->getSystemid());
        if($objEvent->rightView() && $objEvent->rightRight1()) {

            if($objEvent->getIntLimitGiven() == "1" &&
                    $objEvent->getIntParticipantsLimit() <= count(class_modul_eventmanager_participant::getAllParticipants($this->getSystemid()))) {

                $strMessage = $this->getText("participantLimitReached");
                $strWrapperID = $this->objTemplate->readTemplate("/modul_eventmanager/".$this->arrElementData["char1"], "event_register_message");
                $strReturn = $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);
                return $strReturn;
            }


            $arrErrors = array();
            $bitForm = true;
            //what to do?
            if($this->getParam("submitUserRegistration") != "") {


                if(!checkText($this->getParam("forename"), 3))
                    $arrErrors[] = $this->getText("noForename");

                if(!checkText($this->getParam("lastname"), 3))
                    $arrErrors[] = $this->getText("noLastname");

                if(!checkEmailaddress($this->getParam("email")))
                   $arrErrors[] = $this->getText("invalidEmailadress");

                //Check captachcode
                if($this->getParam("form_captcha") == "" || $this->getParam("form_captcha") != $this->objSession->getCaptchaCode())
                    $arrErrors[] = $this->getText("commons_captcha");

                if(count($arrErrors) == 0)
                   $bitForm = false;
            }

            if($bitForm) {

                $arrTemplate = array();

                $arrTemplate["forename"] = $this->getParam("forename");
                $arrTemplate["lastname"] = $this->getParam("lastname");
                $arrTemplate["phone"] = $this->getParam("phone");
                $arrTemplate["comment"] = $this->getParam("comment");
                $arrTemplate["email"] = $this->getParam("email");

                $arrTemplate["title"] = $objEvent->getStrTitle();
                $arrTemplate["dateTimeFrom"] = dateToString($objEvent->getObjStartDate(), true);
                $arrTemplate["dateFrom"] = dateToString($objEvent->getObjStartDate(), false);
                $arrTemplate["dateTimeUntil"] = dateToString($objEvent->getObjEndDate(), true);
                $arrTemplate["dateUntil"] = dateToString($objEvent->getObjEndDate(), false);

                $arrTemplate["formaction"] = getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $this->getSystemid());

                $arrTemplate["formErrors"] = "";
                if(count($arrErrors) > 0) {
                    $strErrTemplate = $this->objTemplate->readTemplate("/modul_eventmanager/".$this->arrElementData["char1"], "error_row");
                    foreach ($arrErrors as $strOneError) {
                        $arrTemplate["formErrors"] .= "".$this->fillTemplate(array("error" => $strOneError), $strErrTemplate);
                    }
                }

                $strWrapperID = $this->objTemplate->readTemplate("/modul_eventmanager/".$this->arrElementData["char1"], "event_register");
                $strReturn .= $this->fillTemplate($arrTemplate, $strWrapperID);

            }
            else {

                $strMessage = "";
                if($objEvent->getIntLimitGiven() == "1" &&
                    $objEvent->getIntParticipantsLimit() <= count(class_modul_eventmanager_participant::getAllParticipants($this->getSystemid()))) {

                    $strMessage = $this->getText("participantLimitReached");
                }
                else {

                    //here we go, create the complete event registration
                    $objParticipant = new class_modul_eventmanager_participant();
                    $objParticipant->setStrForename($this->getParam("forename"));
                    $objParticipant->setStrLastname($this->getParam("lastname"));
                    $objParticipant->setStrPhone($this->getParam("phone"));
                    $objParticipant->setStrEmail($this->getParam("email"));
                    $objParticipant->setStrComment($this->getParam("comment"));

                    $objParticipant->updateObjectToDb($this->getSystemid());

                    $objParticipant->setStatus("", "0");

                    $objMail = new class_mail();
                    
                    $objMail->setSubject($this->getText("registerMailSubject"));

                    $strBody = $this->getText("registerMailBodyIntro");
                    $strBody .= $objEvent->getStrTitle()."<br />";
                    $strBody .= dateToString($objEvent->getObjStartDate(), true)."<br />";
                    $strBody .= $objEvent->getStrLocation()."<br />";
                    $strBody .= "\n";
                    $strTemp = getLinkPortalHref($this->getPagename(), "", "participantConfirmation", "&participantId=".$objParticipant->getSystemid(), $this->getSystemid());
                    $strBody .= html_entity_decode("<a href=\"".$strTemp."\">".$strTemp."</a>");

                    $this->objTemplate->setTemplate($strBody);
                    $this->objTemplate->fillConstants();
                    $this->objTemplate->deletePlaceholder();
                    $strBody = $this->objTemplate->getTemplate();

                    $objMail->setHtml($strBody);
                    $objMail->addTo($objParticipant->getStrEmail());
                    $objMail->sendMail();

                    $strMessage = $this->getText("participantSuccessMail");

                }



                $strWrapperID = $this->objTemplate->readTemplate("/modul_eventmanager/".$this->arrElementData["char1"], "event_register_message");
                $strReturn .= $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);
            }

        }
        else
            $strReturn = $this->getText("commons_error_permissions");

        return $strReturn;
    }

    protected function actionParticipantConfirmation() {
        $strMessage = "";
        $objEvent = new class_modul_eventmanager_event($this->getSystemid());
        if($objEvent->rightView() && $objEvent->rightRight1() && validateSystemid($this->getParam("participantId"))) {

            $arrParticipants = class_modul_eventmanager_participant::getAllParticipants($objEvent->getSystemid());
            foreach($arrParticipants as $objOneParticipant) {
                if($objOneParticipant->getSystemid() == $this->getParam("participantId")) {
                    $objOneParticipant->setStatus("", "1");
                    $strMessage = $this->getText("participantSuccessConfirmation");
                    break;
                }
            }

            if($strMessage == "")
                $strMessage = $this->getText("participantErrorConfirmation");

        }
        else
            $strMessage = $this->getText("commons_error_permissions");

        $strWrapperID = $this->objTemplate->readTemplate("/modul_eventmanager/".$this->arrElementData["char1"], "event_register_message");
        $strReturn = $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);

        return $strReturn;
    }
    
}
?>