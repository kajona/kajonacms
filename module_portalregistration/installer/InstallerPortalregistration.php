<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Portalregistration\Installer;

use class_installer_base;
use class_module_system_module;
use class_orm_schemamanager;
use interface_installer_removable;
use Kajona\Pages\System\PagesElement;

/**
 * Installer to install a login-element to use in the portal
 *
 * @author sidler@mulchprod.de
 * @moduleId _portalregistration_module_id_
 */
class InstallerPortalregistration extends class_installer_base implements interface_installer_removable {

	public function install() {
		$strReturn = "";

        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _portalregistration_module_id_, "", "", $this->objMetadata->getStrVersion(), false);

       	//Table for page-element
		$strReturn .= "Installing formular-element table...\n";
        $objManager = new class_orm_schemamanager();
        $objManager->createTable("class_element_portalregistration_admin");

		//Register the element
		$strReturn .= "Registering portalregistration-element...\n";
		//check, if not already existing
        $objElement = PagesElement::getElement("portalregistration");
        if($objElement == null) {
		    $objElement = new PagesElement();
		    $objElement->setStrName("portalregistration");
		    $objElement->setStrClassAdmin("ElementPortalregistrationAdmin.php");
		    $objElement->setStrClassPortal("ElementPortalregistrationPortal.php");
		    $objElement->setIntCachetime(-1);
		    $objElement->setIntRepeat(1);
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

    public function remove(&$strReturn) {


        //delete the tables
        foreach(array("element_preg") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if(!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }

        }

        return $this->removeModuleAndElement($strReturn);
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


}
