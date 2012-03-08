<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
********************************************************************************************************/

/**
 * Class representing the admin-part of the gallery element
 *
 * @package module_mediamanager
 */
class class_element_gallery_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

        $this->setArrModuleEntry("name", "element_gallery");
        $this->setArrModuleEntry("table", _dbprefix_."element_gallery");
        $this->setArrModuleEntry("tableColumns", "gallery_id,gallery_mode,gallery_template,gallery_maxh_p,gallery_maxh_d,gallery_maxh_m,gallery_maxw_p,gallery_maxw_d,gallery_maxw_m,gallery_text,gallery_text_x,gallery_text_y,gallery_imagesperpage,gallery_overlay");

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

        $arrRawGals = class_module_mediamanager_repo::getAllRepos();
        $arrGalleries = array();
        foreach ($arrRawGals as $objOneGal)
            $arrGalleries[$objOneGal->getSystemid()] = $objOneGal->getStrTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("gallery_id", $arrGalleries, $this->getLang("gallery_id"), (isset($arrElementData["gallery_id"]) ? $arrElementData["gallery_id"] : "" ));

		//Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_mediamanager");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("gallery_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["gallery_template"]) ? $arrElementData["gallery_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("gallery_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["gallery_template"]) ? $arrElementData["gallery_template"] : "" ));

		//And a lot of inputs
        $strReturn .= $this->objToolkit->formHeadline($this->getLang("headline_list"));
		$strReturn .= $this->objToolkit->formTextRow($this->getLang("hint_preview"));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxw_p", $this->getLang("gallery_maxw_p"), (isset($arrElementData["gallery_maxw_p"]) ? $arrElementData["gallery_maxw_p"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxh_p", $this->getLang("gallery_maxh_p"), (isset($arrElementData["gallery_maxh_p"]) ? $arrElementData["gallery_maxh_p"] : ""));
		$strReturn .= $this->objToolkit->formTextRow($this->getLang("gallery_imagesperpage_hint"));
		$strReturn .= $this->objToolkit->formInputText("gallery_imagesperpage", $this->getLang("gallery_imagesperpage"), (isset($arrElementData["gallery_imagesperpage"]) ? $arrElementData["gallery_imagesperpage"] : ""));

		$strReturn .= $this->objToolkit->formHeadline($this->getLang("headline_detail"));
		$strReturn .= $this->objToolkit->formTextRow($this->getLang("hint_detail"));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxw_d", $this->getLang("gallery_maxw_d"), (isset($arrElementData["gallery_maxw_d"]) ? $arrElementData["gallery_maxw_d"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxh_d", $this->getLang("gallery_maxh_d"), (isset($arrElementData["gallery_maxh_d"]) ? $arrElementData["gallery_maxh_d"] : ""));

		$strReturn .= $this->objToolkit->formHeadline($this->getLang("headline_moviestrip"));
		$strReturn .= $this->objToolkit->formTextRow($this->getLang("hint_movie"));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxw_m", $this->getLang("gallery_maxw_m"), (isset($arrElementData["gallery_maxw_m"]) ? $arrElementData["gallery_maxw_m"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_maxh_m", $this->getLang("gallery_maxh_m"), (isset($arrElementData["gallery_maxh_m"]) ? $arrElementData["gallery_maxh_m"] : ""));

		$strReturn .= $this->objToolkit->formHeadline($this->getLang("headline_overlay"));
		$strReturn .= $this->objToolkit->formTextRow($this->getLang("hint_text"));
		$strReturn .= $this->objToolkit->formInputText("gallery_text", $this->getLang("gallery_text"), (isset($arrElementData["gallery_text"]) ? $arrElementData["gallery_text"] : ""));
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("hint_overlay"));
		$strReturn .= $this->objToolkit->formInputFileSelector("gallery_overlay", $this->getLang("gallery_overlay"), (isset($arrElementData["gallery_overlay"]) ? $arrElementData["gallery_overlay"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_text_x", $this->getLang("gallery_text_x"), (isset($arrElementData["gallery_text_x"]) ? $arrElementData["gallery_text_x"] : ""));
		$strReturn .= $this->objToolkit->formInputText("gallery_text_y", $this->getLang("gallery_text_y"), (isset($arrElementData["gallery_text_y"]) ? $arrElementData["gallery_text_y"] : ""));
		$strReturn .= $this->objToolkit->formInputHidden("gallery_mode", "0");

		$strReturn .= $this->objToolkit->setBrowserFocus("gallery_id");

		return $strReturn;
	}

    public function doBeforeSaveToDb() {
        $this->arrParamData["gallery_overlay"] = str_replace(_webpath_, "", $this->arrParamData["gallery_overlay"]);
    }



}
