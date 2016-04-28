<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Guestbook\Portal;

use Kajona\Guestbook\System\GuestbookGuestbook;
use Kajona\Guestbook\System\GuestbookPost;
use Kajona\Guestbook\System\Messageproviders\MessageproviderGuestbook;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Exception;
use Kajona\System\System\Link;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\Rights;
use Kajona\System\System\UserGroup;
use Kajona\System\System\Validators\EmailValidator;

/**
 * Portal-class of the guestbook. Handles postings
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 *
 * @module guestbook
 * @moduleId _guestbook_module_id_
 */
class GuestbookPortal extends PortalController implements PortalInterface
{

    private $arrErrors = array();


    /**
     * Returns a list of all posts in the current gb
     *
     * @return string
     * @permissions view
     */
    protected function actionList()
    {
        $strReturn = "";
        $arrTemplate = array();
        $arrTemplate["liste_posts"] = "";

        $objBook = new GuestbookGuestbook($this->arrElementData["guestbook_id"]);

        //Load all posts
        $objArraySectionIterator = new ArraySectionIterator(GuestbookPost::getPostsCount($this->arrElementData["guestbook_id"], true));
        $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["guestbook_amount"]);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(
            GuestbookPost::getPosts($this->arrElementData["guestbook_id"], true, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
        );

        $arrObjPosts = $this->objToolkit->simplePager($objArraySectionIterator, $this->getLang("commons_next"), $this->getLang("commons_back"), "", $this->getPagename(), "", "pv", "/module_guestbook/".$this->arrElementData["guestbook_template"]);

        //and put posts into a template
        /** @var GuestbookPost $objOnePost */
        foreach ($objArraySectionIterator as $objOnePost) {
            if ($objOnePost->rightView()) {
                $arrTemplatePost = array();
                $arrTemplatePost["post_name"] = "<a href=\"mailto:".$objOnePost->getStrGuestbookPostEmail()."\">".$objOnePost->getStrGuestbookPostName()."</a>";
                $arrTemplatePost["post_name_plain"] = $objOnePost->getStrGuestbookPostName();
                $arrTemplatePost["post_email"] = $objOnePost->getStrGuestbookPostEmail();
                $arrTemplatePost["post_page"] = "<a href=\"http://".$objOnePost->getStrGuestbookPostPage()."\">".$objOnePost->getStrGuestbookPostPage()."</a>";
                //replace encoded newlines
                $arrTemplatePost["post_text"] = uniStrReplace("&lt;br /&gt;", "<br />", $objOnePost->getStrGuestbookPostText());
                $arrTemplatePost["post_date"] = timeToString($objOnePost->getIntGuestbookPostDate());
                $arrTemplate["liste_posts"] .= $this->objTemplate->fillTemplateFile($arrTemplatePost, "/module_guestbook/".$this->arrElementData["guestbook_template"], "post", false);
            }
        }

        //link to the post-form & pageview links
        if ($objBook->rightRight1()) {
            $arrTemplate["link_newentry"] = $this->objTemplate->fillTemplateFile(array("link_href" => Link::getLinkPortalHref(($this->getParam("page") ? $this->getParam("page") : ""), "", "insertGuestbook")), "/module_guestbook/".$this->arrElementData["guestbook_template"], "insert_link");
        }
        $arrTemplate["link_forward"] = $arrObjPosts["strForward"];
        $arrTemplate["link_pages"] = $arrObjPosts["strPages"];
        $arrTemplate["link_back"] = $arrObjPosts["strBack"];

        $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_guestbook/".$this->arrElementData["guestbook_template"], "list");
        return $strReturn."";
    }


    /**
     * Creates a form to handle a new post
     *
     * @return string
     */
    protected function actionInsertGuestbook()
    {
        $strReturn = "";

        $strErrors = "";
        $arrErrorFields = array();
        if (count($this->arrErrors) > 0) {
            foreach ($this->arrErrors as $strKey => $strOneError) {
                $strErrors .= $this->objTemplate->fillTemplateFile(array("error" => $strOneError), "/module_guestbook/".$this->arrElementData["guestbook_template"], "error_row");
                $arrErrorFields[] = "'{$strKey}'";
            }

            $strErrors = $this->objTemplate->fillTemplateFile(array("error_list" => $strErrors), "/module_guestbook/".$this->arrElementData["guestbook_template"], "errors");
        }

        //update elements
        $arrTemplate = array();
        $arrTemplate["validation_errors"] = $strErrors;
        $arrTemplate["gb_post_name"] = $this->getParam("gb_post_name");
        $arrTemplate["gb_post_email"] = $this->getParam("gb_post_email");
        $arrTemplate["gb_post_text"] = $this->getParam("gb_post_text");
        $arrTemplate["gb_post_page"] = $this->getParam("gb_post_page");
        $arrTemplate["error_fields"] = implode(",", $arrErrorFields);

        foreach ($arrTemplate as $strKey => $strValue) {
            if (uniStrpos($strKey, "gb_post_") !== false) {
                $arrTemplate[$strKey] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
            }
        }

        $arrTemplate["action"] = getLinkPortalHref($this->getPagename(), "", "saveGuestbook");
        $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_guestbook/".$this->arrElementData["guestbook_template"], "entry_form");
        return $strReturn;
    }


    /**
     * Saves the passed values to db
     *
     * @throws Exception
     * @return string "" in case of success
     */
    protected function actionSaveGuestbook()
    {
        $strReturn = "";

        if (!$this->validateData()) {
            $this->setParam("eintragen_fehler", $this->getLang("eintragen_fehler"));
            return $this->actionInsertGuestbook();
        }

        $objBook = new GuestbookGuestbook($this->arrElementData["guestbook_id"]);

        //check rights
        if ($objBook->rightRight1()) {
            //create a post-object
            $objPost = new GuestbookPost();
            $objPost->setStrGuestbookPostName($this->getParam("gb_post_name"));
            $objPost->setStrGuestbookPostEmail($this->getParam("gb_post_email"));
            $objPost->setStrGuestbookPostPage($this->getParam("gb_post_page"));
            $objPost->setStrGuestbookPostText($this->getParam("gb_post_text"));
            $objPost->setIntGuestbookPostDate(time());

            //save obj to db
            if (!$objPost->updateObjectToDb($objBook->getSystemid())) {
                throw new Exception("Error saving entry", Exception::$level_ERROR);
            }


            $strMailtext = $this->getLang("new_post_mail");
            $strMailtext .= getLinkAdminHref("guestbook", "edit", "&systemid=".$objPost->getSystemid(), false);
            $objMessageHandler = new MessagingMessagehandler();

            $arrGroups = array();
            $allGroups = UserGroup::getObjectListFiltered();
            foreach ($allGroups as $objOneGroup) {
                if (Rights::getInstance()->checkPermissionForGroup($objOneGroup->getSystemid(), Rights::$STR_RIGHT_EDIT, $this->getObjModule()->getSystemid())) {
                    $arrGroups[] = $objOneGroup;
                }
            }

            $objMessageHandler->sendMessage($strMailtext, $arrGroups, new MessageproviderGuestbook());

            //Flush the page from cache
            $this->flushCompletePagesCache();
            $this->portalReload(getLinkPortalHref($this->getPagename()));

        }
        else {
            $strReturn = $this->getLang("commons_error_permissions");
        }
        return $strReturn;
    }


    /**
     * Validates the submitted data
     *
     * @return bool
     */
    private function validateData()
    {
        $bitReturn = true;

        //Check captachcode
        if ($this->getParam("gb_post_captcha") != $this->objSession->getCaptchaCode() || $this->getParam("gb_post_captcha") == "") {
            $bitReturn = false;
            $this->arrErrors["form_captcha"] = $this->getLang("insert_error_captcha");
        }

        //Check mailaddress
        $objMailValidator = new EmailValidator();
        if (!$objMailValidator->validate($this->getParam("gb_post_email"))) {
            $this->arrErrors["gb_post_email"] = $this->getLang("insert_error_email");
            $bitReturn = false;
        }

        if (uniStrlen($this->getParam("gb_post_name")) == 0) {
            $this->arrErrors["gb_post_name"] = $this->getLang("insert_error_name");
            $bitReturn = false;
        }

        if (uniStrlen($this->getParam("gb_post_text")) == 0) {
            $this->arrErrors["gb_post_text"] = $this->getLang("insert_error_post");
            $bitReturn = false;
        }

        //if there ain't any errors, update texts
        if ($bitReturn) {
            $this->setParam("gb_post_name", htmlToString($this->getParam("gb_post_name")));
            $this->setParam("gb_post_email", htmlToString($this->getParam("gb_post_email")));
            $this->setParam("gb_post_text", htmlToString($this->getParam("gb_post_text")));
        }

        return $bitReturn;
    }

}
