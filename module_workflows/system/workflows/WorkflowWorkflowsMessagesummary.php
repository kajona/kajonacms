<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Workflows\System\Workflows;

use Kajona\System\System\Carrier;
use Kajona\System\System\Link;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\UserUser;
use Kajona\Workflows\System\Messageproviders\MessageproviderSummary;
use Kajona\Workflows\System\WorkflowsHandlerInterface;
use Kajona\Workflows\System\WorkflowsWorkflow;


/**
 * This workflow creates a summary of new message for every user - if the user activated the summary provider
 *
 * @package module_workflows
 */
class WorkflowWorkflowsMessagesummary implements WorkflowsHandlerInterface
{

    private $intIntervalDays = 1;
    private $intSendTime = 8;

    /** @var int The number of messages rendered with the message body */
    private $intBodysRendered = 5;

    /**
     * @var WorkflowsWorkflow
     */
    private $objWorkflow = null;

    /**
     * @inheritdoc
     */
    public function getConfigValueNames()
    {
        return array(
            Carrier::getInstance()->getObjLang()->getLang("workflow_messagesummary_val1", "workflows"),
            Carrier::getInstance()->getObjLang()->getLang("workflow_messagesummary_val2", "workflows"),
            Carrier::getInstance()->getObjLang()->getLang("workflow_messagesummary_val3", "workflows")
        );
    }

    /**
     * @inheritdoc
     */
    public function setConfigValues($strVal1, $strVal2, $strVal3)
    {
        if ($strVal1 != "" && is_numeric($strVal1)) {
            $this->intIntervalDays = $strVal1;
        }

        if ($strVal2 != "" && is_numeric($strVal2)) {
            $this->intSendTime = $strVal2;
        }

        if ($strVal3 != "" && is_numeric($strVal3)) {
            $this->intBodysRendered = $strVal3;
        }

    }

    /**
     * @inheritdoc
     */
    public function getDefaultValues()
    {
        return array(1, 8, 5); // by default the summary is sent at 8 o' clock every day
    }

    /**
     * @param WorkflowsWorkflow $objWorkflow
     *
     * @return void
     */
    public function setObjWorkflow($objWorkflow)
    {
        $this->objWorkflow = $objWorkflow;
    }

    /**
     * @return string
     */
    public function getStrName()
    {
        return Carrier::getInstance()->getObjLang()->getLang("workflow_messagesummary_title", "workflows");
    }

    /**
     * @return bool
     */
    public function execute()
    {

        //loop all messages by user
        foreach (UserUser::getObjectListFiltered() as $objOneUser) {

            //skip inactive users
            if ($objOneUser->getIntRecordStatus() == 0) {
                continue;
            }

            if (MessagingMessage::getNumberOfMessagesForUser($objOneUser->getSystemid(), true) == 0) {
                continue;
            }

            $arrUnreadMessages = array();
            foreach (MessagingMessage::getObjectListFiltered(null, $objOneUser->getSystemid()) as $objOneMessage) {
                if ($objOneMessage->getBitRead() == 0 && !$objOneMessage->getObjMessageProvider() instanceof MessageproviderSummary) {
                    $arrUnreadMessages[] = $objOneMessage;
                }

                if ($objOneMessage->getObjMessageProvider() instanceof MessageproviderSummary) {
                    $objOneMessage->deleteObjectFromDatabase();
                }
            }

            if (count($arrUnreadMessages) > 0) {
                $this->createMessageForUser($objOneUser, $arrUnreadMessages);
            }
        }


        //trigger again
        return false;

    }

    /**
     * @param UserUser $objUser
     * @param MessagingMessage[] $arrMessages
     *
     * @return void
     */
    private function createMessageForUser(UserUser $objUser, array $arrMessages)
    {

        $objLang = Carrier::getInstance()->getObjLang();
        $objLang->setStrTextLanguage($objUser->getStrAdminlanguage());

        $strBody = $objLang->getLang("message_messagesummary_intro", "workflows", array(count($arrMessages)))."\n\n";

        $intI = 0;
        foreach ($arrMessages as $objOneMessage) {

            $strBody .= $objLang->getLang("message_messagesummary_body_indicator", "workflows", array(++$intI, count($arrMessages)))."\n";

            if($objOneMessage->getStrTitle() != "") {
                $strBody .= $objLang->getLang("message_subject", "messaging").": ".$objOneMessage->getStrTitle()."\n";
            }

            $strBody .= $objLang->getLang("message_link", "messaging").": ".Link::getLinkAdminHref("messaging", "view", "&systemid=".$objOneMessage->getSystemid(), false)."\n";

            if($intI <= $this->intBodysRendered) {
                $strBody .= $objLang->getLang("message_body", "messaging").":\n".$objOneMessage->getStrBody()."\n";
            }

            $strBody .= "\n";
            $strBody .= "-------------------------------------------\n";
        }

        $strSubject = $objLang->getLang("message_messagesummary_subject", "workflows", array(count($arrMessages)));

        $objSummary = new MessagingMessage();
        $objSummary->setStrTitle($strSubject);
        $objSummary->setStrBody($strBody);
        $objSummary->setObjMessageProvider(new MessageproviderSummary());

        $objMessaging = new MessagingMessagehandler();
        $objMessaging->sendMessageObject($objSummary, $objUser);

    }


    /**
     * @return void
     */
    public function onDelete()
    {

    }


    /**
     * @return void
     */
    public function schedule()
    {

        $objDate = clone $this->objWorkflow->getObjTriggerdate();
        //reschedule
        for ($intI = 0; $intI < $this->intIntervalDays; $intI++) {
            $objDate->setNextDay();
        }

        $objDate->setIntHour($this->intSendTime)->setIntMin(0)->setIntSec(0);

        if ($objDate->getLongTimestamp() < \Kajona\System\System\Date::getCurrentTimestamp()) {
            $objDate = new \Kajona\System\System\Date();
            $objDate->setNextDay()->setIntHour($this->intSendTime)->setIntMin(0)->setIntSec(0);
        }

        $this->objWorkflow->setObjTriggerdate($objDate);

    }

    /**
     * @return void
     */
    public function getUserInterface()
    {

    }

    /**
     * @param array $arrParams
     *
     * @return void
     */
    public function processUserInput($arrParams)
    {
        return;

    }

    /**
     * @return bool
     */
    public function providesUserInterface()
    {
        return false;
    }


}
