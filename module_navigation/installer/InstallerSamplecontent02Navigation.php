<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Navigation\Installer;

use Kajona\Navigation\System\NavigationPoint;
use Kajona\Navigation\System\NavigationTree;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Database;
use Kajona\System\System\SamplecontentInstallerInterface;

/**
 * Installer of the navigation samplecontent
 *
 */
class InstallerSamplecontent02Navigation implements SamplecontentInstallerInterface {

    /**
     * @var Database
     */
    private $objDB;
    private $strContentLanguage;

    private $strMasterID = "";

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     * @return string
     */
    public function install() {

        //search the master page
        $objMaster = PagesPage::getPageByName("master");
        if($objMaster != null)
            $this->strMasterID = $objMaster->getSystemid();

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();


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



        if($this->strMasterID != "") {
            $strReturn .= "Adding mainnavigation to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            if(PagesElement::getElement("navigation") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("mastermainnavi_navigation");
                $objPagelement->setStrName("mastermainnavi");
                $objPagelement->setStrElement("navigation");
                $objPagelement->updateObjectToDb($this->strMasterID);
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_navigation
                                SET navigation_id= ?,
                                    navigation_template = ?
                                WHERE content_id = ?";
                if($this->objDB->_pQuery($strQuery, array($strTreeId, "mainnavi.tpl", $strElementId)))
                    $strReturn .= "Navigation element created.\n";
                else
                    $strReturn .= "Error creating navigation element.\n";
            }

            $strReturn .= "Adding portalnavigation to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            if(PagesElement::getElement("navigation") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("masterportalnavi_navigation");
                $objPagelement->setStrName("masterportalnavi");
                $objPagelement->setStrElement("navigation");
                $objPagelement->updateObjectToDb($this->strMasterID);
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_navigation
                                SET navigation_id= ?,
                                    navigation_template = ?
                                WHERE content_id = ?";
                if($this->objDB->_pQuery($strQuery, array($strTreePortalId, "portalnavi.tpl", $strElementId)))
                    $strReturn .= "Navigation element created.\n";
                else
                    $strReturn .= "Error creating navigation element.\n";

            }

            $strReturn .= "Adding pathnavigation to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            if(PagesElement::getElement("navigation") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("masterpathnavi_navigation");
                $objPagelement->setStrName("masterpathnavi");
                $objPagelement->setStrElement("navigation");
                $objPagelement->updateObjectToDb($this->strMasterID);
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_navigation
                                SET navigation_id= ?,
                                    navigation_template = ?
                                WHERE content_id = ?";
                if($this->objDB->_pQuery($strQuery, array($strTreeId, "breadcrumbnavi.tpl", $strElementId)))
                    $strReturn .= "Navigation element created.\n";
                else
                    $strReturn .= "Error creating navigation element.\n";

            }
        }

        $strReturn .= "Creating simple sitemap...\n";
        $objPage = new PagesPage();
        $objPage->setStrName("sitemap");
        $objPage->setStrBrowsername("Sitemap");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb();
        $strSitemapId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strSitemapId."\n";
        $strReturn .= "Adding sitemap to new page\n";

        if(PagesElement::getElement("navigation") != null) {
            $objPagelement = new PagesPageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("navigation");
            $objPagelement->updateObjectToDb($strSitemapId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_navigation
                            SET navigation_id=?,
                                navigation_template = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array($strTreeId, "sitemap.tpl", $strElementId)))
                $strReturn .= "Sitemapelement created.\n";
            else
                $strReturn .= "Error creating sitemapelement.\n";

        }

        $strReturn .= "Adding headline-element to new page\n";
        if(PagesElement::getElement("row") != null) {
            $objPagelement = new PagesPageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strSitemapId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = ?
                          WHERE content_id = ? ";
            if($this->objDB->_pQuery($strQuery, array("Sitemap", $strElementId)))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";
        }

        $strReturn .= "Creating navigation points\n";
        $objNaviPoint = new NavigationPoint();
        $objNaviPoint->setStrName("Sitemap");
        $objNaviPoint->setStrPageI("sitemap");
        $objNaviPoint->updateObjectToDb($strTreePortalId);
        $strReturn .= "ID of new navigation point ".$objNaviPoint->getSystemid().".\n";

        $objNaviPoint = new NavigationPoint();
        if($this->strContentLanguage == "de")
            $objNaviPoint->setStrName("Impressum");
        else
            $objNaviPoint->setStrName("Imprint");
        $objNaviPoint->setStrPageI("imprint");
        $objNaviPoint->updateObjectToDb($strTreePortalId);
        $strReturn .= "ID of new navigation point ".$objNaviPoint->getSystemid().".\n";


        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "navigation";
    }
}
