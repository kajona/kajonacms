<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

/**
 * Admin class to handle the paragraphs
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 */
class class_element_paragraph_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

        $this->setArrModuleEntry("name", "element_paragraph");
        $this->setArrModuleEntry("table", _dbprefix_."element_paragraph");
		parent::__construct();
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

		$strReturn .= $this->objToolkit->formInputText("paragraph_title", $this->getLang("commons_title"), (isset($arrElementData["paragraph_title"]) ? $arrElementData["paragraph_title"] : ""));
		$strReturn .= $this->objToolkit->formWysiwygEditor("paragraph_content", $this->getLang("paragraph_content"), (isset($arrElementData["paragraph_content"]) ? $arrElementData["paragraph_content"] : ""));
		$strReturn .= $this->objToolkit->formInputImageSelector("paragraph_image", $this->getLang("commons_image"), (isset($arrElementData["paragraph_image"]) ? $arrElementData["paragraph_image"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("paragraph_link", $this->getLang("paragraph_link"), (isset($arrElementData["paragraph_link"]) ? $arrElementData["paragraph_link"] : ""));

        //load templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_paragraph");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("paragraph_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["paragraph_template"]) ? $arrElementData["paragraph_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("paragraph_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["paragraph_template"]) ? $arrElementData["paragraph_template"] : "" ));

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
		$strImage = str_replace(_webpath_, "", $this->getParam("paragraph_image"));

		//and save to database
		$strQuery = "UPDATE ".$this->arrModule["table"]." SET
				paragraph_title = '".dbsafeString($this->arrParamData["paragraph_title"])."',
				paragraph_content = '".dbsafeString($strContent, false)."',
				paragraph_link = '".dbsafeString($this->arrParamData["paragraph_link"])."',
				paragraph_image = '".dbsafeString($strImage)."',
				paragraph_template = '".dbsafeString($this->arrParamData["paragraph_template"])."'
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
