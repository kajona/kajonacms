<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");
//needed models
include_once(_systempath_."/class_modul_gallery_gallery.php");
include_once(_systempath_."/class_modul_gallery_pic.php");


/**
 * Class representing the admin-part of the gallery element
 *
 * @package modul_gallery
 */
class class_element_gallery extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_gallery";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_gallery";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]  = "gallery_id|char,gallery_mode|number,gallery_template|char,gallery_maxh_p|number,gallery_maxh_d|number,gallery_maxh_m|number,";
		$arrModule["tableColumns"] .= "gallery_maxw_p|number,gallery_maxw_d|number,gallery_maxw_m|number,gallery_nrow|number,gallery_text|char,gallery_text_x|number,";
		$arrModule["tableColumns"] .= "gallery_text_y|number,gallery_imagesperpage|number";

		parent::__construct($arrModule);
	}


   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData)	{
		$strReturn = "";
		//Load all galleries
        include_once(_adminpath_."/class_modul_gallery_admin.php");
        $objGallery = new class_modul_gallery_admin();
        $arrRawGals = class_modul_gallery_gallery::getGalleries();
        $arrGalleries = array();
        foreach ($arrRawGals as $objOneGal)
            $arrGalleries[$objOneGal->getSystemid()] = $objOneGal->getStrTitle();


		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("gallery_id", $arrGalleries, $this->getText("gallery_id"), (isset($arrElementData["gallery_id"]) ? $arrElementData["gallery_id"] : "" ));

		$arrModes = array(0 => $this->getText("mode_standard"),
		                  1 => $this->getText("mode_random"));

		//Load the available templates
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/modul_gallery", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("gallery_template", $arrTemplatesDD, $this->getText("gallery_template"), (isset($arrElementData["gallery_template"]) ? $arrElementData["gallery_template"] : "" ));

		//And a lot of inputs
        $strReturn .= $this->objToolkit->formHeadline($this->getText("headline_list"));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("hint_preview"));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxw_p", $this->getText("gallery_maxw_p"), (isset($arrElementData["gallery_maxw_p"]) ? $arrElementData["gallery_maxw_p"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxh_p", $this->getText("gallery_maxh_p"), (isset($arrElementData["gallery_maxh_p"]) ? $arrElementData["gallery_maxh_p"] : ""));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("hint_nrow"));
		$strReturn .= $this->objToolkit->formInputText("gallery_nrow", $this->getText("gallery_nrow"), (isset($arrElementData["gallery_nrow"]) ? $arrElementData["gallery_nrow"] : ""));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("gallery_imagesperpage_hint"));
		$strReturn .= $this->objToolkit->formInputText("gallery_imagesperpage", $this->getText("gallery_imagesperpage"), (isset($arrElementData["gallery_imagesperpage"]) ? $arrElementData["gallery_imagesperpage"] : ""));

		$strReturn .= $this->objToolkit->formHeadline($this->getText("headline_detail"));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("hint_detail"));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxw_d", $this->getText("gallery_maxw_d"), (isset($arrElementData["gallery_maxw_d"]) ? $arrElementData["gallery_maxw_d"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxh_d", $this->getText("gallery_maxh_d"), (isset($arrElementData["gallery_maxh_d"]) ? $arrElementData["gallery_maxh_d"] : ""));

		$strReturn .= $this->objToolkit->formHeadline($this->getText("headline_moviestrip"));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("hint_movie"));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxw_m", $this->getText("gallery_maxw_m"), (isset($arrElementData["gallery_maxw_m"]) ? $arrElementData["gallery_maxw_m"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxh_m", $this->getText("gallery_maxh_m"), (isset($arrElementData["gallery_maxh_m"]) ? $arrElementData["gallery_maxh_m"] : ""));

		$strReturn .= $this->objToolkit->formHeadline($this->getText("headline_overlay"));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("hint_text"));
		$strReturn .= $this->objToolkit->formInputText("gallery_text", $this->getText("gallery_text"), (isset($arrElementData["gallery_text"]) ? $arrElementData["gallery_text"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_text_x", $this->getText("gallery_text_x"), (isset($arrElementData["gallery_text_x"]) ? $arrElementData["gallery_text_x"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_text_y", $this->getText("gallery_text_y"), (isset($arrElementData["gallery_text_y"]) ? $arrElementData["gallery_text_y"] : ""));
		$strReturn .= $this->objToolkit->formInputHidden("gallery_mode", "0");

		$strReturn .= $this->objToolkit->setBrowserFocus("gallery_id");

		return $strReturn;
	}


}
?>