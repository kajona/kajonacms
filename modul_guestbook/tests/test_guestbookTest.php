<?php

require_once (dirname(__FILE__)."/../system/class_testbase.php");

class class_test_guestbook extends class_testbase  {

    public function test() {

        $objDB = class_carrier::getInstance()->getObjDB();

        echo "\tmodul_guestbook...\n";

        echo "creating a new guestbook, moderated...\n";
        $objGuestbook = new class_modul_guestbook_guestbook();
        $objGuestbook->setGuestbookTitle("test guestbook");
        $objGuestbook->setGuestbookModerated(0);

        $objGuestbook->updateObjectToDb();
        $strGBId = $objGuestbook->getSystemid();

        echo "adding a new post...\n";
        $objPost = new class_modul_guestbook_post();
        $objPost->setGuestbookPostName("subject");
        $objPost->setGuestbookPostText("test");
        $objPost->updateObjectToDb($strGBId);

        $objDB->flushQueryCache();

        $this->assertEquals(0, count(class_modul_guestbook_post::getPosts($strGBId, true)), __FILE__." check nr of posts portal");
        $this->assertEquals(1, count(class_modul_guestbook_post::getPosts($strGBId)), __FILE__." check nr of posts total");

        echo "setting guestbook non-moderated...\n";
        $objGuestbook->setGuestbookModerated(1);
        $objGuestbook->updateObjectToDb();


        $objDB->flushQueryCache();

        echo "adding a new post...\n";
        $objPost = new class_modul_guestbook_post();
        $objPost->setGuestbookPostName("subject2");
        $objPost->setGuestbookPostText("test2");
        $objPost->updateObjectToDb($strGBId);

        $this->assertEquals(1, count(class_modul_guestbook_post::getPosts($strGBId, true)), __FILE__." check nr of posts portal");
        $this->assertEquals(2, count(class_modul_guestbook_post::getPosts($strGBId)), __FILE__." check nr of posts total");


        $objDB->flushQueryCache();

        echo "deleting the guestbook...\n";
        $objGuestbook->deleteGuestbook();

       
    }

}

?>