<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_portallogin.php 3998 2011-07-15 12:18:29Z sidler $                                   *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the portallogin-element
 *
 * @package element_portallogin
 * @author sidler@mulchprod.de
 */
class class_element_portallogin_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_portallogin");
        $this->setArrModuleEntry("table", _dbprefix_."element_plogin");
        $this->setArrModuleEntry("tableColumns", "portallogin_template,portallogin_error,portallogin_success,portallogin_logout_success,portallogin_profile,portallogin_pwdforgot,portallogin_editmode");
        parent::__construct();
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
		//Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_portallogin", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		
        if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("portallogin_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["portallogin_template"]) ? $arrElementData["portallogin_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("portallogin_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["portallogin_template"]) ? $arrElementData["portallogin_template"] : "" ));
        
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_error", $this->getLang("portallogin_error"), (isset($arrElementData["portallogin_error"]) ? $arrElementData["portallogin_error"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_success", $this->getLang("commons_page_success"), (isset($arrElementData["portallogin_success"]) ? $arrElementData["portallogin_success"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_logout_success", $this->getLang("portallogin_logout_success"), (isset($arrElementData["portallogin_logout_success"]) ? $arrElementData["portallogin_logout_success"] : ""));
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("portallogin_profile_hint"));
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_profile", $this->getLang("portallogin_profile"), (isset($arrElementData["portallogin_profile"]) ? $arrElementData["portallogin_profile"] : ""));
		$strReturn .= $this->objToolkit->formInputPageSelector("portallogin_pwdforgot", $this->getLang("portallogin_pwdforgot"), (isset($arrElementData["portallogin_pwdforgot"]) ? $arrElementData["portallogin_pwdforgot"] : ""));

        $arrKeyValues = array(
           0 => $this->getLang("portallogin_editmode_0"),
           1 => $this->getLang("portallogin_editmode_1")
        );
		$strReturn .= $this->objToolkit->formInputDropdown("portallogin_editmode", $arrKeyValues, $this->getLang("portallogin_editmode"), (isset($arrElementData["portallogin_editmode"]) ? $arrElementData["portallogin_editmode"] : ""));
                                                 

		$strReturn .= $this->objToolkit->setBrowserFocus("portallogin_template");

		return $strReturn;
	}


}
