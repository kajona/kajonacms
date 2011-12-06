<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/

/**
 * Admin class of the navigation element
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 */
class class_element_navigation_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

        $this->setArrModuleEntry("name", "element_navigation");
        $this->setArrModuleEntry("table", _dbprefix_."element_navigation");
        $this->setArrModuleEntry("tableColumns", "navigation_id|char,navigation_template|char,navigation_mode|char");
		parent::__construct();
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
		  $objNavigation = new class_module_navigation_admin();
		  $objNavi = new class_module_navigation_tree($arrElementData["navigation_id"]);
		  $strNaviName = $objNavi->getStrName();
		}
		else
		  $strNaviName = "";

		//Build the form
		$strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("commons_name"), $strNaviName, "inputText", getLinkAdminDialog("navigation", "navigationBrowser", "", $this->getText("commons_open_browser"), $this->getText("commons_open_browser"), "icon_externalBrowser.gif", $this->getText("commons_open_browser")), true);
		$strReturn .= $this->objToolkit->formInputHidden("navigation_id", (isset($arrElementData["navigation_id"]) ? $arrElementData["navigation_id"] : ""));
		//Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_navigation");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("navigation_template", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["navigation_template"]) ? $arrElementData["navigation_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("navigation_template", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["navigation_template"]) ? $arrElementData["navigation_template"] : "" ));

        //and finally offer the different modes
        $arrModes = array("tree" => $this->getText("modus_baum"), "sitemap" => $this->getText("modus_sitemap"));
        $strReturn .= $this->objToolkit->formInputDropdown("navigation_mode", $arrModes, $this->getText("navigation_mode"), (isset($arrElementData["navigation_mode"]) ? $arrElementData["navigation_mode"] : "" ));

        $strReturn .= $this->objToolkit->setBrowserFocus("navigation_template");

        return $strReturn;
	}


}
