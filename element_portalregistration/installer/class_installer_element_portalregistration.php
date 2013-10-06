<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                    *
********************************************************************************************************/

/**
 * Installer to install a login-element to use in the portal
 *
 * @package element_portalregistration
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_portalregistration extends class_installer_base implements interface_installer {

	public function install() {
		$strReturn = "";

       	//Table for page-element
		$strReturn .= "Installing formular-element table...\n";

		$arrFields = array();
		$arrFields["content_id"] 				   = array("char20", false);
		$arrFields["portalregistration_template"]  = array("char254", true);
		$arrFields["portalregistration_group"] 	   = array("char254", true);
		$arrFields["portalregistration_success"]   = array("char254", true);

		if(!$this->objDB->createTable("element_preg", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering portalregistration-element...\n";
		//check, if not already existing
		if(class_module_pages_element::getElement("portalregistration") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("portalregistration");
		    $objElement->setStrClassAdmin("class_element_portalregistration_admin.php");
		    $objElement->setStrClassPortal("class_element_portalregistration_portal.php");
		    $objElement->setIntCachetime(-1);
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

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "3.4.2") {
            $strReturn .= "Updating element portalregistration to 3.4.9...\n";
            $this->updateElementVersion("portalregistration", "3.4.9");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "3.4.9") {
            $strReturn .= "Updating element portalregistration to 4.0...\n";
            $this->updateElementVersion("portalregistration", "4.0");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "4.0") {
            $strReturn .= "Updating element portalregistration to 4.1...\n";
            $this->updateElementVersion("portalregistration", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "4.1") {
            $strReturn .= "Updating element portalregistration to 4.2...\n";
            $this->updateElementVersion("portalregistration", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "4.2") {
            $strReturn .= "Updating element portalregistration to 4.3...\n";
            $this->updateElementVersion("portalregistration", "4.3");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }


}
