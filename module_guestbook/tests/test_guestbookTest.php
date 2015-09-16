<?php
require_once (__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_guestbook extends class_testbase  {

    public function test() {

        $objDB = class_carrier::getInstance()->getObjDB();

        echo "\tmodul_guestbook...\n";

        echo "creating a new guestbook, moderated...\n";
        $objGuestbook = new class_module_guestbook_guestbook();
        $objGuestbook->setStrGuestbookTitle("test guestbook");
        $objGuestbook->setIntGuestbookModerated(1);

        $objGuestbook->updateObjectToDb();
        $strGBId = $objGuestbook->getSystemid();

        echo "adding a new post...\n";
        $objPost = new class_module_guestbook_post();
        $objPost->setStrGuestbookPostName("subject");
        $objPost->setStrGuestbookPostText("test");
        $objPost->updateObjectToDb($strGBId);

        $objDB->flushQueryCache();

        $this->assertEquals(0, count(class_module_guestbook_post::getPosts($strGBId, true)), __FILE__." check nr of posts portal");
        $this->assertEquals(1, count(class_module_guestbook_post::getPosts($strGBId)), __FILE__." check nr of posts total");

        echo "setting guestbook non-moderated...\n";
        $objGuestbook->setIntGuestbookModerated(0);
        $objGuestbook->updateObjectToDb();


        $objDB->flushQueryCache();

        echo "adding a new post...\n";
        $objPost = new class_module_guestbook_post();
        $objPost->setStrGuestbookPostName("subject2");
        $objPost->setStrGuestbookPostText("test2");
        $objPost->updateObjectToDb($strGBId);

        $this->assertEquals(1, count(class_module_guestbook_post::getPosts($strGBId, true)), __FILE__." check nr of posts portal");
        $this->assertEquals(2, count(class_module_guestbook_post::getPosts($strGBId)), __FILE__." check nr of posts total");


        $objDB->flushQueryCache();

        echo "deleting the guestbook...\n";
        $objGuestbook->deleteObjectFromDatabase();

       
    }

}
