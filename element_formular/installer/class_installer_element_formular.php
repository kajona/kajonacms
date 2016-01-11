<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Installer to install a form-element (provides a basic contact form)
 *
 * @package element_formular
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_formular extends class_elementinstaller_base implements interface_installer {

    public function install() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing formular-element table...\n";
        $objManager = new class_orm_schemamanager();
        $objManager->createTable("class_element_formular_admin");

		//Register the element
		$strReturn .= "Registering formular-element...\n";
        if(class_module_pages_element::getElement("form") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("form");
            $objElement->setStrClassAdmin("class_element_formular_admin.php");
            $objElement->setStrClassPortal("class_element_formular_portal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(0);
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
        foreach(array("element_formular") as $strOneTable) {
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

        if(class_module_pages_element::getElement("form")->getStrVersion() == "3.4.2") {
            $strReturn .= $this->postUpdate_342_349();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("form")->getStrVersion() == "3.4.9") {
            $strReturn .= "Updating element form to 4.0...\n";
            $this->updateElementVersion("form", "4.0");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("form")->getStrVersion() == "4.0") {
            $strReturn .= "Updating element form to 4.1...\n";
            $this->updateElementVersion("form", "4.1");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("form")->getStrVersion() == "4.1") {
            $strReturn .= "Updating element form to 4.2...\n";
            $this->updateElementVersion("form", "4.2");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("form")->getStrVersion() == "4.2") {
            $strReturn .= "Updating element form to 4.3...\n";
            $this->updateElementVersion("form", "4.3");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("form")->getStrVersion() == "4.3") {
            $strReturn .= "Updating element form to 4.4...\n";
            $this->updateElementVersion("form", "4.4");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("form")->getStrVersion() == "4.4") {
            $strReturn .= "Updating element form to 4.5...\n";
            $this->updateElementVersion("form", "4.5");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("form")->getStrVersion() == "4.5") {
            $strReturn .= "Updating element form to 4.6...\n";
            $this->updateElementVersion("form", "4.6");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("form")->getStrVersion() == "4.6") {
            $strReturn .= "Updating element form to 4.7...\n";
            $this->updateElementVersion("form", "4.7");
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement("form")->getStrVersion() == "4.7") {
            $strReturn .= "Updating element form to 4.7.1...\n";
            $this->updateElementVersion("form", "4.7.1");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }


    public function postUpdate_342_349() {
        $strReturn = "Updating element form to 3.4.9...\n";

        $strReturn .= "Updating element-classes...\n";
        $strQuery = "UPDATE "._dbprefix_."element_formular SET formular_class = ? where formular_class = ?";
        $this->objDB->_pQuery($strQuery, array("class_formular_contact.php", "class_formular_kontakt.php"));

        $this->updateElementVersion("form", "3.4.9");
        return $strReturn;
    }


}
