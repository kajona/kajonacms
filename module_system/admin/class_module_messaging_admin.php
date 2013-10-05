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
 */
class class_module_messaging_admin extends class_admin_simple implements interface_admin {


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

        foreach($arrMessageproviders as $objOneProvider) {

            $objConfig = class_module_messaging_config::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);

            $strReturn .= $this->objToolkit->formHeadline($objOneProvider->getStrName());
            $strReturn .= $this->objToolkit->formInputCheckbox($objOneProvider->getStrIdentifier()."_enabled", $this->getLang("provider_enabled"), $objConfig->getBitEnabled() == 1);
            $strReturn .= $this->objToolkit->formInputCheckbox($objOneProvider->getStrIdentifier()."_bymail", $this->getLang("provider_bymail"), $objConfig->getBitBymail() == 1);
        }

        $strReturn .= $this->objToolkit->formInputSubmit();
        $strReturn .= $this->objToolkit->formClose();

        return $strReturn;
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return "";
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

    /**
     * Renders the form to create a new entry
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        $this->adminReload(getLinkAdminHref($this->getArrModule("modul")));
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

            $strReturn .= $this->objToolkit->formHeadline(dateToString($objMessage->getObjDate()). " ".$objMessage->getStrTitle());

            //$strBody = nl2br($objMessage->getStrBody());
            $strBody = nl2br($objMessage->getStrBody());
            $strBody = replaceTextLinks($strBody);
            $strReturn .= $this->objToolkit->getTextRow($strBody);

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
