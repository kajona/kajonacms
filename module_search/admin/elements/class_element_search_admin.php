<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                *
********************************************************************************************************/

/**
 * Class representing the search element on the admin side
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_element_search_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_search");
        $this->setArrModuleEntry("table", _dbprefix_."element_search");
        $this->setArrModuleEntry("tableColumns", "search_template,search_amount,search_page");
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

		//Build the form
		$strReturn .= $this->objToolkit->formInputText("search_amount", $this->getLang("search_amount"), (isset($arrElementData["search_amount"]) ? $arrElementData["search_amount"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("search_page", $this->getLang("commons_result_page"), (isset($arrElementData["search_page"]) ? $arrElementData["search_page"] : "" ));
		//Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_search");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("search_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["search_template"]) ? $arrElementData["search_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("search_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["search_template"]) ? $arrElementData["search_template"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("search_amount");

		return $strReturn;
	}

}
