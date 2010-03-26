<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
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

		$arrModule["tableColumns"]   = "";

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
		$strReturn .= $this->objToolkit->formInputText("image_image", $this->getText("image_image"), (isset($arrElementData["image_image"]) ? $arrElementData["image_image"] : "" ), "inputText", getLinkAdminPopup("folderview", "list", "&form_element=image_image&systemid="._filemanager_default_imagesrepoid_, $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("image_xy_hint"));
		$strReturn .= $this->objToolkit->formInputText("image_x", $this->getText("image_x"), (isset($arrElementData["image_x"]) ? $arrElementData["image_x"] : "" ));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("image_xy_hint"));
		$strReturn .= $this->objToolkit->formInputText("image_y", $this->getText("image_y"), (isset($arrElementData["image_y"]) ? $arrElementData["image_y"] : "" ));

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
            $this->addHiddenFormElement($this->objToolkit->formInputDropdown("image_template", $arrTemplatesDD, $this->getText("image_template"), (isset($arrElementData["image_template"]) ? $arrElementData["image_template"] : "" )));
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
	 * saves the submitted data to the database
	 * It IS wanted to not let the system save the element here!
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function actionSave($strSystemid) {
		$strImage = $this->getParam("image_image");
		//We have to replace the webpath to remain flexible
		$strImage = str_replace(_webpath_, "", $strImage);
		//Secure the text
		//And to the database
		$strQuery = "UPDATE ".$this->arrModule["table"]." SET
				image_title = '".dbsafeString($this->getParam("image_title"))."',
				image_link = '".dbsafeString($this->getParam("image_link"))."',
				image_image = '".dbsafeString($strImage)."',
				image_x = ".(int)dbsafeString($this->getParam("image_x")).",
				image_y = ".(int)dbsafeString($this->getParam("image_y")).",
				image_template = '".dbsafeString($this->getParam("image_template"))."'
				WHERE content_id='".dbsafeString($strSystemid)."'";

		if($this->objDB->_query($strQuery))
			return true;
		else
			return false;
	}


}
?>