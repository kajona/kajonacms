<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_postacomment.php 3386 2010-08-23 18:28:50Z sidler $                                *
********************************************************************************************************/

/**
 * Class providing an install for the tags module
 *
 * @package modul_tags
 */
class class_installer_tags extends class_installer_base implements interface_installer {

	public function __construct() {
        $arrModule = array();
		$arrModule["version"] 		  = "3.3.1.8";
		$arrModule["name"] 			  = "tags";
		$arrModule["name_lang"] 	  = "Module Tags";
		$arrModule["moduleId"] 		  = _tags_modul_id_;

		$arrModule["table1"]          = _dbprefix_."tags_tag";
		$arrModule["table2"]          = _dbprefix_."tags_member";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system");
	}
	
    public function getMinSystemVersion() {
	    return "3.3.1.8";
	}

	public function hasPostInstalls() {
        //check, if not already existing
	    $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("tags");
		}
		catch (class_exception $objEx)  {
		}
        if($objElement == null)
            return true;

        return false;

	}

   public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//tags_tag --------------------------------------------------------------------------------------
		$strReturn .= "Installing table tags_tag...\n";

		$arrFields = array();
		$arrFields["tags_tag_id"] 		= array("char20", false);
		$arrFields["tags_tag_name"] 	= array("char254", true);
		
		if(!$this->objDB->createTable("tags_tag", $arrFields, array("tags_tag_id")))
			$strReturn .= "An error occured! ...\n";

        //tags_member --------------------------------------------------------------------------------------
		$strReturn .= "Installing table tags_member...\n";

        $arrFields = array();
		$arrFields["tags_systemid"] 	= array("char20", false);
		$arrFields["tags_tagid"]        = array("char20", false);
		$arrFields["tags_attribute"]    = array("char254", true);

		if(!$this->objDB->createTable("tags_member", $arrFields, array("tags_systemid", "tags_tagid", "tags_attribute")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule("tags",
		                                     _tags_modul_id_,
		                                     "", 
		                                     "class_modul_tags_admin.php",
		                                     $this->arrModule["version"], 
		                                     true,
		                                     "",
		                                     "class_modul_tags_admin_xml.php");

		$strReturn .= "Registering system-constants...\n";

		return $strReturn;

	}

	public function postInstall() {

         //Register the element
		$strReturn = "Registering tags-element...\n";

        //check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_modul_pages_element::getElement("tags");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_modul_pages_element();
		    $objElement->setStrName("tags");
		    $objElement->setStrClassAdmin("class_element_tags.php");
		    $objElement->setStrClassPortal("class_element_tags.php");
		    $objElement->setIntCachetime(3600*24*30);
		    $objElement->setIntRepeat(0);
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
            $this->objDB->flushQueryCache();
        }
	   
        return $strReturn."\n\n";
	}
	
    private function update_3311_3318() {
        $strReturn = "Updating 3.3.1.1 to 3.3.1.8...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->arrModule["name"], "3.3.1.8");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "3.3.1.8");
        return $strReturn;
    }



}
?>