<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                            *
********************************************************************************************************/


/**
 * Installer of the guestbook samplecontent
 *
 * @package modul_guestbook
 */
class class_installer_sc_guestbook implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = class_modul_pages_folder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();

        $strReturn .= "Creating new guestbook...\n";
        $objGuestbook = new class_modul_guestbook_guestbook();
        $objGuestbook->setGuestbookTitle("Guestbook");
        $objGuestbook->setGuestbookModerated(1);
        $objGuestbook->updateObjectToDb();
        $strGuestbookID = $objGuestbook->getSystemid();
        $strReturn .= "ID of new guestbook: ".$strGuestbookID."\n";


        $strReturn .= "Creating new guestbook page...\n";

        $objPage = new class_modul_pages_page();
        $objPage->setStrName("guestbook");
        $objPage->setStrBrowsername("Guestbook");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strGuestbookpageID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strGuestbookpageID."\n";
        $strReturn .= "Adding pagelement to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("gb1_guestbook");
        $objPagelement->setStrName("gb1");
        $objPagelement->setStrElement("guestbook");
        $objPagelement->updateObjectToDb($strGuestbookpageID);
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_guestbook
                        SET guestbook_id='".dbsafeString($strGuestbookID)."',
                            guestbook_template = 'guestbook.tpl',
                            guestbook_amount = '5'
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
        $objPagelement->updateObjectToDb($strGuestbookpageID);
        $strElementId = $objPagelement->getSystemid();
         $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Guestbook'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

//        $strReturn .= "Creating Navigation-Entry...\n";
//        //navigations installed?
//        try {
//            $objModule = class_modul_system_module::getModuleByName("navigation", true);
//        }
//        catch (class_exception $objException) {
//            $objModule = null;
//        }
//        if($objModule != null) {
//
//	        $objNavi = class_modul_navigation_tree::getNavigationByName("mainnavigation");
//	        $strTreeId = $objNavi->getSystemid();
//
//	        $objNaviPoint = new class_modul_navigation_point();
//	        if($this->strContentLanguage == "de")
//	            $objNaviPoint->setStrName("Gästebuch");
//	        else
//	            $objNaviPoint->setStrName("Guestbook");
//	        $objNaviPoint->setStrPageI("guestbook");
//	        $objNaviPoint->updateObjectToDb($strTreeId);
//	        $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
//        }
        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "guestbook";
    }

}
?>