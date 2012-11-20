<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class representing the admin-part of the votings element
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 */
class class_element_votings_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_votings");
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
        $this->setArrModuleEntry("tableColumns", "char1,char2,int1");
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
		//Load all votings available
        $arrRawVotings = class_module_votings_voting::getObjectList(true);
        $arrVotings = array();

        foreach ($arrRawVotings as $objOneVoting)
            $arrVotings[$objOneVoting->getSystemid()] = $objOneVoting->getStrTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("char1", $arrVotings, $this->getLang("votings_voting"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));

		//Load the available templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_votings", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("char2", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("char2", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" ));


        //the mode itself
        $arrModeDD = array(
            "0" => $this->getLang("votings_mode_voting"),
            "1" => $this->getLang("votings_mode_result")
        );
        $strReturn .= $this->objToolkit->formInputDropdown("int1", $arrModeDD, $this->getLang("votings_mode"), (isset($arrElementData["int1"]) ? $arrElementData["int1"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("char1");

		return $strReturn;
	}


}
