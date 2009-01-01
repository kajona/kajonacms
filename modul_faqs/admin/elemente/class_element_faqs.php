<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/
//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

include_once(_systempath_."/class_modul_faqs_category.php");
include_once(_systempath_."/class_modul_faqs_faq.php");
include_once(_systempath_."/class_filesystem.php");


/**
 * Class representing the admin-part of the faqs element
 * 
 * @package modul_faqs
 *
 */
class class_element_faqs extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModule["name"] 			= "element_faqs";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_faqs";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]  = "faqs_category|char,faqs_template|char";

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
		//Load all faqcats available
        $arrRawCats = class_modul_faqs_category::getCategories();
        $arrCats = array();
        //addd an "i want all" cat ;)
        $arrCats["0"] = $this->getText("all_cats");
        
        foreach ($arrRawCats as $objOneCat)
            $arrCats[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("faqs_category", $arrCats, $this->getText("faqs_category"), (isset($arrElementData["faqs_category"]) ? $arrElementData["faqs_category"] : "" ));

		//Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/modul_faqs", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("faqs_template", $arrTemplatesDD, $this->getText("faqs_template"), (isset($arrElementData["faqs_template"]) ? $arrElementData["faqs_template"] : "" ));
        //and finally offer the different modes
		return $strReturn;
	}


} //class_element_absatz.php
?>