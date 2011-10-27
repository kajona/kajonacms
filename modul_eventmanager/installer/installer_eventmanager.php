<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class providing an installer for the eventmanager module
 *
 * @package modul_eventmanager
 */
class class_installer_eventmanager extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.4.1";
		$arrModule["name"] 			  = "eventmanager";
		$arrModule["name_lang"] 	  = "Module Eventmanager";
		$arrModule["moduleId"] 		  = _eventmanager_modul_id_;
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}

    public function getMinSystemVersion() {
	    return "3.3.1.8";
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='eventmanager'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

        return false;
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
		$strSystemID = $this->registerModule("eventmanager", _eventmanager_modul_id_, "class_modul_eventmanager_portal.php", "class_modul_eventmanager_admin.php", $this->arrModule["version"], true);

        //modify default rights to allow guests to participate
		$strReturn .= "Modifying modules' rights node...\n";
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right1");

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Register the element
		$strReturn .= "Registering eventmanager-element...\n";
		//check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("eventmanager");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("eventmanager");
		    $objElement->setStrClassAdmin("class_element_eventmanager.php");
		    $objElement->setStrClassPortal("class_element_eventmanager.php");
		    $objElement->setIntCachetime(-1);
		    $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->getVersion());
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
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.1") {
            $strReturn .= $this->update_3311_3318();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.8") {
            $strReturn .= $this->update_3318_340();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0") {
            $strReturn .= $this->update_340_3401();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0.1") {
            $strReturn .= $this->update_3401_341();
        }

        return $strReturn."\n\n";
	}

    private function update_3311_3318() {
        $strReturn = "Updating 3.3.1.1 to 3.3.1.8...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("eventmanager", "3.3.1.8");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("eventmanager", "3.3.1.8");
        return $strReturn;
    }

    private function update_3318_340() {
        $strReturn = "Updating 3.3.1.8 to 3.4.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("eventmanager", "3.4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("eventmanager", "3.4.0");
        return $strReturn;
    }


    private function update_340_3401() {
        $strReturn = "Updating 3.4.0 to 3.4.0.1...\n";

        $strReturn .= "Updating eventmanager_event-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."eventmanager_event")."
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_event_id")." ".$this->objDB->encloseColumnName("em_ev_id")." ".$this->objDB->getDatatype("char20")." NOT NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_event_title")." ".$this->objDB->encloseColumnName("em_ev_title")." ".$this->objDB->getDatatype("char254")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_event_description")." ".$this->objDB->encloseColumnName("em_e_description")." ".$this->objDB->getDatatype("text")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_event_location")." ".$this->objDB->encloseColumnName("em_ev_location")." ".$this->objDB->getDatatype("char254")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_event_participant_registration")." ".$this->objDB->encloseColumnName("em_ev_participant_registration")." ".$this->objDB->getDatatype("int")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_event_participant_limit")." ".$this->objDB->encloseColumnName("em_ev_participant_limit")." ".$this->objDB->getDatatype("int")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_event_participant_max")." ".$this->objDB->encloseColumnName("em_ev_participant_max")." ".$this->objDB->getDatatype("int")." NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating eventmanager_participant-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."eventmanager_participant")."
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_participant_id")." ".$this->objDB->encloseColumnName("em_pt_id")." ".$this->objDB->getDatatype("char20")." NOT NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_participant_forename")." ".$this->objDB->encloseColumnName("em_pt_forename")." ".$this->objDB->getDatatype("char254")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_participant_lastname")." ".$this->objDB->encloseColumnName("em_pt_lastname")." ".$this->objDB->getDatatype("char254")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_participant_email")." ".$this->objDB->encloseColumnName("em_pt_email")." ".$this->objDB->getDatatype("char254")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_participant_phone")." ".$this->objDB->encloseColumnName("em_pt_phone")." ".$this->objDB->getDatatype("char254")." NULL,
                    CHANGE ".$this->objDB->encloseColumnName("eventmanager_participant_comment")." ".$this->objDB->encloseColumnName("em_pt_comment")." ".$this->objDB->getDatatype("text")." NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strQuery = "RENAME TABLE ".$this->objDB->encloseTableName(_dbprefix_."eventmanager_event")." TO ".$this->objDB->encloseTableName(_dbprefix_."em_event")."";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strQuery = "RENAME TABLE ".$this->objDB->encloseTableName(_dbprefix_."eventmanager_participant")." TO ".$this->objDB->encloseTableName(_dbprefix_."em_participant")."";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("eventmanager", "3.4.0.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("eventmanager", "3.4.0.1");
        return $strReturn;
    }


    private function update_3401_341() {
        $strReturn = "Updating 3.4.0.1 to 3.4.1...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("eventmanager", "3.4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("eventmanager", "3.4.1");
        return $strReturn;
    }
}
?>