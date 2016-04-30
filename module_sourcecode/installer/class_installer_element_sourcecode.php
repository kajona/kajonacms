<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Installer to install a sourcecode-element to use in the portal
 *
 * @package element_sourcecode
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_sourcecode extends class_elementinstaller_base implements interface_installer {

	public function install() {
		$strReturn = "";

		//Register the element
		$strReturn .= "Registering sourcecode-element...\n";
		//check, if not already existing
		if(class_module_pages_element::getElement("sourcecode") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("sourcecode");
		    $objElement->setStrClassAdmin("class_element_sourcecode_admin.php");
		    $objElement->setStrClassPortal("class_element_sourcecode_portal.php");
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


        return $strReturn;
    }


}
