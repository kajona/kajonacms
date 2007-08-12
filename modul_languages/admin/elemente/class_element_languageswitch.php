<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_languageswitch.php																	*
* 	Admin-class of the languageswitch element															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

//base class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

/**
 * Class to handle the admin-part of the element
 * 
 * @package modul_languages 
 */
class class_element_languageswitch extends class_element_admin implements interface_admin_element {


	public function __construct() {
		$arrModul["name"] 			= "element_languageswitch";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"]	 		= "";
		$arrModul["modul"]			= "elemente";

		$arrModul["tableColumns"]   = "";

		parent::__construct($arrModul);
	}


	/**
	 * Returns the element-part of the admin-form
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";
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

	/**
	 * Dummy for saving, returns true, no foreign table used by this element
	 *
	 * @return bool true
	 */
    public function actionSave() {
        return true;
    }
}
?>