<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Installer to install a login-element to use in the portal
 *
 * @package element_portallogin
 * @author sidler@mulchprod.de
 */
class class_installer_element_portallogin extends class_installer_base implements interface_installer {

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
		$strReturn .= "Installing element_plogin-element table...\n";

		$arrFields = array();
		$arrFields["content_id"] 				= array("char20", false);
		$arrFields["portallogin_template"] 		= array("char254", true);
		$arrFields["portallogin_error"] 		= array("char254", true);
		$arrFields["portallogin_success"] 		= array("char254", true);
		$arrFields["portallogin_logout_success"]= array("char254", true);
        $arrFields["portallogin_profile"]       = array("char254", true);
        $arrFields["portallogin_pwdforgot"]     = array("char254", true);
        $arrFields["portallogin_editmode"]      = array("int", true);

		if(!$this->objDB->createTable("element_plogin", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering portallogin-element...\n";
		//check, if not already existing
		if(class_module_pages_element::getElement("portallogin") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("portallogin");
		    $objElement->setStrClassAdmin("class_element_portallogin_admin.php");
		    $objElement->setStrClassPortal("class_element_portallogin_portal.php");
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

        if(class_module_pages_element::getElement("portallogin")->getStrVersion() == "3.4.2") {
            $strReturn .= $this->postUpdate_342_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

    public function postUpdate_342_349() {
        $strReturn = "Updating element portallogin to 3.4.9...\n";
        $this->updateElementVersion("portallogin", "3.4.9");
        return $strReturn;
    }
}
