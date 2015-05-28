<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_messaging extends class_testbase  {


    public function testSendMessage() {

        $strText = generateSystemid()." autotest";
        $strTitle = generateSystemid()." title";
        $strIdentifier = generateSystemid()." identifier";

        $objMessageHandler = new class_module_messaging_messagehandler();
        $objMessageHandler->sendMessage($strText, new class_module_user_group(class_module_system_setting::getConfigValue("_admins_group_id_")), new class_messageprovider_exceptions(), $strIdentifier, $strTitle);

        $bitFound = false;

        $objGroup = new class_module_user_group(class_module_system_setting::getConfigValue("_admins_group_id_"));
        $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();

        $arrMessages = class_module_messaging_message::getObjectList($arrUsers[0]);

        foreach($arrMessages as $objOneMessage) {
            if($objOneMessage->getStrBody() == $strText && $objOneMessage->getStrMessageProvider() == "class_messageprovider_exceptions") {
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

        $objMessage = new class_module_messaging_message();
        $objMessage->setStrTitle($strTitle);
        $objMessage->setStrBody($strText);
        $objMessage->setStrInternalIdentifier($strIdentifier);
        $objMessage->setObjMessageProvider(new class_messageprovider_exceptions());
        $objMessage->setStrSenderId($strSender);
        $objMessage->setStrMessageRefId($strReference);

        $objMessageHandler = new class_module_messaging_messagehandler();
        $objMessageHandler->sendMessageObject($objMessage, new class_module_user_group(class_module_system_setting::getConfigValue("_admins_group_id_")));


        $objGroup = new class_module_user_group(class_module_system_setting::getConfigValue("_admins_group_id_"));
        $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();

        foreach($arrUsers as $objOneUser) {
            $bitFound = false;
            $arrMessages = class_module_messaging_message::getObjectList($objOneUser);

            foreach($arrMessages as $objOneMessage) {
                if($objOneMessage->getStrBody() == $strText && $objOneMessage->getStrMessageProvider() == "class_messageprovider_exceptions") {
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

        $objMessageHandler = new class_module_messaging_messagehandler();
        $objMessageHandler->sendMessage($strText, new class_module_user_group(class_module_system_setting::getConfigValue("_admins_group_id_")), new class_messageprovider_exceptions());

        $bitFound = false;

        $objGroup = new class_module_user_group(class_module_system_setting::getConfigValue("_admins_group_id_"));
        $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();

        $arrMessages = class_module_messaging_message::getObjectList($arrUsers[0]);

        $intUnread = class_module_messaging_message::getNumberOfMessagesForUser($arrUsers[0], true);

        $this->assertTrue($intUnread > 0);
        $this->flushDBCache();

        foreach($arrMessages as $objOneMessage) {
            if($objOneMessage->getStrBody() == $strText && $objOneMessage->getStrMessageProvider() == "class_messageprovider_exceptions") {
                $bitFound = true;
                $objOneMessage->setBitRead(true);
                $objOneMessage->updateObjectToDb();

                $this->assertEquals($intUnread-1, class_module_messaging_message::getNumberOfMessagesForUser($arrUsers[0], true));


                $objOneMessage->deleteObjectFromDatabase();
            }
        }


        $this->assertTrue($bitFound);
        $this->flushDBCache();
    }




}

