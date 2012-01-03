<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/



/**
 * Admin class to handle the paragraphs
 * @package module_pages
 * @author sidler@mulchprod.de
 */
class class_element_row_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

        $this->setArrModuleEntry("name", "element_row");
        $this->setArrModuleEntry("table", _dbprefix_."element_paragraph");
        $this->setArrModuleEntry("tableColumns", "paragraph_title|char,paragraph_template|char");

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
		$strReturn .= $this->objToolkit->formInputText("paragraph_title", $this->getLang("commons_title"), (isset($arrElementData["paragraph_title"]) ? $arrElementData["paragraph_title"] : ""));

        //load templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_row");
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
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {
	    $arrData = $this->loadElementData();
        return uniStrTrim(htmlStripTags(isset($arrData["paragraph_title"]) ? $arrData["paragraph_title"] : ""), 60);
	}

	/**
     * Overwrite this function, if you want to validate passed form-input
     *
     * @return mixed
     */
    public function getRequiredFields() {
        return array("paragraph_title" => "string");
    }


}
