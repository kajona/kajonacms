<?php

namespace Kajona\Guestbook\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\Guestbook\System\GuestbookGuestbook;
use Kajona\Guestbook\System\GuestbookPost;
use Kajona\System\System\Carrier;
use Kajona\System\System\Testbase;

class GuestbookTest extends Testbase
{

    public function test()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        echo "\tmodul_guestbook...\n";

        echo "creating a new guestbook, moderated...\n";
        $objGuestbook = new GuestbookGuestbook();
        $objGuestbook->setStrGuestbookTitle("test guestbook");
        $objGuestbook->setIntGuestbookModerated(1);

        $objGuestbook->updateObjectToDb();
        $strGBId = $objGuestbook->getSystemid();

        echo "adding a new post...\n";
        $objPost = new GuestbookPost();
        $objPost->setStrGuestbookPostName("subject");
        $objPost->setStrGuestbookPostText("test");
        $objPost->updateObjectToDb($strGBId);

        $objDB->flushQueryCache();

        $this->assertEquals(0, count(GuestbookPost::getPosts($strGBId, true)), __FILE__ . " check nr of posts portal");
        $this->assertEquals(1, count(GuestbookPost::getPosts($strGBId)), __FILE__ . " check nr of posts total");

        echo "setting guestbook non-moderated...\n";
        $objGuestbook->setIntGuestbookModerated(0);
        $objGuestbook->updateObjectToDb();


        $objDB->flushQueryCache();

        echo "adding a new post...\n";
        $objPost = new GuestbookPost();
        $objPost->setStrGuestbookPostName("subject2");
        $objPost->setStrGuestbookPostText("test2");
        $objPost->updateObjectToDb($strGBId);

        $this->assertEquals(1, count(GuestbookPost::getPosts($strGBId, true)), __FILE__ . " check nr of posts portal");
        $this->assertEquals(2, count(GuestbookPost::getPosts($strGBId)), __FILE__ . " check nr of posts total");


        $objDB->flushQueryCache();

        echo "deleting the guestbook...\n";
        $objGuestbook->deleteObjectFromDatabase();


    }

}
