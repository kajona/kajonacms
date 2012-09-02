<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_element_portalregistration.php 4148 2011-10-27 19:47:06Z sidler $                    *
********************************************************************************************************/

/**
 * Installer to install a login-element to use in the portal
 *
 * @package element_portalregistration
 * @author sidler@mulchprod.de
 */
class class_installer_element_portalregistration extends class_installer_base implements interface_installer {

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
            $strReturn .= $this->postUpdate_342_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

    public function postUpdate_342_349() {
        $strReturn = "Updating element portalregistration to 3.4.9...\n";
        $this->updateElementVersion("portalregistration", "3.4.9");
        return $strReturn;
    }
}
