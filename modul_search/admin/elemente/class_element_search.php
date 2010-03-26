<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

/**
 * Class representing the search element on the admin side
 *
 * @package modul_search
 *
 */
class class_element_search extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_suche";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_search";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]     = "search_template|char,search_amount|number,search_page|char";

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

		//Build the form
		$strReturn .= $this->objToolkit->formInputText("search_amount", $this->getText("search_amount"), (isset($arrElementData["search_amount"]) ? $arrElementData["search_amount"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("search_page", $this->getText("search_page"), (isset($arrElementData["search_page"]) ? $arrElementData["search_page"] : "" ));
		//Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/modul_search", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("search_template", $arrTemplatesDD, $this->getText("search_template"), (isset($arrElementData["search_template"]) ? $arrElementData["search_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("search_template", $arrTemplatesDD, $this->getText("search_template"), (isset($arrElementData["search_template"]) ? $arrElementData["search_template"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("search_amount");

		return $strReturn;
	}

}
?>