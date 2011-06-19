<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                               *
********************************************************************************************************/


/**
 * Interface of the tags samplecontent
 *
 * @package modul_tags
 */
class class_installer_sc_tags implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    private $strMasterID = "";

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

        //search the master page
        $objMaster = class_modul_pages_page::getPageByName("master");
        if($objMaster != null)
            $this->strMasterID = $objMaster->getSystemid();

        $strReturn .= "Creating tags page\n";
            $objPage = new class_modul_pages_page();
            $objPage->setStrName("tags");

            if($this->strContentLanguage == "de")
                $objPage->setStrBrowsername("Tags");
            else
                $objPage->setStrBrowsername("Tags");

            //set language to "" - being update by the languages sc installer later
            $objPage->setStrLanguage("");
            $objPage->setStrTemplate("kajona_demo.tpl");
            $objPage->updateObjectToDb($strNaviFolderId);
            $strSearchresultsId = $objPage->getSystemid();
            $strReturn .= "ID of new page: ".$strSearchresultsId."\n";
            $strReturn .= "Adding tags-element to new page\n";
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("mixed3_flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("mixed3");
            $objPagelement->setStrElement("tags");
            $objPagelement->updateObjectToDb($strSearchresultsId);
            $strElementId = $objPagelement->getSystemid();
             $strQuery = "UPDATE "._dbprefix_."element_universal
                                SET char1 = 'tags.tpl'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
                if($this->objDB->_query($strQuery))
                    $strReturn .= "Tags element created.\n";
                else
                    $strReturn .= "Error creating tags element.\n";

            $strReturn .= "Adding headline-element to new page\n";
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strSearchresultsId);
            $strElementId = $objPagelement->getSystemid();

            if($this->strContentLanguage == "de") {
                $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = 'Tags'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
            }
            else {
                $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = 'Tags'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
            }

            if($this->objDB->_query($strQuery))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

            $strReturn .= "Creating navigation point.\n";

//            //navigations installed?
//	        try {
//	            $objModule = class_modul_system_module::getModuleByName("navigation", true);
//	        }
//	        catch (class_exception $objException) {
//	            $objModule = null;
//	        }
//	        if($objModule != null) {
//
//		        $arrNavis = class_modul_navigation_tree::getAllNavis();
//		        $objNavi = class_modul_navigation_tree::getNavigationByName("mainnavigation");
//		        $strTreeId = $objNavi->getSystemid();
//
//		        $objNaviPoint = new class_modul_navigation_point();
//		        if($this->strContentLanguage == "de") {
//		            $objNaviPoint->setStrName("Tags");
//		        }
//		        else {
//		        	$objNaviPoint->setStrName("Tags");
//		        }
//
//		        $objNaviPoint->setStrPageI("tags");
//		        $objNaviPoint->updateObjectToDb($strTreeId);
//		        $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
//            }

        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "tags";
    }
}
?>