<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * Class representing the admin-part of the guestbook element
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 */
class class_element_guestbook_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct(){
		$this->setArrModuleEntry("name", "element_guestbook");
		$this->setArrModuleEntry("table", _dbprefix_."element_guestbook");
		$this->setArrModuleEntry("tableColumns", "guestbook_id,guestbook_template,guestbook_amount");

		parent::__construct();
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

        $objGuestbooks = class_module_guestbook_guestbook::getObjectList();
        $arrGuestbooks = array();
        foreach ($objGuestbooks as $objOneGuestbook)
            $arrGuestbooks[$objOneGuestbook->getSystemid()] = $objOneGuestbook->getStrGuestbookTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown(
            "guestbook_id", $arrGuestbooks, $this->getLang("guestbook_id"), (isset($arrElementData["guestbook_id"]) ? $arrElementData["guestbook_id"] : "" )
        );
		$strReturn .= $this->objToolkit->formInputText(
            "guestbook_amount", $this->getLang("guestbook_amount"), (isset($arrElementData["guestbook_amount"]) ? $arrElementData["guestbook_amount"] : "")
        );
		//Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_guestbook");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		
        if(count($arrTemplates) == 1)
            $this->addOptionalFormElement(
                $this->objToolkit->formInputDropdown(
                    "guestbook_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["guestbook_template"]) ? $arrElementData["guestbook_template"] : "" )
                )
            );
        else
            $strReturn .= $this->objToolkit->formInputDropdown(
                "guestbook_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["guestbook_template"]) ? $arrElementData["guestbook_template"] : "" )
            );

		$strReturn .= $this->objToolkit->setBrowserFocus("guestbook_id");

		return $strReturn;
	}

}
