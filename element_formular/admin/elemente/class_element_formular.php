<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the formular-element
 *
 * @package modul_pages
 */
class class_element_formular extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_formular";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 	    = _dbprefix_."element_formular";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]   = "formular_class|char,formular_email|char,formular_success|char,formular_error|char";

		parent::__construct($arrModule);
	}

   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";

		//Build the form
		$strReturn .= $this->objToolkit->formInputText("formular_email", $this->getText("formular_email"), (isset($arrElementData["formular_email"]) ? $arrElementData["formular_email"] : ""));
		$strReturn .= $this->objToolkit->formInputText("formular_success", $this->getText("formular_success"), (isset($arrElementData["formular_success"]) ? $arrElementData["formular_success"] : ""));
		$strReturn .= $this->objToolkit->formInputText("formular_error", $this->getText("formular_error"), (isset($arrElementData["formular_error"]) ? $arrElementData["formular_error"] : ""));
		//Load the available classes
		$objFilesystem = new class_filesystem();
		$arrClasses = $objFilesystem->getFilelist("/portal/forms", ".php");
		$arrClassesDD = array();
		if(count($arrClasses) > 0) {
			foreach($arrClasses as $strClass) {
				$arrClassesDD[$strClass] = $strClass;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("formular_class", $arrClassesDD, $this->getText("formular_class"), (isset($arrElementData["formular_class"]) ? $arrElementData["formular_class"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("formular_email");

		return $strReturn;
	}


}
?>