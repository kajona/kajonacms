<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/


/**
 * Installer of the rssfeed samplecontent
 *
 * @package modul_pages
 */
class class_installer_sc_rssfeed implements interface_sc_installer  {

    /**
     *
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";

        $strReturn .= "Creating new page rssfeed...\n";

        $objPage = new class_modul_pages_page();
        $objPage->setStrName("rssfeed");
        $objPage->setStrBrowsername("Rssfeed");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb();

        $strPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strPageId."\n";
        $strReturn .= "Adding pagelement to new page\n";

        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("mixed_rssfeed|tagto|imagelightbox|portallogin|portalregistration|lastmodified|rendertext|tagcloud|downloadstoplist|textticker");
        $objPagelement->setStrName("mixed");
        $objPagelement->setStrElement("rssfeed");
        $objPagelement->updateObjectToDb($strPageId);
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_universal
                        SET char1 = 'rssfeed.tpl',
                            ".$this->objDB->encloseColumnName("int1")." = 10,
                            char2 = 'http://www.kajona.de/kajona_news.rss'
                        WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_universal
                        SET char1 = 'rssfeed.tpl',
                            ".$this->objDB->encloseColumnName("int1")." = 10,
                            char2 = 'http://www.kajona.de/kajona_news_en.rss'
                        WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        
        if($this->objDB->_query($strQuery))
            $strReturn .= "Rssfeed element created.\n";
        else
            $strReturn .= "Error creating Rssfeed element.\n";



        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($strPageId);
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Rssfeed'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";


        $strReturn .= "Creating Navigation-Entry...\n";
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
	        $objNaviPoint->setStrName("Rssfeed");
	        $objNaviPoint->setStrPageI("rssfeed");
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
        return "pages";
    }

}
?>