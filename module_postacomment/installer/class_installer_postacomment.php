<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Class providing an install for the postacomment module
 *
 * @package module_postacomment
 */
class class_installer_postacomment extends class_installer_base implements interface_installer {

	public function __construct() {
        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));

        $this->setArrModuleEntry("moduleId", _postacomment_modul_id_);

        parent::__construct();
	}


    public function install() {
		$strReturn = "";

		$strReturn .= "Installing table postacomment...\n";

		$arrFields = array();
		$arrFields["postacomment_id"] 		= array("char20", false);
		$arrFields["postacomment_date"] 	= array("int", true);
		$arrFields["postacomment_page"] 	= array("char254", true);
		$arrFields["postacomment_language"] = array("char20", true);
		$arrFields["postacomment_systemid"] = array("char20", true);
		$arrFields["postacomment_username"] = array("char254", true);
		$arrFields["postacomment_title"] 	= array("char254", true);
		$arrFields["postacomment_comment"] 	= array("text", true);

		if(!$this->objDB->createTable("postacomment", $arrFields, array("postacomment_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule(
            "postacomment",
		    _postacomment_modul_id_,
		    "class_module_postacomment_portal.php",
		    "class_module_postacomment_admin.php",
            $this->objMetadata->getStrVersion(),
		    true,
		    "class_module_postacomment_portal_xml.php");

		//modify default rights to allow guests to post
		$strReturn .= "Modifying modules' rights node...\n";
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right1");
		$this->objRights->addGroupToRight(_guests_group_id_, $strSystemID, "right2");


        $strReturn .= "Registering postacomment-element...\n";
        //check, if not already existing
        $objElement = class_module_pages_element::getElement("postacomment");
        if($objElement == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("postacomment");
            $objElement->setStrClassAdmin("class_element_postacomment_admin.php");
            $objElement->setStrClassPortal("class_element_postacomment_portal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
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
        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";


        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.2") {
            $strReturn .= $this->update_342_349();
        }

        return $strReturn."\n\n";
	}




    private function update_342_349() {
        $strReturn = "Updating 3.4.1.1 to 3.4.9...\n";

        $strReturn .= "Adding classes for existing records...\n";


        $strReturn .= "Postacomment\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."postacomment, "._dbprefix_."system WHERE system_id = postacomment_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_postacomment_post', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Removing old notify-constant\n";
        $strQuery = "DELETE FROM "._dbprefix_."system_config WHERE system_config_name = ? ";
        $this->objDB->_pQuery($strQuery, array("_postacomment_notify_mail_"));

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("tags", "3.4.9");

        return $strReturn;
    }

}
