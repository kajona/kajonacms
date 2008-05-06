<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_tagcloud.php																		    *
* 	Admin-class of the tagcloud-element															        *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_tagcloud.php 1884 2007-12-26 15:04:48Z sidler $                                  *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

/**
 * Class to handle the admin-stuff of the tagcloud-element
 *
 * @package modul_pages
 *
 */
class class_element_tagcloud extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "element_tagcloud";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"] 		    = _dbprefix_."element_universal";
		$arrModul["modul"]			= "elemente";
		//char1 => template, char2 => detailspage
		$arrModul["tableColumns"]   = "char1|char,char2|char";

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

		//Load the available templates
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/portal/element_tagcloud/", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getText("tagcloud_template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));
		$strReturn .= $this->objToolkit->formInputPageSelector("char2", $this->getText("tagcloud_detailspage"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" ));

		return $strReturn;
	}


} //class_element_tagcloud.php
?>