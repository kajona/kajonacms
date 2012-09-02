<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_userlist.php 4042 2011-07-25 17:37:44Z sidler $                               *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the userlist-element
 *
 * @package element_userlist
 * @author sidler@mulchprod.de
 */
class class_element_userlist_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_userlist");
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
        $this->setArrModuleEntry("tableColumns", "char1,char2,char3,int1");
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
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_userlist", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

        //load the available groups
        $arrGroups = class_module_user_group::getAllGroups();
		$arrGroupsDD = array();
        $arrGroupsDD[0] = $this->getLang("userlist_all");
		if(count($arrGroups) > 0) {
			foreach($arrGroups as $objOneGroup) {
				$arrGroupsDD[$objOneGroup->getSystemid()] = $objOneGroup->getStrName();
			}
		}

        $arrGroupsActive = array(
            0 => $this->getLang("userlist_status_all"),
            1 => $this->getLang("userlist_status_active"),
            2 => $this->getLang("userlist_status_inactive")
        );

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("userlist_template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("userlist_template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));
        
		$strReturn .= $this->objToolkit->formInputDropdown("char2", $arrGroupsDD, $this->getLang("userlist_group"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" ));
		$strReturn .= $this->objToolkit->formInputDropdown("int1", $arrGroupsActive, $this->getLang("userlist_status"), (isset($arrElementData["int1"]) ? $arrElementData["int1"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("char1");

		return $strReturn;
	}


    public function getRequiredFields() {
        return array("char1" => "string");
    }
}
