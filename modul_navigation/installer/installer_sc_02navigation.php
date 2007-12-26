<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_installer_sc_navigation.php                                                                   *
*   Interface of the navigations samplecontent                                                          *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                            *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");
include_once(_systempath_."/class_modul_pages_page.php");

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
 
