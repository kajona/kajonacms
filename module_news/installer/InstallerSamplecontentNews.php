<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\News\Installer;

use Kajona\News\Admin\Elements\ElementNewsAdmin;
use Kajona\News\System\NewsCategory;
use Kajona\News\System\NewsFeed;
use Kajona\News\System\NewsNews;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\SamplecontentContentHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\Rights;
use Kajona\System\System\SamplecontentInstallerInterface;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;


/**
 * Installer of the news samplecontenht
 *
 */
class InstallerSamplecontentNews implements SamplecontentInstallerInterface
{
    /**
     * @var Database
     */
    private $objDB;
    private $strContentLanguage;

    private $strIndexID = "";
    private $strMasterID = "";

    /**
     * @inheritDoc
     */
    public function isInstalled()
    {
        return NewsNews::getObjectCountFiltered() > 0;
    }

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install()
    {
        $strReturn = "";


        //fetch navifolder-id
        $strSystemFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach ($arrFolder as $objOneFolder) {

            if ($objOneFolder->getStrName() == "_system") {
                $strSystemFolderId = $objOneFolder->getSystemid();
            }
        }

        //search the index page
        $objIndex = PagesPage::getPageByName("index");
        $objMaster = PagesPage::getPageByName("master");
        if ($objIndex != null) {
            $this->strIndexID = $objIndex->getSystemid();
        }

        if ($objMaster != null) {
            $this->strMasterID = $objMaster->getSystemid();
        }

        $strReturn .= "Creating new category...\n";
        $objNewsCategory = new NewsCategory();
        $objNewsCategory->setStrTitle("TOP-News");
        $objNewsCategory->updateObjectToDb();
        $strCategoryID = $objNewsCategory->getSystemid();
        $strReturn .= "ID of new category: ".$strCategoryID."\n";
        $strReturn .= "Creating news\n";
        $objNews = new NewsNews();

        if ($this->strContentLanguage == "de") {
            $objNews->setStrTitle("Erfolgreich installiert");
            $objNews->setStrText("Eine weitere Installation von Kajona V3 war erfolgreich. Für weitere Infomationen zu Kajona besuchen Sie www.kajona.de.");
            $objNews->setStrIntro("Kajona wurde erfolgreich installiert...");
        } else {
            $objNews->setStrTitle("Installation successful");
            $objNews->setStrText("Another installation of Kajona was successful. For further information, support or proposals, please visit our website: www.kajona.de");
            $objNews->setStrIntro("Kajona installed successfully...");
        }

        $objNews->setObjDateStart(new \Kajona\System\System\Date());
        $objNews->setArrCats(array($strCategoryID));
        $objNews->updateObjectToDb();
        $strNewsId = $objNews->getSystemid();
        $strReturn .= "ID of new news: ".$strNewsId."\n";


        $strReturn .= "Creating news\n";
        $objNews = new NewsNews();

        $objNews->setStrTitle("Sed non enim est");
        $objNews->setStrText("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non enim est, id hendrerit metus. Sed tempor quam sed ante viverra porta. Quisque sagittis egestas tortor, in euismod sapien iaculis at. Nullam vitae nunc tortor. Mauris justo lectus, bibendum et rutrum id, fringilla eget ipsum. Nullam volutpat sodales mollis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Duis tempor ante eget justo blandit imperdiet. Praesent ut risus tempus metus sagittis fermentum eget eu elit. Mauris consequat ornare massa, a rhoncus enim sodales auctor. Duis lacinia dignissim eros vel mollis. Etiam metus tortor, pellentesque eu ultricies sit amet, elementum et dolor. Proin tincidunt nunc id magna volutpat lobortis. Vivamus metus quam, accumsan eget vestibulum vel, rutrum sit amet mauris. Phasellus lectus leo, vulputate eget molestie et, consectetur nec urna. ");
        $objNews->setStrIntro("Quisque sagittis egestas tortor");

        $objNews->setObjDateStart(new \Kajona\System\System\Date());
        $objNews->setArrCats(array($strCategoryID));
        $objNews->updateObjectToDb();
        $strNewsId = $objNews->getSystemid();
        $strReturn .= "ID of new news: ".$strNewsId."\n";


        $strReturn .= "Adding news element to the master-page...\n";
        if ($this->strMasterID != "") {

            if (PagesElement::getElement("news") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("mastertopnews_news");
                $objPagelement->setStrName("mastertopnews");
                $objPagelement->setStrElement("news");
                $objPagelement->updateObjectToDb($this->strMasterID);
                /** @var ElementNewsAdmin $objAdminInstance */
                $objAdminInstance = $objPagelement->getConcreteAdminInstance();
                $objAdminInstance->setStrCategory($strCategoryID);
                $objAdminInstance->setIntView(0);
                $objAdminInstance->setIntListMode(0);
                $objAdminInstance->setIntOrder(0);
                $objAdminInstance->setIntAmount(10);
                $objAdminInstance->setStrDetailspage("news");
                $objAdminInstance->setStrTemplate("demo_teaserlist.tpl");
                $objAdminInstance->updateForeignElement();
            }
        }


        $objHelper = new SamplecontentContentHelper();

        $strReturn .= "Creating news-list-page\n";
        $objPage = $objHelper->createPage("news", "News", PagesPage::getPageByName("samplepages")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("News");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("News", $objBlocks);

        $objElement = $objHelper->createPageElement("news_news", $objBlock);
        /** @var ElementNewsAdmin $objAdminInstance */
        $objAdminInstance = $objElement->getConcreteAdminInstance();
        $objAdminInstance->setStrCategory($strCategoryID);
        $objAdminInstance->setIntListMode(0);
        $objAdminInstance->setIntOrder(0);
        $objAdminInstance->setIntAmount(20);
        $objAdminInstance->setStrTemplate("demo.tpl");
        $objAdminInstance->updateForeignElement();


        $strReturn .= "Creating news-feed\n";
        $objNewsFeed = new NewsFeed();
        $objNewsFeed->setStrTitle("kajona news");
        $objNewsFeed->setStrUrlTitle("kajona_news");
        $objNewsFeed->setStrLink("https://www.kajona.de");

        if ($this->strContentLanguage == "de") {
            $objNewsFeed->setStrDesc("Dies ist ein Kajona demo news feed");
        } else {
            $objNewsFeed->setStrDesc("This is a Kajona demo news feed");
        }

        $objNewsFeed->setStrPage("news");
        $objNewsFeed->setStrCat("0");
        $objNewsFeed->setIntAmount(25);
        $objNewsFeed->updateObjectToDb();
        $strNewsFeedId = $objNewsFeed->getSystemid();
        $strReturn .= "ID of new news-feed: ".$strNewsFeedId."\n";

        $strReturn .= "Adding rating permissions...\n";
        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), SystemModule::getModuleByName("news")->getSystemid(), Rights::$STR_RIGHT_RIGHT3);

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
        return "news";
    }

}
