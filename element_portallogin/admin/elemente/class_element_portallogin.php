<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the portallogin-element
 *
 * @package modul_pages
 *
 */
class class_element_portallogin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
        $arrModule = array();
		$arrModule["name"] 			= "element_portallogin";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_portallogin";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]   = "portallogin_template|char,portallogin_error|char,portallogin_success|char,portallogin_logout_success|char,portallogin_profile|char,portallogin_pwdforgot|char";

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

		//Build the form
		//Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/element_portallogin", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("portallogin_template", $arrTemplatesDD, $this->getText("portallogin_template"), (isset($arrElementData["portallogin_template"]) ? $arrElementData["portallogin_template"] : "" ));
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_error", $this->getText("portallogin_error"), (isset($arrElementData["portallogin_error"]) ? $arrElementData["portallogin_error"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_success", $this->getText("portallogin_success"), (isset($arrElementData["portallogin_success"]) ? $arrElementData["portallogin_success"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_logout_success", $this->getText("portallogin_logout_success"), (isset($arrElementData["portallogin_logout_success"]) ? $arrElementData["portallogin_logout_success"] : ""));
        $strReturn .= $this->objToolkit->formTextRow($this->getText("portallogin_profile_hint"));
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_profile", $this->getText("portallogin_profile"), (isset($arrElementData["portallogin_profile"]) ? $arrElementData["portallogin_profile"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_pwdforgot", $this->getText("portallogin_pwdforgot"), (isset($arrElementData["portallogin_pwdforgot"]) ? $arrElementData["portallogin_pwdforgot"] : ""));

		$strReturn .= $this->objToolkit->setBrowserFocus("portallogin_template");

		return $strReturn;
	}


}
?>