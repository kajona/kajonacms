<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Votings\Installer;

use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Samplecontent\System\SamplecontentContentHelper;
use Kajona\System\System\Database;
use Kajona\System\System\SamplecontentInstallerInterface;
use Kajona\Votings\Admin\Elements\ElementVotingsAdmin;
use Kajona\Votings\System\VotingsAnswer;
use Kajona\Votings\System\VotingsVoting;

/**
 *
 */
class InstallerSamplecontentVotings implements SamplecontentInstallerInterface
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
        return VotingsVoting::getObjectCount() > 0;
    }

    public function install()
    {
        $strReturn = "";

        $strReturn .= "Creating voting\n";

        $objVoting = new VotingsVoting();
        if ($this->strContentLanguage == "de") {
            $objVoting->setStrTitle("Wie gefällt Ihnen unsere neue Webseite?");
        }
        else {
            $objVoting->setStrTitle("How do you like our new website?");
        }

        $objVoting->updateObjectToDb();
        $objAnswer1 = new VotingsAnswer();
        $objAnswer2 = new VotingsAnswer();
        $objAnswer3 = new VotingsAnswer();
        if ($this->strContentLanguage == "de") {
            $objAnswer1->setStrText("Gefällt mir sehr gut!");
            $objAnswer2->setStrText("Ausbaufähig...");
            $objAnswer3->setStrText("Brennt im Kühlschrank immer Licht?");
        }
        else {
            $objAnswer1->setStrText("I like it!");
            $objAnswer2->setStrText("Well, work on it");
            $objAnswer3->setStrText("Sunglasses at night");
        }

        $objAnswer1->updateObjectToDb($objVoting->getSystemid());
        $objAnswer2->updateObjectToDb($objVoting->getSystemid());
        $objAnswer3->updateObjectToDb($objVoting->getSystemid());


        $strReturn .= "Creating voting-page\n";

        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("votings", "Votings", PagesPage::getPageByName("samplepages")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Votings");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("Votings", $objBlocks);

        $objVotingElement = $objHelper->createPageElement("votings_votings", $objBlock);
        /** @var ElementVotingsAdmin $objVotingsAdmin */
        $objVotingsAdmin = $objVotingElement->getConcreteAdminInstance();
        $objVotingsAdmin->setStrChar1($objVoting->getSystemid());
        $objVotingsAdmin->setStrChar2("votings.tpl");
        $objVotingsAdmin->setIntInt1(0);
        $objVotingsAdmin->updateForeignElement();

        $objBlock = $objHelper->createBlockElement("Votings", $objBlocks);

        $objVotingElement = $objHelper->createPageElement("votings_votings", $objBlock);
        /** @var ElementVotingsAdmin $objVotingsAdmin */
        $objVotingsAdmin = $objVotingElement->getConcreteAdminInstance();
        $objVotingsAdmin->setStrChar1($objVoting->getSystemid());
        $objVotingsAdmin->setStrChar2("votings.tpl");
        $objVotingsAdmin->setIntInt1(1);
        $objVotingsAdmin->updateForeignElement();

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
        return "faqs";
    }

}
