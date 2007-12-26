<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_element_bild.php                                                                              *
*   Admin-Part of the navigation element                                                                *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

include_once(_systempath_."/class_modul_navigation_tree.php");
/**
 * Admin-Class of the navigation element
 * 
 * @package modul_navigation
 *
 */
class class_element_navigation extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "element_navigation";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"] 		    = _dbprefix_."element_navigation";
		$arrModul["modul"]			= "elemente";

		$arrModul["tableColumns"]      = "navigation_id|char,navigation_template|char,navigation_css|char,navigation_mode|char";

		parent::__construct($arrModul);
	}


   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData)	{
		$strReturn = "";
		//Load all navigations available
		if(isset($arrElementData["navigation_id"]) && $arrElementData["navigation_id"] != '') {
		  include_once(_adminpath_."/class_modul_navigation_admin.php");
		  $objNavigation = new class_modul_navigation_admin();
		  $objNavi = new class_modul_navigation_tree($arrElementData["navigation_id"]);
		  $strNaviName = $objNavi->getStrName();
		}
		else
		  $strNaviName = "";

		//Build the form
		$strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("navigation_name"), $strNaviName, "inputText", getLinkAdminPopup("folderview", "navigationBrowser", "", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"), true);
		$strReturn .= $this->objToolkit->formInputHidden("navigation_id", (isset($arrElementData["navigation_id"]) ? $arrElementData["navigation_id"] : ""));
		$strReturn .= $this->objToolkit->formInputText("navigation_css", $this->getText("navigation_css"), (isset($arrElementData["navigation_css"]) ? $arrElementData["navigation_css"] : ""));
		//Load the available templates
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/portal/modul_navigation", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("navigation_template", $arrTemplatesDD, $this->getText("navigation_template"), (isset($arrElementData["navigation_template"]) ? $arrElementData["navigation_template"] : "" ));
        //and finally offer the different modes
        $arrModes = array("tree" => $this->getText("modus_baum"), "sitemap" => $this->getText("modus_sitemap"));
        $strReturn .= $this->objToolkit->formInputDropdown("navigation_mode", $arrModes, $this->getText("navigation_mode"), (isset($arrElementData["navigation_mode"]) ? $arrElementData["navigation_mode"] : "" ));
		return $strReturn;
	}


}
?>