<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Lastmodified\Installer;

use class_installer_base;
use class_module_system_module;
use interface_installer;
use Kajona\Pages\System\PagesElement;

/**
 * Installer to install a lastmodified-element to use in the portal
 *
 * @author sidler@mulchprod.de
 * @moduleId _lastmodified_module_id_
 */
class InstallerLastmodified extends class_installer_base implements interface_installer {

	public function install() {
		$strReturn = "";

        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _lastmodified_module_id_, "", "", $this->objMetadata->getStrVersion(), false);

        //Register the element
		$strReturn .= "Registering lastmodified-element...\n";
		//check, if not already existing
        $objElement = PagesElement::getElement("lastmodified");
        if($objElement == null) {
		    $objElement = new PagesElement();
		    $objElement->setStrName("lastmodified");
		    $objElement->setStrClassAdmin("ElementLastmodifiedAdmin.php");
		    $objElement->setStrClassPortal("ElementLastmodifiedPortal.php");
		    $objElement->setIntCachetime(60);
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
