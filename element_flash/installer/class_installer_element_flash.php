<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * Installer to install a flash-element to use in the portal
 *
 * @package element_flash
 * @author jschroeter@kajona.de
 */
class class_installer_element_flash extends class_installer_base implements interface_installer {

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
		$strReturn .= "Registering flash-element...\n";
		if(class_module_pages_element::getElement("flash") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("flash");
		    $objElement->setStrClassAdmin("class_element_flash_admin.php");
		    $objElement->setStrClassPortal("class_element_flash_portal.php");
		    $objElement->setIntCachetime(3600);
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

        if(class_module_pages_element::getElement("flash")->getStrVersion() == "3.4.2") {
            $strReturn .= $this->postUpdate_342_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
	}

    public function postUpdate_342_349() {
        $strReturn = "Updating element flash to 3.4.9...\n";
        $this->updateElementVersion("flash", "3.4.9");
        return $strReturn;
    }
}
