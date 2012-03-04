<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * Class to handle the admin-part of the element
 *
 * @package module_languages
 * @author sidler@mulchprod.de
 */
class class_element_languageswitch_admin extends class_element_admin implements interface_admin_element {


	public function __construct() {

        $this->setArrModuleEntry("name", "element_languageswitch");
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
        $this->setArrModuleEntry("tableColumns", "char1");
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

        //Load the available templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_languageswitch");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));

        $strReturn .= $this->objToolkit->setBrowserFocus("char1");

		return $strReturn;
	}

	/**
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {
	    return "";
	}


}
