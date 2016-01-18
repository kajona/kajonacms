<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flash\Installer;

use class_elementinstaller_base;
use class_module_system_module;
use interface_installer_removable;
use Kajona\Pages\System\PagesElement;

/**
 * Installer to install a flash-element to use in the portal
 *
 * @author jschroeter@kajona.de
 * @moduleId _flash_module_id_
 */
class InstallerFlash extends class_elementinstaller_base implements interface_installer_removable {

	public function install() {

        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _flash_module_id_, "", "", $this->objMetadata->getStrVersion(), false);


        $strReturn = "";
		//Register the element
		$strReturn .= "Registering flash-element...\n";
        $objElement = PagesElement::getElement($this->objMetadata->getStrTitle());
        if($objElement == null) {
		    $objElement = new PagesElement();
		    $objElement->setStrName($this->objMetadata->getStrTitle());
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

            if($objElement->getStrVersion() < 5) {
                $strReturn .= "Updating element version!...\n";
                $objElement->setStrVersion("5.0");
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
        if($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating 5.0 to 5.1...\n";
            $this->updateElementAndModule("5.1");
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
