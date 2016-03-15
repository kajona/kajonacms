<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

namespace Kajona\Votings\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\Carrier;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\Votings\System\VotingsVoting;

/**
 * Class providing an installer for the votings module
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 * @moduleId _votings_module_id_
 */
class InstallerVotings extends InstallerBase implements InstallerRemovableInterface {

    public function install() {
		$strReturn = "";
        $objManager = new OrmSchemamanager();
		$strReturn .= "Installing table votings_voting...\n";
        $objManager->createTable("Kajona\\Votings\\System\\VotingsVoting");

        $strReturn .= "Installing table votings_answer...\n";
        $objManager->createTable("Kajona\\Votings\\System\\VotingsAnswer");

		//register the module
		$strSystemID = $this->registerModule(
            $this->objMetadata->getStrTitle(),
            _votings_module_id_,
            "VotingsPortal.php",
            "VotingsAdmin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        //modify default rights to allow guests to vote
		$strReturn .= "Modifying modules' rights node...\n";
        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), $strSystemID, "right1");

        $strReturn .= "Registering votings-element...\n";
        if(PagesElement::getElement("votings") == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("votings");
            $objElement->setStrClassAdmin("ElementVotingsAdmin.php");
            $objElement->setStrClassPortal("ElementVotingsPortal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(1);
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
        $objElement = PagesElement::getElement("votings");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'votings'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element 'votings', aborting.\n";
            return false;
        }

        /** @var VotingsVoting $objOneObject */
        foreach(VotingsVoting::getObjectList() as $objOneObject) {
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
        foreach(array("votings_voting", "votings_answer") as $strOneTable) {
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
        if($arrModule["module_version"] == "1.2") {
            $strReturn .= "Updating 1.2 to 1.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.3");
            $this->objDB->flushQueryCache();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.3") {
            $strReturn .= "Updating 1.3 to 1.4...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.4");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.4");
            $this->objDB->flushQueryCache();
        }


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.4") {
            $strReturn .= "Updating 1.4 to 1.5...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.5");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.5");
            $this->objDB->flushQueryCache();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.5") {
            $strReturn .= "Updating 1.5 to 1.6...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.6");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.6");
            $this->objDB->flushQueryCache();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "1.6") {
            $strReturn .= "Updating to 1.7...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("votings", "1.7");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("votings", "1.7");
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
	}
    


}
