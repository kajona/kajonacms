<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

/**
 * Installer to install the downloads-module
 *
 * @package module_mediamanager
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_downloads extends class_installer_base implements interface_installer {

	public function __construct() {
		parent::__construct();
        $this->objMetadata->setStrTitle("element_downloads");
        $this->objMetadata->setStrTitle("downloads");
        $this->objMetadata->setStrType(class_module_packagemanager_manager::STR_TYPE_ELEMENT);
	}

	public function install() {
		$strReturn = "";

        if(class_module_system_module::getModuleByName("mediamanager") == null)
            return "Mediamanger not installed, skipping element\n";

		//Table for page-element
		$strReturn .= "Installing downloads-element table...\n";
        $objManager = new class_orm_schemamanager();
        $objManager->createTable("class_element_downloads_admin");

		//Register the element
		$strReturn .= "Registering downloads-element...\n";
        if(class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("downloads") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("downloads");
            $objElement->setStrClassAdmin("class_element_downloads_admin.php");
            $objElement->setStrClassPortal("class_element_downloads_portal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed or pages module not installed!...\n";
        }
		return $strReturn;
	}



    public function update() {

        $strReturn = "";
        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "3.4.2") {
            $strReturn .= "Updating element downloads to 3.4.9...\n";
            $this->updateElementVersion("downloads", "3.4.9");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "3.4.9"
            || class_module_pages_element::getElement("downloads")->getStrVersion() == "3.4.9.1"
            || class_module_pages_element::getElement("downloads")->getStrVersion() == "3.4.9.2"
            || class_module_pages_element::getElement("downloads")->getStrVersion() == "3.4.9.3"
        ) {
            $strReturn .= "Updating element downloads to 4.0...\n";
            $this->updateElementVersion("downloads", "4.0");
            $this->objDB->flushQueryCache();
        }


        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "4.0") {
            $strReturn .= "Updating element downloads to 4.1...\n";
            $this->updateElementVersion("downloads", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "4.1") {
            $strReturn .= "Updating element downloads to 4.2...\n";
            $this->updateElementVersion("downloads", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "4.2") {
            $strReturn .= "Updating element downloads to 4.3...\n";
            $this->updateElementVersion("downloads", "4.3");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "4.3") {
            $strReturn .= "Updating element downloads to 4.4...\n";
            $this->updateElementVersion("downloads", "4.4");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "4.4") {
            $strReturn .= "Updating element downloads to 4.5...\n";
            $this->updateElementVersion("downloads", "4.5");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "4.5") {
            $strReturn .= "Updating element downloads to 4.6...\n";
            $this->updateElementVersion("downloads", "4.6");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "4.6") {
            $strReturn .= "Updating element downloads to 4.6.1...\n";
            $this->updateElementVersion("downloads", "4.6.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "4.6.1") {
            $strReturn .= "Updating element downloads to 4.6.2...\n";
            $this->updateElementVersion("downloads", "4.6.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "4.6.2") {
            $strReturn .= "Updating element downloads to 4.7...\n";
            $this->updateElementVersion("downloads", "4.7");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;

    }

}