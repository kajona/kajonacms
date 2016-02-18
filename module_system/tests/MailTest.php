<?php

namespace Kajona\System\Tests;
require_once __DIR__."../../../core/module_system/system/Testbase.php";
use Kajona\System\System\Mail;
use Kajona\System\System\Testbase;

class MailTest extends Testbase  {

    public function test() {

        $strTo = "postmaster@localhost";
        $intSentMails = 0;

        echo "\tsend a test email to ".$strTo."...\n";

        $objMail = new Mail();
        $objMail->setSender("test@kajona.de");
        $objMail->setSenderName("Kajona System a ö ü ");
        $objMail->addTo($strTo);
        $objMail->setSubject("Kajona test mail ä ö ü Kajona test mail ä ö ü Kajona test mail ä ö ü Kajona test mail ä ö ü Kajona test mail ä ö ü ");
        $objMail->setText("This is the plain text ä ö ü");
        $objMail->setHtml("This is<br />the <b>html-content ä ö ü</b><br /><img src=\"cid:IMG_3000.jpg\" />");
        $objMail->addAttachement("/files/images/samples/IMG_3000.jpg");
        $objMail->addAttachement("/files/images/samples/P3197800.jpg", "", true);

        if ($objMail->sendMail() === true) {
            $intSentMails++;
        }

        $this->assertEquals($intSentMails, 1, __FILE__." checkNrOfMails");
    }

}

