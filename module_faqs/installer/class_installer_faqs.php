<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

/**
 * Class providing an installer for the faqs module
 *
 * @package module_faqs
 * @moduleId _faqs_module_id_
 */
class class_installer_faqs extends class_installer_base implements interface_installer {

    public function install() {
		$strReturn = "";

		//faqs cat-------------------------------------------------------------------------------------
		$strReturn .= "Installing table faqs_category...\n";

		$arrFields = array();
		$arrFields["faqs_cat_id"] 		= array("char20", false);
		$arrFields["faqs_cat_title"]	= array("char254", true);

		if(!$this->objDB->createTable("faqs_category", $arrFields, array("faqs_cat_id")))
			$strReturn .= "An error occured! ...\n";

		//faqs----------------------------------------------------------------------------------
		$strReturn .= "Installing table faqs...\n";

		$arrFields = array();
		$arrFields["faqs_id"] 		= array("char20", false);
		$arrFields["faqs_question"]	= array("text", true);
		$arrFields["faqs_answer"]	= array("text", true);

		if(!$this->objDB->createTable("faqs", $arrFields, array("faqs_id")))
			$strReturn .= "An error occured! ...\n";

		//faqs_member----------------------------------------------------------------------------------
		$strReturn .= "Installing table faqs_member...\n";

		$arrFields = array();
		$arrFields["faqsmem_id"] 		= array("char20", false);
		$arrFields["faqsmem_faq"]		= array("char20", false);
		$arrFields["faqsmem_category"]	= array("char20", false);

		if(!$this->objDB->createTable("faqs_member", $arrFields, array("faqsmem_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$this->registerModule("faqs", _faqs_module_id_, "class_module_faqs_portal.php", "class_module_faqs_admin.php", $this->objMetadata->getStrVersion(), true);

       //Table for page-element
       $strReturn .= "Installing faqs-element table...\n";

       $arrFields = array();
       $arrFields["content_id"] 	= array("char20", false);
       $arrFields["faqs_category"]	= array("char20", true);
       $arrFields["faqs_template"]	= array("char254", true);

       if(!$this->objDB->createTable("element_faqs", $arrFields, array("content_id")))
           $strReturn .= "An error occured! ...\n";

       //Register the element
       $strReturn .= "Registering faqs-element...\n";
       //check, if not already existing
       $objElement = class_module_pages_element::getElement("faqs");
       if($objElement == null) {
           $objElement = new class_module_pages_element();
           $objElement->setStrName("faqs");
           $objElement->setStrClassAdmin("class_element_faqs_admin.php");
           $objElement->setStrClassPortal("class_element_faqs_portal.php");
           $objElement->setIntCachetime(3600);
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
        if($arrModul["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= $this->update_349_40();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= $this->update_40_41();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1") {
            $strReturn .= "Updating 4.1 to 4.2...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("faqs", "4.2");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("faqs", "4.2");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("faqs", "4.3");
            $strReturn .= "Updating element-versions...\n";
            $this->updateElementVersion("faqs", "4.3");
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.3") {
            $strReturn .= "Updating 4.3 to 4.4...\n";
            $this->updateModuleVersion("faqs", "4.4");
            $this->updateElementVersion("faqs", "4.4");
        }

        return $strReturn."\n\n";
	}


    private function update_342_349() {

        $strReturn = "Updating 3.4.2 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";
        $strReturn .= "FAQs\n";
        $arrRows = $this->objDB->getPArray("SELECT faqs_id FROM "._dbprefix_."faqs, "._dbprefix_."system WHERE system_id = faqs_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_faqs_faq', $arrOneRow["faqs_id"] ) );
        }

        $strReturn .= "Categories\n";
        $arrRows = $this->objDB->getPArray("SELECT faqs_cat_id FROM "._dbprefix_."faqs_category, "._dbprefix_."system WHERE system_id = faqs_cat_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_faqs_category', $arrOneRow["faqs_cat_id"] ) );
        }

        $strReturn .= "Removing old constants\n";
        $strQuery = "DELETE FROM "._dbprefix_."system_config WHERE system_config_name = ?";
        $this->objDB->_pQuery($strQuery, array("_faqs_search_resultpage_"));


        $strReturn .= "Setting aspect assignments...\n";
        if(class_module_system_aspect::getAspectByName("content") != null) {
            $objModule = class_module_system_module::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("faqs", "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("faqs", "3.4.9");
        return $strReturn;
    }

    private function update_349_40() {
        $strReturn = "Updating 3.4.9 to 4.0...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("faqs", "4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("faqs", "4.0");
        return $strReturn;
    }

    private function update_40_41() {
        $strReturn = "Updating 4.0 to 4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("faqs", "4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("faqs", "4.1");
        return $strReturn;
    }


}
