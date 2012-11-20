<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * Installer to install a mediaplayer-element to use in the portal
 *
 * @package element_mediaplayer
 * @author sidler@mulchprod.de
 */
class class_installer_element_mediaplayer extends class_installer_base implements interface_installer {

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
		$strReturn .= "Registering mediaplayer-element...\n";
		//check, if not already existing
		if(class_module_pages_element::getElement("mediaplayer") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("mediaplayer");
		    $objElement->setStrClassAdmin("class_element_mediaplayer_admin.php");
		    $objElement->setStrClassPortal("class_element_mediaplayer_portal.php");
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
        if(class_module_pages_element::getElement("mediaplayer")->getStrVersion() == "3.4.2") {
            $strReturn .= $this->postUpdate_342_349();
            $this->objDB->flushQueryCache();
        }
        return $strReturn;
    }


    public function postUpdate_342_349() {
        $strReturn = "Updating element mediaplayer to 3.4.9...\n";
        $this->updateElementVersion("mediaplayer", "3.4.9");
        return $strReturn;
    }
}
