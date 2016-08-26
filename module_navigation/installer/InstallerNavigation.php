<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

namespace Kajona\Navigation\Installer;

use Kajona\Navigation\System\NavigationTree;
use Kajona\Pages\System\PagesElement;
use Kajona\System\System\Exception;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;

/**
 * Installer of the navigation
 *
 * @package module_navigation
 * @moduleId _navigation_modul_id_
 */
class InstallerNavigation extends InstallerBase implements InstallerRemovableInterface {

    public function install() {

		$strReturn = "Installing ".$this->objMetadata->getStrTitle()."...\n";
        $objManager = new OrmSchemamanager();

		$strReturn .= "Installing table navigation...\n";
        $objManager->createTable("Kajona\\Navigation\\System\\NavigationPoint");

		//register the module
		$this->registerModule("navigation", _navigation_modul_id_, "NavigationPortal.php", "NavigationAdmin.php", $this->objMetadata->getStrVersion() , true);

        $strReturn .= "Installing navigation-element table...\n";
        $objManager->createTable("Kajona\\Navigation\\Admin\\Elements\\ElementNavigationAdmin");

        //Register the element
        $strReturn .= "Registering navigation-element...\n";
        //check, if not already existing
        $objElement = null;
        try {
            $objElement = PagesElement::getElement("navigation");
        }
        catch (Exception $objEx)  {
        }
        if($objElement == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("navigation");
            $objElement->setStrClassAdmin("ElementNavigationAdmin.php");
            $objElement->setStrClassPortal("ElementNavigationPortal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(SystemAspect::getAspectByName("content") != null) {
            $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(SystemAspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }


		return $strReturn;

	}

    /**
     * Validates whether the current module/element is removable or not.
     * This is the place to trigger special validations and consistency checks going
     * beyond the common metadata-dependencies.
     *
     * @return bool
     */
    public function isRemovable() {
        return true;
    }

    /**
     * Removes the elements / modules handled by the current installer.
     * Use the reference param to add a human readable logging.
     *
     * @param string &$strReturn
     *
     * @return bool
     */
    public function remove(&$strReturn) {

        //delete the page-element
        $objElement = PagesElement::getElement("navigation");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'navigation'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element 'navigation', aborting.\n";
            return false;
        }

        /** @var NavigationTree $objOneObject */
        foreach(NavigationTree::getObjectListFiltered() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach(array("navigation", "element_navigation") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if(!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }

        }

        return true;
    }


    public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";
        

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn = "Updating to 4.7...\n";
            $this->updateModuleVersion("navigation", "4.7");
            $this->updateElementVersion("navigation", "4.7");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn = "Updating to 5.0...\n";
            $this->updateModuleVersion("navigation", "5.0");
            $this->updateElementVersion("navigation", "5.0");
        }


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0") {
            $strReturn = "Updating to 5.1...\n";
            $this->updateModuleVersion("navigation", "5.1");
            $this->updateElementVersion("navigation", "5.1");
        }

        return $strReturn."\n\n";
	}
    
}
