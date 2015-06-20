<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/

/**
 * Portal-class of the postacomment. Handles the printing of postacomment lists / detail
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @module postacomment
 * @moduleId _postacomment_modul_id_
 */
class class_module_postacomment_portal extends class_portal_controller implements interface_portal {

    private $strErrors = "";
    private $strPagefilter = null;

    /**
     * Returns a list of comments.
     *
     * @return string
     * @permissions view
     */
    protected function actionList() {
        $strReturn = "";
        $strPosts = "";
        $strForm = "";
        $strNewButton = "";

        //pageid or systemid to filter?
        $strSystemidfilter = "";
        $strPagefilter = $this->strPagefilter;

        if($this->getSystemid() != "")
            $strSystemidfilter = $this->getSystemid();

        if($strPagefilter === null && class_module_pages_page::getPageByName($this->getPagename()) !== null)
            $strPagefilter = class_module_pages_page::getPageByName($this->getPagename())->getSystemid();

        $intNrOfPosts = isset($this->arrElementData["int1"]) ? $this->arrElementData["int1"] : 0;

        //Load all posts
        $objArraySectionIterator = new class_array_section_iterator(class_module_postacomment_post::getNumberOfPostsAvailable(true, $strPagefilter, $strSystemidfilter, $this->getStrPortalLanguage()));
        $objArraySectionIterator->setIntElementsPerPage($intNrOfPosts);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pvPAC") != "" ? $this->getParam("pvPAC") : 1));
        $objArraySectionIterator->setArraySection(
            class_module_postacomment_post::loadPostList(true, $strPagefilter, $strSystemidfilter, $this->getStrPortalLanguage(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
        );


        //params to add?
        $strAdd = "";
        if($this->getParam("action") != "")
            $strAdd .= "&action=".$this->getParam("action");
        if($this->getParam("systemid") != "")
            $strAdd .= "&systemid=".$this->getParam("systemid");
        if($this->getParam("pv") != "")
            $strAdd .= "&pv=".$this->getParam("pv");

        $arrComments = $this->objToolkit->simplePager($objArraySectionIterator, $this->getLang("commons_next"), $this->getLang("commons_back"), "", $this->getPagename(), $strAdd, "pvPAC");


        $strTemplateID = $this->objTemplate->readTemplate("/module_postacomment/".$this->arrElementData["char1"], "postacomment_post");


        if(!$objArraySectionIterator->valid())
            $strPosts .= $this->getLang("postacomment_empty");

        //Check rights
        /** @var class_module_postacomment_post $objOnePost */
        foreach($objArraySectionIterator as $objOnePost) {
            if($objOnePost->rightView()) {
                $strOnePost = "";
                $arrOnePost = array();
                $arrOnePost["postacomment_post_name"] = $objOnePost->getStrUsername();
                $arrOnePost["postacomment_post_subject"] = $objOnePost->getStrTitle();
                $arrOnePost["postacomment_post_message"] = $objOnePost->getStrComment();
                $arrOnePost["postacomment_post_systemid"] = $objOnePost->getSystemid();
                $arrOnePost["postacomment_post_date"] = timeToString($objOnePost->getIntDate(), true);
                //ratings available?
                if($objOnePost->getFloatRating() !== null) {
                    /** @var $objRating class_module_rating_portal */
                    $objRating = class_module_system_module::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
                    $arrOnePost["postacomment_post_rating"] = $objRating->buildRatingBar(
                        $objOnePost->getFloatRating(), $objOnePost->getIntRatingHits(), $objOnePost->getSystemid(), $objOnePost->isRateableByUser(), $objOnePost->rightRight2()
                    );
                }


                $strOnePost .= $this->objTemplate->fillTemplate($arrOnePost, $strTemplateID, false);

                //Add pe code
                $arrPeConfig = array(
                    "pe_module"               => "postacomment",
                    "pe_action_edit"          => "edit",
                    "pe_action_edit_params"   => "&systemid=".$objOnePost->getSystemid(),
                    "pe_action_new"           => "",
                    "pe_action_new_params"    => "",
                    "pe_action_delete"        => "delete",
                    "pe_action_delete_params" => "&systemid=".$objOnePost->getSystemid()
                );
                $strPosts .= class_element_portal::addPortalEditorCode($strOnePost, $objOnePost->getSystemid(), $arrPeConfig);
            }
        }

        //Create form
        if($this->getObjModule()->rightRight1()) {
            $strTemplateID = $this->objTemplate->readTemplate("/module_postacomment/".$this->arrElementData["char1"], "postacomment_form");
            $arrForm = array();

            if($this->getParam("comment_name") == "" && $this->objSession->isLoggedin())
                $this->setParam("comment_name", $this->objSession->getUsername());

            $arrForm["formaction"] = class_link::getLinkPortalHref($this->getPagename(), "", "postComment", "", $this->getSystemid());
            $arrForm["comment_name"] = $this->getParam("comment_name");
            $arrForm["comment_subject"] = $this->getParam("comment_subject");
            $arrForm["comment_message"] = $this->getParam("comment_message");
            $arrForm["comment_template"] = $this->arrElementData["char1"];
            $arrForm["comment_systemid"] = $this->getSystemid();
            $arrForm["comment_page"] = $this->getPagename();
            $arrForm["validation_errors"] = $this->strErrors;

            foreach($arrForm as $strKey => $strValue) {
                if(uniStrpos($strKey, "comment_") !== false) {
                    $arrForm[$strKey] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
                }
            }

            $strForm .= $this->objTemplate->fillTemplate($arrForm, $strTemplateID, false);

            //button to show the form
            $strTemplateNewButtonID = $this->objTemplate->readTemplate("/module_postacomment/".$this->arrElementData["char1"], "postacomment_new_button");
            $strNewButton = $this->objTemplate->fillTemplate(array("comment_systemid" => $this->getSystemid()), $strTemplateNewButtonID, false);
        }
        //add sourrounding list template
        $strTemplateID = $this->objTemplate->readTemplate("/module_postacomment/".$this->arrElementData["char1"], "postacomment_list");


        //link to the post-form & pageview links
        $arrTemplate = array();
        $arrTemplate["postacomment_forward"] = $arrComments["strForward"];
        $arrTemplate["postacomment_pages"] = $arrComments["strPages"];
        $arrTemplate["postacomment_back"] = $arrComments["strBack"];
        $arrTemplate["postacomment_form"] = $strForm;
        $arrTemplate["postacomment_new_button"] = $strNewButton;
        $arrTemplate["postacomment_systemid"] = $this->getSystemid();
        $arrTemplate["postacomment_list"] = $strPosts;

        $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

        return $strReturn;
    }


    /**
     * Saves a post to the databases
     *
     * @permissions right1
     * @return string
     */
    protected function actionPostComment() {

        if(!$this->validateForm())
            return $this->actionList();

        $strSystemidfilter = "";
        if($this->getSystemid() != "")
            $strSystemidfilter = $this->getSystemid();

        if(class_module_pages_page::getPageByName($this->getPagename()) !== null)
            $strPagefilter = class_module_pages_page::getPageByName($this->getPagename())->getSystemid();
        else
            $strPagefilter = "";

        $objPost = new class_module_postacomment_post();
        $objPost->setStrUsername($this->getParam("comment_name"));
        $objPost->setStrTitle($this->getParam("comment_subject"));
        $objPost->setStrComment($this->getParam("comment_message"));

        $objPost->setStrAssignedPage($strPagefilter);
        $objPost->setStrAssignedSystemid($strSystemidfilter);
        $objPost->setStrAssignedLanguage($this->getStrPortalLanguage());

        $objPost->updateObjectToDb();

        $this->flushPageFromPagesCache($this->getPagename());


        $strMailtext = $this->getLang("new_comment_mail")."\r\n\r\n".$objPost->getStrComment()."\r\n";
        $strMailtext .= class_link::getLinkAdminHref("postacomment", "edit", "&systemid=".$objPost->getSystemid(), false);
        $objMessageHandler = new class_module_messaging_messagehandler();
        $arrGroups = array();
        $allGroups = class_module_user_group::getObjectList();
        foreach($allGroups as $objOneGroup) {
            if(class_rights::getInstance()->checkPermissionForGroup($objOneGroup->getSystemid(), class_rights::$STR_RIGHT_EDIT, $this->getObjModule()->getSystemid()))
                $arrGroups[] = $objOneGroup;
        }


        $objMessage = new class_module_messaging_message();
        $objMessage->setStrBody($strMailtext);
        $objMessage->setObjMessageProvider(new class_messageprovider_postacomment());
        $objMessageHandler->sendMessageObject($objMessage, $arrGroups);

        $this->portalReload(_indexpath_."?".$this->getHistory(1)/*."#comments"*/);
        return "";
    }

    /**
     * Validates the form data provided by the user
     *
     * @return bool
     */
    private function validateForm() {
        $bitReturn = true;

        $strTemplateId = $this->objTemplate->readTemplate("/module_postacomment/".$this->arrElementData["char1"], "validation_error_row");
        if(uniStrlen($this->getParam("comment_name")) < 2) {
            $bitReturn = false;
            $this->strErrors .= $this->objTemplate->fillTemplate(array("error" => $this->getLang("validation_name")), $strTemplateId);
        }
        if(uniStrlen($this->getParam("comment_message")) < 2) {
            $bitReturn = false;
            $this->strErrors .= $this->objTemplate->fillTemplate(array("error" => $this->getLang("validation_message")), $strTemplateId);
        }
        if($this->objSession->getCaptchaCode() != $this->getParam("form_captcha") || $this->getParam("form_captcha") == "") {
            $bitReturn = false;
            $this->strErrors .= $this->objTemplate->fillTemplate(array("error" => $this->getLang("validation_code")), $strTemplateId);
        }
        return $bitReturn;
    }


    /**
     * If you want to set a sepcial page to be used for loading and rendering the portal list, use this
     * setter. Pass the systemid (!) of the page to load.
     *
     * @param string $strPagefilter
     * @return void
     */
    public function setStrPagefilter($strPagefilter) {
        $this->strPagefilter = $strPagefilter;
    }

}
