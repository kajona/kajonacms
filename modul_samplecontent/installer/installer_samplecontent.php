<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_samplecontent.php																			*
* 	Installer of the samplecontent module															    *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");

/**
 * Class providing an installer for the samplecontent.
 * Samplecontent is not installed as a module, it just creates a few default entries
 * for other modules and installes a few sample-templates
 *
 * @package modul_samplecontent
 */
class class_installer_samplecontent extends class_installer_base implements interface_installer {


    private $strContentLanguage;

    private $strMasterID = "";
    private $strIndexID = "";

	public function __construct() {
		$arrModule["version"] 		  = "3.0.2";
		$arrModule["name"] 			  = "samplecontent";
		$arrModule["class_admin"]  	  = "";
		$arrModule["file_admin"] 	  = "";
		$arrModule["class_portal"] 	  = "";
		$arrModule["file_portal"] 	  = "";
		$arrModule["name_lang"] 	  = "Module Samplecontent";
		$arrModule["moduleId"] 		  = _samplecontent_modul_id_;

		$arrModule["tabellen"][]      = _dbprefix_."languages";
		parent::__construct($arrModule);

		//set the correct language
		if($this->objSession->getAdminLanguage() == "en")
		    $this->strContentLanguage = "en";
		else
		    $this->strContentLanguage = "de";
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.2";
	}

	public function hasPostInstalls() {
        return false;
	}


   public function install() {
        $strReturn = "";
        include_once(_systempath_."/class_modul_system_module.php");
        $strPageId = "";

		$strReturn = "Installing ".$this->arrModule["name_lang"]."...\n";

		//Register the module
        $strReturn .= "\nRegistering module\n";
        $strSystemID = $this->registerModule($this->arrModule["name"], _samplecontent_modul_id_, "", "", $this->arrModule["version"] , false);

        //Module-Pages
        $strReturn .= "Module Pages...\n";
        $objModulePages = class_modul_system_module::getModuleByName("pages");
        if($objModulePages == null) {
            $strReturn .= "\t... not installed!\n";
        }
        else {
            $strReturn .= "\t... installed.\n";
            $strReturn .= $this->installModulePages();
        }

        //Module-News
        $strReturn .= "\nModule News...\n";
        $objModulePages = class_modul_system_module::getModuleByName("news");
        if($objModulePages == null) {
            $strReturn .= "\t... not installed!\n";
        }
        else {
            $strReturn .= "\t... installed.\n";
            $strReturn .= $this->installModuleNews();

        }

        //Module-Navigation
        $strReturn .= "\nModule Navigation...\n";
        $objModulePages = class_modul_system_module::getModuleByName("navigation");
        if($objModulePages == null) {
            $strReturn .= "\t... not installed!\n";
        }
        else {
            $strReturn .= "\t... installed.\n";
            $strReturn .= $this->installModuleNavigations();
        }

        //Module-Search
        $strReturn .= "\nModule Search...\n";
        $objModulePages = class_modul_system_module::getModuleByName("search");
        if($objModulePages == null) {
            $strReturn .= "\t... not installed!\n";
        }
        else {
            $strReturn .= "\t... installed.\n";
            $strReturn .= $this->installModuleSearch();

        }

        //Module-Filemanager
        $strReturn .= "\nModule Filemanager...\n";
        $objModulePages = class_modul_system_module::getModuleByName("filemanager");
        if($objModulePages == null) {
            $strReturn .= "\t... not installed!\n";
        }
        else {
            $strReturn .= "\t... installed.\n";
            $strReturn .= $this->installModuleFilemanager();
        }

        //Module-Languages
        $strReturn .= "\nModule Guestbook...\n";
        $objModulePages = class_modul_system_module::getModuleByName("guestbook");
        if($objModulePages == null) {
            $strReturn .= "\t... not installed!\n";
        }
        else {
            $strReturn .= "\t... installed.\n";
            $strReturn .= $this->installModuleGuestbook();

        }


        //Module-Languages
        $strReturn .= "\nModule Languages...\n";
        $objModulePages = class_modul_system_module::getModuleByName("languages");
        if($objModulePages == null) {
            $strReturn .= "\t... not installed!\n";
        }
        else {
            $strReturn .= "\t... installed.\n";
            $strReturn .= $this->installModuleLanguages();

        }



		return $strReturn;
	}

	public function postInstall() {
	}


	public function update() {
	    $strReturn = "";
        //check the version we have and to what version to update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.0") {
            $strReturn .= $this->update_300_301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.1") {
            $strReturn .= $this->update_301_302();
        }
	}

	private function update_300_301() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.0 to 3.0.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.0.1");

        return $strReturn;
	}

	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("samplecontent", "3.0.2");

        return $strReturn;
	}

	private function installModulePages() {
        $strReturn = "";

        $strReturn .= "Creating index-site...\n";
        include_once(_systempath_."/class_modul_pages_page.php");
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("index");

        if($this->strContentLanguage == "de") {
            $objPage->setStrBrowsername("Willkommen");
        }
        else {
            $objPage->setStrBrowsername("Welcome");
        }

        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->saveObjectToDb();
        $this->strIndexID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$this->strIndexID."\n";
        include_once(_systempath_."/class_modul_pages_pageelement.php");
        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->saveObjectToDb($this->strIndexID, "headline_row", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = 'Willkommen'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = 'Welcome'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
        }



        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Adding paragraph-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->saveObjectToDb($this->strIndexID, "text_paragraph", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = 'Herzlichen Glückwunsch!',
                                absatz_inhalt ='Diese Installation von Kajona war erfolgreich. Wir wünschen viel Spaß mit Kajona V3.<br />
                                                Für weitere Informationen und Support besuchen Sie unsere Webseite: <a href=\"http://www.kajona.de\">www.kajona.de</a>'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = 'Congratulations!',
                                    absatz_inhalt ='This installation of Kajona was successful. Have fun using Kajona!<br />
                                                     For further information, support or proposals, please visit our website: <a href=\"http://www.kajona.de\">www.kajona.de</a>'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
        }


        if($this->objDB->_query($strQuery))
            $strReturn .= "Paragraph element created.\n";
        else
            $strReturn .= "Error creating paragraph element.\n";

        $strReturn .= "Adding image-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("picture1_image");
        $objPagelement->setStrName("picture1");
        $objPagelement->setStrElement("image");
        $objPagelement->saveObjectToDb($this->strIndexID, "picture1_image", _dbprefix_."element_bild", "first");
        $strElementId = $objPagelement->getSystemid();
         $strQuery = "UPDATE "._dbprefix_."element_bild
                            SET bild_bild = '/portal/pics/kajona/login_logo.gif'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "Image element created.\n";
            else
                $strReturn .= "Error creating image element.\n";

        $strReturn .= "Creating system folder...\n";
        include_once(_systempath_."/class_modul_pages_folder.php");
        $objFolder = new class_modul_pages_folder();
        $objFolder->setStrName("_system");
        $objFolder->saveObjectToDb("0");
        $strFolderID = $objFolder->getSystemid();
        $strReturn .= "ID of new folder: ".$strFolderID."\n";
        $strReturn .= "Creating master-page\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("master");
        $objPage->setStrTemplate("master.tpl");
        $objPage->saveObjectToDb($strFolderID);
        $this->strMasterID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$this->strMasterID."\n";


        $strReturn .= "Creating error-site...\n";
        include_once(_systempath_."/class_modul_pages_page.php");
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("error");

        if($this->strContentLanguage == "de")
            $objPage->setStrBrowsername("Fehler");
        else
            $objPage->setStrBrowsername("Error");


        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->saveObjectToDb($strFolderID);
        $strErrorPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strErrorPageId."\n";

        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->saveObjectToDb($strErrorPageId, "headline_row", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = 'Fehler'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = 'Error'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Adding paragraph-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->saveObjectToDb($strErrorPageId, "text_paragraph", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();


        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                        SET absatz_titel = 'Ein Fehler ist aufgetreten',
                           absatz_inhalt ='Während Ihre Anfrage ist leider ein Fehler aufgetreten.<br />
                                           Bitte versuchen Sie die letzte Aktion erneut.'
                      WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
             $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = 'An error occured',
                                    absatz_inhalt ='Maybe the requested page doesn\'t exist anymore.<br />
                                                    Please try again later.'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Paragraph element created.\n";
        else
            $strReturn .= "Error creating paragraph element.\n";

        return $strReturn;
	}


	private function installModuleNews() {
        $strReturn = "";
        $strNewsdetailsId = "";

        $strReturn .= "Creating new category...\n";
            include_once(_systempath_."/class_modul_news_category.php");
            $objNewsCategory = new class_modul_news_category();
            $objNewsCategory->setStrTitle("TOP-News");
            $objNewsCategory->saveObjectToDb();
            $strCategoryID = $objNewsCategory->getSystemid();
            $strReturn .= "ID of new category: ".$strCategoryID."\n";
            $strReturn .= "Creating news\n";
            include_once(_systempath_."/class_modul_news_news.php");
            $objNews = new class_modul_news_news();

            if($this->strContentLanguage == "de") {
                $objNews->setStrTitle("Kajona wurde erfolgreich installiert");
                $objNews->setStrNewstext("Eine weitere Installation von Kajona V3 war erfolgreich. Für weitere Infomationen zu Kajona besuchen Sie www.kajona.de.");
                $objNews->setStrIntro("Kajona wurde erfolgreich installiert...");
            }
            else {
                $objNews->setStrTitle("Installation was successful");
                $objNews->setStrNewstext("Another installation of Kajona was successful. For further information, support or proposals, please visit our website: www.kajona.de");
                $objNews->setStrIntro("Kajona installed successfully...");
            }

            $objNews->setIntDateStart(time());
            $objNews->setArrCats(array($strCategoryID => 1));
            $objNews->saveObjectToDb();
            $objNews->updateObjectToDb(false);
            $strNewsId = $objNews->getSystemid();
            $strReturn .= "ID of new news: ".$strNewsId."\n";
            $strReturn .= "Adding news element to the index-page...\n";
            if($this->strIndexID != "") {
                include_once(_systempath_."/class_modul_pages_pageelement.php");
                $objPagelement = new class_modul_pages_pageelement();
                $objPagelement->setStrPlaceholder("news_news");
                $objPagelement->setStrName("news");
                $objPagelement->setStrElement("news");
                $objPagelement->saveObjectToDb($this->strIndexID, "news_news", _dbprefix_."element_news", "first");
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_news
                                SET news_category='".dbsafeString($strCategoryID)."',
                                    news_view = '0',
                                    news_mode = '0',
                                    news_detailspage = 'newsdetails',
                                    news_template = 'demo.tpl'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
                if($this->objDB->_query($strQuery))
                    $strReturn .= "Newselement created.\n";
                else
                    $strReturn .= "Error creating newselement.\n";
            }
            $strReturn .= "Creating news-detail\n";
            $objPage = new class_modul_pages_page();
            $objPage->setStrName("newsdetails");
            $objPage->setStrBrowsername("News");
            $objPage->setStrTemplate("kajona_demo.tpl");
            $objPage->saveObjectToDb();
            $strNewsdetailsId = $objPage->getSystemid();
            $strReturn .= "ID of new page: ".$strNewsdetailsId."\n";
            $strReturn .= "Adding newsdetails to new page\n";
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("news_news");
            $objPagelement->setStrName("news");
            $objPagelement->setStrElement("news");
            $objPagelement->saveObjectToDb($strNewsdetailsId, "news_news", _dbprefix_."element_news", "first");
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_news
                            SET news_category='".dbsafeString($strCategoryID)."',
                                news_view = '1',
                                news_mode = '0',
                                news_detailspage = 'index',
                                news_template = 'demo.tpl'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "Newselement created.\n";
            else
                $strReturn .= "Error creating newselement.\n";

            $strReturn .= "Adding headline-element to new page\n";
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->saveObjectToDb($strNewsdetailsId, "headline_row", _dbprefix_."element_absatz", "first");
            $strElementId = $objPagelement->getSystemid();
             $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = 'News'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
                if($this->objDB->_query($strQuery))
                    $strReturn .= "Headline element created.\n";
                else
                    $strReturn .= "Error creating headline element.\n";

			$strReturn .= "Creating news-feed\n";
            include_once(_systempath_."/class_modul_news_feed.php");
            $objNewsFeed = new class_modul_news_feed();
            $objNewsFeed->setStrTitle("kajona³ news");
            $objNewsFeed->setStrUrlTitle("kajona_news");
            $objNewsFeed->setStrLink("http://www.kajona.de");

            if($this->strContentLanguage == "de")
                $objNewsFeed->setStrDesc("Dies ist ein Kajona³ demo news feed");
            else
                $objNewsFeed->setStrDesc("This is a Kajona³ demo news feed");

			$objNewsFeed->setStrPage($objPage->getStrName());
			$objNewsFeed->setStrCat("0");
            $objNewsFeed->saveObjectToDb();
            $objNewsFeed->updateObjectToDb(false);
            $strNewsFeedId = $objNewsFeed->getSystemid();
            $strReturn .= "ID of new news-feed: ".$strNewsFeedId."\n";

        return $strReturn;
	}


	private function installModuleNavigations() {
	    $strReturn = "";
        $strReturn .= "Creating new navigation-tree\n";
        include_once(_systempath_."/class_modul_navigation_tree.php");
        $objNaviTree = new class_modul_navigation_tree();
        $objNaviTree->setStrName("mainnavigation");
        $objNaviTree->saveObjectToDb();
        $strTreeId = $objNaviTree->getSystemid();
        $strReturn .= "ID of new navigation-tree: ".$strTreeId."\n";
        $strReturn .= "Creating navigation points\n";
        include_once(_systempath_."/class_modul_navigation_point.php");
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("Home");
        $objNaviPoint->setStrPageI("index");
        $objNaviPoint->saveObjectToDb($strTreeId);
        $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("News");
        $objNaviPoint->setStrPageI("");
        $objNaviPoint->saveObjectToDb($strTreeId);
        $strNewsPointID = $objNaviPoint->getSystemid();
        $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";

        $objModuleNews = class_modul_system_module::getModuleByName("news");
        if($objModuleNews != null) {
            $objNaviPoint = new class_modul_navigation_point();
            $objNaviPoint->setStrName("Details");
            $objNaviPoint->setStrPageI("newsdetails");
            $objNaviPoint->saveObjectToDb($strNewsPointID);
            $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
        }

        $objModuleNews = class_modul_system_module::getModuleByName("guestbook");
        if($objModuleNews != null) {
            $objNaviPoint = new class_modul_navigation_point();
            if($this->strContentLanguage == "de")
                $objNaviPoint->setStrName("Gästebuch");
            else
                $objNaviPoint->setStrName("Guestbook");
            $objNaviPoint->setStrPageI("guestbook");
            $objNaviPoint->saveObjectToDb($strTreeId);
            $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
        }


        if($this->strMasterID != "") {
            $strReturn .= "Adding navigation to master page\n";

            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("mastermainnavi_navigation");
            $objPagelement->setStrName("mastermainnavi");
            $objPagelement->setStrElement("navigation");
            $objPagelement->saveObjectToDb($this->strMasterID, "mastermainnavi_navigation", _dbprefix_."element_navigation", "first");
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_navigation
                            SET navigation_id='".dbsafeString($strTreeId)."',
                                navigation_template = 'mainnavi.tpl',
                                navigation_css = 'navi',
                                navigation_mode = 'tree'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "Navigation element created.\n";
            else
                $strReturn .= "Error creating navigation element.\n";
        }

	    return $strReturn;
	}

	private function installModuleSearch() {
	    $strReturn = "";

	    $strReturn .= "Creating searchresults page\n";
            $objPage = new class_modul_pages_page();
            $objPage->setStrName("searchresults");

            if($this->strContentLanguage == "de")
                $objPage->setStrBrowsername("Suchergebnisse");
            else
                $objPage->setStrBrowsername("Search results");


            $objPage->setStrTemplate("kajona_demo.tpl");
            $objPage->saveObjectToDb();
            $strSearchresultsId = $objPage->getSystemid();
            $strReturn .= "ID of new page: ".$strSearchresultsId."\n";
            $strReturn .= "Adding search-element to new page\n";
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("results_search");
            $objPagelement->setStrName("results");
            $objPagelement->setStrElement("search");
            $objPagelement->saveObjectToDb($strSearchresultsId, "results_search", _dbprefix_."element_search", "first");
            $strElementId = $objPagelement->getSystemid();
             $strQuery = "UPDATE "._dbprefix_."element_search
                                SET search_template = 'search_results.tpl',
                                    search_amount = 6,
                                    search_page = ''
                                WHERE content_id = '".dbsafeString($strElementId)."'";
                if($this->objDB->_query($strQuery))
                    $strReturn .= "Search element created.\n";
                else
                    $strReturn .= "Error creating search element.\n";

            $strReturn .= "Adding headline-element to new page\n";
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->saveObjectToDb($strSearchresultsId, "headline_row", _dbprefix_."element_absatz", "first");
            $strElementId = $objPagelement->getSystemid();

            if($this->strContentLanguage == "de") {
                $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = 'Suchergebnisse'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
            }
            else {
                $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = 'Search results'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
            }

            if($this->objDB->_query($strQuery))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

            if($this->strMasterID != "") {
                $strReturn .= "Adding search to master page\n";
                $objPagelement = new class_modul_pages_pageelement();
                $objPagelement->setStrPlaceholder("mastersearch_search");
                $objPagelement->setStrName("mastersearch");
                $objPagelement->setStrElement("search");
                $objPagelement->saveObjectToDb($this->strMasterID, "mastersearch_search", _dbprefix_."element_search", "first");
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_search
                                SET search_template = 'search.tpl',
                                    search_amount = 6,
                                    search_page = 'searchresults'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
                if($this->objDB->_query($strQuery))
                    $strReturn .= "Search element created.\n";
                else
                    $strReturn .= "Error creating search element.\n";
            }
	    return $strReturn;
	}

	private function installModuleFilemanager() {
	    $strReturn = "";

	    $strReturn .= "Creating upload-folder\n";
            if(!is_dir(_portalpath_."/pics/upload"))
                mkdir(_realpath_."/portal/pics/upload");

            $strReturn .= "Creating new file-repository\n";
            include_once(_systempath_."/class_modul_filemanager_repo.php");
            $objRepo = new class_modul_filemanager_repo();

            if($this->strContentLanguage == "de")
                $objRepo->setStrName("Hochgeladene Bilder");
            else
                $objRepo->setStrName("Picture uploads");

            $objRepo->setStrPath("/portal/pics/upload");
            $objRepo->setStrUploadFilter(".jpg,.gif,.png");
            $objRepo->setStrViewFilter(".jpg,.gif,.png");
            $objRepo->saveObjectToDb();
            $strReturn .= "ID of new repo: ".$objRepo->getSystemid()."\n";
	    return $strReturn;
	}

	private function installModuleLanguages() {
	    $strReturn = "";

	    $strReturn .= "Creating new default-language\n";
            include_once(_systempath_."/class_modul_languages_language.php");
            $objLanguage = new class_modul_languages_language();

            if($this->strContentLanguage == "de")
                $objLanguage->setStrName("de");
            else
               $objLanguage->setStrName("en");

            $objLanguage->setBitDefault(true);
            $objLanguage->saveObjectToDb();
            $strReturn .= "ID of new language: ".$objLanguage->getSystemid()."\n";
            $strReturn .= "Assigning null-properties and elements to the default language.\n";
            if($this->strContentLanguage == "de") {
                if(include_once(_systempath_."/class_modul_pages_page.php"))
                    class_modul_pages_page::assignNullProperties("de");
                if(include_once(_systempath_."/class_modul_pages_pageelement.php"))
                    class_modul_pages_pageelement::assignNullElements("de");
            }
            else {
                if(include_once(_systempath_."/class_modul_pages_page.php"))
                    class_modul_pages_page::assignNullProperties("en");
                if(include_once(_systempath_."/class_modul_pages_pageelement.php"))
                    class_modul_pages_pageelement::assignNullElements("en");
            }

	    return $strReturn;
	}

	private function installModuleGuestbook() {
	    $strReturn = "";

	    $strReturn .= "Creating new guestbook...\n";
        include_once(_systempath_."/class_modul_guestbook_guestbook.php");
        $objGuestbook = new class_modul_guestbook_guestbook();
        $objGuestbook->setGuestbookTitle("Guestbook");
        $objGuestbook->setGuestbookModerated(1);
        $objGuestbook->saveObjectToDb();
        $strGuestbookID = $objGuestbook->getSystemid();
        $strReturn .= "ID of new guestbook: ".$strGuestbookID."\n";


        $strReturn .= "Creating new guestbook page...\n";

        $objPage = new class_modul_pages_page();
        $objPage->setStrName("guestbook");
        $objPage->setStrBrowsername("Guestbook");
        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->saveObjectToDb();

        $strGuestbookpageID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strGuestbookpageID."\n";
        $strReturn .= "Adding pagelement to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("gb1_guestbook");
        $objPagelement->setStrName("gb1");
        $objPagelement->setStrElement("guestbook");
        $objPagelement->saveObjectToDb($strGuestbookpageID, "gb1_guestbook", _dbprefix_."element_guestbook", "first");
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_guestbook
                        SET guestbook_id='".dbsafeString($strGuestbookID)."',
                            guestbook_template = 'guestbook.tpl',
                            guestbook_amount = '7'
                        WHERE content_id = '".dbsafeString($strElementId)."'";
        if($this->objDB->_query($strQuery))
            $strReturn .= "Guestbookelement created.\n";
        else
            $strReturn .= "Error creating Guestbookelement.\n";

        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->saveObjectToDb($strGuestbookpageID, "headline_row", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();
         $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = 'Guestbook'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Creating Navigation-Entry...\n";


	    return $strReturn;
	}
}
?>
