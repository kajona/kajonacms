<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_system.php 4552 2012-03-12 08:36:48Z sidler $                                        *
********************************************************************************************************/


/**
 * Installer for the system-module
 *
 * @package element_languageswitch
 * @author sidler@mulchprod.de
 */
class class_installer_element_languageswitch extends class_installer_base implements interface_installer {

    private $strContentLanguage;

    public function __construct() {

        $this->setArrModuleEntry("version", "3.4.9");
        $this->setArrModuleEntry("moduleId", _languages_modul_id_);
        $this->setArrModuleEntry("name", "element_languageswitch");
        $this->setArrModuleEntry("name_lang", "Element Languageswitch");

        parent::__construct();

        //set the correct language
        $this->strContentLanguage = $this->objSession->getAdminLanguage();
    }


    public function getMinSystemVersion() {
        return "3.4.9";
    }

    public function getNeededModules() {
        return array("system", "pages", "languages");
    }

    public function hasPostInstalls() {
        //check, if not already existing
        $objElement = class_module_pages_element::getElement("languageswitch");
        if($objElement === null)
            return true;

        return false;
    }

    public function postInstall() {
        //Register the element
        $strReturn = "Registering languageswitch-element...\n";

        //check, if not already existing
        $objElement = class_module_pages_element::getElement("languageswitch");
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("languageswitch");
            $objElement->setStrClassAdmin("class_element_languageswitch_admin.php");
            $objElement->setStrClassPortal("class_element_languageswitch_portal.php");
            $objElement->setIntCachetime(3600*24*30);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->getVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        return $strReturn;
    }


    public function install() {
        return "";
    }

    public function hasPostUpdates() {
        $objElement = null;
        try {
            $objElement = class_module_pages_element::getElement("languageswitch");
            if($objElement != null && version_compare($this->arrModule["version"], $objElement->getStrVersion(), ">"))
                return true;
        }
        catch (class_exception $objEx)  {
        }

        return false;
    }

    public function update() {
        return "";
    }

    public function postUpdate() {
        $strReturn = "";

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.0") {
            $strReturn .= $this->update_340_3401();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.0.1") {
            $strReturn .= $this->update_3401_3402();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.0.2") {
            $strReturn .= $this->update_3402_341();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.1") {
            $strReturn .= $this->update_341_349();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.1.1") {
            $strReturn .= $this->update_341_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
    }


    private function update_340_3401() {
        $strReturn = "Updating 3.4.0 to 3.4.0.1...\n";
        $this->updateElementVersion("languageswitch", "3.4.0.1");
        return $strReturn;
    }


    private function update_3401_3402() {
        $strReturn = "Updating 3.4.0.1 to 3.4.0.2...\n";
        $this->updateElementVersion("languageswitch", "3.4.0.2");
        return $strReturn;
    }

    private function update_3402_341() {
        $strReturn = "Updating 3.4.0.2 to 3.4.1...\n";
        $this->updateElementVersion("languageswitch", "3.4.1");
        return $strReturn;
    }


    private function update_341_349() {
        $strReturn = "Updating 3.4.1 to 3.4.9...\n";
        $this->updateElementVersion("languageswitch", "3.4.9");
        return $strReturn;
    }



}
