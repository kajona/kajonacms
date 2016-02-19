<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\Installer;

use Kajona\Navigation\System\NavigationPoint;
use Kajona\Navigation\System\NavigationTree;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Database;
use Kajona\System\System\Exception;
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
     * Does the hard work: installs the module and registers needed constants

     */
    public function install() {
        $strReturn = "";

        //fetch navifolder-id

        //navigations installed?
        $objModule = null;
        try {
            $objModule = SystemModule::getModuleByName("pages", true);
        }
        catch(Exception $objException) {
            $objModule = null;
        }
        if($objModule != null) {

            $strSystemFolderId = "";
            $arrFolder = PagesFolder::getFolderList();
            foreach($arrFolder as $objOneFolder) {
                if($objOneFolder->getStrName() == "_system")
                    $strSystemFolderId = $objOneFolder->getSystemid();
            }

            //search the master page
            $objMaster = PagesPage::getPageByName("master");
            if($objMaster != null)
                $this->strMasterID = $objMaster->getSystemid();


            $strReturn .= "Adding search-element to master page\n";

            if($this->strMasterID != "" && PagesElement::getElement("search") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("mastersearch_search");
                $objPagelement->setStrName("mastersearch");
                $objPagelement->setStrElement("search");
                $objPagelement->updateObjectToDb($this->strMasterID);
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_search
                                        SET search_template = ?,
                                            search_amount = ?,
                                            search_page = ?
                                        WHERE content_id = ?";
                if($this->objDB->_pQuery($strQuery, array("search_ajax_small.tpl", 0, "search", $strElementId)))
                    $strReturn .= "Search element created.\n";
                else
                    $strReturn .= "Error creating search element.\n";
            }

            $strReturn .= "Creating search page\n";
            $objPage = new PagesPage();
            $objPage->setStrName("search");

            if($this->strContentLanguage == "de")
                $objPage->setStrBrowsername("Suchergebnisse");
            else
                $objPage->setStrBrowsername("Search results");

            $objPage->setStrTemplate("standard.tpl");
            $objPage->updateObjectToDb($strSystemFolderId);
            $strSearchresultsId = $objPage->getSystemid();
            $strReturn .= "ID of new page: ".$strSearchresultsId."\n";
            $strReturn .= "Adding search-element to new page\n";

            if(PagesElement::getElement("search") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
                $objPagelement->setStrName("special");
                $objPagelement->setStrElement("search");
                $objPagelement->updateObjectToDb($strSearchresultsId);
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_search
                                        SET search_template = ?,
                                            search_amount = ?,
                                            search_page = ?
                                        WHERE content_id = ?";
                if($this->objDB->_pQuery($strQuery, array("search_ajax.tpl", 0, "", $strElementId)))
                    $strReturn .= "Search element created.\n";
                else
                    $strReturn .= "Error creating search element.\n";
            }


            $strReturn .= "Adding headline-element to new page\n";
            if(PagesElement::getElement("row") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("headline_row");
                $objPagelement->setStrName("headline");
                $objPagelement->setStrElement("row");
                $objPagelement->updateObjectToDb($strSearchresultsId);
                $strElementId = $objPagelement->getSystemid();

                $arrParams = array();
                if($this->strContentLanguage == "de") {
                    $arrParams = array("Suchergebnisse", $strElementId);
                }
                else {
                    $arrParams = array("Search results", $strElementId);
                }

                $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                        SET paragraph_title = ?
                                        WHERE content_id = ?";

                if($this->objDB->_pQuery($strQuery, $arrParams))
                    $strReturn .= "Headline element created.\n";
                else
                    $strReturn .= "Error creating headline element.\n";
            }
        }

        $strReturn .= "Creating navigation point.\n";

        //navigations installed?
        $objModule = null;
        try {
            $objModule = SystemModule::getModuleByName("navigation", true);
        }
        catch(Exception $objException) {
            $objModule = null;
        }
        if($objModule != null) {

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
