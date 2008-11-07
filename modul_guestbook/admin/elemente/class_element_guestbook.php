<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/


//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");


//include needed classes
include_once(_systempath_."/class_modul_guestbook_post.php");
include_once(_systempath_."/class_modul_guestbook_guestbook.php");

/**
 * Class representing the admin-part of the guestbook element
 *
 * @package modul_guestbook
 */
class class_element_guestbook extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct(){
		$arrModul["name"] 			= "element_gaestebuch";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"] 		    = _dbprefix_."element_guestbook";
		$arrModul["modul"]			= "elemente";

		$arrModul["tableColumns"]   = "guestbook_id|char,guestbook_template|char,guestbook_amount|number";

		parent::__construct($arrModul);
	}


   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData)	{
		$strReturn = "";
		//Load all guestbooks available

        $objGuestbooks = class_modul_guestbook_guestbook::getGuestbooks();
        $arrGuestbooks = array();
        foreach ($objGuestbooks as $objOneGuestbook)
            $arrGuestbooks[$objOneGuestbook->getSystemid()] = $objOneGuestbook->getGuestbookTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("guestbook_id", $arrGuestbooks, $this->getText("guestbook_id"), (isset($arrElementData["guestbook_id"]) ? $arrElementData["guestbook_id"] : "" ));
		$strReturn .= $this->objToolkit->formInputText("guestbook_amount", $this->getText("guestbook_amount"), (isset($arrElementData["guestbook_amount"]) ? $arrElementData["guestbook_amount"] : ""));
		//Load the available templates
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/portal/modul_guestbook", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("guestbook_template", $arrTemplatesDD, $this->getText("guestbook_template"), (isset($arrElementData["guestbook_template"]) ? $arrElementData["guestbook_template"] : "" ));
        //and finally offer the different modes
		return $strReturn;
	}

}
?>