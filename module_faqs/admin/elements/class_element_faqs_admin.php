<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_faqs.php 3949 2011-06-25 10:46:38Z sidler $                                       *
********************************************************************************************************/

/**
 * Class representing the admin-part of the faqs element
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 */
class class_element_faqs_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

        $this->setArrModuleEntry("name", "element_faqs");
        $this->setArrModuleEntry("table", _dbprefix_."element_faqs");
        $this->setArrModuleEntry("tableColumns", "faqs_category,faqs_template");

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
		//Load all faqcats available
        $arrRawCats = class_module_faqs_category::getCategories();
        $arrCats = array();
        //addd an "i want all" cat ;)
        $arrCats["0"] = $this->getLang("commons_all_categories");

        foreach ($arrRawCats as $objOneCat)
            $arrCats[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("faqs_category", $arrCats, $this->getLang("commons_category"), (isset($arrElementData["faqs_category"]) ? $arrElementData["faqs_category"] : "" ));

		//Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_faqs");

        $arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("faqs_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["faqs_template"]) ? $arrElementData["faqs_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("faqs_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["faqs_template"]) ? $arrElementData["faqs_template"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("faqs_category");

		return $strReturn;
	}


}
