<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_element_rendertext.php                                                                        *
*   Admin-Class of the rendertext                                                                       *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_element_rendertext.php 1487 2007-04-10 19:34:56Z rsr $                                   *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");
include_once(_systempath_."/class_image.php");

/**
 * Admin-Class to handle the rendertext
 *
 * @package modul_pages
 */
class class_element_rendertext extends class_element_admin implements interface_admin_element {

	/**
	 * Contructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "element_rendertext";
		$arrModul["author"] 		= "rsr@itx.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"]			= _dbprefix_."element_rendertext";
		$arrModul["modul"]			= "elemente";
		
		$arrModul["tableColumns"]        = "rendertext_text|char,rendertext_title|char,rendertext_width|number,rendertext_height|number,rendertext_font_family|char,rendertext_font_color|char,";
		$arrModul["tableColumns"]       .= "rendertext_font_size|char,rendertext_background_color|char,rendertext_transparency|char,rendertext_mode|char";

		parent::__construct($arrModul);
	}


	/**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData){
		
		$strReturn = "";
		$arrFontsDD = array();		

		$arrOptionen = array(
			"rendertext_img"	=>  $this->getText("rendertext_img"),
			"rendertext_pfad"	=>  $this->getText("rendertext_pfad")
			);

		$strReturn .= $this->objToolkit->formInputText("rendertext_text", $this->getText("rendertext_text"), (isset($arrElementData["rendertext_text"]) ? $arrElementData["rendertext_text"] : "" ), "inputText");                
		                
		//Load the available fonts
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		
		$arrFonts = $objFilesystem->getFilelist("/system/fonts", ".ttf");
		if(count($arrFonts) > 0) {
			foreach($arrFonts as $strFont) {
				$arrFontsDD[$strFont] = $strFont;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("rendertext_font_family", $arrFontsDD, $this->getText("rendertext_font_family"), (isset($arrElementData["rendertext_font_family"]) ? $arrElementData["rendertext_font_family"] : "" ));

		$strReturn .= $this->objToolkit->formInputText("rendertext_font_color", $this->getText("rendertext_font_color"), (isset($arrElementData["rendertext_font_color"]) ? $arrElementData["rendertext_font_color"] : "" ), "inputText");                
		$strReturn .= $this->objToolkit->formInputText("rendertext_font_size", $this->getText("rendertext_font_size"), (isset($arrElementData["rendertext_font_size"]) ? $arrElementData["rendertext_font_size"] : "" ), "inputText");
		$strReturn .= $this->objToolkit->divider();
		                
		$strReturn .= $this->objToolkit->formInputText("rendertext_title", $this->getText("rendertext_title"), (isset($arrElementData["rendertext_title"]) ? $arrElementData["rendertext_title"] : "" ), "inputText");
		$strReturn .= $this->objToolkit->formInputText("rendertext_width", $this->getText("rendertext_width"), (isset($arrElementData["rendertext_width"]) ? $arrElementData["rendertext_width"] : "" ), "inputText");                
		$strReturn .= $this->objToolkit->formInputText("rendertext_height", $this->getText("rendertext_height"), (isset($arrElementData["rendertext_height"]) ? $arrElementData["rendertext_height"] : "" ), "inputText");		
		$strReturn .= $this->objToolkit->formInputText("rendertext_background_color", $this->getText("rendertext_background_color"), (isset($arrElementData["rendertext_background_color"]) ? $arrElementData["rendertext_background_color"] : "" ), "inputText");                
		$strReturn .= $this->objToolkit->formInputCheckbox("rendertext_transparency", $this->getText("rendertext_transparency"), (isset($arrElementData["rendertext_transparency"]) ? ($arrElementData["rendertext_transparency"] == "checked" ? true : false) : ""));
		                
		$strReturn .= $this->objToolkit->formInputDropdown("rendertext_mode", $arrOptionen, $this->getText("rendertext_mode"), (isset($arrElementData["rendertext_mode"]) ? $arrElementData["rendertext_mode"] : "" ));

		return $strReturn;
	}

	
	/**
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {

	  $arrData = $this->loadElementData();

      return htmlToString(uniStrTrim($arrData["rendertext_text"], 25), true);
	}


} //class_element_rendertext.php
?>