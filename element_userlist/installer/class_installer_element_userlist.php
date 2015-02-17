<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/

/**
 * Installer to install a login-element to use in the portal
 *
 * @package element_userlist
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_userlist extends class_elementinstaller_base implements interface_installer {

	public function install() {
		$strReturn = "";

		//Register the element
        $strReturn .= "Registering userlist-element...\n";
        //check, if not already existing
        if(class_module_pages_element::getElement("userlist") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("userlist");
            $objElement->setStrClassAdmin("class_element_userlist_admin.php");
            $objElement->setStrClassPortal("class_element_userlist_portal.php");
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
        if(class_module_pages_element::getElement("userlist")->getStrVersion() == "0.2") {
            $strReturn = "Updating element userlist to 0.3...\n";
            $this->updateElementVersion("userlist", "0.3");
            $this->objDB->flushQueryCache();
        }
        if(class_module_pages_element::getElement("userlist")->getStrVersion() == "0.3") {
            $strReturn = "Updating element userlist to 0.4...\n";
            $this->updateElementVersion("userlist", "0.4");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }
    

    
}
