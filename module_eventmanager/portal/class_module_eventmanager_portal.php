<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_eventmanager_portal.php 4181 2011-11-08 08:37:08Z sidler $						*
********************************************************************************************************/

/**
 * Portal-class of the eventmanager. Handles the printing of eventmanager lists / detail
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_module_eventmanager_portal extends class_portal implements interface_portal {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $this->setArrModuleEntry("moduleId", _eventmanager_module_id_);
        $this->setArrModuleEntry("modul", "eventmanager");
        parent::__construct($arrElementData);

	}


	/**
     * Creates the list of events available
     *
     * @return string
     * @permissions view
     */
	protected function actionList() {
		$strReturn = "";
        $strEvents = "";

        //switch between calendar and list-modes
        if($this->arrElementData["int2"] == "0") {
            //calendar mode
            $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_calendar");
            $arrTemplate = array();
            $arrTemplate["cal_eventsource"] = _xmlpath_."?module=eventmanager&action=getJsonEvents&page=".$this->getPagename();
            $strReturn .= $this->fillTemplate($arrTemplate, $strWrapperID);
        }
        else {
            //list based mode
            $arrEvents = class_module_eventmanager_event::getAllEvents(false, false, null, null, true, $this->arrElementData["int1"]);
            $strEventTemplateID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_list_entry");
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
                    $arrTemplate["detailsLinkHref"] = getLinkPortalHref($this->getPagename(), "", "eventDetails", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle());

                    if($objOneEvent->getIntRegistrationRequired() == "1" && $objOneEvent->rightRight1()) {
                        $arrTemplate["registerLinkHref"] = getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle());
                        $strRegisterLinkID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_details_registerlink");
                        $arrTemplate["registerLink"] = $this->fillTemplate($arrTemplate, $strRegisterLinkID);
                    }
                    $strEvents .= $this->fillTemplate($arrTemplate, $strEventTemplateID);
                }
            }

            $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_list");
            $strReturn .= $this->fillTemplate(array("events" => $strEvents), $strWrapperID);
        }

		return $strReturn;
	}

    /**
     *
     * @xml
     * @return string
     * @permissions view
     */
    protected function actionGetJsonEvents() {

        $arrPrintableEvents = array();
        $objStartDate = null; $objEndDate = null;
        if($this->getParam("start") != "" && $this->getParam("end") != "") {
            $objStartDate = new class_date($this->getParam("start"));
            $objEndDate = new class_date($this->getParam("end"));
        }

        $arrEvents = class_module_eventmanager_event::getAllEvents(null, null, $objStartDate, $objEndDate);
        foreach($arrEvents as $objOneEvent) {
            if($objOneEvent->rightView()) {
                $arrSingleEvent = array();
                $arrSingleEvent["id"] = $objOneEvent->getSystemid();
                $arrSingleEvent["title"] = $objOneEvent->getStrTitle();
                $arrSingleEvent["start"] = $objOneEvent->getObjStartDate()->getTimeInOldStyle();
                $arrSingleEvent["end"] = $objOneEvent->getObjEndDate() != null ? $objOneEvent->getObjEndDate()->getTimeInOldStyle() : "";
                $arrSingleEvent["url"] = uniStrReplace("&amp;", "&", getLinkPortalHref($this->getParam("page"), "", "eventDetails", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle()));
                $arrPrintableEvents[] = $arrSingleEvent;
            }
        }

        class_response_object::getInstance()->setStResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($arrPrintableEvents);
    }


    /**
     * Creates a view of all event-details
     * @return string
     */
    protected function actionEventDetails() {
        $strReturn = "";
        $objEvent = new class_module_eventmanager_event($this->getSystemid());
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
            $arrTemplate["currentParticipants"] = count(class_module_eventmanager_participant::getAllParticipants($this->getSystemid()));

            if($objEvent->getIntRegistrationRequired() == "1" && $objEvent->rightRight1()) {
                $arrTemplate["registerLinkHref"] = getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objEvent->getSystemid(), "", $objEvent->getStrTitle());
                $strRegisterLinkID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_details_registerlink");
                $arrTemplate["registerLink"] = $this->fillTemplate($arrTemplate, $strRegisterLinkID);
            }
            $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_details");
            $strReturn .= $this->fillTemplate($arrTemplate, $strWrapperID);

            class_module_pages_portal::registerAdditionalTitle($objEvent->getStrTitle());
        }
        else
            $strReturn = $this->getLang("commons_error_permissions");

        return $strReturn;
    }

    protected function actionRegisterForEvent() {
        $strReturn = "";
        $objEvent = new class_module_eventmanager_event($this->getSystemid());
        if($objEvent->rightView() && $objEvent->rightRight1()) {

            if($objEvent->getIntLimitGiven() == "1" &&
                    $objEvent->getIntParticipantsLimit() <= count(class_module_eventmanager_participant::getAllParticipants($this->getSystemid()))) {

                $strMessage = $this->getLang("participantLimitReached");
                $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_register_message");
                $strReturn = $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);
                return $strReturn;
            }


            $arrErrors = array();
            $bitForm = true;
            //what to do?
            if($this->getParam("submitUserRegistration") != "") {
                $objTextValidator = new class_text_validator();
                $objMailValidator = new class_email_validator();

                if(!$objTextValidator->validate($this->getParam("forename"), 3))
                    $arrErrors[] = $this->getLang("noForename");

                if(!$objTextValidator->validate($this->getParam("lastname"), 3))
                    $arrErrors[] = $this->getLang("noLastname");

                if(!$objMailValidator->validate($this->getParam("email")))
                   $arrErrors[] = $this->getLang("invalidEmailadress");

                //Check captachcode
                if($this->getParam("form_captcha") == "" || $this->getParam("form_captcha") != $this->objSession->getCaptchaCode())
                    $arrErrors[] = $this->getLang("commons_captcha");

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

                $arrTemplate["formaction"] = getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $this->getSystemid(), "", $objEvent->getStrTitle());

                $arrTemplate["formErrors"] = "";
                if(count($arrErrors) > 0) {
                    $strErrTemplate = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "error_row");
                    foreach ($arrErrors as $strOneError) {
                        $arrTemplate["formErrors"] .= "".$this->fillTemplate(array("error" => $strOneError), $strErrTemplate);
                    }
                }

                $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_register");
                $strReturn .= $this->fillTemplate($arrTemplate, $strWrapperID);

            }
            else {

                $strMessage = "";
                if($objEvent->getIntLimitGiven() == "1" &&
                    $objEvent->getIntParticipantsLimit() <= count(class_module_eventmanager_participant::getAllParticipants($this->getSystemid()))) {

                    $strMessage = $this->getLang("participantLimitReached");
                }
                else {

                    //here we go, create the complete event registration
                    $objParticipant = new class_module_eventmanager_participant();
                    $objParticipant->setStrForename($this->getParam("forename"));
                    $objParticipant->setStrLastname($this->getParam("lastname"));
                    $objParticipant->setStrPhone($this->getParam("phone"));
                    $objParticipant->setStrEmail($this->getParam("email"));
                    $objParticipant->setStrComment($this->getParam("comment"));

                    $objParticipant->updateObjectToDb($this->getSystemid());

                    $objParticipant->setStatus("", "0");

                    $objMail = new class_mail();

                    $objMail->setSubject($this->getLang("registerMailSubject"));

                    $strBody = $this->getLang("registerMailBodyIntro");
                    $strBody .= $objEvent->getStrTitle()."<br />";
                    $strBody .= dateToString($objEvent->getObjStartDate(), true)."<br />";
                    $strBody .= $objEvent->getStrLocation()."<br />";
                    $strBody .= "\n";
                    $strTemp = getLinkPortalHref($this->getPagename(), "", "participantConfirmation", "&participantId=".$objParticipant->getSystemid(), $this->getSystemid(), "", $objEvent->getStrTitle());
                    $strBody .= html_entity_decode("<a href=\"".$strTemp."\">".$strTemp."</a>");

                    $objScriptlet = new class_scriptlet_helper();
                    $strBody = $objScriptlet->processString($strBody);

                    $objMail->setHtml($strBody);
                    $objMail->addTo($objParticipant->getStrEmail());
                    $objMail->sendMail();

                    $strMessage = $this->getLang("participantSuccessMail");

                }



                $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_register_message");
                $strReturn .= $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);
            }

            class_module_pages_portal::registerAdditionalTitle($objEvent->getStrTitle());
        }
        else
            $strReturn = $this->getLang("commons_error_permissions");

        return $strReturn;
    }

    protected function actionParticipantConfirmation() {
        $strMessage = "";
        $objEvent = new class_module_eventmanager_event($this->getSystemid());
        if($objEvent->rightView() && $objEvent->rightRight1() && validateSystemid($this->getParam("participantId"))) {

            $arrParticipants = class_module_eventmanager_participant::getAllParticipants($objEvent->getSystemid());
            foreach($arrParticipants as $objOneParticipant) {
                if($objOneParticipant->getSystemid() == $this->getParam("participantId")) {
                    $objOneParticipant->setStatus("", "1");
                    $strMessage = $this->getLang("participantSuccessConfirmation");
                    break;
                }
            }

            if($strMessage == "")
                $strMessage = $this->getLang("participantErrorConfirmation");

            class_module_pages_portal::registerAdditionalTitle($objEvent->getStrTitle());
        }
        else
            $strMessage = $this->getLang("commons_error_permissions");

        $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_register_message");
        $strReturn = $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);

        return $strReturn;
    }

}
