<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * This workflow creates a summary of new message for every user - if the user activated the summary provider
 *
 * @package module_workflows
 */
class class_workflow_workflows_messagesummary implements interface_workflows_handler  {

    private $intIntervalDays = 1;
    private $intSendTime = 8;

    /**
     * @var class_module_workflows_workflow
     */
    private $objWorkflow = null;

    /**
     * @see interface_workflows_handler::getConfigValueNames()
     */
    public function getConfigValueNames() {
        return array(
            class_carrier::getInstance()->getObjLang()->getLang("workflow_messagesummary_val1", "workflows"),
            class_carrier::getInstance()->getObjLang()->getLang("workflow_messagesummary_val2", "workflows")
        );
    }

    /**
     * @see interface_workflows_handler::setConfigValues()
     */
    public function setConfigValues($strVal1, $strVal2, $strVal3) {
        if($strVal1 != "" && is_numeric($strVal1))
            $this->intIntervalDays = $strVal1;

        if($strVal2 != "" && is_numeric($strVal2))
            $this->intSendTime = $strVal2;

    }

    /**
     * @see interface_workflows_handler::getDefaultValues()
     */
    public function getDefaultValues() {
        return array(1, 8); // by default the summary is sent at 8 o' clock every day
    }

    public function setObjWorkflow($objWorkflow) {
        $this->objWorkflow = $objWorkflow;
    }

    public function getStrName() {
        return class_carrier::getInstance()->getObjLang()->getLang("workflow_messagesummary_title", "workflows");
    }


    public function execute() {

        //loop all messages by user
        foreach(class_module_user_user::getObjectList() as $objOneUser) {

            if(class_module_messaging_message::getNumberOfMessagesForUser($objOneUser->getSystemid(), true) == 0)
                continue;

            $arrUnreadMessages = array();
            foreach(class_module_messaging_message::getObjectList($objOneUser->getSystemid()) as $objOneMessage) {
                if($objOneMessage->getBitRead() == 0 && !$objOneMessage->getObjMessageProvider() instanceof class_messageprovider_summary)
                    $arrUnreadMessages[] = $objOneMessage;
            }

            $this->createMessageForUser($objOneUser, $arrUnreadMessages);
        }



        //trigger again
        return false;

    }

    /**
     * @param class_module_user_user $objUser
     * @param class_module_messaging_message[] $arrMessages
     */
    private function createMessageForUser(class_module_user_user $objUser, array $arrMessages) {

        $objLang = class_carrier::getInstance()->getObjLang();
        $objLang->setStrTextLanguage($objUser->getStrAdminlanguage());

        $objTemplate = class_carrier::getInstance()->getObjTemplate();

        $objTemplate->setTemplate($objLang->getLang("message_messagesummary_intro", "workflows"));

        $strBody = $objTemplate->fillCurrentTemplate(array("count" => count($arrMessages)))."\n\n";

        $intI = 0;
        foreach($arrMessages as $objOneMessage) {

            $objTemplate->setTemplate($objLang->getLang("message_messagesummary_body_indicator", "workflows"));
            $strBody .= $objTemplate->fillCurrentTemplate(array("current" => ++$intI, "total" => count($arrMessages)))."\n";

            $strBody .= $objLang->getLang("message_subject", "messaging").": ".$objOneMessage->getStrTitle()."\n";
            $strBody .= $objLang->getLang("message_body", "messaging").": ".$objOneMessage->getStrBody()."\n";
            $strBody .= getLinkAdminHref("messaging", "view", "&systemid=".$objOneMessage->getSystemid(), false)."\n";

            $strBody .= "\n";
            $strBody .= "-------------------------------------------\n";
            $strBody .= "\n";
        }


        $objTemplate->setTemplate($objLang->getLang("message_messagesummary_subject", "workflows"));
        $strSubject = $objTemplate->fillCurrentTemplate(array("count" => count($arrMessages)))."\n\n";


        $objSummary = new class_module_messaging_message();
        $objSummary->setStrTitle($strSubject);
        $objSummary->setStrBody($strBody);
        $objSummary->setObjMessageProvider(new class_messageprovider_summary());

        $objMessaging = new class_module_messaging_messagehandler();
        $objMessaging->sendMessageObject($objSummary, $objUser);

    }


    public function onDelete() {

    }


    public function schedule() {

        $objDate = clone $this->objWorkflow->getObjTriggerdate();
        //reschedule
        for($intI = 0; $intI < $this->intIntervalDays; $intI++)
            $objDate->setNextDay();

        $objDate->setIntHour($this->intSendTime)->setIntMin(0)->setIntSec(0);

        if($objDate->getLongTimestamp() < class_date::getCurrentTimestamp()) {
            $objDate = new class_date();
            $objDate->setNextDay()->setIntHour($this->intSendTime)->setIntMin(0)->setIntSec(0);
        }

        $this->objWorkflow->setObjTriggerdate($objDate);

    }

    public function getUserInterface() {

    }

    public function processUserInput($arrParams) {
        return;

    }

    public function providesUserInterface() {
        return false;
    }



}
