<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Installer to install a form-element (provides a basic contact form)
 *
 * @package element_maps
 * @author jschroeter@kajona.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_maps extends class_elementinstaller_base implements interface_installer {

    public function install() {
		$strReturn = "";

		//Register the element
		$strReturn .= "Registering maps-element...\n";
		//check, if not already existing
        if(class_module_pages_element::getElement("maps") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("maps");
            $objElement->setStrClassAdmin("class_element_maps_admin.php");
            $objElement->setStrClassPortal("class_element_maps_portal.php");
            $objElement->setIntCachetime(3600*24*30);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }
		return $strReturn;
	}


    public function update() {
        $strReturn = "";

        if(class_module_pages_element::getElement("maps")->getStrVersion() == "3.4.2" || class_module_pages_element::getElement("maps")->getStrVersion() == "3.4.0") {
            $strReturn .= "Updating element maps to 3.4.9...\n";
            $this->updateElementVersion("maps", "3.4.9");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("maps")->getStrVersion() == "3.4.9") {
            $strReturn .= "Updating element maps to 4.0...\n";
            $this->updateElementVersion("maps", "4.0");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("maps")->getStrVersion() == "4.0") {
            $strReturn .= "Updating element maps to 4.1...\n";
            $this->updateElementVersion("maps", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("maps")->getStrVersion() == "4.1") {
            $strReturn .= "Updating element maps to 4.2...\n";
            $this->updateElementVersion("maps", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("maps")->getStrVersion() == "4.2") {
            $strReturn .= "Updating element maps to 4.3...\n";
            $this->updateElementVersion("maps", "4.3");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("maps")->getStrVersion() == "4.3") {
            $strReturn .= "Updating element maps to 4.4...\n";
            $this->updateElementVersion("maps", "4.4");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("maps")->getStrVersion() == "4.4") {
            $strReturn .= "Updating element maps to 4.5...\n";
            $this->updateElementVersion("maps", "4.5");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("maps")->getStrVersion() == "4.5") {
            $strReturn .= "Updating element maps to 4.6...\n";
            $this->updateElementVersion("maps", "4.6");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("maps")->getStrVersion() == "4.6") {
            $strReturn .= "Updating element maps to 4.7...\n";
            $this->updateElementVersion("maps", "4.7");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }


}
