<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\Installer;

use Kajona\Navigation\System\NavigationPoint;
use Kajona\Navigation\System\NavigationTree;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\SamplecontentContentHelper;
use Kajona\Search\Admin\Elements\ElementSearchAdmin;
use Kajona\System\System\Database;
use Kajona\System\System\SamplecontentInstallerInterface;
use Kajona\System\System\SystemModule;


/**
 * Interface of the search samplecontent
 *
 */
class InstallerSamplecontentSearch implements SamplecontentInstallerInterface
{
    /**
     * @var Database
     */
    private $objDB;
    private $strContentLanguage;

    private $strMasterID = "";


    /**
     * @inheritDoc
     */
    public function isInstalled()
    {
        return SystemModule::getModuleByName("pages") == null || PagesPage::getPageByName("search") != null;
    }

    /**
     * Does the hard work: installs the module and registers needed constants
     */
    public function install() {
        $strReturn = "";

        //pages installed?
        if(SystemModule::getModuleByName("pages", true) != null) {

            //search the master page
            $objMaster = PagesPage::getPageByName("master");
            if ($objMaster != null) {
                $this->strMasterID = $objMaster->getSystemid();
            }


            $strReturn .= "Adding search-element to master page\n";

            if ($this->strMasterID != "" && PagesElement::getElement("search") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("mastersearch_search");
                $objPagelement->setStrName("mastersearch");
                $objPagelement->setStrElement("search");
                $objPagelement->updateObjectToDb($this->strMasterID);
                /** @var ElementSearchAdmin $objSearchAdmin */
                $objSearchAdmin = $objPagelement->getConcreteAdminInstance();
                $objSearchAdmin->setStrTemplate("search_ajax_small.tpl");
                $objSearchAdmin->setIntAmount(0);
                $objSearchAdmin->setStrPage("search");
                $objSearchAdmin->updateForeignElement();
            }

            $strReturn .= "Creating search page\n";
            $objHelper = new SamplecontentContentHelper();

            $objPage = $objHelper->createPage("search", $this->strContentLanguage == "de" ? " Suche" : "Search", PagesPage::getPageByName("index")->getSystemid());
            $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

            $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
            $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

            $strReturn .= "Adding headline-element to new page\n";
            $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
            /** @var ElementPlaintextAdmin $objHeadlineAdmin */
            $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
            $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? " Suche" : "Search");
            $objHeadlineAdmin->updateForeignElement();


            $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
            $objBlock = $objHelper->createBlockElement("Search", $objBlocks);

            $objSearchElement = $objHelper->createPageElement("search_search", $objBlock);
            /** @var ElementSearchAdmin $objSearchAdminElement */
            $objSearchAdminElement = $objSearchElement->getConcreteAdminInstance();
            $objSearchAdminElement->setStrTemplate("search_ajax.tpl");
            $objSearchAdminElement->setIntAmount(10);
            $objSearchAdminElement->updateForeignElement();

        }

        $strReturn .= "Creating navigation point.\n";

        //navigations installed?
        if(SystemModule::getModuleByName("navigation", true) != null) {

            $objNavi = NavigationTree::getNavigationByName("portalnavigation");
            $strTreeId = $objNavi->getSystemid();

            $objNaviPoint = new NavigationPoint();
            if($this->strContentLanguage == "de") {
                $objNaviPoint->setStrName("Suche");
            }
            else {
                $objNaviPoint->setStrName("Search");
            }

            $objNaviPoint->setStrPageI("search");
            $objNaviPoint->updateObjectToDb($strTreeId);
            $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
        }

        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "search";
    }
}
