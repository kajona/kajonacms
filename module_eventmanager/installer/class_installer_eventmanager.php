<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class providing an installer for the eventmanager module
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 */
class class_installer_eventmanager extends class_installer_base implements interface_installer {

	public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
        $this->setArrModuleEntry("moduleId", _eventmanager_module_id_);
        parent::__construct();
	}

    public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//eventmanager cat-------------------------------------------------------------------------------------
		$strReturn .= "Installing table em_event...\n";

		$arrFields = array();
		$arrFields["em_ev_id"]                        = array("char20", false);
		$arrFields["em_ev_title"]                     = array("char254", true);
		$arrFields["em_ev_description"]               = array("text", true);
		$arrFields["em_ev_location"]                  = array("char254", true);
		$arrFields["em_ev_participant_registration"]  = array("int", true);
		$arrFields["em_ev_participant_limit"]         = array("int", true);
		$arrFields["em_ev_participant_max"]           = array("int", true);

		if(!$this->objDB->createTable("em_event", $arrFields, array("em_ev_id")))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Installing table em_participant...\n";

		$arrFields = array();
		$arrFields["em_pt_id"]           = array("char20", false);
		$arrFields["em_pt_forename"]     = array("char254", true);
		$arrFields["em_pt_lastname"]     = array("char254", true);
		$arrFields["em_pt_email"]        = array("char254", true);
		$arrFields["em_pt_phone"]        = array("char254", true);
		$arrFields["em_pt_comment"]      = array("text", true);

		if(!$this->objDB->createTable("em_participant", $arrFields, array("em_pt_id")))
			$strReturn .= "An error occured! ...\n";

		//register the module
		$strSystemID = $this->registerModule(
            "eventmanager",
            _eventmanager_module_id_,
            "class_module_eventmanager_portal.php",
            "class_module_eventmanager_admin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        //modify default rights to allow guests to participate
		$strReturn .= "Modifying modules' rights node...\n";
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right1");

        $strReturn .= "Registering eventmanager-element...\n";
        //check, if not already existing
        if(class_module_pages_element::getElement("eventmanager") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("eventmanager");
            $objElement->setStrClassAdmin("class_element_eventmanager_admin.php");
            $objElement->setStrClassPortal("class_element_eventmanager_portal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else
            $strReturn .= "Element already installed!...\n";

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

		return $strReturn;

	}



	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        return $strReturn."\n\n";
	}

    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";

        $strReturn .= "Events\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."em_event, "._dbprefix_."system WHERE system_id = em_ev_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_eventmanager_event', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Participants\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."em_participant, "._dbprefix_."system WHERE system_id = em_pt_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_eventmanager_particpant', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("eventmanager", "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("eventmanager", "3.4.9");
        return $strReturn;
    }
}
