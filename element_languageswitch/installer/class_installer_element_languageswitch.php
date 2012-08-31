<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * Installer for the languageswitch element
 *
 * @package element_languageswitch
 * @author sidler@mulchprod.de
 */
class class_installer_element_languageswitch extends class_installer_base implements interface_installer {


    public function __construct() {

        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
        $this->setArrModuleEntry("moduleId", _languages_modul_id_);
        parent::__construct();

    }


    public function install() {

        //Register the element
        $strReturn = "Registering languageswitch-element...\n";

        //check, if not already existing
        $objElement = class_module_pages_element::getElement("languageswitch");
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("languageswitch");
            $objElement->setStrClassAdmin("class_element_languageswitch_admin.php");
            $objElement->setStrClassPortal("class_element_languageswitch_portal.php");
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

        if(class_module_pages_element::getElement("languageswitch")->getStrVersion() == "3.4.2") {
            $strReturn .= $this->update_342_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
    }



    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";
        $this->updateElementVersion("languageswitch", "3.4.9");
        return $strReturn;
    }



}
