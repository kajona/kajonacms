<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_element_portalupload.php 4704 2012-06-21 13:36:17Z sidler $                          *
********************************************************************************************************/

/**
 * Installer to install a login-element to use in the portal
 *
 * @package element_portalupload
 */
class class_installer_element_portalupload extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
    public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
        $this->setArrModuleEntry("moduleId", _pages_content_modul_id_);
        parent::__construct();
    }



    public function install() {
		$strReturn = "";

		//Register the element
        $strReturn .= "Registering portalupload-element...\n";
        //check, if not already existing
        if(class_module_pages_element::getElement("portalupload") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("portalupload");
            $objElement->setStrClassAdmin("class_element_portalupload_admin.php");
            $objElement->setStrClassPortal("class_element_portalupload_portal.php");
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


    public function update() {
        $strReturn = "";
        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.2.0.9") {
            $strReturn .= $this->postUpdate_3209_321();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.2.1") {
            $strReturn .= $this->postUpdate_321_330();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.3.0") {
            $strReturn .= $this->postUpdate_330_3301();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.3.0.1") {
            $strReturn .= $this->postUpdate_3301_331();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.3.1") {
            $strReturn .= $this->postUpdate_331_340();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.4.0") {
            $strReturn .= $this->postUpdate_340_341();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.4.1") {
            $strReturn .= $this->postUpdate_341_342();
            $this->objDB->flushQueryCache();
        }

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "3.4.2") {
            $strReturn .= $this->postUpdate_342_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

    public function postUpdate_3209_321() {
        $strReturn = "Updating element portalupload to 3.2.1...\n";
        $this->updateElementVersion("portalupload", "3.2.1");
        return $strReturn;
    }

    public function postUpdate_321_330() {
        $strReturn = "Updating element portalupload to 3.3.0...\n";
        $this->updateElementVersion("portalupload", "3.3.0");
        return $strReturn;
    }

    public function postUpdate_330_3301() {
        $strReturn = "Updating element portalupload to 3.3.0.1...\n";
        $this->updateElementVersion("portalupload", "3.3.0.1");
        return $strReturn;
    }

    public function postUpdate_3301_331() {
        $strReturn = "Updating element portalupload to 3.3.1...\n";
        $this->updateElementVersion("portalupload", "3.3.1");
        return $strReturn;
    }

    public function postUpdate_331_340() {
        $strReturn = "Updating element portalupload to 3.4.0...\n";
        $this->updateElementVersion("portalupload", "3.4.0");
        return $strReturn;
    }

    public function postUpdate_340_341() {
        $strReturn = "Updating element portalupload to 3.4.1...\n";
        $this->updateElementVersion("portalupload", "3.4.1");
        return $strReturn;
    }

    public function postUpdate_341_342() {
        $strReturn = "Updating element portalupload to 3.4.2...\n";
        $this->updateElementVersion("portalupload", "3.4.2");
        return $strReturn;
    }

    public function postUpdate_342_349() {
        $strReturn = "Updating element portalupload to 3.4.9...\n";
        $this->updateElementVersion("portalupload", "3.4.9");
        return $strReturn;
    }
}
