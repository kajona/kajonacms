<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\Messageproviders\MessageproviderExtendedInterface;
use Kajona\System\System\Messageproviders\MessageproviderPersonalmessage;
use Kajona\System\System\MessagingConfig;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\Model;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Session;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\UserUser;


/**
 * Admin-class to manage a users messages.
 * In addition, the user is able to configure each messageprovider (enable / disable, send by mail, ...)
 *
 * @package module_messaging
 * @author sidler@mulchprod.de
 * @since 4.0
 *
 * @module messaging
 * @moduleId _messaging_module_id_
 *
 * @objectList Kajona\System\System\MessagingMessage
 * @objectNew Kajona\System\System\MessagingMessage
 * @objectEdit Kajona\System\System\MessagingMessage
 */
class MessagingAdmin extends AdminEvensimpler implements AdminInterface
{


    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", Link::getLinkAdmin($this->getArrModule("modul"), "config", "", $this->getLang("action_config"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * @return array
     */
    protected function getArrOutputNaviEntries()
    {
        $arrEntries = parent::getArrOutputNaviEntries();
        $objObject = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objObject instanceof MessagingMessage) {
            $arrEntries[] = Link::getLinkAdmin("messaging", "edit", "&systemid=".$objObject->getSystemid(), $objObject->getStrDisplayName());
        }

        return $arrEntries;
    }


    /**
     * Renders the form to configure each messageprovider
     *
     * @permissions edit
     * @autoTestable
     *
     * @return string
     */
    protected function actionConfig()
    {
        $objHandler = new MessagingMessagehandler();
        $arrMessageproviders = $objHandler->getMessageproviders();

        $strReturn = "";

        //create callback for the on-off toogle which is passed to formInputOnOff
        $strCallback = <<<JS
            //data contains the clicked element
            var inputId = $(this).attr('id');
            var messageProviderType = inputId.slice(0, inputId.lastIndexOf("_"));

            var param1 =inputId+'='+state; //value for clicked toggle element
            var param2 = 'messageprovidertype='+messageProviderType; //messageprovide type
            var postBody = param1+'&'+param2;

            require('ajax').genericAjaxCall("messaging", "saveConfigAjax", "&"+postBody, require('ajax').regularCallback);

            if(inputId.indexOf("_enabled") > 0 ) {
                $("#"+inputId).closest("tr").find("div.checkbox input:not(.blockEnable)").slice(1).bootstrapSwitch("disabled", state);
            }
JS;
        $arrRows = array();
        foreach ($arrMessageproviders as $objOneProvider) {

            if ($objOneProvider instanceof MessageproviderExtendedInterface && !$objOneProvider->isVisibleInConfigView()) {
                continue;
            }

            $objConfig = MessagingConfig::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);

            $bitAlwaysEnabled = $objOneProvider instanceof MessageproviderExtendedInterface && $objOneProvider->isAlwaysActive();
            $bitAlwaysMail = $objOneProvider instanceof MessageproviderExtendedInterface && $objOneProvider->isAlwaysByMail();

            $strClassname = uniStrReplace("\\", "-", get_class($objOneProvider));

            $arrRows[] = array(
                $objOneProvider->getStrName(),
                "inlineFormEntry 1" => $this->objToolkit->formInputOnOff($strClassname."_enabled", $this->getLang("provider_enabled"), $objConfig->getBitEnabled() == 1, $bitAlwaysEnabled, $strCallback),
                "inlineFormEntry 2" => $this->objToolkit->formInputOnOff($strClassname."_bymail", $this->getLang("provider_bymail"), $objConfig->getBitBymail() == 1, $bitAlwaysMail, $strCallback, ($bitAlwaysMail ? "blockEnable" : ""))
            );

        }

        $arrHeader = array(
            $this->getLang("provider_title"),
            $this->getLang("provider_enabled"),
            $this->getLang("provider_bymail"),
        );

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrRows);
        return $strReturn;
    }

    /**
     * @param Model $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(Model $objListEntry)
    {
        if ($objListEntry instanceof MessagingMessage) {
            return array(
                Link::getLinkAdminDialog($this->getArrModule("modul"), "new", "&messaging_user_id=".$objListEntry->getStrSenderId()."&messaging_messagerefid=".$objListEntry->getSystemid()."&messaging_title=RE: ".$objListEntry->getStrTitle(), $this->getLang("message_reply"), $this->getLang("message_reply"), "icon_reply")
            );
        }

        return array();
    }


    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return array|string
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        return parent::getNewEntryAction($strListIdentifier, true);
    }

    /**
     * @param Model $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(Model $objListEntry)
    {
        return "";
    }


    /**
     * Stores the submitted config-data back to the database
     *
     * @permissions edit
     * @return void
     */
    protected function actionSaveConfig()
    {

        $objHandler = new MessagingMessagehandler();
        $arrMessageproviders = $objHandler->getMessageproviders();

        foreach ($arrMessageproviders as $objOneProvider) {

            $strClassname = uniStrReplace("\\", "", get_class($objOneProvider));

            $objConfig = MessagingConfig::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);
            $objConfig->setBitBymail($this->getParam($strClassname."_bymail") != "");
            $objConfig->setBitEnabled($this->getParam($strClassname."_enabled") != "");
            $objConfig->updateObjectToDb();

        }

        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul")));
    }

    /**
     * Stores the submitted config-data back to the database.
     * This method stores only one value message for one messageprovider (either "_bymail" or "_enabled").
     *
     * @permissions edit
     * @xml
     *
     * @return string
     */
    protected function actionSaveConfigAjax()
    {
        $objHandler = new MessagingMessagehandler();
        $arrMessageproviders = $objHandler->getMessageproviders();
        $strMessage = "";

        foreach ($arrMessageproviders as $objOneProvider) {
            $objConfig = MessagingConfig::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);

            $strClassname = uniStrReplace("\\", "-", get_class($objOneProvider));

            //only update the message provider which is set in the param "messageprovidertype"
            if ($this->getParam("messageprovidertype") == $strClassname) {
                if ($this->getParam($strClassname."_bymail") != "") {
                    $bitA = $this->getParam($strClassname."_bymail") == "true";
                    $objConfig->setBitBymail($bitA);
                    $objConfig->updateObjectToDb();
                    $strMessage = $objOneProvider->getStrName()." ".$this->getLang("provider_bymail")."=".($bitA ? "enabled" : "disabled");
                    break;

                }
                elseif ($this->getParam($strClassname."_enabled") != "") {
                    $bitA = $this->getParam($strClassname."_enabled") == "true";
                    $objConfig->setBitEnabled($bitA);
                    $objConfig->updateObjectToDb();
                    $strMessage = $objOneProvider->getStrName()." ".$this->getLang("provider_enabled")."=".($bitA ? "enabled" : "disabled");
                    break;
                }
            }
        }

        return "<message>".$strMessage."</message>";
    }

    /**
     * @param Model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(Model $objListEntry, $bitDialog = false)
    {
        if ($objListEntry->rightView()) {
            return $this->objToolkit->listButton(
                Link::getLinkAdmin(
                    $objListEntry->getArrModule("modul"),
                    "edit",
                    "&systemid=".$objListEntry->getSystemid(),
                    $this->getLang("action_edit"),
                    $this->getLang("action_edit"),
                    "icon_lens"
                )
            );
        }
        return "";
    }

    /**
     * @param Model $objListEntry
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @return string
     */
    protected function renderStatusAction(Model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        return "";
    }


    /**
     * Returns a list of the languages
     *
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList()
    {

        //render two multi-buttons
        $strReturn = "";


        //create the list-button and the js code to show the dialog
        $strDeleteAllRead = Link::getLinkAdminManual(
            "href=\"#\" onclick=\"javascript:jsDialog_1.setTitle('".Carrier::getInstance()->getObjLang()->getLang("dialog_deleteHeader", "system")."'); jsDialog_1.setContent('".$this->getLang("delete_all_read_question")."', '".Carrier::getInstance()->getObjLang()->getLang("dialog_deleteButton", "system")."',  function() {jsDialog_3.init(); document.location.href= '".getLinkAdminHref($this->getArrModule("module"), "deleteAllRead")."';}); jsDialog_1.init(); return false;\"",
            $this->getLang("action_delete_all_read")
        );

        $strDeleteAll = Link::getLinkAdminManual(
            "href=\"#\" onclick=\"javascript:jsDialog_1.setTitle('".Carrier::getInstance()->getObjLang()->getLang("dialog_deleteHeader", "system")."'); jsDialog_1.setContent('".$this->getLang("delete_all_question")."', '".Carrier::getInstance()->getObjLang()->getLang("dialog_deleteButton", "system")."',  function() {jsDialog_3.init(); document.location.href= '".getLinkAdminHref($this->getArrModule("module"), "deleteAll")."';}); jsDialog_1.init(); return false;\"",
            $this->getLang("action_delete_all")
        );


        $strReturn .= $this->objToolkit->getContentToolbar(array(
            getLinkAdmin($this->getArrModule("module"), "setAllRead", "", $this->getLang("action_set_all_read")),
            $strDeleteAllRead,
            $strDeleteAll
        ));

        $objArraySectionIterator = new ArraySectionIterator(MessagingMessage::getNumberOfMessagesForUser($this->objSession->getUserID()));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(
            MessagingMessage::getObjectListFiltered(
                null, $this->objSession->getUserID(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()
            )
        );

        $strReturn .= $this->renderList($objArraySectionIterator);
        return $strReturn;

    }

    /**
     * @param string $strListIdentifier
     *
     * @return array
     */
    protected function getBatchActionHandlers($strListIdentifier)
    {
        $arrDefault = array();
        if ($this->getObjModule()->rightDelete()) {
            $arrDefault[] = new AdminBatchaction(AdminskinHelper::getAdminImage("icon_delete"), Link::getLinkAdminXml("system", "delete", "&systemid=%systemid%"), $this->getLang("commons_batchaction_delete"));
        }
        $arrDefault[] = new AdminBatchaction(AdminskinHelper::getAdminImage("icon_mail"), Link::getLinkAdminXml("messaging", "setRead", "&systemid=%systemid%"), $this->getLang("batchaction_read"));
        $arrDefault[] = new AdminBatchaction(AdminskinHelper::getAdminImage("icon_mailNew"), Link::getLinkAdminXml("messaging", "setUnread", "&systemid=%systemid%"), $this->getLang("batchaction_unread"));
        return $arrDefault;
    }

    /**
     * @return string
     * @permissions delete
     */
    protected function actionDeleteAllRead()
    {
        MessagingMessage::deleteAllReadMessages($this->objSession->getUserID());
        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "list"));
        return "";
    }

    /**
     * @return string
     * @permissions delete
     */
    protected function actionDeleteAll()
    {
        MessagingMessage::deleteAllMessages($this->objSession->getUserID());
        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "list"));
        return "";
    }

    /**
     * @return string
     * @permissions view
     */
    protected function actionSetAllRead()
    {
        MessagingMessage::markAllMessagesAsRead($this->objSession->getUserID());
        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "list"));
        return "";
    }

    /**
     * Marks a single message as read
     *
     * @return string
     * @xml
     * @permissions view
     */
    protected function actionSetRead()
    {
        $objMessage = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objMessage instanceof MessagingMessage) {
            $objMessage->setBitRead(true);
            $objMessage->updateObjectToDb();

            return "<message><success /></message>";
        }

        return "<message><error /></message>";
    }

    /**
     * Marks a single message as unread
     *
     * @return string
     * @xml
     * @permissions view
     */
    protected function actionSetUnread()
    {
        $objMessage = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objMessage instanceof MessagingMessage) {
            $objMessage->setBitRead(false);
            $objMessage->updateObjectToDb();

            return "<message><success /></message>";
        }

        return "<message><error /></message>";
    }

    /**
     * @return string
     */
    protected function actionEdit()
    {
        return $this->actionView();
    }

    /**
     * @return string
     */
    protected function actionNew()
    {
        $this->setStrCurObjectTypeName("");
        $this->setCurObjectClassName("Kajona\\System\\System\\MessagingMessage");
        $this->setArrModuleEntry("template", "/folderview.tpl");
        return parent::actionNew();
    }


    /**
     * @return string
     */
    protected function actionSave()
    {

        $this->setArrModuleEntry("template", "/folderview.tpl");

        /** @var $objMessage MessagingMessage */
        $objMessage = null;

        $objMessage = new MessagingMessage();

        $objForm = $this->getAdminForm($objMessage);
        if (!$objForm->validateForm()) {
            if ($this->getParam("mode") === "new") {
                return $this->actionNew();
            }
        }

        $objForm->updateSourceObject();

        $objMessageHandler = new MessagingMessagehandler();
        $objMessage->setObjMessageProvider(new MessageproviderPersonalmessage());
        $objMessageHandler->sendMessageObject($objMessage, Objectfactory::getInstance()->getObject($objMessage->getStrUser()));


        return $this->objToolkit->warningBox($this->getLang("message_sent_success")).
        $this->objToolkit->formHeader("").
        $this->objToolkit->formInputSubmit($this->getLang("commons_ok"), "", "onclick=parent.require('folderview').dialog.hide();").
        $this->objToolkit->formClose();
    }


    /**
     * Creates a summary of the message
     *
     * @return string
     */
    protected function actionView()
    {
        /** @var MessagingMessage $objMessage */
        $objMessage = Objectfactory::getInstance()->getObject($this->getSystemid());

        //different permission handlings
        if ($objMessage !== null && !$objMessage->rightView()) {
            return $this->strOutput = $this->getLang("commons_error_permissions");
        }
        elseif ($objMessage == null) {

            $strText = $this->getLang("message_not_existing");
            $strOk = $this->getLang("commons_ok");
            $strLink = Link::getLinkAdminHref($this->getArrModule("modul"), "list");
            $strMessage = "<script type='text/javascript'>
                $(function() { setTimeout(function() {
                    jsDialog_1.setTitle('&nbsp; ');
                    jsDialog_1.setContent('{$strText}', '{$strOk}', '{$strLink}'); jsDialog_1.init();
                    $('#'+jsDialog_1.containerId+'_cancelButton').css('display', 'none');
                }, 500) } );
            </script>";

            return $strMessage;
        }


        if ($objMessage->getStrUser() == $this->objSession->getUserID()) {

            $strReturn = "";
            if (!$objMessage->getBitRead()) {
                $objMessage->setBitRead(true);
                $objMessage->updateObjectToDb();
            }

            $objSender = Objectfactory::getInstance()->getObject($objMessage->getStrSenderId());

            $strReference = "";
            if (validateSystemid($objMessage->getStrMessageRefId())) {
                $objRefMessage = new MessagingMessage($objMessage->getStrMessageRefId());
                $strReference = $objRefMessage->getStrDisplayName();
                if ($objRefMessage->rightView()) {
                    $strReference = getLinkAdmin($this->getArrModule("modul"), "view", "&systemid=".$objRefMessage->getSystemid(), $strReference, "", "", false);
                }
            }

            $arrMetaData = array(
                array($this->getLang("message_subject"), $objMessage->getStrTitle()),
                array($this->getLang("message_date"), dateToString($objMessage->getObjDate())),
                array($this->getLang("message_type"), $objMessage->getObjMessageProvider()->getStrName()),
                array($this->getLang("message_sender"), $objSender != null ? $objSender->getStrDisplayName() : ""),
                array($this->getLang("message_reference"), $strReference)
            );

            $strReturn .= $this->objToolkit->dataTable(array(), $arrMetaData);

            $strBody = nl2br($objMessage->getStrBody());
            $strBody = replaceTextLinks($strBody);
            $strReturn .= $this->objToolkit->getFieldset($objMessage->getStrTitle(), $this->objToolkit->getTextRow($strBody));

            return $strReturn;
        }
        else {
            return $this->getLang("commons_error_permissions");
        }

    }


    /**
     * Gets the number of unread messages for the current user
     *
     * @permissions view
     * @autoTestable
     * @xml
     *
     * @deprecated
     *
     * @return string
     */
    protected function actionGetUnreadMessagesCount()
    {
        Carrier::getInstance()->getObjSession()->setBitBlockDbUpdate(true);
        Session::getInstance()->sessionClose();
        return "<messageCount>".MessagingMessage::getNumberOfMessagesForUser($this->objSession->getUserID(), true)."</messageCount>";
    }

    /**
     * Creates a list of the recent messages for the current user.
     * The structure is returned in an json-format.
     *
     * @permissions view
     * @xml
     * @autoTestable
     *
     * @return string
     */
    protected function actionGetRecentMessages()
    {
        Carrier::getInstance()->getObjSession()->setBitBlockDbUpdate(true);
        Session::getInstance()->sessionClose();
        SystemChangelog::$bitChangelogEnabled = false;
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);

        $intMaxAmount = $this->getParam("limit") != "" ? $this->getParam("limit") : 5;

        $arrMessages = MessagingMessage::getObjectListFiltered(null, $this->objSession->getUserID(), 0, $intMaxAmount - 1);
        $arrReturn = array();
        foreach ($arrMessages as $objOneMessage) {
            $arrReturn[] = array(
                "systemid" => $objOneMessage->getSystemid(),
                "title"    => $objOneMessage->getStrDisplayName(),
                "unread"   => $objOneMessage->getBitRead(),
                "details"  => Link::getLinkAdminHref($objOneMessage->getArrModule("modul"), "edit", "&systemid=".$objOneMessage->getSystemid(), false)
            );
        }

        $arrReturn = array(
            "messages"     => $arrReturn,
            "messageCount" => MessagingMessage::getNumberOfMessagesForUser($this->objSession->getUserID(), true)
        );

        return json_encode($arrReturn);
    }


}
