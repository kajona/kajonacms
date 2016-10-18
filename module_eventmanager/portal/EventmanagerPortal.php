<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Eventmanager\Portal;

use DateTime;
use Kajona\Eventmanager\System\EventmanagerEvent;
use Kajona\Eventmanager\System\EventmanagerParticipant;
use Kajona\Pages\Portal\PagesPortalController;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\Mail;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Rssfeed;
use Kajona\System\System\ScriptletHelper;
use Kajona\System\System\TemplateMapper;
use Kajona\System\System\UserUser;
use Kajona\System\System\Validators\EmailValidator;
use Kajona\System\System\Validators\TextValidator;

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
class EventmanagerPortal extends PortalController implements PortalInterface
{

    /**
     * Creates the list of events available
     *
     * @return string
     * @permissions view
     */
    protected function actionList()
    {
        $strReturn = "";
        $strEvents = "";

        //switch between calendar and list-modes
        if ($this->arrElementData["int2"] == "0") {
            //calendar mode
            $arrTemplate = array();
            $arrTemplate["cal_eventsource"] = _xmlpath_."?module=eventmanager&action=getJsonEvents&page=".$this->getPagename();
            $arrTemplate["rssurl"] = _xmlpath_."?module=eventmanager&action=eventRssFeed&page=".$this->getPagename();
            $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_eventmanager/".$this->arrElementData["char1"], "event_calendar");
        }
        else {
            //list based mode

            $objFilterStartDate = null;
            $objFilterEndDate = null;
            $intFilterStatus = $this->getParam("event_filter_status") != "" ? htmlspecialchars($this->getParam("event_filter_status"), ENT_QUOTES, "UTF-8", false) : null;

            if ($this->getParam("event_filter_date_from") != "") {
                $objDateTime = DateTime::createFromFormat("Y-m-d", $this->getParam("event_filter_date_from"));
                $objFilterStartDate = new \Kajona\System\System\Date();
                $objFilterStartDate->setTimeInOldStyle($objDateTime->getTimestamp());
            }
            if ($this->getParam("event_filter_date_to") != "") {
                $objDateTime = DateTime::createFromFormat("Y-m-d", $this->getParam("event_filter_date_to"));
                $objFilterEndDate = new \Kajona\System\System\Date();
                $objFilterEndDate->setTimeInOldStyle($objDateTime->getTimestamp());
            }


            $arrEvents = EventmanagerEvent::getAllEvents(false, false, $objFilterStartDate, $objFilterEndDate, true, $this->arrElementData["int1"], $intFilterStatus);
            foreach ($arrEvents as $objOneEvent) {
                if ($objOneEvent->rightView()) {
                    $objMapper = new TemplateMapper($objOneEvent);

                    //legacy support
                    $objMapper->addPlaceholder("dateTimeFrom", dateToString($objOneEvent->getObjStartDate(), true));
                    $objMapper->addPlaceholder("dateFrom", dateToString($objOneEvent->getObjStartDate(), false));
                    $objMapper->addPlaceholder("dateTimeUntil", dateToString($objOneEvent->getObjEndDate(), true));
                    $objMapper->addPlaceholder("dateUntil", dateToString($objOneEvent->getObjEndDate(), false));
                    $objMapper->addPlaceholder("title", $objOneEvent->getStrTitle());
                    $objMapper->addPlaceholder("description", $objOneEvent->getStrDescription());
                    $objMapper->addPlaceholder("location", $objOneEvent->getStrLocation());
                    $objMapper->addPlaceholder("eventStatus", $objOneEvent->getIntEventStatus());
                    $objMapper->addPlaceholder("systemid", $objOneEvent->getSystemid());
                    $objMapper->addPlaceholder("detailsLinkHref", Link::getLinkPortalHref($this->getPagename(), "", "eventDetails", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle()));

                    if ($objOneEvent->getIntRegistrationRequired() == "1" && $objOneEvent->rightRight1()) {
                        $objMapper->addPlaceholder("registerLinkHref", Link::getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle()));
                        $objMapper->addPlaceholder("registerLink", $objMapper->writeToTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_details_registerlink"));
                    }
                    $strEvents .= $objMapper->writeToTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_list_entry");
                }
            }

            $strRssUrl = _xmlpath_."?module=eventmanager&action=eventRssFeed&page=".$this->getPagename();


            $arrListTemplate = array(
                "events"                 => $strEvents,
                "rssurl"                 => $strRssUrl,
                "formaction"             => Link::getLinkPortalHref($this->getPagename()),
                "event_filter_status"    => $intFilterStatus != null ? $intFilterStatus : "",
                "event_filter_date_from" => $objFilterStartDate != null ? htmlspecialchars($this->getParam("event_filter_date_from"), ENT_QUOTES, "UTF-8", false) : "",
                "event_filter_date_to"   => $objFilterEndDate != null ? htmlspecialchars($this->getParam("event_filter_date_to"), ENT_QUOTES, "UTF-8", false) : ""
            );

            $strReturn .= $this->objTemplate->fillTemplateFile($arrListTemplate, "/module_eventmanager/".$this->arrElementData["char1"], "event_list");
        }

        return $strReturn;
    }

    /**
     * Returns all eventes in json-format.
     * Expects the params start & end.
     *
     * @return string
     * @permissions view
     */
    protected function actionGetJsonEvents()
    {

        $arrPrintableEvents = array();
        $objStartDate = null;
        $objEndDate = null;
        if ($this->getParam("start") != "" && $this->getParam("end") != "") {
            $objStartDate = new \Kajona\System\System\Date($this->getParam("start"));
            $objEndDate = new \Kajona\System\System\Date($this->getParam("end"));
        }

        $arrEvents = EventmanagerEvent::getAllEvents(false, false, $objStartDate, $objEndDate, true);
        foreach ($arrEvents as $objOneEvent) {
            if ($objOneEvent->rightView()) {
                $arrSingleEvent = array();
                $arrSingleEvent["id"] = $objOneEvent->getSystemid();
                $arrSingleEvent["title"] = $objOneEvent->getStrTitle();
                $arrSingleEvent["start"] = $objOneEvent->getObjStartDate()->getTimeInOldStyle();
                $arrSingleEvent["end"] = $objOneEvent->getObjEndDate() != null ? $objOneEvent->getObjEndDate()->getTimeInOldStyle() : "";
                $arrSingleEvent["url"] = uniStrReplace("&amp;", "&", Link::getLinkPortalHref($this->getParam("page"), "", "eventDetails", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle()));
                $arrPrintableEvents[] = $arrSingleEvent;
            }
        }

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
        return json_encode($arrPrintableEvents);
    }

    /**
     * Renders the current list of events in a rss-feed.
     * Expecets the param pagename for rendering the detail-links
     *
     * @permissions view
     * @return string
     */
    protected function actionEventRssFeed()
    {
        $arrEvents = EventmanagerEvent::getAllEvents(false, false, null, null, true);

        $objFeed = new Rssfeed();
        $objFeed->setStrTitle($this->getLang("modul_titel"));

        foreach ($arrEvents as $objOneEvent) {
            if ($objOneEvent->rightView()) {
                $objFeed->addElement(
                    $objOneEvent->getStrTitle(),
                    Link::getLinkPortalHref($this->getParam("pagename"), "", "eventDetails", "", $objOneEvent->getSystemid(), "", $objOneEvent->getStrTitle()),
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
    protected function actionEventDetails()
    {
        $strReturn = "";
        $objEvent = new EventmanagerEvent($this->getSystemid());
        $objMapper = new TemplateMapper($objEvent);


        //legacy support
        $objMapper->addPlaceholder("title", $objEvent->getStrTitle());
        $objMapper->addPlaceholder("description", $objEvent->getStrDescription());
        $objMapper->addPlaceholder("location", $objEvent->getStrLocation());
        $objMapper->addPlaceholder("dateTimeFrom", dateToString($objEvent->getObjStartDate(), true));
        $objMapper->addPlaceholder("dateFrom", dateToString($objEvent->getObjStartDate(), false));
        $objMapper->addPlaceholder("dateTimeUntil", dateToString($objEvent->getObjEndDate(), true));
        $objMapper->addPlaceholder("dateUntil", dateToString($objEvent->getObjEndDate(), false));
        $objMapper->addPlaceholder("systemid", $objEvent->getSystemid());
        $objMapper->addPlaceholder("eventStatus", $objEvent->getIntEventStatus());
        $objMapper->addPlaceholder("maximumParticipants", $objEvent->getIntParticipantsLimit());
        $objMapper->addPlaceholder("intMaximumParticipants", $objEvent->getIntParticipantsLimit());
        $objMapper->addPlaceholder("currentParticipants", EventmanagerParticipant::getActiveParticipantsCount($this->getSystemid()));

        if ($objEvent->getIntRegistrationRequired() == "1" && $objEvent->rightRight1()) {
            if ($this->objSession->isLoggedin()
                && $this->objTemplate->providesSection("/module_eventmanager/".$this->arrElementData["char1"], "event_register_loggedin")
                && $objEvent->isParticipant($this->objSession->getUserID())
            ) {
                $objMapper->addPlaceholder("registerLinkHref", Link::getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objEvent->getSystemid(), "", $objEvent->getStrTitle()));
                $objMapper->addPlaceholder("registerLink", $objMapper->writeToTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_details_updatelink"));
            }
            else {

                $objMapper->addPlaceholder("registerLinkHref", Link::getLinkPortalHref($this->getPagename(), "", "registerForEvent", "", $objEvent->getSystemid(), "", $objEvent->getStrTitle()));
                $objMapper->addPlaceholder("registerLink", $objMapper->writeToTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_details_registerlink"));
            }
        }
        $strReturn .= $objMapper->writeToTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_details");

        PagesPortalController::registerAdditionalTitle($objEvent->getStrTitle());

        return $strReturn;
    }

    /**
     * @param array $arrErrors
     *
     * @return string
     * @permissions view,right1
     */
    protected function actionRegisterForEvent($arrErrors = array())
    {
        $strReturn = "";
        $objEvent = new EventmanagerEvent($this->getSystemid());

        if ($objEvent->getIntLimitGiven() == "1" && $objEvent->getIntParticipantsLimit() <= EventmanagerParticipant::getActiveParticipantsCount($this->getSystemid())) {
            $strMessage = $this->getLang("participantLimitReached");
            $strReturn = $this->objTemplate->fillTemplateFile(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), "/module_eventmanager/".$this->arrElementData["char1"], "event_register_message");
            return $strReturn;
        }

        $bitIsLoggedin = false;
        if ($this->objSession->isLoggedin() && $this->objTemplate->providesSection("/module_eventmanager/".$this->arrElementData["char1"], "event_register_loggedin")) {
            $bitIsLoggedin = true;

            if ($objEvent->isParticipant($this->objSession->getUserID())) {
                $objParticpant = EventmanagerParticipant::getParticipantByUserid($this->objSession->getUserID(), $objEvent->getSystemid());
                $this->setParam("comment", $objParticpant->getStrComment());
                $this->setParam("participant_status", $objParticpant->getIntParticipationStatus());
            }
        }


        $objMapper = new TemplateMapper($objEvent);

        $objMapper->addPlaceholder("forename", htmlspecialchars($this->getParam("forename"), ENT_QUOTES, "UTF-8", false));
        $objMapper->addPlaceholder("lastname", htmlspecialchars($this->getParam("lastname"), ENT_QUOTES, "UTF-8", false));
        $objMapper->addPlaceholder("phone", htmlspecialchars($this->getParam("phone"), ENT_QUOTES, "UTF-8", false));
        $objMapper->addPlaceholder("comment", htmlspecialchars($this->getParam("comment"), ENT_QUOTES, "UTF-8", false));
        $objMapper->addPlaceholder("email", htmlspecialchars($this->getParam("email"), ENT_QUOTES, "UTF-8", false));
        $objMapper->addPlaceholder("participant_status", htmlspecialchars($this->getParam("participant_status"), ENT_QUOTES, "UTF-8", false));
        $objMapper->addPlaceholder("title", $objEvent->getStrTitle());
        $objMapper->addPlaceholder("dateTimeFrom", dateToString($objEvent->getObjStartDate(), true));
        $objMapper->addPlaceholder("dateFrom", dateToString($objEvent->getObjStartDate(), false));
        $objMapper->addPlaceholder("dateTimeUntil", dateToString($objEvent->getObjEndDate(), true));
        $objMapper->addPlaceholder("dateUntil", dateToString($objEvent->getObjEndDate(), false));
        $objMapper->addPlaceholder("formaction", Link::getLinkPortalHref($this->getPagename(), "", "saveRegisterForEvent", "", $this->getSystemid(), "", $objEvent->getStrTitle()));

        if ($bitIsLoggedin) {
            $objUser = $this->objSession->getUser();
            $objMapper->addPlaceholder("username", $objUser->getStrUsername());
        }

        $strErrors = "";
        $arrErrorFields = array();
        if (count($arrErrors) > 0) {
            foreach ($arrErrors as $strKey => $strOneError) {
                $strErrors .= "".$this->objTemplate->fillTemplateFile(array("error" => $strOneError), "/module_eventmanager/".$this->arrElementData["char1"], "error_row");
                $arrErrorFields[] = "'{$strKey}'";
            }
            $strErrors = $this->objTemplate->fillTemplateFile(array("error_list" => $strErrors), "/module_eventmanager/".$this->arrElementData["char1"], "errors");
        }
        $objMapper->addPlaceholder("formErrors", $strErrors);
        $objMapper->addPlaceholder("error_fields", implode(",", $arrErrorFields));

        $strReturn .= $objMapper->writeToTemplate("/module_eventmanager/".$this->arrElementData["char1"], "event_register".($bitIsLoggedin ? "_loggedin" : ""));

        PagesPortalController::registerAdditionalTitle($objEvent->getStrTitle());

        return $strReturn;
    }


    /**
     * @return string
     * @permissions view,right1
     */
    protected function actionSaveRegisterForEvent()
    {
        $strReturn = "";
        $objEvent = new EventmanagerEvent($this->getSystemid());
        PagesPortalController::registerAdditionalTitle($objEvent->getStrTitle());


        $bitIsLoggedin = false;
        $bitIsParticipant = false;
        if ($this->objSession->isLoggedin() && $this->objTemplate->providesSection("/module_eventmanager/".$this->arrElementData["char1"], "event_register_loggedin")) {
            $bitIsLoggedin = true;

            if ($objEvent->isParticipant($this->objSession->getUserID())) {
                $bitIsParticipant = true;
            }
        }

        $arrErrors = array();
        //what to do?
        $objTextValidator = new TextValidator();
        $objMailValidator = new EmailValidator();

        if (!$bitIsLoggedin && !$objTextValidator->validate($this->getParam("forename"))) {
            $arrErrors["forename"] = $this->getLang("noForename");
        }

        if (!$bitIsLoggedin && !$objTextValidator->validate($this->getParam("lastname"))) {
            $arrErrors["lastname"] = $this->getLang("noLastname");
        }


        if (!$bitIsLoggedin && !$objMailValidator->validate($this->getParam("email"))) {
            $arrErrors["email"] = $this->getLang("invalidEmailadress");
        }


        //Check captachcode
        if (!$bitIsLoggedin && ($this->getParam("form_captcha") == "" || $this->getParam("form_captcha") != $this->objSession->getCaptchaCode())) {
            $arrErrors["form_captcha"] = $this->getLang("commons_captcha");
        }


        if (count($arrErrors) != 0) {
            return $this->actionRegisterForEvent($arrErrors);
        }


        if ($objEvent->getIntLimitGiven() == "1" && $objEvent->getIntParticipantsLimit() <= EventmanagerParticipant::getActiveParticipantsCount($this->getSystemid())) {
            $strMessage = $this->getLang("participantLimitReached");
            $strReturn = $this->objTemplate->fillTemplateFile(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), "/module_eventmanager/".$this->arrElementData["char1"], "event_register_message");
            return $strReturn;
        }

        if ($bitIsParticipant) {
            $objParticipant = EventmanagerParticipant::getParticipantByUserid($this->objSession->getUserID(), $objEvent->getSystemid());
        }
        else {
            $objParticipant = new EventmanagerParticipant();
        }

        //here we go, create the complete event registration
        $objParticipant->setStrComment($this->getParam("comment"));


        if ($bitIsLoggedin) {
            $objParticipant->setStrUserId($this->objSession->getUserID());
            $objParticipant->setIntParticipationStatus($this->getParam("participant_status"));
        }
        else {
            $objParticipant->setStrForename($this->getParam("forename"));
            $objParticipant->setStrLastname($this->getParam("lastname"));
            $objParticipant->setStrPhone($this->getParam("phone"));
            $objParticipant->setStrEmail($this->getParam("email"));
        }

        $objParticipant->updateObjectToDb($this->getSystemid());

        if ($bitIsParticipant) {
            $strMessage = $this->getLang("participantUpdateMessage");
            return $this->objTemplate->fillTemplateFile(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), "/module_eventmanager/".$this->arrElementData["char1"], "event_register_message");
        }


        $objParticipant->setIntRecordStatus(0);
        $objParticipant->updateObjectToDb();

        $objMail = new Mail();
        $objMail->setSubject($this->getLang("registerMailSubject"));

        $strBody = $this->getLang("registerMailBodyIntro");
        $strBody .= $objEvent->getStrTitle()."<br />";
        $strBody .= dateToString($objEvent->getObjStartDate(), true)."<br />";
        $strBody .= $objEvent->getStrLocation()."<br />";
        $strBody .= "\n";
        $strTemp = Link::getLinkPortalHref($this->getPagename(), "", "participantConfirmation", "&participantId=".$objParticipant->getSystemid(), $this->getSystemid(), "", $objEvent->getStrTitle());
        $strBody .= html_entity_decode("<a href=\"".$strTemp."\">".$strTemp."</a>");

        $objScriptlet = new ScriptletHelper();
        $strBody = $objScriptlet->processString($strBody);

        $objMail->setHtml($strBody);
        $objMail->addTo($objParticipant->getStrEmail());
        $objMail->sendMail();

        $strMessage = $this->getLang("participantSuccessMail");
        $strReturn .= $this->objTemplate->fillTemplateFile(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), "/module_eventmanager/".$this->arrElementData["char1"], "event_register_message");

        return $strReturn;
    }

    /**
     * @return string
     * @permissions view,right
     */
    protected function actionParticipantConfirmation()
    {
        $strMessage = "";
        $objEvent = new EventmanagerEvent($this->getSystemid());
        if (validateSystemid($this->getParam("participantId"))) {

            $arrParticipants = EventmanagerParticipant::getObjectListFiltered(null, $objEvent->getSystemid());
            foreach ($arrParticipants as $objOneParticipant) {
                if ($objOneParticipant->getSystemid() == $this->getParam("participantId")) {
                    $objOneParticipant->setIntRecordStatus(1);
                    $objOneParticipant->updateObjectToDb();
                    $strMessage = $this->getLang("participantSuccessConfirmation");
                    break;
                }
            }

            if ($strMessage == "") {
                $strMessage = $this->getLang("participantErrorConfirmation");
            }

            PagesPortalController::registerAdditionalTitle($objEvent->getStrTitle());
        }
        else {
            $strMessage = $this->getLang("commons_error_permissions");
        }

        $strReturn = $this->objTemplate->fillTemplateFile(array("title" => $objEvent->getStrTitle(), "message" => $strMessage), "/module_eventmanager/".$this->arrElementData["char1"], "event_register_message");
        return $strReturn;
    }

}
