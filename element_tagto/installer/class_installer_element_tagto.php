<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_element_tagto.php 4153 2011-10-29 12:02:11Z sidler $                                 *
********************************************************************************************************/

/**
 * Installer to install a tagto-element to use in the portal
 *
 * @package element_tagto
 */
class class_installer_element_tagto extends class_installer_base implements interface_installer {

    /**
     * Constructor
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
		$strReturn .= "Registering tagto-element...\n";
		//check, if not already existing
		if(class_module_pages_element::getElement("tagto") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("tagto");
		    $objElement->setStrClassAdmin("class_element_tagto_admin.php");
		    $objElement->setStrClassPortal("class_element_tagto_portal.php");
		    $objElement->setIntCachetime(3600*24*30);
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

        if(class_module_pages_element::getElement("tagto")->getStrVersion() == "3.4.2") {
            $strReturn .= $this->postUpdate_342_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

    public function postUpdate_3209_321() {
        $strReturn = "Updating element tagto to 3.2.1...\n";
        $this->updateElementVersion("tagto", "3.2.1");
        return $strReturn;
    }

    public function postUpdate_342_349() {
        $strReturn = "Updating element tagto to 3.4.9...\n";
        $this->updateElementVersion("tagto", "3.4.9");
        return $strReturn;
    }
}
