<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_installer_sc_guestbook.php                                                                    *
*   Interface of the guestbook samplecontent                                                            *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                            *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");

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
        
        include_once(_systempath_."/class_modul_navigation_tree.php");
        include_once(_systempath_."/class_modul_navigation_point.php");
        $objNavi = class_modul_navigation_tree::getNavigationByName("mainnavigation");
        $strTreeId = $objNavi->getSystemid();
        
        $objNaviPoint = new class_modul_navigation_point();
        if($this->strContentLanguage == "de")
            $objNaviPoint->setStrName("Gästebuch");
        else
            $objNaviPoint->setStrName("Guestbook");
        $objNaviPoint->setStrPageI("guestbook");
        $objNaviPoint->saveObjectToDb($strTreeId);
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
        return "guestbook";
    }
    
}
?> 
 
