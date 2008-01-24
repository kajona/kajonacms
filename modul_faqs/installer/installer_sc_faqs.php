<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_installer_sc_faqs.php                                                                         *
*   Interface of the faqs samplecontent                                                                 *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                       *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");
include_once(_systempath_."/class_modul_pages_page.php");

/**
 * Installer of the faqs samplecontenht
 *
 * @package modul_faqs
 */
class class_installer_sc_faqs implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;
    
    private $strIndexID = "";

    public function install() {
        $strReturn = "";
        $strfaqsdetailsId = "";
        
        //search the index page
        $objIndex = class_modul_pages_page::getPageByName("index");
        if($objIndex != null)
            $this->strIndexID = $objIndex->getSystemid();

        $strReturn .= "Creating faqs\n";
        include_once(_systempath_."/class_modul_faqs_faq.php");
        $objFaq1 = new class_modul_faqs_faq();
        $objFaq2 = new class_modul_faqs_faq();
        
        if($this->strContentLanguage == "de") {
        	$objFaq1->setStrQuestion("Was is Kajona?");
        	$objFaq1->setStrAnswer("Kajona ist ein Open Source Content Management System basierend auf einer SQL-Datenbank und PHP. Dank der modularen Bauweise ist Kajona einfach zu erweitern und anzupassen.");
        	
        	$objFaq2->setStrQuestion("Wer entwickelt Kajona, wo gibt es weitere Infos?");
        	$objFaq2->setStrAnswer("Kajona wird von einer Community entwickelt. Da Kajona ständig weiterentwickelt wird, sind wir immer auf der Suche nach Helfern. Weitere Infos hierzu auch auf <a href=\"http://www.kajona.de\">www.kajona.de</a>");
        }
        else {
        	$objFaq1->setStrQuestion("What is Kajona?");
            $objFaq1->setStrAnswer("Kajona is a open source content management system based on a sql-database and php. Due to its modular design, it can be extended and adopted very easily.");

            $objFaq2->setStrQuestion("Who develops Kajona, where can I find more infos?");
            $objFaq2->setStrAnswer("Kajona is being developed by a community. Since Kajona is still being developed, we are searching for contributors. Further infos can be found at <a href=\"http://www.kajona.de\">www.kajona.de</a>");
        }
        
        $strReturn .= "Saving faqs...\n";
        $objFaq1->saveObjectToDb();
        $objFaq2->saveObjectToDb();

        
        $strReturn .= "Creating faqs-page\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("faqs");
        $objPage->setStrBrowsername("FAQs");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->saveObjectToDb();

        $strFaqsPageId = $objPage->getSystemid();
        
        $strReturn .= "ID of new page: ".$strFaqsPageId."\n";
        $strReturn .= "Adding faqs-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("faqs_faqs");
        $objPagelement->setStrName("faqs");
        $objPagelement->setStrElement("faqs");
        $objPagelement->saveObjectToDb($strFaqsPageId, "faqs_faqs", _dbprefix_."element_faqs", "first");
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_faqs
                        SET faqs_category=0,
                            faqs_template = 'demo_foldable.tpl'
                      WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "faqselement created.\n";
            else
                $strReturn .= "Error creating faqselement.\n";

        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->saveObjectToDb($strFaqsPageId, "headline_row", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_absatz
                         SET absatz_titel = 'FAQs'
                       WHERE content_id = '".dbsafeString($strElementId)."'";
        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Creating navigation entries...\n";
        include_once(_systempath_."/class_modul_navigation_tree.php");
        include_once(_systempath_."/class_modul_navigation_point.php");
        $objNavi = class_modul_navigation_tree::getNavigationByName("mainnavigation");
        $strTreeId = $objNavi->getSystemid();
        
            
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("FAQs");
        $objNaviPoint->setStrPageI("faqs");
        $objNaviPoint->saveObjectToDb($strTreeId);
        $strfaqsPointID = $objNaviPoint->getSystemid();
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
        return "faqs";
    }
    
}
?> 
 
