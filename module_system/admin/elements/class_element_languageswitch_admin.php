<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * Class to handle the admin-part of the element
 *
 * @package modul_languages
 * @author sidler@mulchprod.de
 */
class class_element_languageswitch_admin extends class_element_admin implements interface_admin_element {


	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_languageswitch";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]	 		= _dbprefix_."element_universal";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]   = "char1|char";

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

        //Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/element_languageswitch", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));

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
?>