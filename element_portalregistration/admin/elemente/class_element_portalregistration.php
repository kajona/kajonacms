<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_portalregistration.php																*
* 	Admin-class of the portalregistration-element														*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_portalregistration.php 1884 2007-12-26 15:04:48Z sidler $                        *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

/**
 * Class to handle the admin-stuff of the portalregistration-element
 *
 * @package modul_pages
 *
 */
class class_element_portalregistration extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "element_portalregistration";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"] 		    = _dbprefix_."element_portalregistration";
		$arrModul["modul"]			= "elemente";

		$arrModul["tableColumns"]   = "portalregistration_template|char,portalregistration_group|char,portalregistration_success|char";

		parent::__construct($arrModul);
	}

   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";

		//Build the form
		//Load the available templates
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/portal/element_portalregistration", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("portalregistration_template", $arrTemplatesDD, $this->getText("portalregistration_template"), (isset($arrElementData["portalregistration_template"]) ? $arrElementData["portalregistration_template"] : "" ));
		
		//Load groups available
		include_once(_systempath_."/class_modul_user_group.php");
		$arrGroups = class_modul_user_group::getAllGroups();
		$arrGroupsDD = array();
		foreach ($arrGroups as $objOneGroup) {
			$arrGroupsDD[$objOneGroup->getSystemid()] = $objOneGroup->getStrName();
		}
		
		$strReturn .= $this->objToolkit->formInputDropdown("portalregistration_group", $arrGroupsDD, $this->getText("portalregistration_group"), (isset($arrElementData["portalregistration_group"]) ? $arrElementData["portalregistration_group"] : "" ));
		$strReturn .= $this->objToolkit->formInputPageSelector("portalregistration_success", $this->getText("portalregistration_success"), (isset($arrElementData["portalregistration_success"]) ? $arrElementData["portalregistration_success"] : ""));

		return $strReturn;
	}


} //class_element_downloads.php
?>