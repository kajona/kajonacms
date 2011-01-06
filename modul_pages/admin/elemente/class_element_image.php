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
 * @package modul_pages
 */
class class_element_image extends class_element_admin implements interface_admin_element {


	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_image";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]	 		= _dbprefix_."element_image";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]   = "image_title|char,image_link|char,image_image|char,image_x|number,image_y|number,image_template|char";

		parent::__construct($arrModule);
	}


	/**
	 * Returns the element-part of the admin-form
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";
		$strReturn .= $this->objToolkit->formInputText("image_title", $this->getText("image_title"), (isset($arrElementData["image_title"]) ? $arrElementData["image_title"] : "" ));
		$strReturn .= $this->objToolkit->formInputPageSelector("image_link", $this->getText("image_link"), (isset($arrElementData["image_link"]) ? $arrElementData["image_link"] : "" ));
		$strReturn .= $this->objToolkit->formInputFileSelector("image_image", $this->getText("image_image"), (isset($arrElementData["image_image"]) ? $arrElementData["image_image"] : "" ), _filemanager_default_imagesrepoid_);

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
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/element_image", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

        if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("image_template", $arrTemplatesDD, $this->getText("image_template"), (isset($arrElementData["image_template"]) ? $arrElementData["image_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("image_template", $arrTemplatesDD, $this->getText("image_template"), (isset($arrElementData["image_template"]) ? $arrElementData["image_template"] : "" ));

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
?>