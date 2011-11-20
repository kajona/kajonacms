<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$								*
********************************************************************************************************/

/**
 * Base Class for all portal-elements
 *
 * @package module_pages
 * @abstract
 */
abstract class class_element_portal extends class_portal {
	protected $arrElementData;

    private $strCacheAddon = "";

    /**
     *
     * @var class_modul_pages_pageelement
     */
    private $objElementData;

	/**
	 * Constructor
	 *
	 * @param mixed $arrModule
	 * @param class_modul_pages_pageelement $objElementData
	 */
	public function __construct($arrModule, $objElementData) {
        $arrModule["modul"]             = "elemente";
		$arrModule["p_name"] 			= "element_portal";
		$arrModule["p_author"] 			= "sidler@mulchprod.de";
		$arrModule["p_nummer"] 			= _pages_elemente_modul_id_;

		parent::__construct($arrModule);

		$arrTemp = array();
		$this->setSystemid($objElementData->getSystemid());
		$this->arrElementData = $arrTemp;
		//merge the attributes of $objElementData to the array
		$this->arrElementData["page_element_ph_placeholder"] = $objElementData->getStrPlaceholder();
		$this->arrElementData["page_element_ph_name"] = $objElementData->getStrName();
		$this->arrElementData["page_element_ph_element"] = $objElementData->getStrElement();
		$this->arrElementData["page_element_ph_title"] = $objElementData->getStrTitle(false);

        $this->objElementData = $objElementData;
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
        $strReturn = "";
        //load the data from the database
        $this->arrElementData = array_merge($this->getElementContent($this->objElementData->getSystemid()), $this->arrElementData);

        //wrap all in a try catch block
        try {

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
            }
        catch (class_exception $objEx) {
            //an error occured during content generation. redirect to error page
            $objEx->processException();
            //if available, show the error-page. on debugging-environments, the exception processing already die()d the process.
            if($this->getPagename() != _pages_errorpage_)
                $this->portalReload(getLinkPortalHref(_pages_errorpage_));

            $strReturn = $objEx->getMessage();
        }

	    //add an anchor to jump to, but exclude navigation-elements
	    $strReturn = $this->getAnchorTag().$strReturn;

		return $strReturn;
	}

    /**
     * Tries to load the content of the element from cache.
     * If a valid entry was found, the cached content is returns.
     * Of no valid entry was found, false is returned instead.
     * In this case, use getElementOutput to load the content.
     *
     * @return string false in case of no matching entry
     * @see class_element_portal::getElementOutput()
     */
    public function getElementOutputFromCache() {
        $strReturn = false;

        //load the matching cache-entry
        $objCacheEntry = class_cache::getCachedEntry(__CLASS__, $this->getCacheHash1(), $this->getCacheHash2(), $this->getPortalLanguage());
        if($objCacheEntry != null && $this->onLoadFromCache())
            $strReturn = $objCacheEntry->getStrContent();

        return $strReturn;
    }

    /**
     * Overwrite this method if you'd like to perform special actions if as soon as content
     * was loaded from the cache.
     * Make sure to return a proper boolean value, otherwise the cached entry may get invalid.
     *
     * @return boolean
     * @since 3.3.1
     */
    public function onLoadFromCache() {
        return true;
    }


    /**
     * Saves the current element to the cache.
     * If passed, the value of the param $strElementOutput is used as content, otherwise
     * content-generation is triggered again.
     *
     * @param string $strElementOutput
     * @since 3.3.1
     */
    public function saveElementToCache($strElementOutput = "") {

        //if no content was passed, rebuild the content
        if($strElementOutput == "")
            $strElementOutput = $this->getElementOutput();

        //load the matching cache-entry
        $objCacheEntry = class_cache::getCachedEntry(__CLASS__, $this->getCacheHash1(), $this->getCacheHash2(), $this->getPortalLanguage(), true);
        $objCacheEntry->setStrContent($strElementOutput);
        $objCacheEntry->setIntLeasetime(time()+$this->objElementData->getIntCachetime());

        $objCacheEntry->updateObjectToDb();
    }

    /**
     * Generates the hash2 sum of the cached entry
     *
     * @return string
     * @since 3.3.1
     */
    private function getCacheHash2() {

        $strGuestId = "";
        //when browsing the site as a guest, drop the userid
        if($this->objSession->isLoggedin())
            $strGuestId = $this->objSession->getUserID();

        return sha1("".$strGuestId.$this->getAction().$this->strCacheAddon.$this->getParam("pv").$this->getSystemid().$this->getParam("systemid").$this->getParam("highlight"));
    }

    /**
     * Generates the hash1 sum of the cached entry
     *
     * @return string
     * @since 3.3.1
     */
    private function getCacheHash1() {
        return $this->getPagename();
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
                //Link to create a new entry - only for modules, so not the page-content directly!
                $strNewLink = "";
                if($strModule != "pages_content") {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_new"]) && $arrConfig["pe_action_new"] != "") {
                        $strNewUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_new"], $arrConfig["pe_action_new_params"].$strAdminLangParam."&pe=1");
                        $strNewLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strNewUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_new_old", "pages", "admin")."</a>";
                    }
                }


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
                    $strCopyUrl = getLinkAdminHref("pages_content", "copyElement", "&systemid=".$strSystemid.$strAdminLangParam."&pe=1");
                    $strCopyLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strCopyUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_copy", "pages", "admin")."</a>";
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
                    $strDeleteLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strDeleteUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("commons_delete", "pages", "admin")."</a>";
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_delete"]) && $arrConfig["pe_action_delete"] != "") {
                        $strDeleteUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_delete"], $arrConfig["pe_action_edit_params"].$strAdminLangParam."&pe=1");
                        $strDeleteLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strDeleteUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("commons_delete", "pages", "admin")."</a>";
                    }
                }

                //---------------------------------------------------
                //TODO: check if there are more than one elements in current placeholder before showing shift buttons

                //link to shift element up
                $strShiftUpLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
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
                //link to set element inactive
                $strSetInactiveLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $strSetInactiveUrl = getLinkAdminHref("pages_content", "elementStatus", "&systemid=".$strSystemid.$strAdminLangParam."&pe=1");
                    $strSetInactiveLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strSetInactiveUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_setinactive", "pages", "admin")."</a>";
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_setStatus"]) && $arrConfig["pe_action_setStatus"] != "") {
                        $strSetInactiveUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_setStatus"], $arrConfig["pe_action_setStatus_params"].$strAdminLangParam."&pe=1");
                        $strSetInactiveLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strSetInactiveUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_setinactive", "pages", "admin")."</a>";
                    }
                }

                //---------------------------------------------------
                // layout generation

                $strReturn .= class_carrier::getInstance()->getObjToolkit("portal")->getPeActionToolbar($strSystemid, array($strNewLink, $strEditLink, $strCopyLink, $strDeleteLink, $strShiftUpLink, $strShiftDownLink, $strSetInactiveLink), $strContent);

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


    public static function addPortalEditorSetActiveCode($strContent, $strSystemid, $arrConfig) {
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
                //param-inits ---------------------------------------
                //Generate url to the admin-area
                if(isset($arrConfig["pe_module"]) && $arrConfig["pe_module"] != "") {
                    $strModule = $arrConfig["pe_module"];
                }
                //---------------------------------------------------


                //---------------------------------------------------
                //link to set element active
                $strSetActiveLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $strSetActiveUrl = getLinkAdminHref("pages_content", "elementStatus", "&systemid=".$strSystemid.$strAdminLangParam."&pe=1");
                    $strSetActiveLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strSetActiveUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_setactive", "pages", "admin")."</a>";
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_setStatus"]) && $arrConfig["pe_action_setStatus"] != "") {
                        $strSetActiveUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_setStatus"], $arrConfig["pe_action_setStatus_params"].$strAdminLangParam."&pe=1");
                        $strSetActiveLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strSetActiveUrl."'); return false;\">".class_carrier::getInstance()->getObjText()->getText("pe_setactive", "pages", "admin")."</a>";
                    }
                }

                //---------------------------------------------------
                // layout generation

                $strReturn .= class_carrier::getInstance()->getObjToolkit("portal")->getPeActionToolbar($strSystemid, array($strSetActiveLink), $strContent);

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
    public static function getPortaleditorNewCode($strSystemid, $strPlaceholder, $strElement, $strElementName) {
        $strReturn = "";
        //switch the text-language temporary
        $strPortalLanguage = class_carrier::getInstance()->getObjText()->getStrTextLanguage();
        class_carrier::getInstance()->getObjText()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());

        //fetch the language to set the correct admin-lang
        $objLanguages = new class_modul_languages_language();
        $strAdminLangParam = "&language=".$objLanguages->getPortalLanguage();

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

    /**
     * Use this method to set additional cache-key-addons.
     * E.g. if you want to cache depending on your own params like a rating history,
     * this is the place to go.
     */
    public function setStrCacheAddon($strCacheAddon) {
        $this->strCacheAddon .= $strCacheAddon;
    }


}

?>