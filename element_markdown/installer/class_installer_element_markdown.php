<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Installer to install a markdown-element to use in the portal
 *
 * @package element_markdown
 * @author sidler@mulchprod.de
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_markdown extends class_elementinstaller_base implements interface_installer {

	public function install() {
		$strReturn = "";

		//Register the element
		$strReturn .= "Registering markdown-element...\n";
		//check, if not already existing
		if(class_module_pages_element::getElement("markdown") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("markdown");
		    $objElement->setStrClassAdmin("class_element_markdown_admin.php");
		    $objElement->setStrClassPortal("class_element_markdown_portal.php");
		    $objElement->setIntCachetime(3600);
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

        return $strReturn;
    }


}
