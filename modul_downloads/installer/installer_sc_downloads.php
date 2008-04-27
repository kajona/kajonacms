<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_installer_sc_downloads.php                                                                    *
*   Interface of the downloads samplecontent                                                            *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                  *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");

/**
 * Installer of the downloads samplecontent
 *
 * @package modul_downloads
 */
class class_installer_sc_downloads implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";

        
        $strReturn .= "Creating new downloads...\n";
        include_once(_systempath_."/class_modul_downloads_archive.php");
        include_once(_systempath_."/class_modul_downloads_file.php");
        $objDownloads = new class_modul_downloads_archive();
        $objDownloads->setTitle("Sample downloads");
        $objDownloads->setPath("/portal/downloads");
        $objDownloads->saveObjectToDb();
        $strDownloadsID = $objDownloads->getSystemid();
        
        $strReturn .= "Modify rights to allow guests to download files...\n";
        class_carrier::getInstance()->getObjRights()->addGroupToRight(_gaeste_gruppe_id_, $strDownloadsID, "right2");
        
        $strReturn .= "Sync downloads..\n";
        class_modul_downloads_file::syncRecursive($objDownloads->getSystemid(), $objDownloads->getPath());
        

        $strReturn .= "Creating new downloads page...\n";

        $objPage = new class_modul_pages_page();
        $objPage->setStrName("downloads");
        $objPage->setStrBrowsername("Downloads");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->saveObjectToDb();

        $strDownloadsPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strDownloadsPageId."\n";
        $strReturn .= "Adding pagelement to new page\n";
        
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("dl1_downloads");
        $objPagelement->setStrName("dl1");
        $objPagelement->setStrElement("downloads");
        $objPagelement->saveObjectToDb($strDownloadsPageId, "dl1_downloads", _dbprefix_."element_downloads", "first");
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_downloads 
                        SET download_id = '".dbsafeString($strDownloadsID)."',
                            download_template = 'downloads.tpl'
                        WHERE content_id = '".dbsafeString($strElementId)."'";
        if($this->objDB->_query($strQuery))
            $strReturn .= "downloads element created.\n";
        else
            $strReturn .= "Error creating downloads element.\n";
            
            

        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->saveObjectToDb($strDownloadsPageId, "headline_row", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = 'Downloads'
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
	        include_once(_systempath_."/class_modul_navigation_tree.php");
	        include_once(_systempath_."/class_modul_navigation_point.php");
	        $objNavi = class_modul_navigation_tree::getNavigationByName("mainnavigation");
	        $strTreeId = $objNavi->getSystemid();
	        
	        $objNaviPoint = new class_modul_navigation_point();
	        $objNaviPoint->setStrName("Downloads");
	        $objNaviPoint->setStrPageI("downloads");
	        $objNaviPoint->saveObjectToDb($strTreeId);
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
        return "downloads";
    }
    
}
?>