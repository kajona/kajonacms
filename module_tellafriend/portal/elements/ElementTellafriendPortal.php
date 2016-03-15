<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Tellafriend\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Mail;
use Kajona\System\System\ScriptletHelper;
use Kajona\System\System\Validators\EmailValidator;
use Kajona\System\System\Validators\TextValidator;


/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @author sidler@mulchprod.de
 * @targetTable element_tellafriend.content_id
 */
class ElementTellafriendPortal extends ElementPortal implements PortalElementInterface
{

    private $arrError = array();

    /**
     * Looks up the last modified-date of the current page
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {
        $strReturn = "";
        //display form or send an email?
        if ($this->getParam("action") != "sendTellafriend") {
            $strReturn .= $this->tellafriendForm();
        }
        else {
            if (!$this->validateForm()) {
                $strReturn .= $this->tellafriendForm();
            }
            else {
                $this->sendForm();
            }
        }
        return $strReturn;
    }

    /**
     * Creates a form
     *
     * @return string
     */
    private function tellafriendForm()
    {
        $arrTemplate = array();
        //any errors to print?
        if (count($this->arrError) > 0) {
            $strError = "";
            //Collect errors
            foreach ($this->arrError as $strOneError) {
                $strError .= $this->objTemplate->fillTemplateFile(array("error" => $strOneError), "/module_tellafriend/".$this->arrElementData["tellafriend_template"], "errorrow");
            }
            //and the complete errorform
            $arrTemplate["tellafriend_errors"] = $this->objTemplate->fillTemplateFile(array("liste_fehler" => $strError), "/module_tellafriend/".$this->arrElementData["tellafriend_template"]);
        }

        $arrTemplate["tellafriend_sender"] = htmlToString($this->getParam("tellafriend_sender"), true);
        $arrTemplate["tellafriend_sender_name"] = htmlToString($this->getParam("tellafriend_sender_name"), true);
        $arrTemplate["tellafriend_receiver"] = htmlToString($this->getParam("tellafriend_receiver"), true);
        $arrTemplate["tellafriend_receiver_name"] = htmlToString($this->getParam("tellafriend_receiver_name"), true);
        $arrTemplate["tellafriend_message"] = htmlToString($this->getParam("tellafriend_message"), true);
        $arrTemplate["tellafriend_action"] = "sendTellafriend";

        $arrTemplate["action"] = getLinkPortalHref($this->getPagename());
        return $this->objTemplate->fillTemplateFile($arrTemplate, "/module_tellafriend/".$this->arrElementData["tellafriend_template"], "tellafriend_form");
    }

    /**
     * Validates all elements sent before
     *
     * @return bool
     */
    private function validateForm()
    {
        $bitReturn = true;

        $objMailValidator = new EmailValidator();
        $objTextValidator = new TextValidator();

        if (!$objMailValidator->validate($this->getParam("tellafriend_sender"))) {
            $bitReturn = false;
            $this->arrError[] = $this->getLang("tellafriend_sender");
        }

        if (!$objMailValidator->validate($this->getParam("tellafriend_receiver"))) {
            $bitReturn = false;
            $this->arrError[] = $this->getLang("tellafriend_receiver");
        }

        if (!$objTextValidator->validate($this->getParam("tellafriend_sender_name"))) {
            $bitReturn = false;
            $this->arrError[] = $this->getLang("tellafriend_sender_name");
        }

        if (!$objTextValidator->validate($this->getParam("tellafriend_receiver_name"))) {
            $bitReturn = false;
            $this->arrError[] = $this->getLang("tellafriend_receiver_name");
        }

        //Check captachcode
        if ($this->getParam("form_captcha") != $this->objSession->getCaptchaCode()) {
            $bitReturn = false;
            $this->arrError[] = $this->getLang("fehler_captcha");
        }

        return $bitReturn;
    }


    /**
     * Creates an email to send to a friend
     *
     * @return void
     */
    private function sendForm()
    {
        //load url the user visited before
        $strUrl = $this->getHistory(2);
        $arrUrl = explode("&", $strUrl);
        $strPage = "";
        $strSystemid = "";
        $strParams = "";
        $strAction = "";
        foreach ($arrUrl as $arrOnePart) {
            $arrPair = explode("=", $arrOnePart);
            if ($arrPair[0] == "page") {
                $strPage = $arrPair[1];
            }
            elseif ($arrPair[0] == "systemid") {
                $strSystemid = $arrPair[1];
            }
            elseif ($arrPair[0] == "action") {
                $strAction = $arrPair[1];
            }
            //everything but the language command
            elseif ($arrPair[0] != "language") {
                $strParams .= "&".$arrPair[0]."=".$arrPair[1];
            }

        }

        $strHref = getLinkPortalHref($strPage, "", $strAction, $strParams, $strSystemid, $this->getStrPortalLanguage());
        $arrMessage = array();
        $arrMessage["tellafriend_url"] = "<a href=\"".$strHref."\">".$strHref."</a>";
        $arrMessage["tellafriend_receiver_name"] = htmlStripTags($this->getParam("tellafriend_receiver_name"));
        $arrMessage["tellafriend_sender_name"] = htmlStripTags($this->getParam("tellafriend_sender_name"));
        $arrMessage["tellafriend_message"] = htmlStripTags($this->getParam("tellafriend_message"));


        $strEmailBody = $this->objTemplate->fillTemplateFile($arrMessage, "/element_tellafriend/".$this->arrElementData["tellafriend_template"], "email_html");
        $objScriptlet = new ScriptletHelper();
        $strEmailBody = $objScriptlet->processString($strEmailBody);

        //TODO: check if we have to remove critical characters here?
        $strSubject = $this->objTemplate->fillTemplateFile(array("tellafriend_sender_name" => htmlStripTags($this->getParam("tellafriend_sender_name"))), "/module_tellafriend/".$this->arrElementData["tellafriend_template"], "email_subject");

        //TODO: check if we have to remove critical characters here?
        $objEmail = new Mail();
        $objEmail->setSender($this->getParam("tellafriend_sender"));
        $objEmail->setSenderName($this->getParam("tellafriend_sender_name"));
        $objEmail->addTo($this->getParam("tellafriend_receiver"));
        $objEmail->setSubject($strSubject);
        $objEmail->setHtml($strEmailBody);

        if ($objEmail->sendMail()) {
            $this->portalReload(Link::getLinkPortalHref($this->arrElementData["tellafriend_success"]));
        }
        else {
            $this->portalReload(Link::getLinkPortalHref($this->arrElementData["tellafriend_error"]));
        }
    }
}
