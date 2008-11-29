<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$								*
********************************************************************************************************/

//Base class
include_once(_portalpath_."/class_portal.php");

/**
 * Base Class for all portal-elements
 *
 * @package modul_pages
 */
class class_element_portal extends class_portal {
	protected $arrElementData;

	/**
	 * Constructor
	 *
	 * @param mixed $arrModule
	 * @param mixed $arrElementData
	 */
	public function __construct($arrModule, $objElementData) {
		$arrModule["p_name"] 			= "element_portal";
		$arrModule["p_author"] 			= "sidler@mulchprod.de";
		$arrModule["p_nummer"] 			= _pages_elemente_modul_id_;

		parent::__construct($arrModule);

		//Load the data of the current Element and merge it
		$arrTemp = $this->getElementContent($objElementData->getSystemid());
		$this->setSystemid($objElementData->getSystemid());
		$this->arrElementData = $arrTemp;
		//merge the attributes of $objElementData to the array
		$this->arrElementData["page_element_placeholder_placeholder"] = $objElementData->getStrPlaceholder();
		$this->arrElementData["page_element_placeholder_name"] = $objElementData->getStrName();
		$this->arrElementData["page_element_placeholder_element"] = $objElementData->getStrElement();
		$this->arrElementData["page_element_placeholder_title"] = $objElementData->getStrTitle(false);
	}


	/**
	 * Loads the content out of the elements-table
	 *
	 * @param string $strSystemid
	 * @return mixed
	 */
	public function getElementContent($strSystemid) {
	    //table given?
	    if($this->arrModule["table"] != "") {
    		$strQuery = "SELECT *
    						FROM ".$this->arrModule["table"]."
    						WHERE content_id = '".$strSystemid."'";
    		return $this->objDB->getRow($strQuery);
	    }
	    else
	       return array();

	}

	/**
	 * Invokes the element to do the work
	 * If enabled, passes to addPortalEditorCode(). This adds the element-based pe-code.
	 * If modules want to create pe code, they have to call the static method addPortalEditorCode
	 * on their own!
	 *
	 * @return string
	 */
	public function getElementOutput() {
	    if(_pages_portaleditor_ == "true") {
	        //Check needed rights
	        if($this->objRights->rightEdit($this->getSystemid())) {
	            $arrConfig = array();
	            $arrConfig["pe_module"] = "";
	            $arrConfig["pe_action"] = "";

	            if(isset($this->arrModule["pe_module"]))
	                $arrConfig["pe_module"] = $this->arrModule["pe_module"];
	            if(isset($this->arrModule["pe_action"]))
	                $arrConfig["pe_action"] = $this->arrModule["pe_action"];

		        $strReturn = $this->addPortalEditorCode($this->loadData(), $this->getSystemid(), $arrConfig);
	        }
		    else
		        $strReturn = $this->loadData();
	    }
	    else
	       $strReturn = $this->loadData();

	    //add an anchor to jump to, but exclude navigation-elements
	    $strReturn = $this->getAnchorTag().$strReturn;

		return $strReturn;
	}


	/**
	 * Creates the code surrounding the element.
	 * Creates the "entry" to the portal-editor
	 *
	 * @param string $strContent elements' output
	 * @param string $strSystemid elements' systemid
	 * @param array $arrConfig : pe_module, pe_action, [pe_action_new, pe_action_new_params]
	 * @param bool $bitSpace add space before code?
	 * @return string
	 * @static
	 */
	public static function addPortalEditorCode($strContent, $strSystemid, $arrConfig, $bitSpace = false) {
	    $strEditorDiv = "";
        $strReturn = "";

        if(_pages_portaleditor_ == "true" && class_carrier::getInstance()->getObjRights()->rightEdit($strSystemid) && class_carrier::getInstance()->getObjSession()->isAdmin()) {

            if(class_carrier::getInstance()->getObjSession()->getSession("pe_disable") != "true" ) {
            	
            	//switch the text-language temporary
            	$strPortalLanguage = class_carrier::getInstance()->getObjText()->getStrTextLanguage();
                class_carrier::getInstance()->getObjText()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());
                
                //fetch the language to set the correct admin-lang
                $objLanguages = new class_modul_languages_language();
                $strAdminLangParam = "&language=".$objLanguages->getPortalLanguage();
                
            	
                $strModule = "pages_content";
                $strAction = "editElement";
                //param-inits ---------------------------------------
                //Generate url to the admin-area
                if($arrConfig["pe_module"] != "") {
                    $strModule = $arrConfig["pe_module"];
                }
                //---------------------------------------------------

                //---------------------------------------------------
                //Link to edit current element
                $strEditLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    //Load element-data
                    $strEditLink = getLinkAdminPopup($strModule, $strAction, "&systemid=".$strSystemid.$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_edit", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_edit", "pages", "admin"), "", "500", "650", "", false, true);
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_edit"]) && $arrConfig["pe_action_edit"] != "") {
                        $strEditLink = getLinkAdminPopup($strModule, $arrConfig["pe_action_edit"], $arrConfig["pe_action_edit_params"].$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_edit", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_edit", "pages", "admin"), "", "500", "650", "", false, true);
                    }
                }

                //---------------------------------------------------
                //link to create a new element
                $strNewLink = "";
                //standard: pages_content. test, if element is allowed multiple times at one placeholder
                if($strModule == "pages_content") {
                    //Load element-data
                    $objElement = new class_modul_pages_pageelement($strSystemid);
                    if($objElement->getIntRepeat() == 1) {
                        $strNewLink = getLinkAdminPopup("pages_content", "newElement", "&placeholder=".$objElement->getStrPlaceholder()."&element=".$objElement->getStrElement()."&systemid=".$objElement->getPrevId().$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_new", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_new", "pages", "admin"), "", "500", "650", "", false, true);
                    }
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_new"]) && $arrConfig["pe_action_new"] != "") {
                        $strNewLink = getLinkAdminPopup($strModule, $arrConfig["pe_action_new"], $arrConfig["pe_action_new_params"].$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_new", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_new", "pages", "admin"), "", "500", "650", "", false, true);
                    }
                }

                //---------------------------------------------------
                //link to delete the current element
                $strDeleteLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $strDeleteLink = getLinkAdminPopup("pages_content", "deleteElement", "&systemid=".$strSystemid.$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_delete", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_delete", "pages", "admin"), "", "500", "650", "", false, true);
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_delete"]) && $arrConfig["pe_action_delete"] != "") {
                        $strDeleteLink = getLinkAdminPopup($strModule, $arrConfig["pe_action_delete"], $arrConfig["pe_action_delete_params"].$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_delete", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_delete", "pages", "admin"), "", "500", "650", "", false, true);
                    }
                }

                //---------------------------------------------------
                //link to shift element up
                $strShiftUp = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $objElement = new class_modul_pages_pageelement($strSystemid);
                    if($objElement->getIntRepeat() == 1)
                        $strShiftUp = getLinkAdminPopup("pages_content", "elementSortUp", "&systemid=".$strSystemid.$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_shiftUp", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_shiftUp", "pages", "admin"), "", "500", "650", "", false, true);
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_shiftUp"]) && $arrConfig["pe_action_shiftUp"] != "") {
                        $strShiftUp = getLinkAdminPopup($strModule, $arrConfig["pe_action_shiftUp"], $arrConfig["pe_action_shiftUp_params"].$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_shiftUp", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_shiftUp", "pages", "admin"), "", "500", "650", "", false, true);
                    }
                }

                //---------------------------------------------------
                //link to shift element down
                $strShiftDown = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $objElement = new class_modul_pages_pageelement($strSystemid);
                    if($objElement->getIntRepeat() == 1)
                        $strShiftDown = getLinkAdminPopup("pages_content", "elementSortDown", "&systemid=".$strSystemid.$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_shiftDown", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_shiftDown", "pages", "admin"), "", "500", "650", "", false, true);
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_shiftDown"]) && $arrConfig["pe_action_shiftDown"] != "") {
                        $strShiftDown = getLinkAdminPopup($strModule, $arrConfig["pe_action_shiftDown"], $arrConfig["pe_action_shiftDown_params"].$strAdminLangParam, class_carrier::getInstance()->getObjText()->getText("pe_shiftDown", "pages", "admin"), class_carrier::getInstance()->getObjText()->getText("pe_shiftDown", "pages", "admin"), "", "500", "650", "", false, true);
                    }
                }

                //---------------------------------------------------
                // layout generation

                if($bitSpace) {
                    $strReturn .= "<br />";
                }
                $strReturn .= class_carrier::getInstance()->getObjToolkit("portal")->getPeActionToolbar($strSystemid, array($strEditLink, $strNewLink, $strDeleteLink, $strShiftUp, $strShiftDown), $strContent);
                
                //reset the portal texts language
                class_carrier::getInstance()->getObjText()->setStrTextLanguage($strPortalLanguage);
            }
            else
                $strReturn = $strContent;
        }
        else
            $strReturn = $strContent;
        return $strReturn;
	}

	/**
	 * Dummy method, element needs to overwrite it
	 *
	 * @return string
	 */
	protected function loadData() {
	    return "Element needs to overwrite loadData()!";
	}
	
	/**
	 * Generates an anchor tag enabling navigation-points to jump to specific page-elements.
	 * can be overwritten by subclasses
	 *
	 * @return string
	 */
	protected function getAnchorTag() {
		return "<a name=\"".$this->getSystemid()."\" class=\"hiddenAnchor\"></a>";
	}
}

?>