<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Eventmanager\Installer;

use Kajona\Eventmanager\Admin\Elements\ElementEventmanagerAdmin;
use Kajona\Eventmanager\System\EventmanagerEvent;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesFolder;
use Kajona\Samplecontent\System\SamplecontentContentHelper;
use Kajona\System\System\SamplecontentInstallerInterface;


/**
 * Installer of the eventmanagers samplecontenht
 *
 */
class InstallerSamplecontentEventmanager implements SamplecontentInstallerInterface
{

    /**
     * @var \Kajona\System\System\Database
     */
    private $objDB;
    private $strContentLanguage;

    public function install()
    {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach ($arrFolder as $objOneFolder) {
            if ($objOneFolder->getStrName() == "mainnavigation") {
                $strNaviFolderId = $objOneFolder->getSystemid();
            }
        }


        $strReturn .= "Creating event\n";
        $objEvent = new EventmanagerEvent();

        $objEvent->setObjStartDate(new \Kajona\System\System\Date());
        $objEvent->setObjEndDate(new \Kajona\System\System\Date(time() + 3600));
        $objEvent->setIntRegistrationRequired(1);
        $objEvent->setIntEventStatus(2);

        if ($this->strContentLanguage == "de") {
            $objEvent->setStrTitle("Start der neuen Webseite mit Kajona");
            $objEvent->setStrDescription("Die neue Webseite ist online!<br />Als Basis dafür kommt das freie Open Source Content Management System <a href=\"http://www.kajona.de\">Kajona</a>, zum Einsatz.");
        }
        else {
            $objEvent->setStrTitle("Launch of the new website");
            $objEvent->setStrDescription("The new website is available!<br />The page is based on <a href=\"http://www.kajona.de\">Kajona</a>, a free open source content management system.");
        }

        $strReturn .= "Saving event...\n";
        $objEvent->updateObjectToDb();


        $strReturn .= "Creating events-page\n";
        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("eventmanager", "Eventmanager", $strNaviFolderId);
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Events");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("Eventmanager", $objBlocks);

        $objEvmgr = $objHelper->createPageElement("events_eventmanager", $objBlock);
        /** @var ElementEventmanagerAdmin $objEvmgrAdmin */
        $objEvmgrAdmin = $objEvmgr->getConcreteAdminInstance();
        $objEvmgrAdmin->setStrChar1("eventmanager.tpl");
        $objEvmgrAdmin->setIntInt1(0);
        $objEvmgrAdmin->setIntInt2(0);
        $objEvmgrAdmin->updateForeignElement();
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
        return "eventmanager";
    }

}
