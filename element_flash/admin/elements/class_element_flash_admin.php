<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_flash.php 4042 2011-07-25 17:37:44Z sidler $                             *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the flash-element
 *
 * @package element_flash
 * @author jschroeter@kajona.de
 */
class class_element_flash_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_flash");
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
        $this->setArrModuleEntry("tableColumns", "char1,char2,int1,int2");
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
		$strReturn .= $this->objToolkit->formInputFileSelector("char1", $this->getLang("flash_file"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : ""));

		//Load the available templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_flash", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("char2", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" ));

		$strReturn .= $this->objToolkit->formInputText("int1", $this->getLang("flash_width"), (isset($arrElementData["int1"]) ? $arrElementData["int1"] : ""));
		$strReturn .= $this->objToolkit->formInputText("int2", $this->getLang("flash_height"), (isset($arrElementData["int2"]) ? $arrElementData["int2"] : ""));

		return $strReturn;
	}

    /**
     * Modifies the passed params in order to have a proper data record in the database.
     * Called right before saving the element to the database.
     *
     * @return void
     */
    public function doBeforeSaveToDb() {
        $this->arrParamData["char1"] = str_replace(_webpath_, "_webpath_", $this->arrParamData["char1"]);
    }

}
