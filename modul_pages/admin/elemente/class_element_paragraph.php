<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

/**
 * Admin class to handle the paragraphs
 *
 * @package modul_pages
 */
class class_element_paragraph extends class_element_admin implements interface_admin_element {

	/**
	 * Contructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_paragraph";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]			= _dbprefix_."element_paragraph";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]   = "";

		parent::__construct($arrModule);
	}


	/**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData)	{

        //$arrElementData["paragraph_content"] = uniStrReplace("%%", "\%\%", $arrElementData["paragraph_content"]);

		$strReturn = "";

		$strReturn .= $this->objToolkit->formInputText("paragraph_title", $this->getText("paragraph_title"), (isset($arrElementData["paragraph_title"]) ? $arrElementData["paragraph_title"] : ""));
		$strReturn .= $this->objToolkit->formWysiwygEditor("paragraph_content", $this->getText("paragraph_content"), (isset($arrElementData["paragraph_content"]) ? $arrElementData["paragraph_content"] : ""));
		$strReturn .= $this->objToolkit->formInputText("paragraph_image", $this->getText("paragraph_image"), (isset($arrElementData["paragraph_image"]) ? $arrElementData["paragraph_image"] : ""), "inputText", getLinkAdminPopup("folderview", "list", "&form_element=paragraph_image&systemid="._filemanager_default_imagesrepoid_, $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
		$strReturn .= $this->objToolkit->formInputPageSelector("paragraph_link", $this->getText("paragraph_link"), (isset($arrElementData["paragraph_link"]) ? $arrElementData["paragraph_link"] : ""));

        //load templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/element_paragraph", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("paragraph_template", $arrTemplatesDD, $this->getText("paragraph_template"), (isset($arrElementData["paragraph_template"]) ? $arrElementData["paragraph_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("paragraph_template", $arrTemplatesDD, $this->getText("paragraph_template"), (isset($arrElementData["paragraph_template"]) ? $arrElementData["paragraph_template"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("paragraph_title");
		return $strReturn;
	}


	/**
	 * saves the submitted data to the database
	 * It IS wanted to not let the system save the element here!
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function actionSave($strSystemid) {

	    //do some cleanups
        $strContent = processWysiwygHtmlContent($this->getParam("paragraph_content"));
		$strImage = str_replace(_webpath_, "_webpath_", $this->getParam("paragraph_image"));

		//and save to database
		$strQuery = "UPDATE ".$this->arrModule["table"]." SET
				paragraph_title = '".dbsafeString($this->getParam("paragraph_title"))."',
				paragraph_content = '".dbsafeString($strContent, false)."',
				paragraph_link = '".dbsafeString($this->getParam("paragraph_link"))."',
				paragraph_image = '".dbsafeString($strImage)."',
				paragraph_template = '".dbsafeString($this->getParam("paragraph_template"))."'
				WHERE content_id='".dbsafeString($strSystemid)."'";

		if($this->objDB->_query($strQuery))
			return true;
		else
			return false;
	}

	/**
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {

	    $arrData = $this->loadElementData();
	    if($arrData["paragraph_title"] != "")
	       return uniStrTrim(htmlStripTags($arrData["paragraph_title"]), 60);
	    else
	       return uniStrTrim(htmlStripTags($arrData["paragraph_content"]), 60);
	}


}
?>