<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                  *
********************************************************************************************************/

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
        $objDownloads = new class_modul_downloads_archive();
        $objDownloads->setTitle("Sample downloads");
        $objDownloads->setPath("/portal/downloads/samples");
        $objDownloads->updateObjectToDb();
        $strDownloadsID = $objDownloads->getSystemid();

        $strReturn .= "Modify rights to allow guests to download files and rate files...\n";
        class_carrier::getInstance()->getObjRights()->addGroupToRight(_guests_group_id_, $strDownloadsID, "right2");
        class_carrier::getInstance()->getObjRights()->addGroupToRight(_guests_group_id_, $strDownloadsID, "right4");

        $strReturn .= "Sync downloads..\n";
        class_modul_downloads_file::syncRecursive($objDownloads->getSystemid(), $objDownloads->getPath());


        $strReturn .= "Creating new downloads page...\n";

        $objPage = new class_modul_pages_page();
        $objPage->setStrName("downloads");
        $objPage->setStrBrowsername("Downloads");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb();

        $strDownloadsPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strDownloadsPageId."\n";
        $strReturn .= "Adding pagelement to new page\n";

        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("dl1_downloads");
        $objPagelement->setStrName("dl1");
        $objPagelement->setStrElement("downloads");
        $objPagelement->updateObjectToDb($strDownloadsPageId);
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
        $objPagelement->updateObjectToDb($strDownloadsPageId);
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Downloads'
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
	        $objNaviPoint->setStrName("Downloads");
	        $objNaviPoint->setStrPageI("downloads");
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
        return "downloads";
    }

}
?>