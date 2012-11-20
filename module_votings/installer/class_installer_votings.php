<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class providing an installer for the votings module
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 */
class class_installer_votings extends class_installer_base implements interface_installer {

	public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
        $this->setArrModuleEntry("moduleId", _votings_module_id_);
        parent::__construct();
	}

    public function install() {
		$strReturn = "";

		//votings cat-------------------------------------------------------------------------------------
		$strReturn .= "Installing table votings_voting...\n";

		$arrFields = array();
		$arrFields["votings_voting_id"] 		= array("char20", false);
		$arrFields["votings_voting_title"]      = array("char254", true);

		if(!$this->objDB->createTable("votings_voting", $arrFields, array("votings_voting_id")))
			$strReturn .= "An error occured! ...\n";

		//votings----------------------------------------------------------------------------------
		$strReturn .= "Installing table votings_answer...\n";

		$arrFields = array();
		$arrFields["votings_answer_id"]         = array("char20", false);
		$arrFields["votings_answer_text"]       = array("text", true);
		$arrFields["votings_answer_hits"]       = array("int", true);

		if(!$this->objDB->createTable("votings_answer", $arrFields, array("votings_answer_id")))
			$strReturn .= "An error occured! ...\n";

		//register the module
		$strSystemID = $this->registerModule(
            "votings",
            _votings_module_id_,
            "class_module_votings_portal.php",
            "class_module_votings_admin.php",
            $this->objMetadata->getStrVersion(),
            true
        );

        //modify default rights to allow guests to vote
		$strReturn .= "Modifying modules' rights node...\n";
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right1");

        $strReturn .= "Registering votings-element...\n";
        if(class_module_pages_element::getElement("votings") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("votings");
            $objElement->setStrClassAdmin("class_element_votings_admin.php");
            $objElement->setStrClassPortal("class_element_votings_portal.php");
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
        if($arrModul["module_version"] == "1.0") {
            $strReturn .= $this->update_10_11();
            $this->objDB->flushQueryCache();
        }
        
        return $strReturn."\n\n";
	}
    
    private function update_10_11() {
        $strReturn = "Updating 1.0 to 1.1...\n";


        $strReturn .= "Adding classes for existing records...\n";

        $strReturn .= "Votings\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."votings_voting, "._dbprefix_."system WHERE system_id = votings_voting_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_votings_voting', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Answerrs\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."votings_answer, "._dbprefix_."system WHERE system_id = votings_answer_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_votings_answer', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("votings", "1.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("votings", "1.1");
        return $strReturn;
    }

}
