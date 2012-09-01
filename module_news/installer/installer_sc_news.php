<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: installer_sc_news.php 4071 2011-08-17 10:32:17Z sidler $                                       *
********************************************************************************************************/


/**
 * Installer of the news samplecontenht
 *
 * @package module_news
 */
class class_installer_sc_news implements interface_sc_installer  {
    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;

    private $strIndexID = "";

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";

        //search the index page
        $objIndex = class_module_pages_page::getPageByName("index");
        if($objIndex != null)
            $this->strIndexID = $objIndex->getSystemid();

        $strReturn .= "Creating new category...\n";
        $objNewsCategory = new class_module_news_category();
        $objNewsCategory->setStrTitle("TOP-News");
        $objNewsCategory->updateObjectToDb();
        $strCategoryID = $objNewsCategory->getSystemid();
        $strReturn .= "ID of new category: ".$strCategoryID."\n";
        $strReturn .= "Creating news\n";
        $objNews = new class_module_news_news();

        if($this->strContentLanguage == "de") {
            $objNews->setStrTitle("Kajona wurde erfolgreich installiert");
            $objNews->setStrText("Eine weitere Installation von Kajona V3 war erfolgreich. Für weitere Infomationen zu Kajona besuchen Sie www.kajona.de.");
            $objNews->setStrIntro("Kajona wurde erfolgreich installiert...");
        }
        else {
            $objNews->setStrTitle("Installation was successful");
            $objNews->setStrText("Another installation of Kajona was successful. For further information, support or proposals, please visit our website: www.kajona.de");
            $objNews->setStrIntro("Kajona installed successfully...");
        }

        $objNews->setIntDateStart(class_date::getCurrentTimestamp());
        $objNews->setArrCats(array($strCategoryID => 1));
        $objNews->setBitUpdateMemberships(true);
        $objNews->updateObjectToDb();
        $strNewsId = $objNews->getSystemid();
        $strReturn .= "ID of new news: ".$strNewsId."\n";
        $strReturn .= "Adding news element to the index-page...\n";
        if($this->strIndexID != "") {
            
            if(class_module_pages_element::getElement("news") != null) {
                $objPagelement = new class_module_pages_pageelement();
                $objPagelement->setStrPlaceholder("news_news");
                $objPagelement->setStrName("news");
                $objPagelement->setStrElement("news");
                $objPagelement->updateObjectToDb($this->strIndexID);
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_news
                                SET news_category=?,
                                    news_view = ?,
                                    news_mode = ?,
                                    news_order = ?,
                                    news_amount = ?,
                                    news_detailspage = ?,
                                    news_template = ?
                                WHERE content_id = ?";
                if($this->objDB->_pQuery($strQuery, array($strCategoryID, 0, 0, 0, 10, "newsdetails", "demo.tpl", $strElementId)))
                    $strReturn .= "Newselement created.\n";
                else
                    $strReturn .= "Error creating newselement.\n";
            }
        }
        $strReturn .= "Creating news-detail\n";
        $objPage = new class_module_pages_page();
        $objPage->setStrName("newsdetails");
        $objPage->setStrBrowsername("News");
        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->updateObjectToDb();
        $strNewsdetailsId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strNewsdetailsId."\n";
        $strReturn .= "Adding newsdetails to new page\n";
        
        if(class_module_pages_element::getElement("news") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("news_news");
            $objPagelement->setStrName("news");
            $objPagelement->setStrElement("news");
            $objPagelement->updateObjectToDb($strNewsdetailsId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_news
                            SET news_category=?,
                                news_view = ?,
                                news_mode = ?,
                                news_order = ?,
                                news_amount = ?,
                                news_detailspage = ?,
                                news_template = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array($strCategoryID, 1, 0, 0, 20, "index", "demo.tpl", $strElementId)))
                $strReturn .= "Newselement created.\n";
            else
                $strReturn .= "Error creating newselement.\n";
        
        }

        $strReturn .= "Adding headline-element to new page\n";
        
        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strNewsdetailsId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("News", $strElementId)))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

        }

        $strReturn .= "Creating news-feed\n";
        $objNewsFeed = new class_module_news_feed();
        $objNewsFeed->setStrTitle("kajona news");
        $objNewsFeed->setStrUrlTitle("kajona_news");
        $objNewsFeed->setStrLink("http://www.kajona.de");

        if($this->strContentLanguage == "de")
            $objNewsFeed->setStrDesc("Dies ist ein Kajona demo news feed");
        else
            $objNewsFeed->setStrDesc("This is a Kajona demo news feed");

        $objNewsFeed->setStrPage($objPage->getStrName());
        $objNewsFeed->setStrCat("0");
        $objNewsFeed->setIntAmount(25);
        $objNewsFeed->updateObjectToDb();
        $strNewsFeedId = $objNewsFeed->getSystemid();
        $strReturn .= "ID of new news-feed: ".$strNewsFeedId."\n";

        $strReturn .= "Creating navigation entries...\n";

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
