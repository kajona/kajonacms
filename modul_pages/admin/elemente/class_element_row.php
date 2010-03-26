<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/



/**
 * Admin class to handle the paragraphs
 * @package modul_pages
 *
 */
class class_element_row extends class_element_admin implements interface_admin_element {

	/**
	 * Contructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_row";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]			= _dbprefix_."element_paragraph";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]  = "paragraph_title|char,paragraph_template|char";

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
		$strReturn .= $this->objToolkit->formInputText("paragraph_title", $this->getText("paragraph_title"), (isset($arrElementData["paragraph_title"]) ? $arrElementData["paragraph_title"] : ""));

        //load templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/element_row", ".tpl");
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
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {
	    $arrData = $this->loadElementData();
        return uniStrTrim(htmlStripTags($arrData["paragraph_title"]), 60);
	}

	/**
     * Overwrite this function, if you want to validate passed form-input
     *
     * @return mixed
     */
    protected function getRequiredFields() {
        return array("paragraph_title" => "string");
    }


}
?>