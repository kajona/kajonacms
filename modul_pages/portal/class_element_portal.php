<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$								*
********************************************************************************************************/

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
        $arrModule["modul"]             = "elemente";
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
	 * @return string
	 * @static
	 */
	public static function addPortalEditorCode($strContent, $strSystemid, $arrConfig) {
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
                    $strEditUrl = getLinkAdminHref($strModule, $strAction, "&systemid=".$strSystemid.$strAdminLangParam."&pe=1");
                    $strEditLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strEditUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_edit", "pages", "admin")."</a>";
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_edit"]) && $arrConfig["pe_action_edit"] != "") {
                        $strEditUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_edit"], $arrConfig["pe_action_edit_params"].$strAdminLangParam."&pe=1");
                        $strEditLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strEditUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_edit", "pages", "admin")."</a>";
                    }
                }

                //---------------------------------------------------
                //link to copy an element to the same or another placeholder
                $strCopyLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    //Load element-data
                    $objElement = new class_modul_pages_pageelement($strSystemid);
                    if($objElement->getIntRepeat() == 1) {
                        $strCopyUrl = getLinkAdminHref("pages_content", "copyElement", "&systemid=".$strSystemid.$strAdminLangParam."&pe=1");
                        $strCopyLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strCopyUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_copy", "pages", "admin")."</a>";
                    }
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_copy"]) && $arrConfig["pe_action_copy"] != "") {
                        $strCopyUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_copy"], $arrConfig["pe_action_copy_params"].$strAdminLangParam."&pe=1");
                        $strCopyLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strCopyUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_copy", "pages", "admin")."</a>";
                    }
                }

                //---------------------------------------------------
                //link to delete the current element
                $strDeleteLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $strDeleteUrl = getLinkAdminHref("pages_content", "deleteElement", "&systemid=".$strSystemid.$strAdminLangParam."&pe=1");
                    $strDeleteLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strDeleteUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_delete", "pages", "admin")."</a>";
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_delete"]) && $arrConfig["pe_action_delete"] != "") {
                        $strDeleteUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_delete"], $arrConfig["pe_action_edit_params"].$strAdminLangParam."&pe=1");
                        $strDeleteLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strDeleteUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_delete", "pages", "admin")."</a>";
                    }
                }

                //---------------------------------------------------
                //TODO: check if there are more than one elements in current placeholder before showing shift buttons

                //link to shift element up
                $strShiftUpLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $objElement = new class_modul_pages_pageelement($strSystemid);
                    $strShiftUpUrl = getLinkAdminHref("pages_content", "elementSortUp", "&systemid=".$strSystemid.$strAdminLangParam."&pe=1");
                    $strShiftUpLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strShiftUpUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_shiftUp", "pages", "admin")."</a>";
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_shiftUp"]) && $arrConfig["pe_action_shiftUp"] != "") {
                        $strShiftUpUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_shiftUp"], $arrConfig["pe_action_shiftUp_params"].$strAdminLangParam."&pe=1");
                        $strShiftUpLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strShiftUpUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_shiftUp", "pages", "admin")."</a>";
                    }
                }

                //---------------------------------------------------
                //link to shift element down
                $strShiftDownLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $objElement = new class_modul_pages_pageelement($strSystemid);
                    $strShiftDownUrl = getLinkAdminHref("pages_content", "elementSortDown", "&systemid=".$strSystemid.$strAdminLangParam."&pe=1");
                    $strShiftDownLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strShiftDownUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_shiftDown", "pages", "admin")."</a>";
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_shiftDown"]) && $arrConfig["pe_action_shiftDown"] != "") {
                        $strShiftDownUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_shiftDown"], $arrConfig["pe_action_shiftDown_params"].$strAdminLangParam."&pe=1");
                        $strShiftDownLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strShiftDownUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_shiftDown", "pages", "admin")."</a>";
                    }
                }

                //---------------------------------------------------
                // layout generation

                $strReturn .= class_carrier::getInstance()->getObjToolkit("portal")->getPeActionToolbar($strSystemid, array($strEditLink, $strCopyLink, $strDeleteLink, $strShiftUpLink, $strShiftDownLink), $strContent);

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
     * Generates the link to create an element at a placeholder not yet existing
     * @param string $strSystemid
     * @param string $strPlaceholder
     * @param string $strElement
     * @return string
     * @static
     */
    public static function getPortaleditorNewCode($strSystemid, $strPlaceholder, $strElement) {
        $strReturn = "";
        //switch the text-language temporary
        $strPortalLanguage = class_carrier::getInstance()->getObjText()->getStrTextLanguage();
        class_carrier::getInstance()->getObjText()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());

        //fetch the language to set the correct admin-lang
        $objLanguages = new class_modul_languages_language();
        $strAdminLangParam = "&language=".$objLanguages->getPortalLanguage();

        $strTooltipText = class_carrier::getInstance()->getObjText()->getText("pe_new", "pages", "admin");
        //TODO: get translated element name
        $strElementName = $strElement;
        $strElementHref = getLinkAdminHref("pages_content", "newElement", "&systemid=".$strSystemid.$strAdminLangParam."&placeholder=".$strPlaceholder."&element=".$strElement."&pe=1");

        $strReturn = class_carrier::getInstance()->getObjToolkit("portal")->getPeNewButton($strPlaceholder, $strElement, $strElementName, $strElementHref);

        //reset the portal texts language
        class_carrier::getInstance()->getObjText()->setStrTextLanguage($strPortalLanguage);

        return $strReturn;
    }

	/**
     * Generates a wrapper for the single new-buttons at a given placeholder
     *
     * @param string $strPlaceholder
     * @param string $strContent
     * @return string
     * @static
     */
    public static function getPortaleditorNewWrapperCode($strPlaceholder, $strContentElements) {
        $strPlaceholderClean = uniSubstr($strPlaceholder, 0, uniStrpos($strPlaceholder, "_"));

        //switch the text-language temporary
        $strPortalLanguage = class_carrier::getInstance()->getObjText()->getStrTextLanguage();
        class_carrier::getInstance()->getObjText()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());

        $strLabel = class_carrier::getInstance()->getObjText()->getText("pe_new", "pages", "admin");

        //reset the portal texts language
        class_carrier::getInstance()->getObjText()->setStrTextLanguage($strPortalLanguage);

        return class_carrier::getInstance()->getObjToolkit("portal")->getPeNewButtonWrapper($strPlaceholder, $strPlaceholderClean, $strLabel, $strContentElements);
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