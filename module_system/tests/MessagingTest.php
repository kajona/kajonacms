<?php

namespace Kajona\System\Tests;
use Kajona\System\System\Messageproviders\MessageproviderExceptions;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\Testbase;
use Kajona\System\System\UserGroup;

class MessagingTest extends Testbase  {


    public function testSendMessage() {

        $strText = generateSystemid()." autotest";
        $strTitle = generateSystemid()." title";
        $strIdentifier = generateSystemid()." identifier";

        $objMessageHandler = new MessagingMessagehandler();
        $objMessageHandler->sendMessage($strText, new UserGroup(SystemSetting::getConfigValue("_admins_group_id_")), new MessageproviderExceptions(), $strIdentifier, $strTitle);

        $bitFound = false;

        $objGroup = new UserGroup(SystemSetting::getConfigValue("_admins_group_id_"));
        $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();

        $arrMessages = MessagingMessage::getObjectList($arrUsers[0]);

        foreach($arrMessages as $objOneMessage) {
            if($objOneMessage->getStrBody() == $strText && $objOneMessage->getStrMessageProvider() == "Kajona\\System\\System\\Messageproviders\\MessageproviderExceptions") {
                $bitFound = true;
                $this->assertEquals($objOneMessage->getStrTitle(), $strTitle);
                $this->assertEquals($objOneMessage->getStrInternalIdentifier(), $strIdentifier);
                $this->assertTrue($objOneMessage->deleteObjectFromDatabase());
            }
        }


        $this->assertTrue($bitFound);
        $this->flushDBCache();
    }



    public function testSendMessageObject() {

        $strText = generateSystemid()." autotest";
        $strTitle = generateSystemid()." title";
        $strIdentifier = generateSystemid()." identifier";
        $strSender = generateSystemid();
        $strReference = generateSystemid();

        $objMessage = new MessagingMessage();
        $objMessage->setStrTitle($strTitle);
        $objMessage->setStrBody($strText);
        $objMessage->setStrInternalIdentifier($strIdentifier);
        $objMessage->setObjMessageProvider(new MessageproviderExceptions());
        $objMessage->setStrSenderId($strSender);
        $objMessage->setStrMessageRefId($strReference);

        $objMessageHandler = new MessagingMessagehandler();
        $objMessageHandler->sendMessageObject($objMessage, new UserGroup(SystemSetting::getConfigValue("_admins_group_id_")));


        $objGroup = new UserGroup(SystemSetting::getConfigValue("_admins_group_id_"));
        $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();

        foreach($arrUsers as $objOneUser) {
            $bitFound = false;
            $arrMessages = MessagingMessage::getObjectList($objOneUser);

            foreach($arrMessages as $objOneMessage) {
                if($objOneMessage->getStrBody() == $strText && $objOneMessage->getStrMessageProvider() == "Kajona\\System\\System\\Messageproviders\\MessageproviderExceptions") {
                    $bitFound = true;
                    $this->assertEquals($objOneMessage->getStrTitle(), $strTitle);
                    $this->assertEquals($objOneMessage->getStrInternalIdentifier(), $strIdentifier);
                    $this->assertEquals($objOneMessage->getStrSenderId(), $strSender);
                    $this->assertEquals($objOneMessage->getStrMessageRefId(), $strReference);
                    $this->assertTrue($objOneMessage->deleteObjectFromDatabase());
                }
            }


            $this->assertTrue($bitFound);
        }

        $this->flushDBCache();
    }


    public function testUnreadCount() {
        $strText = generateSystemid()." autotest";

        $objMessageHandler = new MessagingMessagehandler();
        $objMessageHandler->sendMessage($strText, new UserGroup(SystemSetting::getConfigValue("_admins_group_id_")), new MessageproviderExceptions());

        $bitFound = false;

        $objGroup = new UserGroup(SystemSetting::getConfigValue("_admins_group_id_"));
        $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();

        $arrMessages = MessagingMessage::getObjectList($arrUsers[0]);

        $intUnread = MessagingMessage::getNumberOfMessagesForUser($arrUsers[0], true);

        $this->assertTrue($intUnread > 0);
        $this->flushDBCache();

        foreach($arrMessages as $objOneMessage) {
            if($objOneMessage->getStrBody() == $strText && $objOneMessage->getStrMessageProvider() == "Kajona\\System\\System\\Messageproviders\\MessageproviderExceptions") {
                $bitFound = true;
                $objOneMessage->setBitRead(true);
                $objOneMessage->updateObjectToDb();

                $this->assertEquals($intUnread-1, MessagingMessage::getNumberOfMessagesForUser($arrUsers[0], true));


                $objOneMessage->deleteObjectFromDatabase();
            }
        }


        $this->assertTrue($bitFound);
        $this->flushDBCache();
    }




}

