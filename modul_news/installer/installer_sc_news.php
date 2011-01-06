<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                       *
********************************************************************************************************/


/**
 * Installer of the news samplecontenht
 *
 * @package modul_news
 */
class class_installer_sc_news implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    private $strIndexID = "";

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";
        $strNewsdetailsId = "";

        //search the index page
        $objIndex = class_modul_pages_page::getPageByName("index");
        if($objIndex != null)
            $this->strIndexID = $objIndex->getSystemid();

        $strReturn .= "Creating new category...\n";
            $objNewsCategory = new class_modul_news_category();
            $objNewsCategory->setStrTitle("TOP-News");
            $objNewsCategory->updateObjectToDb();
            $strCategoryID = $objNewsCategory->getSystemid();
            $strReturn .= "ID of new category: ".$strCategoryID."\n";
            $strReturn .= "Creating news\n";
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

            $objNews->setIntDateStart(class_date::getCurrentTimestamp());
            $objNews->setArrCats(array($strCategoryID => 1));
            $objNews->updateObjectToDb();
            $strNewsId = $objNews->getSystemid();
            $strReturn .= "ID of new news: ".$strNewsId."\n";
            $strReturn .= "Adding news element to the index-page...\n";
            if($this->strIndexID != "") {
                $objPagelement = new class_modul_pages_pageelement();
                $objPagelement->setStrPlaceholder("news_news");
                $objPagelement->setStrName("news");
                $objPagelement->setStrElement("news");
                $objPagelement->updateObjectToDb($this->strIndexID);
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_news
                                SET news_category='".dbsafeString($strCategoryID)."',
                                    news_view = '0',
                                    news_mode = '0',
                                    news_order = '0',
                                    news_amount = '10',
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
            //set language to "" - being update by the languages sc installer later
            $objPage->setStrLanguage("");
            $objPage->updateObjectToDb();
            $strNewsdetailsId = $objPage->getSystemid();
            $strReturn .= "ID of new page: ".$strNewsdetailsId."\n";
            $strReturn .= "Adding newsdetails to new page\n";
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("news_news");
            $objPagelement->setStrName("news");
            $objPagelement->setStrElement("news");
            $objPagelement->updateObjectToDb($strNewsdetailsId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_news
                            SET news_category='".dbsafeString($strCategoryID)."',
                                news_view = '1',
                                news_mode = '0',
                                news_order = '0',
                                news_amount = '20',
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
            $objPagelement->updateObjectToDb($strNewsdetailsId);
            $strElementId = $objPagelement->getSystemid();
             $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = 'News'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
                if($this->objDB->_query($strQuery))
                    $strReturn .= "Headline element created.\n";
                else
                    $strReturn .= "Error creating headline element.\n";

            $strReturn .= "Creating news-feed\n";
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
            $objNewsFeed->setIntAmount(25);
            $objNewsFeed->updateObjectToDb();
            $strNewsFeedId = $objNewsFeed->getSystemid();
            $strReturn .= "ID of new news-feed: ".$strNewsFeedId."\n";

            $strReturn .= "Creating navigation entries...\n";

            //navigations installed?
	        try {
	            $objModule = class_modul_system_module::getModuleByName("navigation", true);
	        }
	        catch (class_exception $objException) {
	            $objModule = null;
	        }
	        if($objModule != null) {

	            $objNavi = class_modul_navigation_tree::getNavigationByName("mainnavigation");
	            $strTreeId = $objNavi->getSystemid();

	            $objNaviPoint = new class_modul_navigation_point();
	            $objNaviPoint->setStrName("News");
	            $objNaviPoint->setStrPageI("index");
	            $objNaviPoint->updateObjectToDb($strTreeId);
	            $strNewsPointID = $objNaviPoint->getSystemid();
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
        return "news";
    }

}
?>