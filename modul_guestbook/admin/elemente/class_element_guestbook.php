<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/


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
        $arrModule = array();
		$arrModule["name"] 			= "element_guestbook";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 	    = _dbprefix_."element_guestbook";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]   = "guestbook_id|char,guestbook_template|char,guestbook_amount|number";

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
		//Load all guestbooks available

        $objGuestbooks = class_modul_guestbook_guestbook::getGuestbooks();
        $arrGuestbooks = array();
        foreach ($objGuestbooks as $objOneGuestbook)
            $arrGuestbooks[$objOneGuestbook->getSystemid()] = $objOneGuestbook->getGuestbookTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("guestbook_id", $arrGuestbooks, $this->getText("guestbook_id"), (isset($arrElementData["guestbook_id"]) ? $arrElementData["guestbook_id"] : "" ));
		$strReturn .= $this->objToolkit->formInputText("guestbook_amount", $this->getText("guestbook_amount"), (isset($arrElementData["guestbook_amount"]) ? $arrElementData["guestbook_amount"] : ""));
		//Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/modul_guestbook", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("guestbook_template", $arrTemplatesDD, $this->getText("guestbook_template"), (isset($arrElementData["guestbook_template"]) ? $arrElementData["guestbook_template"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("guestbook_id");

		return $strReturn;
	}

}
?>