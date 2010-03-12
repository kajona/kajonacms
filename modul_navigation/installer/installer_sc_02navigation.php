<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                               *
********************************************************************************************************/

/**
 * Installer of the navigation samplecontent
 *
 * @package modul_navigation
 */
class class_installer_sc_02navigation implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    private $strMasterID = "";

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {

        //search the master page
        $objMaster = class_modul_pages_page::getPageByName("master");
        if($objMaster != null)
            $this->strMasterID = $objMaster->getSystemid();


        $strReturn = "";
        $strReturn .= "Creating new mainnavigation-tree\n";
        $objNaviTree = new class_modul_navigation_tree();
        $objNaviTree->setStrName("mainnavigation");
        $objNaviTree->updateObjectToDb();
        $strTreeId = $objNaviTree->getSystemid();
        $strReturn .= "ID of new navigation-tree: ".$strTreeId."\n";

        $strReturn .= "Creating navigation points\n";
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("Page 1");
        $objNaviPoint->setStrPageI("page_1");
        $objNaviPoint->updateObjectToDb($strTreeId);
        $strNaviPointID = $objNaviPoint->getSystemid();
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("Subpage 1");
        $objNaviPoint->setStrPageI("subpage_1");
        $objNaviPoint->updateObjectToDb($strNaviPointID);

        $strReturn .= "Creating new portalnavigation-tree\n";
        $objNaviTree = new class_modul_navigation_tree();
        $objNaviTree->setStrName("portalnavigation");
        $objNaviTree->updateObjectToDb();
        $strTreePortalId = $objNaviTree->getSystemid();
        $strReturn .= "ID of new navigation-tree: ".$strTreePortalId."\n";

        $strReturn .= "Creating navigation points\n";
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("Home");
        $objNaviPoint->setStrPageI("index");
        $objNaviPoint->updateObjectToDb($strTreePortalId);



        if($this->strMasterID != "") {
            $strReturn .= "Adding mainnavigation to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("mastermainnavi_navigation");
            $objPagelement->setStrName("mastermainnavi");
            $objPagelement->setStrElement("navigation");
            $objPagelement->updateObjectToDb($this->strMasterID);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_navigation
                            SET navigation_id='".dbsafeString($strTreeId)."',
                                navigation_template = 'mainnavi.tpl',
                                navigation_mode = 'tree'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "Navigation element created.\n";
            else
                $strReturn .= "Error creating navigation element.\n";

            $strReturn .= "Adding portalnavigation to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("masterportalnavi_navigation");
            $objPagelement->setStrName("masterportalnavi");
            $objPagelement->setStrElement("navigation");
            $objPagelement->updateObjectToDb($this->strMasterID);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_navigation
                            SET navigation_id='".dbsafeString($strTreePortalId)."',
                                navigation_template = 'portalnavi.tpl',
                                navigation_mode = 'tree'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "Navigation element created.\n";
            else
                $strReturn .= "Error creating navigation element.\n";
        }

        $strReturn .= "Creating simple sitemap...\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("sitemap");
        $objPage->setStrBrowsername("Sitemap");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb();
        $strSitemapId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strSitemapId."\n";
        $strReturn .= "Adding sitemap to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("sitemap_navigation");
        $objPagelement->setStrName("sitemap");
        $objPagelement->setStrElement("navigation");
        $objPagelement->updateObjectToDb($strSitemapId);
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_navigation
                        SET navigation_id='".dbsafeString($strTreeId)."',
                            navigation_template = 'sitemap.tpl',
                            navigation_mode = 'sitemap'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        if($this->objDB->_query($strQuery))
            $strReturn .= "Sitemapelement created.\n";
        else
            $strReturn .= "Error creating sitemapelement.\n";

        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($strSitemapId);
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_paragraph
                        SET paragraph_title = 'Sitemap'
                      WHERE content_id = '".dbsafeString($strElementId)."'";
        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Creating navigation points\n";
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("Sitemap");
        $objNaviPoint->setStrPageI("sitemap");
        $objNaviPoint->updateObjectToDb($strTreePortalId);
        $strReturn .= "ID of new navigation point ".$objNaviPoint->getSystemid().".\n";

        $objNaviPoint = new class_modul_navigation_point();
        if($this->strContentLanguage == "de")
            $objNaviPoint->setStrName("Impressum");
        else
            $objNaviPoint->setStrName("Imprint");
        $objNaviPoint->setStrPageI("imprint");
        $objNaviPoint->updateObjectToDb($strTreePortalId);
        $strReturn .= "ID of new navigation point ".$objNaviPoint->getSystemid().".\n";


        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "navigation";
    }
}
?>