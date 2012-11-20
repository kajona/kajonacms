<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Installer to install a form-element (provides a basic contact form)
 *
 * @package element_maps
 * @author jschroeter@kajona.de
 */
class class_installer_element_maps extends class_installer_base implements interface_installer {

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
            $strReturn .= $this->postUpdate_342_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }



    public function postUpdate_342_349() {
        $strReturn = "Updating element maps to 3.4.9...\n";
        $this->updateElementVersion("maps", "3.4.9");
        return $strReturn;
    }
}
