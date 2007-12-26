<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_rssfeed.php																		    *
* 	Admin-class of the rssfeed-element															        *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

/**
 * Class to handle the admin-stuff of the rssfeed-element
 *
 * @package modul_pages
 *
 */
class class_element_rssfeed extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "element_rssfeed";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"] 		    = _dbprefix_."element_universal";
		$arrModul["modul"]			= "elemente";

		$arrModul["tableColumns"]   = "char1|char,char2|char,int1|number";

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
		
		//url_fopen allowed?
		if($this->objConfig->getPhpIni("allow_url_fopen") != 1)
            $strReturn .= $this->objToolkit->warningBox($this->getText("rssfeed_urlfopen"));
            
		//Build the form
		//Load the available templates
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/portal/element_rssfeed", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getText("rssfeed_template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));
		$strReturn .= $this->objToolkit->formInputText("char2", $this->getText("rssfeed_url"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : ""));
		$strReturn .= $this->objToolkit->formInputText("int1", $this->getText("rssfeed_numberofposts"), (isset($arrElementData["int1"]) ? $arrElementData["int1"] : ""));

		return $strReturn;
	}


} //class_element_downloads.php
?>