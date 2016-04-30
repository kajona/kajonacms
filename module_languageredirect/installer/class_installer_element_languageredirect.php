<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: installer_element_downloads_toplist.php 4161 2011-10-29 12:03:12Z sidler $                     *
********************************************************************************************************/

/**
 *
 * @package element_languageredirect
 * @author sidler@mulchprod.de
 *
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_languageredirect extends class_installer_base implements interface_installer {


    public function install() {
        $strReturn = "";
        //Register the element
        $strReturn .= "Registering languageredirect-element...\n";
        //check, if not already existing
        $objElement = null;
        if(class_module_pages_element::getElement("languageredirect") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("languageredirect");
            $objElement->setStrClassAdmin("class_element_languageredirect_admin.php");
            $objElement->setStrClassPortal("class_element_languageredirect_portal.php");
            $objElement->setIntCachetime(60);
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

        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "0.1") {
            $strReturn = "Updating element languageredirect to 0.2...\n";
            $this->updateElementVersion("languageredirect", "0.2");
            $this->objDB->flushQueryCache();
        }


        if(class_module_pages_element::getElement($this->objMetadata->getStrTitle())->getStrVersion() == "0.2") {
            $strReturn = "Updating element languageredirect to 0.3...\n";
            $this->updateElementVersion("languageredirect", "0.3");
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

}
