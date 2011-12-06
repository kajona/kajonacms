<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class to handle the admin-part of the element
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 */
class class_element_image_admin extends class_element_admin implements interface_admin_element {


	public function __construct() {

        $this->setArrModuleEntry("name", "element_image");
        $this->setArrModuleEntry("table", _dbprefix_."element_image");
        $this->setArrModuleEntry("tableColumns", "image_title|char,image_link|char,image_image|char,image_x|number,image_y|number,image_template|char");
		parent::__construct();
	}


	/**
	 * Returns the element-part of the admin-form
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";
		$strReturn .= $this->objToolkit->formInputText("image_title", $this->getText("commons_title"), (isset($arrElementData["image_title"]) ? $arrElementData["image_title"] : "" ));
		$strReturn .= $this->objToolkit->formInputPageSelector("image_link", $this->getText("image_link"), (isset($arrElementData["image_link"]) ? $arrElementData["image_link"] : "" ));
		$strReturn .= $this->objToolkit->formInputImageSelector("image_image", $this->getText("commons_image"), (isset($arrElementData["image_image"]) ? $arrElementData["image_image"] : "" ));

		$strXY = $this->objToolkit->formTextRow($this->getText("image_xy_hint"));
		$strXY .= $this->objToolkit->formInputText("image_x", $this->getText("image_x"), (isset($arrElementData["image_x"]) ? $arrElementData["image_x"] : "" ));
		$strXY .= $this->objToolkit->formTextRow($this->getText("image_xy_hint"));
		$strXY .= $this->objToolkit->formInputText("image_y", $this->getText("image_y"), (isset($arrElementData["image_y"]) ? $arrElementData["image_y"] : "" ));

		if ( (isset($arrElementData["image_x"]) && $arrElementData["image_x"] > 0 ) || ( isset($arrElementData["image_y"]) && $arrElementData["image_y"] > 0 )) {
		    $strReturn .= $strXY;
		} else {
		    $this->addOptionalFormElement($strXY);
		}

        //load templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_image");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

        if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("image_template", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["image_template"]) ? $arrElementData["image_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("image_template", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["image_template"]) ? $arrElementData["image_template"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("image_title");

		return $strReturn;
	}

	/**
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {
	    $arrData = $this->loadElementData();
        return uniStrTrim(htmlStripTags($arrData["image_image"]), 60);
	}


    /**
     * Modifies the passed params in order to have a proper data record in the database.
     * Called right before saving the element to the database.
     *
     * @return void
     */
    public function doBeforeSaveToDb() {
        $this->arrParamData["image_image"] = str_replace(_webpath_, "", $this->arrParamData["image_image"]);
    }


}
