<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/

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
 * @objectList class_module_messaging_message
 * @objectNew class_module_messaging_message
 * @objectEdit class_module_messaging_message
 */
class class_module_messaging_admin extends class_admin_evensimpler implements interface_admin {


    /**
     * @return array
     */
    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", class_link::getLinkAdmin($this->getArrModule("modul"), "config", "", $this->getLang("action_config"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * @return array
     */
    protected function getArrOutputNaviEntries() {
        $arrEntries = parent::getArrOutputNaviEntries();
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objObject instanceof class_module_messaging_message)
            $arrEntries[] = class_link::getLinkAdmin("messaging", "edit", "&systemid=".$objObject->getSystemid(), $objObject->getStrDisplayName());

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
    protected function actionConfig() {
        $objHandler = new class_module_messaging_messagehandler();
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

            KAJONA.admin.ajax.genericAjaxCall("messaging", "saveConfigAjax", "&"+postBody, KAJONA.admin.ajax.regularCallback);

            if(inputId.indexOf("_enabled") > 0 ) {
                $("#"+inputId).closest("tr").find("div.checkbox input:not(.blockEnable)").slice(1).bootstrapSwitch("disabled", state);
            }
JS;
        $arrRows = array();
        foreach($arrMessageproviders as $objOneProvider) {

            if($objOneProvider instanceof interface_messageprovider_extended && !$objOneProvider->isVisibleInConfigView())
                continue;

            $objConfig = class_module_messaging_config::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);

            $bitAlwaysEnabled = $objOneProvider instanceof interface_messageprovider_extended && $objOneProvider->isAlwaysActive();
            $bitAlwaysMail = $objOneProvider instanceof interface_messageprovider_extended && $objOneProvider->isAlwaysByMail();

            $arrRows[] = array(
                $objOneProvider->getStrName(),
                "inlineFormEntry 1" => $this->objToolkit->formInputOnOff(get_class($objOneProvider)."_enabled", $this->getLang("provider_enabled"), $objConfig->getBitEnabled() == 1, $bitAlwaysEnabled, $strCallback),
                "inlineFormEntry 2" => $this->objToolkit->formInputOnOff(get_class($objOneProvider)."_bymail", $this->getLang("provider_bymail"), $objConfig->getBitBymail() == 1, $bitAlwaysMail, $strCallback, ($bitAlwaysMail ? "blockEnable" : ""))
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
     * @param class_model $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_messaging_message) {
            return array(
                class_link::getLinkAdminDialog($this->getArrModule("modul"), "new", "&messaging_user_id=".$objListEntry->getStrSenderId()."&messaging_messagerefid=".$objListEntry->getSystemid()."&messaging_title=RE: ".$objListEntry->getStrTitle(), $this->getLang("message_reply"), $this->getLang("message_reply"), "icon_reply")
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
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return parent::getNewEntryAction($strListIdentifier, true);
    }

    /**
     * @param class_model $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(class_model $objListEntry) {
        return "";
    }


    /**
     * Stores the submitted config-data back to the database
     *
     * @permissions edit
     * @return void
     */
    protected function actionSaveConfig() {

        $objHandler = new class_module_messaging_messagehandler();
        $arrMessageproviders = $objHandler->getMessageproviders();

        foreach($arrMessageproviders as $objOneProvider) {

            $objConfig = class_module_messaging_config::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);
            $objConfig->setBitBymail($this->getParam(get_class($objOneProvider)."_bymail") != "");
            $objConfig->setBitEnabled($this->getParam(get_class($objOneProvider)."_enabled") != "");
            $objConfig->updateObjectToDb();

        }

        $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul")));
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
    protected function actionSaveConfigAjax() {
        $objHandler = new class_module_messaging_messagehandler();
        $arrMessageproviders = $objHandler->getMessageproviders();
        $strMessage = "";

        foreach($arrMessageproviders as $objOneProvider) {
            $objConfig = class_module_messaging_config::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);

            //only update the message provider which is set in the param "messageprovidertype"
            if($this->getParam("messageprovidertype") == get_class($objOneProvider)) {
                if($this->getParam(get_class($objOneProvider)."_bymail") != "") {
                    $bitA = $this->getParam(get_class($objOneProvider)."_bymail") == "true";
                    $objConfig->setBitBymail($bitA);
                    $objConfig->updateObjectToDb();
                    $strMessage = $objOneProvider->getStrName()." ".$this->getLang("provider_bymail")."=".$this->getParam(get_class($objOneProvider)."_bymail");
                    break;

                }
                else if($this->getParam(get_class($objOneProvider)."_enabled") != "") {
                    $bitA = $this->getParam(get_class($objOneProvider)."_enabled") == "true";
                    $objConfig->setBitEnabled($bitA);
                    $objConfig->updateObjectToDb();
                    $strMessage = $objOneProvider->getStrName()." ".$this->getLang("provider_enabled")."=".$this->getParam(get_class($objOneProvider)."_enabled");
                    break;
                }
            }
        }

        return "<message>".$strMessage."</message>";
    }

    /**
     * @param class_model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        if($objListEntry->rightView()) {
            return $this->objToolkit->listButton(
                class_link::getLinkAdmin(
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
     * @param class_model $objListEntry
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @return string
     */
    protected function renderStatusAction(class_model $objListEntry, $strAltActive = "", $strAltInactive = "") {
        return "";
    }


    /**
     * Returns a list of the languages
     *
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList() {

        $objArraySectionIterator = new class_array_section_iterator(class_module_messaging_message::getNumberOfMessagesForUser($this->objSession->getUserID()));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(
            class_module_messaging_message::getObjectList(
                $this->objSession->getUserID(),
                $objArraySectionIterator->calculateStartPos(),
                $objArraySectionIterator->calculateEndPos()
            )
        );

        return $this->renderList($objArraySectionIterator);

    }

    /**
     * @param string $strListIdentifier
     *
     * @return array
     */
    protected function getBatchActionHandlers($strListIdentifier) {
        $arrDefault = array();
        if($this->getObjModule()->rightDelete())
            $arrDefault[] = new class_admin_batchaction(class_adminskin_helper::getAdminImage("icon_delete"), class_link::getLinkAdminXml("system", "delete", "&systemid=%systemid%"), $this->getLang("commons_batchaction_delete"));
        $arrDefault[] = new class_admin_batchaction(class_adminskin_helper::getAdminImage("icon_mail"), class_link::getLinkAdminXml("messaging", "setRead", "&systemid=%systemid%"), $this->getLang("batchaction_read"));
        $arrDefault[] = new class_admin_batchaction(class_adminskin_helper::getAdminImage("icon_mailNew"), class_link::getLinkAdminXml("messaging", "setUnread", "&systemid=%systemid%"), $this->getLang("batchaction_unread"));
        return $arrDefault;
    }


    /**
     * Marks a single message as read
     *
     * @return string
     * @xml
     * @permissions view
     */
    protected function actionSetRead() {
        $objMessage = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objMessage instanceof class_module_messaging_message) {
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
    protected function actionSetUnread() {
        $objMessage = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objMessage instanceof class_module_messaging_message) {
            $objMessage->setBitRead(false);
            $objMessage->updateObjectToDb();

            return "<message><success /></message>";
        }

        return "<message><error /></message>";
    }

    /**
     * @param interface_model $objInstance
     *
     * @return class_admin_formgenerator
     */
    protected function getAdminForm(interface_model $objInstance) {
        $objForm = parent::getAdminForm($objInstance);

        if($objInstance instanceof class_module_messaging_message) {
            if(validateSystemid($objForm->getField("messagerefid")->getStrValue()) && $objForm->getField("body")->getStrValue() == "") {
                $objRefMessage = class_objectfactory::getInstance()->getObject($objForm->getField("messagerefid")->getStrValue());
                if($objRefMessage instanceof class_module_messaging_message) {

                    $arrBody = preg_split('/$\R?^/m', $objRefMessage->getStrBody());
                    array_walk($arrBody, function (&$strValue) {
                        $strValue = "> ".$strValue;
                    });

                    $objForm->getField("body")->setStrValue("\r\n\r\n\r\n".implode("\r\n", $arrBody));
                }
            }
        }

        return $objForm;
    }


    /**
     * @return string
     */
    protected function actionEdit() {
        return $this->actionView();
    }

    /**
     * @return string
     */
    protected function actionNew() {
        $this->setStrCurObjectTypeName("");
        $this->setCurObjectClassName("class_module_messaging_message");
        $this->setArrModuleEntry("template", "/folderview.tpl");
        return parent::actionNew();
    }


    /**
     * @return string
     */
    protected  function actionSave() {

        $this->setArrModuleEntry("template", "/folderview.tpl");

        /** @var $objMessage class_module_messaging_message */
        $objMessage = null;

        $objMessage = new class_module_messaging_message();

            $objForm = $this->getAdminForm($objMessage);
            if(!$objForm->validateForm())
                if($this->getParam("mode") === "new")
                    return $this->actionNew();

            $objForm->updateSourceObject();

            $objMessageHandler = new class_module_messaging_messagehandler();
            $objMessage->setObjMessageProvider(new class_messageprovider_personalmessage());
            $objMessageHandler->sendMessageObject($objMessage, new class_module_user_user($objMessage->getStrUser()));


            return $this->objToolkit->warningBox($this->getLang("message_sent_success")).
                $this->objToolkit->formHeader("").
                $this->objToolkit->formInputSubmit($this->getLang("commons_ok"), "", "onclick=parent.KAJONA.admin.folderview.dialog.hide();").
                $this->objToolkit->formClose();
    }


    /**
     * Creates a summary of the message
     *
     * @return string
     */
    protected function actionView() {
        /** @var class_module_messaging_message $objMessage */
        $objMessage = class_objectfactory::getInstance()->getObject($this->getSystemid());

        //different permission handlings
        if($objMessage !== null && !$objMessage->rightView()) {
            return $this->strOutput = $this->getLang("commons_error_permissions");
        }
        else if($objMessage == null) {

            $strText = $this->getLang("message_not_existing");
            $strOk = $this->getLang("commons_ok");
            $strLink = class_link::getLinkAdminHref($this->getArrModule("modul"), "list");
            $strCore = class_resourceloader::getInstance()->getCorePathForModule("module_v4skin");
            $strMessage = "<script type='text/javascript'>
                KAJONA.admin.loader.loadFile('_webpath_{$strCore}/module_v4skin/admin/skins/kajona_v4/js/kajona_dialog.js', function() {
                    jsDialog_1.setTitle('&nbsp; ');
                    jsDialog_1.setContent('{$strText}', '{$strOk}', '{$strLink}'); jsDialog_1.init();
                    $('#'+jsDialog_1.containerId+'_cancelButton').css('display', 'none');
                });
            </script>";

            return $strMessage;
        }


        if($objMessage->getStrUser() == $this->objSession->getUserID()) {

            $strReturn = "";
            if(!$objMessage->getBitRead()) {
                $objMessage->setBitRead(true);
                $objMessage->updateObjectToDb();
            }

            $objSender = new class_module_user_user($objMessage->getStrSenderId());

            $strReference = "";
            if(validateSystemid($objMessage->getStrMessageRefId())) {
                $objRefMessage = new class_module_messaging_message($objMessage->getStrMessageRefId());
                $strReference = $objRefMessage->getStrDisplayName();
                if($objRefMessage->rightView())
                    $strReference = getLinkAdmin($this->getArrModule("modul"), "view", "&systemid=".$objRefMessage->getSystemid(), $strReference, "", "", false);
            }

            $arrMetaData = array(
                array($this->getLang("message_subject"), $objMessage->getStrTitle()),
                array($this->getLang("message_date"), dateToString($objMessage->getObjDate())),
                array($this->getLang("message_type"), $objMessage->getObjMessageProvider()->getStrName()),
                array($this->getLang("message_sender"), $objSender->getStrDisplayName()),
                array($this->getLang("message_reference"), $strReference)
            );

            $strReturn .= $this->objToolkit->dataTable(null, $arrMetaData);

            $strBody = nl2br($objMessage->getStrBody());
            $strBody = replaceTextLinks($strBody);
            $strReturn .= $this->objToolkit->getFieldset($objMessage->getStrTitle(), $this->objToolkit->getTextRow($strBody));

            return $strReturn;
        }
        else
            return $this->getLang("commons_error_permissions");

    }


    /**
     * Gets the number of unread messages for the current user
     *
     * @permissions view
     * @autoTestable
     * @xml
     *
     * @return string
     */
    protected function actionGetUnreadMessagesCount() {
        class_carrier::getInstance()->getObjSession()->setBitBlockDbUpdate(true);
        return "<messageCount>".class_module_messaging_message::getNumberOfMessagesForUser($this->objSession->getUserID(), true)."</messageCount>";
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
    protected function actionGetRecentMessages() {
        class_carrier::getInstance()->getObjSession()->setBitBlockDbUpdate(true);
        class_session::getInstance()->sessionClose();
        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);

        $intMaxAmount = $this->getParam("limit") != "" ? $this->getParam("limit") : 5 ;

        $arrMessages = class_module_messaging_message::getObjectList($this->objSession->getUserID(), 0, $intMaxAmount-1);
        $arrReturn = array();
        foreach($arrMessages as $objOneMessage) {
            $arrReturn[] = array(
                "systemid" => $objOneMessage->getSystemid(),
                "title" => $objOneMessage->getStrDisplayName(),
                "unread" => $objOneMessage->getBitRead(),
                "details" => class_link::getLinkAdminHref($objOneMessage->getArrModule("modul"), "edit", "&systemid=".$objOneMessage->getSystemid(), false)
            );
        }

        $arrReturn = array(
            "messages" => $arrReturn,
            "messageCount" => class_module_messaging_message::getNumberOfMessagesForUser($this->objSession->getUserID(), true)
        );

        return json_encode($arrReturn);
    }


}
