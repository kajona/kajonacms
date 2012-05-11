<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Installer of the guestbook module
 *
 * @package module_guestbook
 */
class class_installer_guestbook extends class_installer_base  {

	public function __construct() {

        $this->objMetadata = new class_module_packagemanager_metadata();
        $this->objMetadata->autoInit(uniStrReplace(array("/installer", _realpath_), array("", ""), __DIR__));

		$this->setArrModuleEntry("moduleId", _guestbook_module_id_);
		parent::__construct();
	}



    public function install() {

		$strReturn = "";
		//Tabellen anlegen

		//guestbook-------------------------------------------------------------------------------------
		$strReturn .= "Installing table guestbook_book...\n";

		$arrFields = array();
		$arrFields["guestbook_id"] 		  = array("char20", false);
		$arrFields["guestbook_title"] 	  = array("char254", true);
		$arrFields["guestbook_moderated"] = array("int", true);

		if(!$this->objDB->createTable("guestbook_book", $arrFields, array("guestbook_id")))
			$strReturn .= "An error occured! ...\n";

		//guestbook_post----------------------------------------------------------------------------------
		$strReturn .= "Installing table guestbook_post...\n";

		$arrFields = array();
		$arrFields["guestbook_post_id"]   = array("char20", false);
		$arrFields["guestbook_post_name"] = array("char254", true);
		$arrFields["guestbook_post_email"]= array("char254", true);
		$arrFields["guestbook_post_page"] = array("char254", true);
		$arrFields["guestbook_post_text"] = array("text", true);
		$arrFields["guestbook_post_date"] = array("int", true);

		if(!$this->objDB->createTable("guestbook_post", $arrFields, array("guestbook_post_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$this->registerModule("guestbook", _guestbook_module_id_, "class_module_guestbook_portal.php", "class_module_guestbook_admin.php", $this->objMetadata->getStrVersion() , true);

		$strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_guestbook_search_resultpage_", "guestbook", class_module_system_setting::$int_TYPE_PAGE, _guestbook_module_id_);


        //Table for page-element
        $strReturn .= "Installing guestbook-element table...\n";

        $arrFields = array();
        $arrFields["content_id"]   		= array("char20", false);
        $arrFields["guestbook_id"] 		= array("char20", true);
        $arrFields["guestbook_template"]= array("char254", true);
        $arrFields["guestbook_amount"] 	= array("int", true);

        if(!$this->objDB->createTable("element_guestbook", $arrFields, array("content_id")))
            $strReturn .= "An error occured! ...\n";

        //Register the element
        $strReturn .= "Registering guestbook-element...\n";
        //check, if not already existing
        $objElement = class_module_pages_element::getElement("guestbook");
        if($objElement === null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("guestbook");
            $objElement->setStrClassAdmin("class_element_guestbook_admin.php");
            $objElement->setStrClassPortal("class_element_guestbook_portal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(1);
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
        if($arrModul["module_version"] == "3.4.0") {
            $strReturn .= $this->update_340_341();
        }

        $arrModul = $this->getModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.1") {
            $strReturn .= $this->update_341_349();
        }

        return $strReturn."\n\n";
	}



    private function update_340_341() {
        $strReturn = "Updating 3.4.0 to 3.4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("guestbook", "3.4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("guestbook", "3.4.1");
        return $strReturn;
    }

    private function update_341_349() {
        $strReturn = "Updating 3.4.1 to 3.4.9...\n";


        $strReturn .= "Adding classes for existing records...\n";
        $strReturn .= "Trees\n";
        foreach(class_module_guestbook_guestbook::getGuestbooks() as $objOneBook) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( get_class($objOneBook), $objOneBook->getSystemid() ) );
        }

        $strReturn .= "Navigation Points\n";
        $arrRows = $this->objDB->getPArray("SELECT guestbook_post_id FROM "._dbprefix_."guestbook_post, "._dbprefix_."system WHERE system_id = guestbook_post_id AND (system_class IS NULL OR system_class = '')", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_guestbook_post', $arrOneRow["system_id"] ) );
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("guestbook", "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("guestbook", "3.4.9");
        return $strReturn;
    }

}
