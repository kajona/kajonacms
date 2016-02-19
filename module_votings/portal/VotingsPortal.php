<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

namespace Kajona\Votings\Portal;

use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\Cookie;
use Kajona\System\System\Link;
use Kajona\Votings\System\VotingsAnswer;
use Kajona\Votings\System\VotingsVoting;

/**
 * Portal-class of the votings. Handles the printing of votings lists / detail
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 *
 * @module votings
 * @moduleId _votings_module_id_
 */
class VotingsPortal extends PortalController implements PortalInterface {

    private $STR_COOKIE_NAME = "kajona_voting";
    private $arrCookieValues = array();

    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct($arrElementData = array(), $strSystemid = "") {
        parent::__construct($arrElementData, $strSystemid);

        // save a cookie to store the voting
        $objCookie = new Cookie();
        $this->arrCookieValues = explode(",", $objCookie->getCookie($this->STR_COOKIE_NAME));

        //any actions to perform before? e.g. voting...
        if($this->getAction() == "submitVoting") {
            $this->actionSubmitVoting();
            $this->setAction("list");
        }

    }


    /**
     * Returns a single view of a single voting.
     * The mode is choosen from the element-config.
     *
     * @return string
     * @permissions view
     */
    public function actionList() {
        $strReturn = "";

        //load the associated voting
        $objVoting = new VotingsVoting($this->arrElementData["char1"]);

        //view-permissions given?
        if($objVoting->rightView()) {

            $strVotingContent = "";

            if($this->arrElementData["int1"] == 0) {
                //voting mode
                //permissions sufficient?
                if($objVoting->rightRight1()) {

                    //check the start n end dates
                    $objDateStart = $objVoting->getObjStartDate();
                    $objDateEnd = $objVoting->getObjEndDate();

                    $bitDatesAllow = true;
                    if($objDateStart != null && $objDateStart->getLongTimestamp() > \Kajona\System\System\Date::getCurrentTimestamp()) {
                        $bitDatesAllow = false;
                    }

                    if($objDateEnd != null && $objDateEnd->getLongTimestamp() < \Kajona\System\System\Date::getCurrentTimestamp()) {
                        $bitDatesAllow = false;
                    }

                    //already voted before?
                    if(in_array($objVoting->getSystemid(), $this->arrCookieValues)) {
                        $strVotingContent = $this->getLang("error_voted");
                    }
                    else if(!$bitDatesAllow) {
                        $strVotingContent = $this->getLang("error_dates");
                    }
                    else {

                        $strAnswers = "";
                        $strAnswerTemplateID = $strListTemplateID = $this->objTemplate->readTemplate("/module_votings/" . $this->arrElementData["char2"], "voting_voting_option");
                        //load the list of answers
                        /** @var VotingsAnswer $objOneAnswer */
                        foreach(VotingsAnswer::getObjectList($objVoting->getSystemid()) as $objOneAnswer) {
                            if($objOneAnswer->getIntRecordStatus() == 0)
                                continue;

                            $arrTemplate = array();
                            $arrTemplate["voting_systemid"] = $objVoting->getSystemid();
                            $arrTemplate["answer_systemid"] = $objOneAnswer->getSystemid();
                            $arrTemplate["answer_text"] = $objOneAnswer->getStrText();

                            $strAnswers .= $this->fillTemplate($arrTemplate, $strAnswerTemplateID);
                        }


                        //create the wrapper
                        $strFormTemplateID = $strListTemplateID = $this->objTemplate->readTemplate("/module_votings/" . $this->arrElementData["char2"], "voting_voting");
                        $arrTemplate = array();
                        $arrTemplate["voting_answers"] = $strAnswers;
                        $arrTemplate["voting_systemid"] = $objVoting->getSystemid();
                        $arrTemplate["voting_action"] = Link::getLinkPortalHref($this->getPagename(), "", "submitVoting");

                        $strVotingContent .= $this->fillTemplate($arrTemplate, $strFormTemplateID);
                    }

                }
                else {
                    $strVotingContent = $this->getLang("commons_error_permissions");
                }

            }
            else if($this->arrElementData["int1"] == 1) {
                //result mode

                $strAnswers = "";
                $intTotalVotes = 0;
                $strAnswerTemplateID = $this->objTemplate->readTemplate("/module_votings/" . $this->arrElementData["char2"], "voting_result_answer");

                //first run to sum up
                /** @var VotingsAnswer $objOneAnswer */
                foreach(VotingsAnswer::getObjectList($objVoting->getSystemid()) as $objOneAnswer) {
                    if($objOneAnswer->getIntRecordStatus() == 0)
                        continue;
                    $intTotalVotes += $objOneAnswer->getIntHits();
                }

                /** @var VotingsAnswer $objOneAnswer */
                foreach(VotingsAnswer::getObjectList($objVoting->getSystemid()) as $objOneAnswer) {
                    if($objOneAnswer->getIntRecordStatus() == 0)
                        continue;
                    $arrTemplate = array();
                    $arrTemplate["answer_text"] = $objOneAnswer->getStrText();
                    $arrTemplate["answer_hits"] = $objOneAnswer->getIntHits();
                    $arrTemplate["answer_systemid"] = $objOneAnswer->getSystemid();

                    $arrTemplate["answer_percent"] = "0";
                    if($objOneAnswer->getIntHits() > 0) {
                        $arrTemplate["answer_percent"] = (int)(100 / ($intTotalVotes / $objOneAnswer->getIntHits()));
                    }

                    $strAnswers .= $this->fillTemplate($arrTemplate, $strAnswerTemplateID);
                }

                $strResultTemplateID = $this->objTemplate->readTemplate("/module_votings/" . $this->arrElementData["char2"], "voting_result");
                $arrTemplate = array();
                $arrTemplate["voting_answers"] = $strAnswers;
                $arrTemplate["voting_hits"] = $intTotalVotes;
                $strVotingContent .= $this->fillTemplate($arrTemplate, $strResultTemplateID);
            }


            $strListTemplateID = $this->objTemplate->readTemplate("/module_votings/" . $this->arrElementData["char2"], "voting_wrapper");
            $arrTemplate = array();
            $arrTemplate["voting_systemid"] = $objVoting->getSystemid();
            $arrTemplate["voting_title"] = $objVoting->getStrTitle();
            $arrTemplate["voting_content"] = $strVotingContent;
            $strReturn .= $this->fillTemplate($arrTemplate, $strListTemplateID);

        }
        else {
            $strReturn = $this->getLang("commons_error_permissions");
        }


        return $strReturn;
    }

    /**
     * Helper method, does the internal updates of the voting-answers
     *
     * @return void
     * @permissions right1
     */
    private function actionSubmitVoting() {
        //load the current voting
        $objVoting = new VotingsVoting($this->arrElementData["char1"]);
        // check if the submitted vote matches the current one -> multiple votings per page
        if($objVoting->getSystemid() == $this->getParam("systemid")) {

            //recheck permissions
            if(!in_array($objVoting->getSystemid(), $this->arrCookieValues)) {
                //load the submitted answer
                $strAnswerID = $this->getParam("voting_" . $objVoting->getSystemid());
                if(validateSystemid($strAnswerID)) {
                    $objAnswer = new VotingsAnswer($strAnswerID);
                    $objAnswer->setIntHits($objAnswer->getIntHits() + 1);
                    $objAnswer->updateObjectToDb();

                    $this->arrCookieValues[] = $objVoting->getSystemid();

                    $objCookie = new Cookie();
                    $objCookie->setCookie($this->STR_COOKIE_NAME, implode(",", $this->arrCookieValues));

                }
            }
        }
    }
}
