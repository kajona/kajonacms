<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Installer to install the mediamanager-module
 *
 * @package module_mediamanager
 * @moduleId _mediamanager_module_id_
 */
class class_installer_element_gallery extends class_installer_base implements interface_installer {

	public function __construct() {
		parent::__construct();
        $this->objMetadata->setStrTitle("gallery");
        $this->objMetadata->setStrType(class_module_packagemanager_manager::STR_TYPE_ELEMENT);
	}

	public function install() {
		$strReturn = "";

        if(class_module_system_module::getModuleByName("mediamanager") == null)
            return "Mediamanger not installed, skipping element\n";

		//Table for page-element
		$strReturn .= "Installing gallery-element table...\n";
        $objManager = new class_orm_schemamanager();
        $objManager->createTable("class_element_gallery_admin");

		//Register the element
		$strReturn .= "Registering gallery-element...\n";
        $objElement = null;
		if(class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("gallery") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("gallery");
		    $objElement->setStrClassAdmin("class_element_gallery_admin.php");
		    $objElement->setStrClassPortal("class_element_gallery_portal.php");
		    $objElement->setIntCachetime(3600);
		    $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed or pages module not installed!...\n";
		}


		$strReturn .= "Registering galleryRandom-element...\n";
		if( class_module_system_module::getModuleByName("pages") !== null && class_module_pages_element::getElement("galleryRandom") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("galleryRandom");
		    $objElement->setStrClassAdmin("class_element_galleryRandom_admin.php");
		    $objElement->setStrClassPortal("class_element_gallery_portal.php");
		    $objElement->setIntCachetime(-1);
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

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.0") {
            $strReturn .= "Updating element gallery to 4.1...\n";
            $this->updateElementVersion("gallery", "4.1");
            $this->updateElementVersion("galleryRandom", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.1") {
            $strReturn .= "Updating element gallery to 4.2...\n";
            $this->updateElementVersion("gallery", "4.2");
            $this->updateElementVersion("galleryRandom", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.2") {
            $strReturn .= "Updating element gallery to 4.3...\n";
            $this->updateElementVersion("gallery", "4.3");
            $this->updateElementVersion("galleryRandom", "4.3");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.3") {
            $strReturn .= "Updating element gallery to 4.4...\n";
            $this->updateElementVersion("gallery", "4.4");
            $this->updateElementVersion("galleryRandom", "4.4");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.4") {
            $strReturn .= "Updating element gallery to 4.5...\n";
            $this->updateElementVersion("gallery", "4.5");
            $this->updateElementVersion("galleryRandom", "4.5");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.5") {
            $strReturn .= "Updating element gallery to 4.6...\n";
            $this->updateElementVersion("gallery", "4.6");
            $this->updateElementVersion("galleryRandom", "4.6");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.6") {
            $strReturn .= "Updating element gallery to 4.6.1...\n";
            $this->updateElementVersion("gallery", "4.6.1");
            $this->updateElementVersion("galleryRandom", "4.6.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.6.1") {
            $strReturn .= "Updating element gallery to 4.6.2...\n";
            $this->updateElementVersion("gallery", "4.6.2");
            $this->updateElementVersion("galleryRandom", "4.6.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.6.2") {
            $strReturn .= "Updating element gallery to 4.7...\n";
            $this->updateElementVersion("gallery", "4.7");
            $this->updateElementVersion("galleryRandom", "4.7");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "4.7") {
            $strReturn .= "Updating element gallery to 4.7.1...\n";
            $this->updateElementVersion("gallery", "4.7.1");
            $this->updateElementVersion("galleryRandom", "4.7.1");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }


}
