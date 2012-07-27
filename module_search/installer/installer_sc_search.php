<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                               *
********************************************************************************************************/


/**
 * Interface of the search samplecontent
 *
 * @package module_search
 */
class class_installer_sc_search implements interface_sc_installer  {

    /**
     * @var class_db
     */
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
        $strSystemFolderId = "";
        $arrFolder = class_module_pages_folder::getFolderList();
        foreach($arrFolder as $objOneFolder) {
            if($objOneFolder->getStrName() == "_system")
                $strSystemFolderId = $objOneFolder->getSystemid();
        }

        //search the master page
        $objMaster = class_module_pages_page::getPageByName("master");
        if($objMaster != null)
            $this->strMasterID = $objMaster->getSystemid();

        $strReturn .= "Creating search page\n";
            $objPage = new class_module_pages_page();
            $objPage->setStrName("search");

            if($this->strContentLanguage == "de")
                $objPage->setStrBrowsername("Suchergebnisse");
            else
                $objPage->setStrBrowsername("Search results");

            $objPage->setStrTemplate("kajona_demo.tpl");
            $objPage->updateObjectToDb($strSystemFolderId);
            $strSearchresultsId = $objPage->getSystemid();
            $strReturn .= "ID of new page: ".$strSearchresultsId."\n";
            $strReturn .= "Adding search-element to new page\n";
            
            if(class_module_pages_element::getElement("search") != null) {
                $objPagelement = new class_module_pages_pageelement();
                $objPagelement->setStrPlaceholder("results_search");
                $objPagelement->setStrName("results");
                $objPagelement->setStrElement("search");
                $objPagelement->updateObjectToDb($strSearchresultsId);
                $strElementId = $objPagelement->getSystemid();
                 $strQuery = "UPDATE "._dbprefix_."element_search
                                    SET search_template = ?,
                                        search_amount = ?,
                                        search_page = ?
                                    WHERE content_id = ?";
                    if($this->objDB->_pQuery($strQuery, array("search_ajax.tpl", 0, "", $strElementId)))
                        $strReturn .= "Search element created.\n";
                    else
                        $strReturn .= "Error creating search element.\n";
            }
            
            
            $strReturn .= "Adding headline-element to new page\n";
            if(class_module_pages_element::getElement("row") != null) {
                $objPagelement = new class_module_pages_pageelement();
                $objPagelement->setStrPlaceholder("headline_row");
                $objPagelement->setStrName("headline");
                $objPagelement->setStrElement("row");
                $objPagelement->updateObjectToDb($strSearchresultsId);
                $strElementId = $objPagelement->getSystemid();

                $arrParams = array();
                if($this->strContentLanguage == "de") {
                    $arrParams = array("Suchergebnisse", $strElementId);
                }
                else {
                    $arrParams = array("Search results", $strElementId);
                }

                $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                    SET paragraph_title = ?
                                    WHERE content_id = ?";

                if($this->objDB->_pQuery($strQuery, $arrParams))
                    $strReturn .= "Headline element created.\n";
                else
                    $strReturn .= "Error creating headline element.\n";
            }

            $strReturn .= "Creating navigation point.\n";

            //navigations installed?
            $objModule = null;
	        try {
	            $objModule = class_module_system_module::getModuleByName("navigation", true);
	        }
	        catch (class_exception $objException) {
	            $objModule = null;
	        }
	        if($objModule != null) {

		        $objNavi = class_module_navigation_tree::getNavigationByName("portalnavigation");
		        $strTreeId = $objNavi->getSystemid();

		        $objNaviPoint = new class_module_navigation_point();
		        if($this->strContentLanguage == "de") {
		            $objNaviPoint->setStrName("Suche");
		        }
		        else {
		        	$objNaviPoint->setStrName("Search");
		        }

		        $objNaviPoint->setStrPageI("search");
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
        return "search";
    }
}
