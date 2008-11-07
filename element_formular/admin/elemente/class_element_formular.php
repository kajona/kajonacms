<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

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
		$arrModul["name"] 			= "element_formular";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"] 		    = _dbprefix_."element_formular";
		$arrModul["modul"]			= "elemente";

		$arrModul["tableColumns"]   = "formular_class|char,formular_email|char,formular_success|char,formular_error|char";

		parent::__construct($arrModul);
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
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrClasses = $objFilesystem->getFilelist("/portal/forms", ".php");
		$arrClassesDD = array();
		if(count($arrClasses) > 0) {
			foreach($arrClasses as $strClass) {
				$arrClassesDD[$strClass] = $strClass;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("formular_class", $arrClassesDD, $this->getText("formular_class"), (isset($arrElementData["formular_class"]) ? $arrElementData["formular_class"] : "" ));
		return $strReturn;
	}


}
?>