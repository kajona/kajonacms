<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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


    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "config", "", $this->getLang("action_config"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"], $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    protected function getArrOutputNaviEntries() {
        $arrEntries = parent::getArrOutputNaviEntries();
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objObject instanceof class_module_messaging_message)
            $arrEntries[] = getLinkAdmin("messaging", "edit", "&systemid=".$objObject->getSystemid(), $objObject->getStrDisplayName());

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

        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->getArrModule("modul"), "saveConfig"));

        $arrRows = array();
        foreach($arrMessageproviders as $objOneProvider) {

            $objConfig = class_module_messaging_config::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);

            $arrRows[] = array(
                $objOneProvider->getStrName(),
                "inlineFormEntry 1" => $this->objToolkit->formInputCheckbox($objOneProvider->getStrIdentifier()."_enabled", $this->getLang("provider_enabled"), $objConfig->getBitEnabled() == 1),
                "inlineFormEntry 2" => $this->objToolkit->formInputCheckbox($objOneProvider->getStrIdentifier()."_bymail", $this->getLang("provider_bymail"), $objConfig->getBitBymail() == 1)
            );

//            $strReturn .= $this->objToolkit->formHeadline($objOneProvider->getStrName());
//            $strReturn .= $this->objToolkit->formInputCheckbox($objOneProvider->getStrIdentifier()."_enabled", $this->getLang("provider_enabled"), $objConfig->getBitEnabled() == 1);
//            $strReturn .= $this->objToolkit->formInputCheckbox($objOneProvider->getStrIdentifier()."_bymail", $this->getLang("provider_bymail"), $objConfig->getBitBymail() == 1);
        }

        $arrHeader = array(
            $this->getLang("provider_title"),
            $this->getLang("provider_enabled"),
            $this->getLang("provider_bymail"),
        );

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrRows);

        $strReturn .= $this->objToolkit->formInputSubmit();
        $strReturn .= $this->objToolkit->formClose();

        return $strReturn;
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_messaging_message) {
            return array(
                getLinkAdminDialog($this->getArrModule("modul"), "new", "&messaging_user_id=".$objListEntry->getStrSenderId()."&messaging_messagerefid=".$objListEntry->getSystemid()."&messaging_title=RE: ".$objListEntry->getStrTitle(), $this->getLang("message_reply"), $this->getLang("message_reply"), "icon_reply")
            );
        }

        return array();
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return parent::getNewEntryAction($strListIdentifier, true);
    }

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
            $objConfig->setBitBymail($this->getParam($objOneProvider->getStrIdentifier()."_bymail") != "");
            $objConfig->setBitEnabled($this->getParam($objOneProvider->getStrIdentifier()."_enabled") != "");
            $objConfig->updateObjectToDb();

        }

        $this->adminReload(getLinkAdminHref($this->getArrModule("modul")));
    }

    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        if($objListEntry->rightView()) {
            return $this->objToolkit->listButton(
                getLinkAdmin(
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

    protected function getBatchActionHandlers($strListIdentifier) {
        $arrDefault = $this->getDefaultActionHandlers();
        $arrDefault[] = new class_admin_batchaction(getImageAdmin("icon_mail"), getLinkAdminXml("messaging", "setRead", "&systemid=%systemid%"), $this->getLang("batchaction_read"));
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
     * @return string
     * @permissions view
     */
    protected function actionEdit() {
        return $this->actionView();
    }

    protected function actionNew() {
        $this->setStrCurObjectTypeName("");
        $this->setCurObjectClassName("class_module_messaging_message");
        $this->setArrModuleEntry("template", "/folderview.tpl");
        return parent::actionNew();
    }


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
     * @permissions view
     */
    protected function actionView() {
        $objMessage = new class_module_messaging_message($this->getSystemid());

        if($objMessage->getStrUser() == $this->objSession->getUserID()) {

            $strReturn = "";
            if(!$objMessage->getBitRead()) {
                $objMessage->setBitRead(true);
                $objMessage->updateObjectToDb();
            }

            $objUser = new class_module_user_user($objMessage->getStrUser());

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
                array($this->getLang("message_sender"), $objUser->getStrDisplayName()),
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
        class_response_object::getInstance()->setStResponseType(class_http_responsetypes::STR_TYPE_JSON);

        $intMaxAmount = $this->getParam("limit") != "" ? $this->getParam("limit") : 5 ;

        $arrMessages = class_module_messaging_message::getObjectList($this->objSession->getUserID(), 0, $intMaxAmount-1);
        $arrReturn = array();
        foreach($arrMessages as $objOneMessage) {
            $arrReturn[] = array(
                "systemid" => $objOneMessage->getSystemid(),
                "title" => $objOneMessage->getStrDisplayName(),
                "unread" => $objOneMessage->getBitRead(),
                "details" => getLinkAdminHref($objOneMessage->getArrModule("modul"), "edit", "&systemid=".$objOneMessage->getSystemid(), false)
            );
        }

        $arrReturn = array(
            "messages" => $arrReturn,
            "messageCount" => class_module_messaging_message::getNumberOfMessagesForUser($this->objSession->getUserID(), true)
        );

        return json_encode($arrReturn);
    }


}
