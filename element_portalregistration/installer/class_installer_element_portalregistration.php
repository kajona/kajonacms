<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Installer to install a login-element to use in the portal
 *
 * @package element_portalregistration
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_portalregistration extends class_elementinstaller_base implements interface_installer {

	public function install() {
		$strReturn = "";

       	//Table for page-element
		$strReturn .= "Installing formular-element table...\n";
        $objManager = new class_orm_schemamanager();
        $objManager->createTable("class_element_portalregistration_admin");

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

    public function remove(&$strReturn) {
        $bitReturn = parent::remove($strReturn);

        //delete the tables
        foreach(array("element_preg") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if(!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }

        }

        return $bitReturn;
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

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "4.3") {
            $strReturn .= "Updating element portalregistration to 4.3.1...\n";
            $this->updateElementVersion("portalregistration", "4.3.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "4.3.1") {
            $strReturn .= "Updating element portalregistration to 4.4...\n";
            $this->updateElementVersion("portalregistration", "4.4");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "4.4") {
            $strReturn .= "Updating element portalregistration to 4.5...\n";
            $this->updateElementVersion("portalregistration", "4.5");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "4.5") {
            $strReturn .= "Updating element portalregistration to 4.6...\n";
            $this->updateElementVersion("portalregistration", "4.6");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("portalregistration")->getStrVersion() == "4.6") {
            $strReturn .= "Updating element portalregistration to 4.7...\n";
            $this->updateElementVersion("portalregistration", "4.7");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }


}
