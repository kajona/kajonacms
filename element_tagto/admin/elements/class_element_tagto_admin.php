<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/


/**
 * Class to handle the admin-stuff of the tagto-element
 *
 * @package element_tagto
 * @author sidler@mulchprod.de
 */
class class_element_tagto_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_tagto");
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
        $this->setArrModuleEntry("tableColumns", "char1");
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

		//Load the available templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_tagto/", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		
        $strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("char1");

		return $strReturn;
	}


}
