<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Navigation\Installer;

use Kajona\Navigation\Admin\Elements\ElementNavigationAdmin;
use Kajona\Navigation\System\NavigationPoint;
use Kajona\Navigation\System\NavigationTree;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\SamplecontentContentHelper;
use Kajona\System\System\Database;
use Kajona\System\System\SamplecontentInstallerInterface;

/**
 * Installer of the navigation samplecontent
 *
 */
class InstallerSamplecontent02Navigation implements SamplecontentInstallerInterface
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
        return NavigationTree::getObjectCountFiltered() > 0;
    }

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     * @return string
     */
    public function install()
    {

        //search the master page
        $objMaster = PagesPage::getPageByName("master");
        if ($objMaster != null) {
            $this->strMasterID = $objMaster->getSystemid();
        }

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach ($arrFolder as $objOneFolder) {
            if ($objOneFolder->getStrName() == "mainnavigation") {
                $strNaviFolderId = $objOneFolder->getSystemid();
            }
        }


        $strReturn = "";
        $strReturn .= "Creating new mainnavigation-tree\n";
        $objNaviTree = new NavigationTree();
        $objNaviTree->setStrName("mainnavigation");
        $objNaviTree->setStrFolderId($strNaviFolderId);
        $objNaviTree->updateObjectToDb();
        $strTreeId = $objNaviTree->getSystemid();
        $strReturn .= "ID of new navigation-tree: ".$strTreeId."\n";


        $strReturn .= "Creating new portalnavigation-tree\n";
        $objNaviTree = new NavigationTree();
        $objNaviTree->setStrName("portalnavigation");
        $objNaviTree->updateObjectToDb();
        $strTreePortalId = $objNaviTree->getSystemid();
        $strReturn .= "ID of new navigation-tree: ".$strTreePortalId."\n";

        $strReturn .= "Creating navigation points\n";
        $objNaviPoint = new NavigationPoint();
        $objNaviPoint->setStrName("Home");
        $objNaviPoint->setStrPageI("index");
        $objNaviPoint->updateObjectToDb($strTreePortalId);


        if ($this->strMasterID != "") {
            $strReturn .= "Adding mainnavigation to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            if (PagesElement::getElement("navigation") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("mastermainnavi_navigation");
                $objPagelement->setStrName("mastermainnavi");
                $objPagelement->setStrElement("navigation");
                $objPagelement->updateObjectToDb($this->strMasterID);

                /** @var ElementNavigationAdmin $objElement */
                $objElement = $objPagelement->getConcreteAdminInstance();
                $objElement->setStrRepo($strTreeId);
                $objElement->setStrTemplate("mainnavi.tpl");
                $objElement->updateForeignElement();
            }

            $strReturn .= "Adding portalnavigation to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            if (PagesElement::getElement("navigation") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("masterportalnavi_navigation");
                $objPagelement->setStrName("masterportalnavi");
                $objPagelement->setStrElement("navigation");
                $objPagelement->updateObjectToDb($this->strMasterID);

                /** @var ElementNavigationAdmin $objElement */
                $objElement = $objPagelement->getConcreteAdminInstance();
                $objElement->setStrRepo($strTreePortalId);
                $objElement->setStrTemplate("portalnavi.tpl");
                $objElement->updateForeignElement();

            }

            $strReturn .= "Adding pathnavigation to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            if (PagesElement::getElement("navigation") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("masterpathnavi_navigation");
                $objPagelement->setStrName("masterpathnavi");
                $objPagelement->setStrElement("navigation");
                $objPagelement->updateObjectToDb($this->strMasterID);

                /** @var ElementNavigationAdmin $objElement */
                $objElement = $objPagelement->getConcreteAdminInstance();
                $objElement->setStrRepo($strTreeId);
                $objElement->setStrTemplate("breadcrumbnavi.tpl");
                $objElement->updateForeignElement();

            }
        }

        $strReturn .= "Creating simple sitemap...\n";

        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("sitemap", "Sitemap", PagesPage::getPageByName("index")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Sitemap");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("Sitemap", $objBlocks);

        $objNavigation = $objHelper->createPageElement("sitemap_navigation", $objBlock);
        /** @var ElementNavigationAdmin $objElement */
        $objElement = $objNavigation->getConcreteAdminInstance();
        $objElement->setStrRepo($strTreeId);
        $objElement->setStrTemplate("sitemap.tpl");
        $objElement->updateForeignElement();


        $strReturn .= "Creating navigation points\n";
        $objNaviPoint = new NavigationPoint();
        $objNaviPoint->setStrName("Sitemap");
        $objNaviPoint->setStrPageI("sitemap");
        $objNaviPoint->updateObjectToDb($strTreePortalId);
        $strReturn .= "ID of new navigation point ".$objNaviPoint->getSystemid().".\n";

        $objNaviPoint = new NavigationPoint();
        if ($this->strContentLanguage == "de") {
            $objNaviPoint->setStrName("Impressum");
        }
        else {
            $objNaviPoint->setStrName("Imprint");
        }
        $objNaviPoint->setStrPageI("imprint");
        $objNaviPoint->updateObjectToDb($strTreePortalId);
        $strReturn .= "ID of new navigation point ".$objNaviPoint->getSystemid().".\n";


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
        return "navigation";
    }
}
