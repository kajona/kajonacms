<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Userlist\Installer;

use class_installer_base;
use class_module_system_module;
use interface_installer_removable;
use Kajona\Pages\System\PagesElement;

/**
 * Installer to install a login-element to use in the portal
 *
 * @author sidler@mulchprod.de
 * @moduleId _userlist_module_id_
 */
class InstallerUserlist extends class_installer_base implements interface_installer_removable {

	public function install() {
		$strReturn = "";

        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _userlist_module_id_, "", "", $this->objMetadata->getStrVersion(), false);

        //Register the element
        $strReturn .= "Registering userlist-element...\n";
        //check, if not already existing
        $objElement = PagesElement::getElement("userlist");
        if($objElement == null) {
            $objElement = new PagesElement();
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

            if($objElement->getStrVersion() < 1) {
                $strReturn .= "Updating element version!...\n";
                $objElement->setStrVersion("1.0");
                $objElement->updateObjectToDb();
            }
        }
        return $strReturn;
    }


    /**
     * @return string
     */
    public function update() {
        $strReturn = "";

        $arrModule = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.0") {
            $strReturn .= "Updating 1.0 to 1.1...\n";
            $this->updateElementAndModule("1.1");
        }

        return $strReturn;
    }

    /**
     * @inheritdoc
     */
    public function isRemovable() {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function remove(&$strReturn) {
        return $this->removeModuleAndElement($strReturn);
    }
}
