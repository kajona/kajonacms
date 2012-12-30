<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the mediaplayer-element
 *
 * @package element_mediaplayer
 * @author sidler@mulchprod.de
 */
class class_element_mediaplayer_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_mediaplayer");
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
        $this->setArrModuleEntry("tableColumns", "char1,char2,char3,int1,int2");

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
		$strReturn .= $this->objToolkit->formInputFileSelector("char1", $this->getLang("mediaplayer_file"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : ""));
		$strReturn .= $this->objToolkit->formInputFileSelector("char2", $this->getLang("mediaplayer_preview"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : ""));

		//Load the available templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_mediaplayer", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("char3", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char3"]) ? $arrElementData["char3"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("char3", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char3"]) ? $arrElementData["char3"] : "" ));

		$strReturn .= $this->objToolkit->formInputText("int1", $this->getLang("mediaplayer_width"), (isset($arrElementData["int1"]) ? $arrElementData["int1"] : ""));
		$strReturn .= $this->objToolkit->formInputText("int2", $this->getLang("mediaplayer_height"), (isset($arrElementData["int2"]) ? $arrElementData["int2"] : ""));

		$strReturn .= $this->objToolkit->setBrowserFocus("char1");

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
        $this->arrParamData["char2"] = str_replace(_webpath_, "_webpath_", $this->arrParamData["char2"]);
    }

}
