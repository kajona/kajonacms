<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

namespace Kajona\Eventmanager\Installer;

use Kajona\Eventmanager\System\EventmanagerEvent;
use Kajona\Pages\System\PagesElement;
use Kajona\System\System\Carrier;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

/**
 * Class providing an installer for the eventmanager module
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @moduleId _eventmanager_module_id_
 */
class InstallerEventmanager extends InstallerBase implements InstallerRemovableInterface {

    public function install() {
		$strReturn = "";
        $objManager = new OrmSchemamanager();

		$strReturn .= "Installing table em_event...\n";
        $objManager->createTable("Kajona\\Eventmanager\\System\\EventmanagerEvent");

        $strReturn .= "Installing table em_participant...\n";
        $objManager->createTable("Kajona\\Eventmanager\\System\\EventmanagerParticipant");

		//register the module
		$strSystemID = $this->registerModule(
            "eventmanager",
            _eventmanager_module_id_,
            "EventmanagerPortal.php",
            "EventmanagerAdmin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        //modify default rights to allow guests to participate
		$strReturn .= "Modifying modules' rights node...\n";
        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), $strSystemID, "right1");

        $strReturn .= "Registering eventmanager-element...\n";
        //check, if not already existing
        if(PagesElement::getElement("eventmanager") == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("eventmanager");
            $objElement->setStrClassAdmin("ElementEventmanagerAdmin.php");
            $objElement->setStrClassPortal("ElementEventmanagerPortal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else
            $strReturn .= "Element already installed!...\n";

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
        $objElement = PagesElement::getElement("eventmanager");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'eventmanager'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element 'eventmanager', aborting.\n";
            return false;
        }

        /** @var \Kajona\Eventmanager\System\EventmanagerEvent $objOneObject */
        foreach(EventmanagerEvent::getObjectListFiltered() as $objOneObject) {
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
        foreach(array("em_event", "em_participant") as $strOneTable) {
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
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("eventmanager", "4.7");
            $this->updateElementVersion("eventmanager", "4.7");
        }
        
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn .= "Updating to 5.0...\n";
            $this->updateModuleVersion("eventmanager", "5.0");
            $this->updateElementVersion("eventmanager", "5.0");
        }
        
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0" || $arrModule["module_version"] == "5.0.1") {
            $strReturn .= "Updating to 5.1...\n";
            $this->updateModuleVersion("eventmanager", "5.1");
            $this->updateElementVersion("eventmanager", "5.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1") {
            $strReturn .= "Updating to 6.2...\n";
            $this->updateModuleVersion("eventmanager", "6.2");
            $this->updateElementVersion("eventmanager", "6.2");
        }

        return $strReturn."\n\n";
	}


}
