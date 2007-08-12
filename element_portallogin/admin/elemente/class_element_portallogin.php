<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_portallogin.php																		*
* 	Admin-class of the portallogin-element															    *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

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
		$arrModul["name"] 			= "element_portallogin";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"] 		    = _dbprefix_."element_portallogin";
		$arrModul["modul"]			= "elemente";

		$arrModul["tableColumns"]      = "portallogin_template|char,portallogin_error|char,portallogin_success|char,portallogin_logout_success|char";

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
		$arrTemplates = $objFilesystem->getFilelist("/templates/portal/element_portallogin", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("portallogin_template", $arrTemplatesDD, $this->getText("portallogin_template"), (isset($arrElementData["portallogin_template"]) ? $arrElementData["portallogin_template"] : "" ));
		$strReturn .= $this->objToolkit->formInputText("portallogin_error", $this->getText("portallogin_error"), (isset($arrElementData["portallogin_error"]) ? $arrElementData["portallogin_error"] : ""), "inputText", getLinkAdminPopup("folderview", "pagesFolderBrowser", "&pages=1&form_element=portallogin_error", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
		$strReturn .= $this->objToolkit->formInputText("portallogin_success", $this->getText("portallogin_success"), (isset($arrElementData["portallogin_success"]) ? $arrElementData["portallogin_success"] : ""), "inputText", getLinkAdminPopup("folderview", "pagesFolderBrowser", "&pages=1&form_element=portallogin_success", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
		$strReturn .= $this->objToolkit->formInputText("portallogin_logout_success", $this->getText("portallogin_logout_success"), (isset($arrElementData["portallogin_logout_success"]) ? $arrElementData["portallogin_logout_success"] : ""), "inputText", getLinkAdminPopup("folderview", "pagesFolderBrowser", "&pages=1&form_element=portallogin_logout_success", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));

		return $strReturn;
	}


} //class_element_downloads.php
?>