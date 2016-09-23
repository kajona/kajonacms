<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Guestbook\Installer;

use Kajona\Guestbook\Admin\Elements\ElementGuestbookAdmin;
use Kajona\Guestbook\System\GuestbookGuestbook;
use Kajona\Guestbook\System\GuestbookPost;
use Kajona\Guestbook\System\Messageproviders\MessageproviderGuestbook;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\SamplecontentContentHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\Rights;
use Kajona\System\System\SamplecontentInstallerInterface;
use Kajona\System\System\SystemModule;
use Kajona\System\System\UserGroup;


/**
 * Installer of the guestbook samplecontent
 *
 */
class InstallerSamplecontentGuestbook implements SamplecontentInstallerInterface
{

    /**
     * @var Database
     */
    private $objDB;
    private $strContentLanguage;

    /**
     * @inheritDoc
     */
    public function isInstalled()
    {
        return GuestbookGuestbook::getObjectCountFiltered() > 0;
    }

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     * @return string
     */
    public function install()
    {
        $strReturn = "";

        $strReturn .= "Creating new guestbook...\n";
        $objGuestbook = new GuestbookGuestbook();
        $objGuestbook->setStrGuestbookTitle("Guestbook");
        $objGuestbook->setIntGuestbookModerated(0);
        $objGuestbook->updateObjectToDb();
        $strGuestbookID = $objGuestbook->getSystemid();
        $strReturn .= "ID of new guestbook: ".$strGuestbookID."\n";


        $strReturn .= "Adding a sample post\n";
        $objPost = new GuestbookPost();
        $objPost->setStrGuestbookPostName("Kajona Team");
        $objPost->setStrGuestbookPostEmail("info@kajona.de");
        $objPost->setStrGuestbookPostPage("www.kajona.de");
        $objPost->setStrGuestbookPostText("This is the first guestbook post!");
        $objPost->updateObjectToDb($objGuestbook->getSystemid());


        $objMessageHandler = new MessagingMessagehandler();
        $arrGroups = array();
        $allGroups = UserGroup::getObjectListFiltered();
        foreach ($allGroups as $objOneGroup) {
            if (Rights::getInstance()->checkPermissionForGroup($objOneGroup->getSystemid(), Rights::$STR_RIGHT_EDIT, SystemModule::getModuleByName("guestbook")->getSystemid())) {
                $arrGroups[] = $objOneGroup;
            }
        }

        $objMessage = new MessagingMessage();
        $objMessage->setStrBody(Carrier::getInstance()->getObjLang()->getLang("new_post_mail", "guestbook").getLinkAdminHref("guestbook", "edit", "&systemid=".$objPost->getSystemid(), false));
        $objMessage->setObjMessageProvider(new MessageproviderGuestbook());
        $objMessageHandler->sendMessageObject($objMessage, $arrGroups);


        $strReturn .= "Creating new guestbook page...\n";
        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("guestbook", "Guestbook", PagesPage::getPageByName("samplepages")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objElement = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objElement->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Guestbook");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("Guestbook", $objBlocks);

        $objMediamanager = $objHelper->createPageElement("guestbook_guestbook", $objBlock);
        /** @var ElementGuestbookAdmin $objGbAdmin */
        $objGbAdmin = $objMediamanager->getConcreteAdminInstance();
        $objGbAdmin->setStrGuestbook($strGuestbookID);
        $objGbAdmin->setStrTemplate("guestbook.tpl");
        $objGbAdmin->setIntAmount(5);
        $objGbAdmin->updateForeignElement();

        return $strReturn;
    }

    public function setObjDb($objDb)
    {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage)
    {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule()
    {
        return "guestbook";
    }

}
