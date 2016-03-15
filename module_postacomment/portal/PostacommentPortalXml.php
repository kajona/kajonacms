<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						*
********************************************************************************************************/

namespace Kajona\Postacomment\Portal;

use Kajona\Pages\System\PagesPage;
use Kajona\Postacomment\System\Messageproviders\MessageproviderPostacomment;
use Kajona\Postacomment\System\PostacommentPost;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\XmlPortalInterface;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Rights;
use Kajona\System\System\UserGroup;


/**
 * Portal-class of the postacomment-module
 * Serves xml-requests, e.g. saves a sent comment
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @module postacomment
 * @moduleId _postacomment_modul_id_
 */
class PostacommentPortalXml extends PortalController implements XmlPortalInterface
{

    private $strErrors;
    private $arrErrorFields = array();

    /**
     * saves a post in the database and returns the post as html.
     * In case of missing fields, the form is returned again
     *
     * @return string
     * @permissons right1
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
                if (uniStrpos($strKey, "comment_") !== false) {
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
        }
        else {
            //save the post to the db
            //pageid or systemid to filter?
            $strSystemidfilter = $this->getParam("comment_systemid");
            if (PagesPage::getPageByName($this->getParam("comment_page")) !== null) {
                $strPagefilter = PagesPage::getPageByName($this->getParam("comment_page"))->getSystemid();
            }
            else {
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
            $allGroups = UserGroup::getObjectList();
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

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
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

        if (uniStrlen($this->getParam("comment_name")) < 2) {
            $bitReturn = false;
            $this->strErrors .= $this->objTemplate->fillTemplateFile(array("error" => $this->getLang("validation_name")), "/module_postacomment/".$this->getParam("comment_template"), "validation_error_row");
            $this->arrErrorFields[] = "'comment_name_{$this->getParam("comment_systemid")}'";
        }
        if (uniStrlen($this->getParam("comment_message")) < 2) {
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
