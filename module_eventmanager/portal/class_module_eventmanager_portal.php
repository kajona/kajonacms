<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						*
********************************************************************************************************/

/**
 * Portal-class of the eventmanager. Handles the printing of eventmanager lists / detail
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 * @module eventmanager
 * @moduleId _eventmanager_module_id_
 */
class class_module_eventmanager_portal extends class_portal implements interface_portal {

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
            $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_calendar");
            $arrTemplate = array();
            $arrTemplate["cal_eventsource"] = _xmlpath_ . "?module=eventmanager&action=getJsonEvents&page=" . $this->getPagename();
            $arrTemplate["rssurl"] = _xmlpath_ . "?module=eventmanager&action=eventRssFeed&page=" . $this->getPagename();
            $strReturn .= $this->fillTemplate($arrTemplate, $strWrapperID);
        }
        else {
            //list based mode

            $objFilterStartDate = null;
            $objFilterEndDate = null;
            $intFilterStatus = $this->getParam("event_filter_status") != "" ? htmlspecialchars($this->getParam("event_filter_status"), ENT_QUOTES, "UTF-8", false) : null;

            if($this->getParam("event_filter_date_from") != "") {
                $objDateTime = DateTime::createFromFormat("Y-m-d", $this->getParam("event_filter_date_from"));
                $objFilterStartDate = new class_date();
                $objFilterStartDate->setTimeInOldStyle($objDateTime->getTimestamp());
            }
            if($this->getParam("event_filter_date_to") != "") {
                $objDateTime = DateTime::createFromFormat("Y-m-d", $this->getParam("event_filter_date_to"));
                $objFilterEndDate = new class_date();
                $objFilterEndDate->setTimeInOldStyle($objDateTime->getTimestamp());
            }



            $arrEvents = class_module_eventmanager_event::getAllEvents(false, false, $objFilterStartDate, $objFilterEndDate, true, $this->arrElementData["int1"], $intFilterStatus);
            $strEventTemplateID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_list_entry");
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
                    $arrTemplate["eventStatus"] = $objOneEvent->getIntEventStatus();
                    $arrTemplate["systemid"] = $objOneEvent->getSystemid();
                    $arrTemplate["detailsLinkHref"] = getLinkPortalHref($this->getPagename(), "", "eventDetails", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle());

                    if($objOneEvent->getIntRegistrationRequired() == "1" && $objOneEvent->rightRight1()) {
                        $arrTemplate["registerLinkHref"] = getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle());
                        $strRegisterLinkID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_details_registerlink");
                        $arrTemplate["registerLink"] = $this->fillTemplate($arrTemplate, $strRegisterLinkID);
                    }
                    $strEvents .= $this->fillTemplate($arrTemplate, $strEventTemplateID);
                }
            }

            $strRssUrl = _xmlpath_ . "?module=eventmanager&action=eventRssFeed&page=" . $this->getPagename();


            $arrListTemplate = array(
                "events" => $strEvents,
                "rssurl" => $strRssUrl,
                "formaction" => getLinkPortalHref($this->getPagename()),
                "event_filter_status" => $intFilterStatus != null ? $intFilterStatus : "",
                "event_filter_date_from" => $objFilterStartDate != null ? htmlspecialchars($this->getParam("event_filter_date_from"), ENT_QUOTES, "UTF-8", false) : "",
                "event_filter_date_to" => $objFilterEndDate != null ? htmlspecialchars($this->getParam("event_filter_date_to"), ENT_QUOTES, "UTF-8", false) : ""
            );

            $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_list");
            $strReturn .= $this->fillTemplate($arrListTemplate, $strWrapperID);
        }

        return $strReturn;
    }

    /**
     * Returns all eventes in json-format.
     * Expects the params start & end.
     * @xml
     * @return string
     * @permissions view
     */
    protected function actionGetJsonEvents() {

        $arrPrintableEvents = array();
        $objStartDate = null;
        $objEndDate = null;
        if($this->getParam("start") != "" && $this->getParam("end") != "") {
            $objStartDate = new class_date($this->getParam("start"));
            $objEndDate = new class_date($this->getParam("end"));
        }

        $arrEvents = class_module_eventmanager_event::getAllEvents(false, false, $objStartDate, $objEndDate, true);
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
     * Renders the current list of events in a rss-feed.
     * Expecets the param pagename for rendering the detail-links
     * @permissions view
     * @xml
     */
    protected function actionEventRssFeed() {
        $arrEvents = class_module_eventmanager_event::getAllEvents(false, false, null, null, true);

        $objFeed = new class_rssfeed();
        $objFeed->setStrTitle($this->getLang("modul_titel"));

        foreach($arrEvents as $objOneEvent) {
            if($objOneEvent->rightView()) {
                $objFeed->addElement(
                    $objOneEvent->getStrTitle(),
                    getLinkPortalHref($this->getParam("pagename"), "", "eventDetails", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle()),
                    $objOneEvent->getSystemid(),
                    $objOneEvent->getStrDescription(),
                    $objOneEvent->getObjStartDate()->getTimeInOldStyle()
                );
            }
        }
        return $objFeed->generateFeed();
    }


    /**
     * Creates a view of all event-details
     *
     * @return string
     * @permissions view
     */
    protected function actionEventDetails() {
        $strReturn = "";
        $objEvent = new class_module_eventmanager_event($this->getSystemid());

        $arrTemplate = array();
        $arrTemplate["title"] = $objEvent->getStrTitle();
        $arrTemplate["description"] = $objEvent->getStrDescription();
        $arrTemplate["location"] = $objEvent->getStrLocation();
        $arrTemplate["dateTimeFrom"] = dateToString($objEvent->getObjStartDate(), true);
        $arrTemplate["dateFrom"] = dateToString($objEvent->getObjStartDate(), false);
        $arrTemplate["dateTimeUntil"] = dateToString($objEvent->getObjEndDate(), true);
        $arrTemplate["dateUntil"] = dateToString($objEvent->getObjEndDate(), false);
        $arrTemplate["systemid"] = $objEvent->getSystemid();
        $arrTemplate["eventStatus"] = $objEvent->getIntEventStatus();

        $arrTemplate["maximumParticipants"] = $objEvent->getIntParticipantsLimit();
        $arrTemplate["currentParticipants"] = class_module_eventmanager_participant::getActiveParticipantsCount($this->getSystemid());

        if($objEvent->getIntRegistrationRequired() == "1" && $objEvent->rightRight1()) {
            if($this->objSession->isLoggedin()
                && $this->objTemplate->containsSection($this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"]), "event_register_loggedin")
                && $objEvent->isParticipant($this->objSession->getUserID())
            ) {
                $arrTemplate["registerLinkHref"] = getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objEvent->getSystemid(), "", $objEvent->getStrTitle());
                $strRegisterLinkID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_details_updatelink");
                $arrTemplate["registerLink"] = $this->fillTemplate($arrTemplate, $strRegisterLinkID);
            }
            else {

                $arrTemplate["registerLinkHref"] = getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objEvent->getSystemid(), "", $objEvent->getStrTitle());
                $strRegisterLinkID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_details_registerlink");
                $arrTemplate["registerLink"] = $this->fillTemplate($arrTemplate, $strRegisterLinkID);
            }
        }
        $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_details");
        $strReturn .= $this->fillTemplate($arrTemplate, $strWrapperID);

        class_module_pages_portal::registerAdditionalTitle($objEvent->getStrTitle());

        return $strReturn;
    }

    /**
     * @param array $arrErrors
     *
     * @return string
     * @permissions view,right1
     */
    protected function actionRegisterForEvent($arrErrors = array()) {
        $strReturn = "";
        $objEvent = new class_module_eventmanager_event($this->getSystemid());

        if($objEvent->getIntLimitGiven() == "1" && $objEvent->getIntParticipantsLimit() <= class_module_eventmanager_participant::getActiveParticipantsCount($this->getSystemid())) {
            $strMessage = $this->getLang("participantLimitReached");
            $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_register_message");
            $strReturn = $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);
            return $strReturn;
        }

        $bitIsLoggedin = false;
        if($this->objSession->isLoggedin() && $this->objTemplate->containsSection($this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"]), "event_register_loggedin")) {
            $bitIsLoggedin = true;

            if($objEvent->isParticipant($this->objSession->getUserID())) {
                $objParticpant = class_module_eventmanager_participant::getParticipantByUserid($this->objSession->getUserID(), $objEvent->getSystemid());
                $this->setParam("comment", $objParticpant->getStrComment());
                $this->setParam("participant_status", $objParticpant->getIntParticipationStatus());
            }
        }


        $arrTemplate = array();

        $arrTemplate["forename"] = $this->getParam("forename");
        $arrTemplate["lastname"] = $this->getParam("lastname");
        $arrTemplate["phone"] = $this->getParam("phone");
        $arrTemplate["comment"] = $this->getParam("comment");
        $arrTemplate["email"] = $this->getParam("email");
        $arrTemplate["participant_status"] = $this->getParam("participant_status");

        $arrTemplate["title"] = $objEvent->getStrTitle();
        $arrTemplate["dateTimeFrom"] = dateToString($objEvent->getObjStartDate(), true);
        $arrTemplate["dateFrom"] = dateToString($objEvent->getObjStartDate(), false);
        $arrTemplate["dateTimeUntil"] = dateToString($objEvent->getObjEndDate(), true);
        $arrTemplate["dateUntil"] = dateToString($objEvent->getObjEndDate(), false);

        $arrTemplate["formaction"] = getLinkPortalHref($this->getPagename(), "", "saveRegisterForEvent", "", $this->getSystemid(), "", $objEvent->getStrTitle());

        if($bitIsLoggedin) {
            $objUser = new class_module_user_user($this->objSession->getUserID());
            $arrTemplate["username"] = $objUser->getStrUsername();
        }

        $arrTemplate["formErrors"] = "";
        if(count($arrErrors) > 0) {
            $strErrTemplate = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "error_row");
            foreach($arrErrors as $strOneError) {
                $arrTemplate["formErrors"] .= "" . $this->fillTemplate(array("error" => $strOneError), $strErrTemplate);
            }
        }

        $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_register".($bitIsLoggedin ? "_loggedin" : ""));
        $strReturn .= $this->fillTemplate($arrTemplate, $strWrapperID);

        class_module_pages_portal::registerAdditionalTitle($objEvent->getStrTitle());

        return $strReturn;
    }




    /**
     * @return string
     * @permissions view,right1
     */
    protected function actionSaveRegisterForEvent() {
        $strReturn = "";
        $objEvent = new class_module_eventmanager_event($this->getSystemid());
        class_module_pages_portal::registerAdditionalTitle($objEvent->getStrTitle());


        $bitIsLoggedin = false;
        $bitIsParticipant = false;
        if($this->objSession->isLoggedin() && $this->objTemplate->containsSection($this->objTemplate->readTemplate("/module_eventmanager/".$this->arrElementData["char1"]), "event_register_loggedin")) {
            $bitIsLoggedin = true;

            if($objEvent->isParticipant($this->objSession->getUserID()))
                $bitIsParticipant = true;
        }

        $arrErrors = array();
        //what to do?
        $objTextValidator = new class_text_validator();
        $objMailValidator = new class_email_validator();

        if(!$bitIsLoggedin && !$objTextValidator->validate($this->getParam("forename"), 3))
            $arrErrors[] = $this->getLang("noForename");

        if(!$bitIsLoggedin && !$objTextValidator->validate($this->getParam("lastname"), 3))
            $arrErrors[] = $this->getLang("noLastname");


        if(!$bitIsLoggedin && !$objMailValidator->validate($this->getParam("email")))
            $arrErrors[] = $this->getLang("invalidEmailadress");


        //Check captachcode
        if(!$bitIsLoggedin && ($this->getParam("form_captcha") == "" || $this->getParam("form_captcha") != $this->objSession->getCaptchaCode()))
            $arrErrors[] = $this->getLang("commons_captcha");


        if(count($arrErrors) != 0)
            return $this->actionRegisterForEvent($arrErrors);


        if($objEvent->getIntLimitGiven() == "1" && $objEvent->getIntParticipantsLimit() <= class_module_eventmanager_participant::getActiveParticipantsCount($this->getSystemid())) {
            $strMessage = $this->getLang("participantLimitReached");
            $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_register_message");
            $strReturn = $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);
            return $strReturn;
        }

        if($bitIsParticipant)
            $objParticipant = class_module_eventmanager_participant::getParticipantByUserid($this->objSession->getUserID(), $objEvent->getSystemid());
        else
            $objParticipant = new class_module_eventmanager_participant();

        //here we go, create the complete event registration
        $objParticipant->setStrComment($this->getParam("comment"));


        if($bitIsLoggedin) {
            $objParticipant->setStrUserId($this->objSession->getUserID());
            $objParticipant->setIntParticipationStatus($this->getParam("participant_status"));
        } else {
            $objParticipant->setStrForename($this->getParam("forename"));
            $objParticipant->setStrLastname($this->getParam("lastname"));
            $objParticipant->setStrPhone($this->getParam("phone"));
            $objParticipant->setStrEmail($this->getParam("email"));
        }

        $objParticipant->updateObjectToDb($this->getSystemid());

        if($bitIsParticipant) {
            $strMessage = $this->getLang("participantUpdateMessage");

            $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_register_message");
            return $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);
        }


        $objParticipant->setIntRecordStatus(0);

        $objMail = new class_mail();
        $objMail->setSubject($this->getLang("registerMailSubject"));

        $strBody = $this->getLang("registerMailBodyIntro");
        $strBody .= $objEvent->getStrTitle() . "<br />";
        $strBody .= dateToString($objEvent->getObjStartDate(), true) . "<br />";
        $strBody .= $objEvent->getStrLocation() . "<br />";
        $strBody .= "\n";
        $strTemp = getLinkPortalHref($this->getPagename(), "", "participantConfirmation", "&participantId=" . $objParticipant->getSystemid(), $this->getSystemid(), "", $objEvent->getStrTitle());
        $strBody .= html_entity_decode("<a href=\"" . $strTemp . "\">" . $strTemp . "</a>");

        $objScriptlet = new class_scriptlet_helper();
        $strBody = $objScriptlet->processString($strBody);

        $objMail->setHtml($strBody);
        $objMail->addTo($objParticipant->getStrEmail());
        $objMail->sendMail();

        $strMessage = $this->getLang("participantSuccessMail");

        $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_register_message");
        $strReturn .= $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);




        return $strReturn;
    }

    /**
     * @return string
     * @permissions view,right
     */
    protected function actionParticipantConfirmation() {
        $strMessage = "";
        $objEvent = new class_module_eventmanager_event($this->getSystemid());
        if(validateSystemid($this->getParam("participantId"))) {

            $arrParticipants = class_module_eventmanager_participant::getObjectList($objEvent->getSystemid());
            foreach($arrParticipants as $objOneParticipant) {
                if($objOneParticipant->getSystemid() == $this->getParam("participantId")) {
                    $objOneParticipant->setIntRecordStatus(1);
                    $strMessage = $this->getLang("participantSuccessConfirmation");
                    break;
                }
            }

            if($strMessage == "") {
                $strMessage = $this->getLang("participantErrorConfirmation");
            }

            class_module_pages_portal::registerAdditionalTitle($objEvent->getStrTitle());
        }
        else {
            $strMessage = $this->getLang("commons_error_permissions");
        }

        $strWrapperID = $this->objTemplate->readTemplate("/module_eventmanager/" . $this->arrElementData["char1"], "event_register_message");
        $strReturn = $this->fillTemplate(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), $strWrapperID);

        return $strReturn;
    }

}
