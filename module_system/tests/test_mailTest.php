<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_mail extends class_testbase  {

    public function test() {

        $strTo = "postmaster@localhost";
        $intSentMails = 0;

        echo "\tsend a test email to ".$strTo."...\n";

        $objMail = new class_mail();
        $objMail->setSender("test@kajona.de");
        $objMail->setSenderName("Kajona System");
        $objMail->addTo($strTo);
        $objMail->setSubject("Kajona test mail");
        $objMail->setText("This is the plain text");
        $objMail->setHtml("This is<br />the <b>html-content</b><br /><img src=\"cid:kajona_poweredby.png\" />");
        $objMail->addAttachement("/portal/pics/kajona/login_logo.gif");
        $objMail->addAttachement("/portal/pics/kajona/kajona_poweredby.png", "", true);

        if ($objMail->sendMail() === true) {
            $intSentMails++;
        }

        $this->assertEquals($intSentMails, 1, __FILE__." checkNrOfMails");
    }

}

