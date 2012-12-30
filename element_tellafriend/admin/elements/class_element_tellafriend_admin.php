<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * Class to handle the admin-stuff of the tellafriend-element
 *
 * @package element_tellafriend
 * @author sidler@mulchprod.de
 */
class class_element_tellafriend_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_tellafriend");
        $this->setArrModuleEntry("table", _dbprefix_."element_tellafriend");
        $this->setArrModuleEntry("tableColumns", "tellafriend_template,tellafriend_error,tellafriend_success");
        parent::__construct();
	}

    /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";

		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_tellafriend", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("tellafriend_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["tellafriend_template"]) ? $arrElementData["tellafriend_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("tellafriend_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["tellafriend_template"]) ? $arrElementData["tellafriend_template"] : "" ));
        
		$strReturn .= $this->objToolkit->formInputPageSelector("tellafriend_error", $this->getLang("tellafriend_error"), (isset($arrElementData["tellafriend_error"]) ? $arrElementData["tellafriend_error"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("tellafriend_success", $this->getLang("tellafriend_success"), (isset($arrElementData["tellafriend_success"]) ? $arrElementData["tellafriend_success"] : ""));

		$strReturn .= $this->objToolkit->setBrowserFocus("tellafriend_template");

		return $strReturn;
	}


}
