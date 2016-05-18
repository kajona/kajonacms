<?php

namespace Kajona\Guestbook\Tests;

use Kajona\Guestbook\System\GuestbookGuestbook;
use Kajona\Guestbook\System\GuestbookPost;
use Kajona\System\System\Carrier;
use Kajona\System\Tests\Testbase;

class GuestbookTest extends Testbase
{

    public function test()
    {

        $objDB = Carrier::getInstance()->getObjDB();

        $objGuestbook = new GuestbookGuestbook();
        $objGuestbook->setStrGuestbookTitle("test guestbook");
        $objGuestbook->setIntGuestbookModerated(1);

        $objGuestbook->updateObjectToDb();
        $strGBId = $objGuestbook->getSystemid();

        $objPost = new GuestbookPost();
        $objPost->setStrGuestbookPostName("subject");
        $objPost->setStrGuestbookPostText("test");
        $objPost->updateObjectToDb($strGBId);

        $objDB->flushQueryCache();

        $this->assertEquals(0, count(GuestbookPost::getPosts($strGBId, true)), __FILE__ . " check nr of posts portal");
        $this->assertEquals(1, count(GuestbookPost::getPosts($strGBId)), __FILE__ . " check nr of posts total");

        $objGuestbook->setIntGuestbookModerated(0);
        $objGuestbook->updateObjectToDb();


        $objDB->flushQueryCache();

        $objPost = new GuestbookPost();
        $objPost->setStrGuestbookPostName("subject2");
        $objPost->setStrGuestbookPostText("test2");
        $objPost->updateObjectToDb($strGBId);

        $this->assertEquals(1, count(GuestbookPost::getPosts($strGBId, true)), __FILE__ . " check nr of posts portal");
        $this->assertEquals(2, count(GuestbookPost::getPosts($strGBId)), __FILE__ . " check nr of posts total");


        $objDB->flushQueryCache();

        $objGuestbook->deleteObjectFromDatabase();


    }

}
