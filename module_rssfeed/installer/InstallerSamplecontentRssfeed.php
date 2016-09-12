<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Rssfeed\Installer;

use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\SamplecontentContentHelper;
use Kajona\Rssfeed\Admin\Elements\ElementRssfeedAdmin;
use Kajona\System\System\Database;
use Kajona\System\System\SamplecontentInstallerInterface;


/**
 * Installer of the rssfeed samplecontent
 *
 * @package element_rssfeed
 */
class InstallerSamplecontentRssfeed implements SamplecontentInstallerInterface
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
        return PagesPage::getPageByName("rssfeed") != null;
    }

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install()
    {
        $strReturn = "";

        $strReturn .= "Creating new page rssfeed...\n";
        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("rssfeed", "Rssfeed", PagesPage::getPageByName("samplepages")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("RSS Feed");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("Feed", $objBlocks);

        $objElement = $objHelper->createPageElement("feed_rssfeed", $objBlock);
        /** @var ElementRssfeedAdmin $objRssAdminEl */
        $objRssAdminEl = $objElement->getConcreteAdminInstance();
        $objRssAdminEl->setStrChar1("rssfeed.tpl");
        $objRssAdminEl->setIntInt1(10);
        if ($this->strContentLanguage == "de") {
            $objRssAdminEl->setStrChar2("http://www.kajona.de/kajona_news.rss");
        }
        else {
            $objRssAdminEl->setStrChar2("http://www.kajona.de/kajona_news_en.rss");
        }
        $objRssAdminEl->updateForeignElement();

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
        return "pages";
    }

}
