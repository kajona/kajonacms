<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                     *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");
include_once(_systempath_."/class_modul_pages_element.php");

/**
 * Installer to install a downloads_toplist-element to use in the portal
 *
 * @package modul_downloads
 */
class class_installer_element_downloads_toplist extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
    public function __construct() {
        $arrModule = array();
        $arrModule["version"]       = "3.2.0";
        $arrModule["name"]          = "element_downloads_toplist";
        $arrModule["name_lang"]     = "Element downloads_toplist";
        $arrModule["nummer2"]       = _pages_inhalte_modul_id_;
        parent::__construct($arrModule);
    }

    public function getNeededModules() {
        return array("system", "pages", "downloads", "rating");
    }
    
    public function getMinSystemVersion() {
        return "3.2.0";
    }

    public function hasPostInstalls() {
        //needed: pages
        try {
            $objModule1 = class_modul_system_module::getModuleByName("downloads");
            $objModule2 = class_modul_system_module::getModuleByName("rating");
            if($objModule1 == null || $objModule2 == null)
                return false;
        }
        catch (class_exception $objE) {
            return false;
        }

        //check, if not already existing
        $objElement = null;
        try {
            $objElement = class_modul_pages_element::getElement("downloadstoplist");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null)
            return true;

        return false;
    }

    public function install() {
    }

    public function postInstall() {
        $strReturn = "";

        //Register the element
        $strReturn .= "Registering downloads_toplist-element...\n";
        //check, if not already existing
        $objElement = null;
        try {
            $objElement = class_modul_pages_element::getElement("downloadstoplist");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null) {
            $objElement = new class_modul_pages_element();
            $objElement->setStrName("downloadstoplist");
            $objElement->setStrClassAdmin("class_element_downloads_toplist.php");
            $objElement->setStrClassPortal("class_element_downloads_toplist.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(0);
            $objElement->saveObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }
        return $strReturn;
    }


    public function update() {
    }
}
?>