<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                        *
********************************************************************************************************/

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
        $arrModule = array();
		$arrModule["name"] 			= "element_portalregistration";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_portalregistration";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]   = "portalregistration_template|char,portalregistration_group|char,portalregistration_success|char";

		parent::__construct($arrModule);
	}

   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";

		$strReturn .= $this->objToolkit->formTextRow($this->getText("portalregistration_hint"));

		//Build the form
		//Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/element_portalregistration", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("portalregistration_template", $arrTemplatesDD, $this->getText("portalregistration_template"), (isset($arrElementData["portalregistration_template"]) ? $arrElementData["portalregistration_template"] : "" ));

		//Load groups available
		$arrGroups = class_modul_user_group::getAllGroups();
		$arrGroupsDD = array();
		foreach ($arrGroups as $objOneGroup) {
			$arrGroupsDD[$objOneGroup->getSystemid()] = $objOneGroup->getStrName();
		}

		$strReturn .= $this->objToolkit->formInputDropdown("portalregistration_group", $arrGroupsDD, $this->getText("portalregistration_group"), (isset($arrElementData["portalregistration_group"]) ? $arrElementData["portalregistration_group"] : "" ));
		$strReturn .= $this->objToolkit->formInputPageSelector("portalregistration_success", $this->getText("portalregistration_success"), (isset($arrElementData["portalregistration_success"]) ? $arrElementData["portalregistration_success"] : ""));

		$strReturn .= $this->objToolkit->setBrowserFocus("portalregistration_template");

		return $strReturn;
	}


} //class_element_downloads.php
?>