<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/

namespace Kajona\Postacomment\Portal;

use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;
use Kajona\Postacomment\System\Messageproviders\MessageproviderPostacomment;
use Kajona\Postacomment\System\PostacommentPost;
use Kajona\Rating\Portal\RatingPortal;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Rights;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemModule;
use Kajona\System\System\UserGroup;

/**
 * Portal-class of the postacomment. Handles the printing of postacomment lists / detail
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @module postacomment
 * @moduleId _postacomment_modul_id_
 */
class PostacommentPortal extends PortalController implements PortalInterface
{

    private $strErrors = "";
    private $arrErrorFields = array();
    private $strPagefilter = null;

    /**
     * Returns a list of comments.
     *
     * @return string
     * @permissions view
     */
    protected function actionList()
    {
        $strReturn = "";
        $strPosts = "";
        $strForm = "";
        $strNewButton = "";

        //pageid or systemid to filter?
        $strSystemidfilter = "";
        $strPagefilter = $this->strPagefilter;

        if ($this->getSystemid() != "") {
            $strSystemidfilter = $this->getSystemid();
        }

        if ($strPagefilter === null && PagesPage::getPageByName($this->getPagename()) !== null) {
            $strPagefilter = PagesPage::getPageByName($this->getPagename())->getSystemid();
        }

        $intNrOfPosts = isset($this->arrElementData["int1"]) ? $this->arrElementData["int1"] : 0;

        //Load all posts
        $objArraySectionIterator = new ArraySectionIterator(PostacommentPost::getNumberOfPostsAvailable(true, $strPagefilter, $strSystemidfilter, $this->getStrPortalLanguage()));
        $objArraySectionIterator->setIntElementsPerPage($intNrOfPosts);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pvPAC") != "" ? $this->getParam("pvPAC") : 1));
        $objArraySectionIterator->setArraySection(
            PostacommentPost::loadPostList(true, $strPagefilter, $strSystemidfilter, $this->getStrPortalLanguage(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
        );


        //params to add?
        $strAdd = "";
        if ($this->getParam("action") != "") {
            $strAdd .= "&action=".$this->getParam("action");
        }
        if ($this->getParam("systemid") != "") {
            $strAdd .= "&systemid=".$this->getParam("systemid");
        }
        if ($this->getParam("pv") != "") {
            $strAdd .= "&pv=".$this->getParam("pv");
        }

        $arrComments = $this->objToolkit->simplePager($objArraySectionIterator, $this->getLang("commons_next"), $this->getLang("commons_back"), "", $this->getPagename(), $strAdd, "pvPAC", "/module_postacomment/".$this->arrElementData["char1"]);

        if (!$objArraySectionIterator->valid()) {
            $strPosts .= $this->getLang("postacomment_empty");
        }

        //Check rights
        /** @var PostacommentPost $objOnePost */
        foreach ($objArraySectionIterator as $objOnePost) {
            if ($objOnePost->rightView()) {
                $strOnePost = "";
                $arrOnePost = array();
                $arrOnePost["postacomment_post_name"] = $objOnePost->getStrUsername();
                $arrOnePost["postacomment_post_subject"] = $objOnePost->getStrTitle();
                $arrOnePost["postacomment_post_message"] = $objOnePost->getStrComment();
                $arrOnePost["postacomment_post_systemid"] = $objOnePost->getSystemid();
                $arrOnePost["postacomment_post_date"] = timeToString($objOnePost->getIntDate(), true);
                //ratings available?
                if ($objOnePost->getFloatRating() !== null) {
                    /** @var $objRating RatingPortal */
                    $objRating = SystemModule::getModuleByName("rating")->getPortalInstanceOfConcreteModule();
                    $arrOnePost["postacomment_post_rating"] = $objRating->buildRatingBar(
                        $objOnePost->getFloatRating(), $objOnePost->getIntRatingHits(), $objOnePost->getSystemid(), $objOnePost->isRateableByUser(), $objOnePost->rightRight2()
                    );
                }

                $strOnePost .= $this->objTemplate->fillTemplateFile($arrOnePost, "/module_postacomment/".$this->arrElementData["char1"], "postacomment_post", false);

                //Add pe code
                $strPosts .= PagesPortaleditor::addPortaleditorContentWrapper($strOnePost, $objOnePost->getSystemid());
                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::EDIT(), Link::getLinkAdminHref($this->getArrModule("module"), "edit", "&pe=1&systemid={$objOnePost->getSystemid()}"), $objOnePost->getSystemid())
                );
                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::DELETE(), Link::getLinkAdminHref($this->getArrModule("module"), "delete", "&pe=1&systemid={$objOnePost->getSystemid()}"), $objOnePost->getSystemid())
                );
            }
        }

        //Create form
        if ($this->getObjModule()->rightRight1()) {
            $arrForm = array();

            if ($this->getParam("comment_name") == "" && $this->objSession->isLoggedin()) {
                $this->setParam("comment_name", $this->objSession->getUsername());
            }

            $arrForm["formaction"] = Link::getLinkPortalHref($this->getPagename(), "", "postComment", "", $this->getSystemid());
            $arrForm["comment_name"] = $this->getParam("comment_name");
            $arrForm["comment_subject"] = $this->getParam("comment_subject");
            $arrForm["comment_message"] = $this->getParam("comment_message");
            $arrForm["comment_template"] = $this->arrElementData["char1"];
            $arrForm["comment_systemid"] = $this->getSystemid();
            $arrForm["comment_page"] = $this->getPagename();
            $arrForm["validation_errors"] = $this->strErrors;

            foreach ($arrForm as $strKey => $strValue) {
                if (StringUtil::indexOf($strKey, "comment_") !== false) {
                    $arrForm[$strKey] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
                }
            }

            $strForm .= $this->objTemplate->fillTemplateFile($arrForm, "/module_postacomment/".$this->arrElementData["char1"], "postacomment_form", false);

            //button to show the form
            $strNewButton = $this->objTemplate->fillTemplateFile(array("comment_systemid" => $this->getSystemid()), "/module_postacomment/".$this->arrElementData["char1"], "postacomment_new_button", false);
        }
        //add sourrounding list template

        //link to the post-form & pageview links
        $arrTemplate = array();
        $arrTemplate["postacomment_forward"] = $arrComments["strForward"];
        $arrTemplate["postacomment_pages"] = $arrComments["strPages"];
        $arrTemplate["postacomment_back"] = $arrComments["strBack"];
        $arrTemplate["postacomment_form"] = $strForm;
        $arrTemplate["postacomment_new_button"] = $strNewButton;
        $arrTemplate["postacomment_systemid"] = $this->getSystemid();
        $arrTemplate["postacomment_list"] = $strPosts;

        $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_postacomment/".$this->arrElementData["char1"], "postacomment_list");

        return $strReturn;
    }



    /**
     * If you want to set a sepcial page to be used for loading and rendering the portal list, use this
     * setter. Pass the systemid (!) of the page to load.
     *
     * @param string $strPagefilter
     *
     * @return void
     */
    public function setStrPagefilter($strPagefilter)
    {
        $this->strPagefilter = $strPagefilter;
    }


    /**
     * saves a post in the database and returns the post as html.
     * In case of missing fields, the form is returned again
     *
     * @return string
     * @permissons right1
     * @responseType json
     */
    protected function actionSavePost()
    {

        $strXMLContent = "";

        //validate needed fields
        if (!$this->validateForm()) {
            //Create form to reenter values
            $arrForm = array();
            $arrForm["formaction"] = Link::getLinkPortalHref($this->getPagename(), "", "postComment", "", $this->getSystemid());
            $arrForm["comment_name"] = $this->getParam("comment_name");
            $arrForm["comment_subject"] = $this->getParam("comment_subject");
            $arrForm["comment_message"] = $this->getParam("comment_message");
            $arrForm["comment_template"] = $this->getParam("comment_template");
            $arrForm["comment_systemid"] = $this->getParam("comment_systemid");
            $arrForm["comment_page"] = $this->getParam("comment_page");
            $arrForm["validation_errors"] = $this->strErrors;
            $arrForm["error_fields"] = implode(",", $this->arrErrorFields);

            foreach ($arrForm as $strKey => $strValue) {
                if (StringUtil::indexOf($strKey, "comment_") !== false) {
                    $arrForm[$strKey] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
                }
            }

            //texts
            $arrForm["postacomment_write_new"] = $this->getLang("postacomment_write_new");
            $arrForm["form_name_label"] = $this->getLang("form_name_label");
            $arrForm["form_subject_label"] = $this->getLang("form_subject_label");
            $arrForm["form_message_label"] = $this->getLang("form_message_label");
            $arrForm["form_captcha_label"] = $this->getLang("commons_captcha");
            $arrForm["form_captcha_reload_label"] = $this->getLang("commons_captcha_reload");
            $arrForm["form_submit_label"] = $this->getLang("form_submit_label");

            $strXMLContent .= $this->objTemplate->fillTemplateFile($arrForm, "/module_postacomment/".$this->getParam("comment_template"), "postacomment_form");
        } else {
            //save the post to the db
            //pageid or systemid to filter?
            $strSystemidfilter = $this->getParam("comment_systemid");
            if (PagesPage::getPageByName($this->getParam("comment_page")) !== null) {
                $strPagefilter = PagesPage::getPageByName($this->getParam("comment_page"))->getSystemid();
            } else {
                $strPagefilter = "";
            }

            $objPost = new PostacommentPost();
            $objPost->setStrUsername($this->getParam("comment_name"));
            $objPost->setStrTitle($this->getParam("comment_subject"));
            $objPost->setStrComment($this->getParam("comment_message"));

            $objPost->setStrAssignedPage($strPagefilter);
            $objPost->setStrAssignedSystemid($strSystemidfilter);
            $objPost->setStrAssignedLanguage($this->getStrPortalLanguage());

            $objPost->updateObjectToDb();
            $this->flushCompletePagesCache();

            $strMailtext = $this->getLang("new_comment_mail")."\r\n\r\n".$objPost->getStrComment()."\r\n";
            $strMailtext .= Link::getLinkAdminHref("postacomment", "edit", "&systemid=".$objPost->getSystemid(), false);
            $objMessageHandler = new MessagingMessagehandler();
            $arrGroups = array();
            $allGroups = UserGroup::getObjectListFiltered();
            foreach ($allGroups as $objOneGroup) {
                if (Rights::getInstance()->checkPermissionForGroup($objOneGroup->getSystemid(), Rights::$STR_RIGHT_EDIT, $this->getObjModule()->getSystemid())) {
                    $arrGroups[] = $objOneGroup;
                }
            }
            $objMessageHandler->sendMessage($strMailtext, $arrGroups, new MessageproviderPostacomment());


            //reinit post -> encoded entities
            $objPost->initObject();


            //load the post as a new post to add it at top of the list
            $arrOnePost = array();
            $arrOnePost["postacomment_post_name"] = $objPost->getStrUsername();
            $arrOnePost["postacomment_post_subject"] = $objPost->getStrTitle();
            $arrOnePost["postacomment_post_message"] = $objPost->getStrComment();
            $arrOnePost["postacomment_post_systemid"] = $objPost->getSystemid();
            $arrOnePost["postacomment_post_date"] = timeToString($objPost->getIntDate(), true);


            $strXMLContent .= $this->objTemplate->fillTemplateFile($arrOnePost, "/module_postacomment/".$this->getParam("comment_template"), "postacomment_post");
        }

        return $strXMLContent;
    }


    /**
     * Validates the form data provided by the user
     *
     * @return bool
     */
    private function validateForm()
    {
        $bitReturn = true;

        if (StringUtil::length($this->getParam("comment_name")) < 2) {
            $bitReturn = false;
            $this->strErrors .= $this->objTemplate->fillTemplateFile(array("error" => $this->getLang("validation_name")), "/module_postacomment/".$this->getParam("comment_template"), "validation_error_row");
            $this->arrErrorFields[] = "'comment_name_{$this->getParam("comment_systemid")}'";
        }
        if (StringUtil::length($this->getParam("comment_message")) < 2) {
            $bitReturn = false;
            $this->strErrors .= $this->objTemplate->fillTemplateFile(array("error" => $this->getLang("validation_message")), "/module_postacomment/".$this->getParam("comment_template"), "validation_error_row");
            $this->arrErrorFields[] = "'comment_message_{$this->getParam("comment_systemid")}'";
        }
        if ($this->objSession->getCaptchaCode() != $this->getParam("form_captcha") || $this->getParam("form_captcha") == "") {
            $bitReturn = false;
            $this->strErrors .= $this->objTemplate->fillTemplateFile(array("error" => $this->getLang("validation_code")), "/module_postacomment/".$this->getParam("comment_template"), "validation_error_row");
            $this->arrErrorFields[] = "'form_captcha_{$this->getParam("comment_systemid")}'";
        }

        $this->strErrors = $this->objTemplate->fillTemplateFile(array("error_list" => $this->strErrors), "/module_postacomment/".$this->getParam("comment_template"), "errors");
        return $bitReturn;
    }

}
