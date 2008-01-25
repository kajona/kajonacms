<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_installer_sc_news.php                                                                         *
*   Interface of the news samplecontent                                                                 *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                       *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");
include_once(_systempath_."/class_modul_pages_page.php");

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
            //set language to "" - being update by the languages sc installer later
            $objPage->setStrLanguage("");
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
            
            $strReturn .= "Creating navigation entries...\n";
            include_once(_systempath_."/class_modul_navigation_tree.php");
            include_once(_systempath_."/class_modul_navigation_point.php");
            $objNavi = class_modul_navigation_tree::getNavigationByName("mainnavigation");
            $strTreeId = $objNavi->getSystemid();
            
            $objNaviPoint = new class_modul_navigation_point();
            $objNaviPoint->setStrName("News");
            $objNaviPoint->setStrPageI("index");
            $objNaviPoint->saveObjectToDb($strTreeId);
            $strNewsPointID = $objNaviPoint->getSystemid();
            $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
    
            $objNaviPoint = new class_modul_navigation_point();
            $objNaviPoint->setStrName("Details");
            $objNaviPoint->setStrPageI("newsdetails");
            $objNaviPoint->saveObjectToDb($strNewsPointID);
            $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";

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
 
