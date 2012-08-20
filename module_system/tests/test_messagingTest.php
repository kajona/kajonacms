<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_messaging extends class_testbase  {


    public function testSendMessage() {

        $strText = generateSystemid()." autotest";

        $objMessageHandler = new class_module_messaging_messagehandler();
        $objMessageHandler->sendMessage($strText, new class_module_user_group(_admins_group_id_), new class_messageprovider_exceptions());

        $bitFound = false;

        $objGroup = new class_module_user_group(_admins_group_id_);
        $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();

        $arrMessages = class_module_messaging_message::getMessagesForUser($arrUsers[0]);

        foreach($arrMessages as $objOneMessage) {
            if($objOneMessage->getStrBody() == $strText && $objOneMessage->getStrMessageProvider() == "class_messageprovider_exceptions") {
                $bitFound = true;
                $objOneMessage->deleteObject();
            }
        }


        $this->assertTrue($bitFound);
        $this->flushDBCache();
    }

    public function testUnreadCount() {
        $strText = generateSystemid()." autotest";

        $objMessageHandler = new class_module_messaging_messagehandler();
        $objMessageHandler->sendMessage($strText, new class_module_user_group(_admins_group_id_), new class_messageprovider_exceptions());

        $bitFound = false;

        $objGroup = new class_module_user_group(_admins_group_id_);
        $arrUsers = $objGroup->getObjSourceGroup()->getUserIdsForGroup();

        $arrMessages = class_module_messaging_message::getMessagesForUser($arrUsers[0]);

        $intUnread = class_module_messaging_message::getNumberOfMessagesForUser($arrUsers[0], true);

        $this->assertTrue($intUnread > 0);
        $this->flushDBCache();

        foreach($arrMessages as $objOneMessage) {
            if($objOneMessage->getStrBody() == $strText && $objOneMessage->getStrMessageProvider() == "class_messageprovider_exceptions") {
                $bitFound = true;
                $objOneMessage->setBitRead(true);
                $objOneMessage->updateObjectToDb();

                $this->assertEquals($intUnread-1, class_module_messaging_message::getNumberOfMessagesForUser($arrUsers[0], true));


                $objOneMessage->deleteObject();
            }
        }


        $this->assertTrue($bitFound);
        $this->flushDBCache();
    }




}

