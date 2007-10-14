<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_installer_sc_search.php                                                                       *
*   Interface of the search samplecontent                                                               *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_installer_sc_search.php 1714 2007-09-06 19:29:59Z sidler $                               *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");
include_once(_systempath_."/class_modul_pages_page.php");

/**
 * Interface of the seach samplecontent
 *
 * @package modul_search
 */
class class_installer_sc_search implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;
    
    private $strMasterID = "";

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";
        
        //search the master page
        $objMaster = class_modul_pages_page::getPageByName("master");
        if($objMaster != null)
            $this->strMasterID = $objMaster->getSystemid();

        $strReturn .= "Creating searchresults page\n";
            $objPage = new class_modul_pages_page();
            $objPage->setStrName("searchresults");

            if($this->strContentLanguage == "de")
                $objPage->setStrBrowsername("Suchergebnisse");
            else
                $objPage->setStrBrowsername("Search results");


            $objPage->setStrTemplate("kajona_demo.tpl");
            $objPage->saveObjectToDb();
            $strSearchresultsId = $objPage->getSystemid();
            $strReturn .= "ID of new page: ".$strSearchresultsId."\n";
            $strReturn .= "Adding search-element to new page\n";
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("results_search");
            $objPagelement->setStrName("results");
            $objPagelement->setStrElement("search");
            $objPagelement->saveObjectToDb($strSearchresultsId, "results_search", _dbprefix_."element_search", "first");
            $strElementId = $objPagelement->getSystemid();
             $strQuery = "UPDATE "._dbprefix_."element_search
                                SET search_template = 'search_results.tpl',
                                    search_amount = 6,
                                    search_page = ''
                                WHERE content_id = '".dbsafeString($strElementId)."'";
                if($this->objDB->_query($strQuery))
                    $strReturn .= "Search element created.\n";
                else
                    $strReturn .= "Error creating search element.\n";

            $strReturn .= "Adding headline-element to new page\n";
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->saveObjectToDb($strSearchresultsId, "headline_row", _dbprefix_."element_absatz", "first");
            $strElementId = $objPagelement->getSystemid();

            if($this->strContentLanguage == "de") {
                $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = 'Suchergebnisse'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
            }
            else {
                $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = 'Search results'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
            }

            if($this->objDB->_query($strQuery))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

            if($this->strMasterID != "") {
                $strReturn .= "Adding search to master page\n";
                $strReturn .= "ID of master page: ".$this->strMasterID."\n";
                $objPagelement = new class_modul_pages_pageelement();
                $objPagelement->setStrPlaceholder("mastersearch_search");
                $objPagelement->setStrName("mastersearch");
                $objPagelement->setStrElement("search");
                $objPagelement->saveObjectToDb($this->strMasterID, "mastersearch_search", _dbprefix_."element_search", "first");
                $strElementId = $objPagelement->getSystemid();
                $strQuery = "UPDATE "._dbprefix_."element_search
                                SET search_template = 'search.tpl',
                                    search_amount = 6,
                                    search_page = 'searchresults'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
                if($this->objDB->_query($strQuery))
                    $strReturn .= "Search element created.\n";
                else
                    $strReturn .= "Error creating search element.\n";
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
?> 
 
