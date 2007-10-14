<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_installer_sc_navigation.php                                                                   *
*   Interface of the navigations samplecontent                                                          *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_installer_sc_guestbook.php 1714 2007-09-06 19:29:59Z sidler $                            *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");
include_once(_systempath_."/class_modul_pages_page.php");

/**
 * Installer of the navigation samplecontent
 *
 * @package modul_navigation
 */
class class_installer_sc_navigation implements interface_sc_installer  {

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
        $strReturn .= "Creating new navigation-tree\n";
        include_once(_systempath_."/class_modul_navigation_tree.php");
        $objNaviTree = new class_modul_navigation_tree();
        $objNaviTree->setStrName("mainnavigation");
        $objNaviTree->saveObjectToDb();
        $strTreeId = $objNaviTree->getSystemid();
        $strReturn .= "ID of new navigation-tree: ".$strTreeId."\n";
        $strReturn .= "Creating navigation points\n";
        include_once(_systempath_."/class_modul_navigation_point.php");
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("Home");
        $objNaviPoint->setStrPageI("index");
        $objNaviPoint->saveObjectToDb($strTreeId);
        $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("News");
        $objNaviPoint->setStrPageI("");
        $objNaviPoint->saveObjectToDb($strTreeId);
        $strNewsPointID = $objNaviPoint->getSystemid();
        $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";

        $objModuleNews = class_modul_system_module::getModuleByName("news");
        if($objModuleNews != null) {
            $objNaviPoint = new class_modul_navigation_point();
            $objNaviPoint->setStrName("Details");
            $objNaviPoint->setStrPageI("newsdetails");
            $objNaviPoint->saveObjectToDb($strNewsPointID);
            $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
        }

        $objModuleNews = class_modul_system_module::getModuleByName("guestbook");
        if($objModuleNews != null) {
            $objNaviPoint = new class_modul_navigation_point();
            if($this->strContentLanguage == "de")
                $objNaviPoint->setStrName("Gästebuch");
            else
                $objNaviPoint->setStrName("Guestbook");
            $objNaviPoint->setStrPageI("guestbook");
            $objNaviPoint->saveObjectToDb($strTreeId);
            $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
        }


        if($this->strMasterID != "") {
            $strReturn .= "Adding navigation to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("mastermainnavi_navigation");
            $objPagelement->setStrName("mastermainnavi");
            $objPagelement->setStrElement("navigation");
            $objPagelement->saveObjectToDb($this->strMasterID, "mastermainnavi_navigation", _dbprefix_."element_navigation", "first");
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_navigation
                            SET navigation_id='".dbsafeString($strTreeId)."',
                                navigation_template = 'mainnavi.tpl',
                                navigation_css = 'navi',
                                navigation_mode = 'tree'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "Navigation element created.\n";
            else
                $strReturn .= "Error creating navigation element.\n";
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
        return "navigation";
    }
}
?> 
 
