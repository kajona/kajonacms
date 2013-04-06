<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$								*
********************************************************************************************************/

/**
 * Base Class for all portal-elements
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 * @abstract
 */
abstract class class_element_portal extends class_portal {

    private $strCacheAddon = "";

    /**
     * @var class_module_pages_pageelement
     */
    private $objElementData;

    /**
     * Constructor
     *
     * @param class_module_pages_pageelement $objElementData
     */
    public function __construct($objElementData) {
        parent::__construct();

        $this->setArrModuleEntry("modul", "elements");
        $this->setArrModuleEntry("moduleId", _pages_elemente_modul_id_);


        $this->setSystemid($objElementData->getSystemid());
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
     *
     * @return mixed
     */
    public function getElementContent($strSystemid) {
        //table given?
        if(isset($this->arrModule["table"]) && $this->arrModule["table"] != "") {
            $strQuery = "SELECT *
    						FROM ".$this->arrModule["table"]."
    						WHERE content_id = ? ";
            return $this->objDB->getPRow($strQuery, array($strSystemid));
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

                $objElement = class_objectfactory::getInstance()->getObject($this->getSystemid());
                if($objElement->rightEdit()) {
                    $arrConfig = array();
                    $arrConfig["pe_module"] = "";
                    $arrConfig["pe_action"] = "";

                    if(isset($this->arrModule["pe_module"]))
                        $arrConfig["pe_module"] = $this->arrModule["pe_module"];
                    if(isset($this->arrModule["pe_action"]))
                        $arrConfig["pe_action"] = $this->arrModule["pe_action"];

                    $strReturn = $this->addPortalEditorCode($this->loadData(), $this->getSystemid(), $arrConfig);
                }
                else {
                    $strReturn = $this->loadData();
                    $strReturn = preg_replace('/data-kajona-editable=\"([a-zA-Z0-9#_]*)\"/i', "", $strReturn);
                }
            }
            else {
                $strReturn = $this->loadData();
                //strip the data-editable values - no use case for regular page views
                $strReturn = preg_replace('/data-kajona-editable=\"([a-zA-Z0-9#_]*)\"/i', "", $strReturn);
            }
        }
        catch(class_exception $objEx) {
            //an error occured during content generation. redirect to error page
            $objEx->processException();
            //if available, show the error-page. on debugging-environments, the exception processing already die()d the process.
            if($this->getPagename() != _pages_errorpage_)
                $this->portalReload(getLinkPortalHref(_pages_errorpage_));

            $strReturn = $objEx->getMessage();
        }

        //add an anchor to jump to, but exclude navigation-elements
        $strReturn = $this->getAnchorTag().$strReturn;

        //apply element-based scriptlets
        $objScriptlets = new class_scriptlet_helper();
        $strReturn = $objScriptlets->processString($strReturn, interface_scriptlet::BIT_CONTEXT_PORTAL_ELEMENT);

        return $strReturn;
    }

    /**
     * Tries to load the content of the element from cache.
     * If a valid entry was found, the cached content is returned.
     * If no valid entry was found, false is returned instead.
     * In this case, use getElementOutput to load the content.
     *
     * @return string false in case of no matching entry
     * @see class_element_portal::getElementOutput()
     */
    public function getElementOutputFromCache() {
        $strReturn = false;

        //load the matching cache-entry
        $objCacheEntry = class_cache::getCachedEntry(__CLASS__, $this->getCacheHash1(), $this->getCacheHash2(), $this->getStrPortalLanguage());
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
     *
     * @since 3.3.1
     */
    public function saveElementToCache($strElementOutput = "") {

        //if no content was passed, rebuild the content
        if($strElementOutput == "")
            $strElementOutput = $this->getElementOutput();

        //strip the data-editable values - no use case for regular page views
        $strElementOutput = preg_replace('/data-kajona-editable=\"([a-zA-Z0-9#_]*)\"/i', "", $strElementOutput);

        //load the matching cache-entry
        $objCacheEntry = class_cache::getCachedEntry(__CLASS__, $this->getCacheHash1(), $this->getCacheHash2(), $this->getStrPortalLanguage(), true);
        $objCacheEntry->setStrContent($strElementOutput);
        $objCacheEntry->setIntLeasetime(time() + $this->objElementData->getIntCachetime());

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
     *
     * @return string
     * @static
     */
    public static function addPortalEditorCode($strContent, $strSystemid, $arrConfig) {
        $strReturn = "";

        if(_pages_portaleditor_ == "true" && class_objectfactory::getInstance()->getObject($strSystemid)->rightEdit($strSystemid) && class_carrier::getInstance()->getObjSession()->isAdmin()) {

            $objInstance = class_objectfactory::getInstance()->getObject($strSystemid);

            if(class_carrier::getInstance()->getObjSession()->getSession("pe_disable") != "true") {

                //switch the text-language temporary
                $strPortalLanguage = class_carrier::getInstance()->getObjLang()->getStrTextLanguage();
                class_carrier::getInstance()->getObjLang()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());

                //fetch the language to set the correct admin-lang
                $objLanguages = new class_module_languages_language();
                $strAdminLangParam = "&language=".$objLanguages->getPortalLanguage();


                $strModule = "pages_content";
                $strAction = "edit";
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
                        $strNewLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strNewUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_new_old", "pages")."</a>";
                    }
                }


                //---------------------------------------------------
                //Link to edit current element
                $strEditLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $arrConfig["pe_action_edit"] = $strAction;
                    $arrConfig["pe_action_edit_params"] = "&systemid=".$strSystemid;
                }
                //Use Module-config to generate link
                if(isset($arrConfig["pe_action_edit"]) && $arrConfig["pe_action_edit"] != "") {
                    $strEditUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_edit"], $arrConfig["pe_action_edit_params"].$strAdminLangParam."&pe=1");
                    $strEditLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strEditUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_edit", "pages")."</a>";
                }

                //---------------------------------------------------
                //link to copy an element to the same or another placeholder
                $strCopyLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $arrConfig["pe_action_copy"] = "copyElement";
                    $arrConfig["pe_action_copy_params"] = "&systemid=".$strSystemid;
                }
                //Use Module-config to generate link
                if(isset($arrConfig["pe_action_copy"]) && $arrConfig["pe_action_copy"] != "") {
                    $strCopyUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_copy"], $arrConfig["pe_action_copy_params"].$strAdminLangParam."&pe=1");
                    $strCopyLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strCopyUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_copy", "pages")."</a>";
                }

                //---------------------------------------------------
                //link to delete the current element
                $strDeleteLink = "";
                if($objInstance->rightDelete()) {

                    //standard: pages_content.
                    if($strModule == "pages_content") {
                        $arrConfig["pe_action_delete"] = "deleteElementFinal";
                        $arrConfig["pe_action_edit_params"] = "&systemid=".$strSystemid;
                    }

                    if(isset($arrConfig["pe_action_delete"]) && $arrConfig["pe_action_delete"] != "") {
                        $strDeleteUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_delete"], $arrConfig["pe_action_edit_params"].$strAdminLangParam."&pe=1");
                        $strElementName = uniStrReplace(array('\''), array('\\\''), $objInstance->getStrDisplayName());
                        $strQuestion = uniStrReplace("%%element_name%%", htmlToString($strElementName, true), class_carrier::getInstance()->getObjLang()->getLang("commons_delete_record_question", "system"));

                        $strCallback = " function() { delDialog.hide(); KAJONA.admin.portaleditor.openDialog('$strDeleteUrl'); return false; } ";
                        $strDeleteLink = getLinkAdminManual(
                            "href=\"#\" onclick=\"javascript:delDialog.setTitle('".class_carrier::getInstance()->getObjLang()->getLang("dialog_deleteHeader", "system")."'); delDialog.setContent('".$strQuestion."', '".class_carrier::getInstance()->getObjLang()->getLang("dialog_deleteButton", "system")."',  ".$strCallback."); delDialog.init(); return false;\"",
                            class_carrier::getInstance()->getObjLang()->getLang("commons_delete", "system"),
                            class_carrier::getInstance()->getObjLang()->getLang("commons_delete", "system")
                        );
                    }
                }

                //---------------------------------------------------
                //TODO: check if there are more than one elements in current placeholder before showing shift buttons

                //link to shift element up
                $strShiftUpLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $arrConfig["pe_action_shiftUp"] = "elementSortUp";
                    $arrConfig["pe_action_shiftUp_params"] = "&systemid=".$strSystemid;
                }
                //Use Module-config to generate link
                if(isset($arrConfig["pe_action_shiftUp"]) && $arrConfig["pe_action_shiftUp"] != "") {
                    $strShiftUpUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_shiftUp"], $arrConfig["pe_action_shiftUp_params"].$strAdminLangParam."&pe=1");
                    $strShiftUpLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strShiftUpUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_shiftUp", "pages")."</a>";
                }

                //---------------------------------------------------
                //link to shift element down
                $strShiftDownLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $arrConfig["pe_action_shiftDown"] = "elementSortDown";
                    $arrConfig["pe_action_shiftDown_params"] = "&systemid=".$strSystemid;
                }
                //Use Module-config to generate link
                if(isset($arrConfig["pe_action_shiftDown"]) && $arrConfig["pe_action_shiftDown"] != "") {
                    $strShiftDownUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_shiftDown"], $arrConfig["pe_action_shiftDown_params"].$strAdminLangParam."&pe=1");
                    $strShiftDownLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strShiftDownUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_shiftDown", "pages")."</a>";
                }

                //---------------------------------------------------
                //link to set element inactive
                $strSetInactiveLink = "";
                //standard: pages_content.
                if($strModule == "pages_content") {
                    $arrConfig["pe_action_setStatus"] = "elementStatus";
                    $arrConfig["pe_action_setStatus_params"] = "&systemid=".$strSystemid;
                }
                //Use Module-config to generate link
                if(isset($arrConfig["pe_action_setStatus"]) && $arrConfig["pe_action_setStatus"] != "") {
                    $strSetInactiveUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_setStatus"], $arrConfig["pe_action_setStatus_params"].$strAdminLangParam."&pe=1");
                    $strSetInactiveLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strSetInactiveUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_setinactive", "pages")."</a>";
                }

                //---------------------------------------------------
                // layout generation

                $strReturn .= class_carrier::getInstance()->getObjToolkit("portal")->getPeActionToolbar($strSystemid, array($strNewLink, $strEditLink, $strCopyLink, $strDeleteLink, $strShiftUpLink, $strShiftDownLink, $strSetInactiveLink), $strContent);

                //reset the portal texts language
                class_carrier::getInstance()->getObjLang()->setStrTextLanguage($strPortalLanguage);
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

            if(class_carrier::getInstance()->getObjSession()->getSession("pe_disable") != "true") {

                //switch the text-language temporary
                $strPortalLanguage = class_carrier::getInstance()->getObjLang()->getStrTextLanguage();
                class_carrier::getInstance()->getObjLang()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());

                //fetch the language to set the correct admin-lang
                $objLanguages = new class_module_languages_language();
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
                    $strSetActiveLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strSetActiveUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_setactive", "pages")."</a>";
                }
                else {
                    //Use Module-config to generate link
                    if(isset($arrConfig["pe_action_setStatus"]) && $arrConfig["pe_action_setStatus"] != "") {
                        $strSetActiveUrl = getLinkAdminHref($strModule, $arrConfig["pe_action_setStatus"], $arrConfig["pe_action_setStatus_params"].$strAdminLangParam."&pe=1");
                        $strSetActiveLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strSetActiveUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_setactive", "pages")."</a>";
                    }
                }

                //---------------------------------------------------
                // layout generation

                $strReturn .= class_carrier::getInstance()->getObjToolkit("portal")->getPeActionToolbar($strSystemid, array($strSetActiveLink), $strContent);

                //reset the portal texts language
                class_carrier::getInstance()->getObjLang()->setStrTextLanguage($strPortalLanguage);
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
     *
     * @param string $strSystemid
     * @param string $strPlaceholder
     * @param string $strElement
     * @param string $strElementName
     *
     * @return string
     * @static
     */
    public static function getPortaleditorNewCode($strSystemid, $strPlaceholder, $strElement, $strElementName) {
        $strReturn = "";
        if(class_carrier::getInstance()->getObjRights()->rightEdit($strSystemid) && class_carrier::getInstance()->getObjSession()->isAdmin()) {
            //switch the text-language temporary
            $strPortalLanguage = class_carrier::getInstance()->getObjLang()->getStrTextLanguage();
            class_carrier::getInstance()->getObjLang()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());

            //fetch the language to set the correct admin-lang
            $objLanguages = new class_module_languages_language();
            $strAdminLangParam = "&language=".$objLanguages->getPortalLanguage();

            $strElementHref = getLinkAdminHref("pages_content", "new", "&systemid=".$strSystemid.$strAdminLangParam."&placeholder=".$strPlaceholder."&element=".$strElement."&pe=1");

            $strReturn = class_carrier::getInstance()->getObjToolkit("portal")->getPeNewButton($strPlaceholder, $strElement, $strElementName, $strElementHref);

            //reset the portal texts language
            class_carrier::getInstance()->getObjLang()->setStrTextLanguage($strPortalLanguage);
        }
        return $strReturn;
    }

    /**
     * Generates a wrapper for the single new-buttons at a given placeholder
     *
     * @param string $strPlaceholder
     * @param string $strContentElements
     *
     * @return string
     * @static
     */
    public static function getPortaleditorNewWrapperCode($strPlaceholder, $strContentElements) {
        $strPlaceholderClean = uniSubstr($strPlaceholder, 0, uniStrpos($strPlaceholder, "_"));

        //switch the text-language temporary
        $strPortalLanguage = class_carrier::getInstance()->getObjLang()->getStrTextLanguage();
        class_carrier::getInstance()->getObjLang()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());

        $strLabel = class_carrier::getInstance()->getObjLang()->getLang("pe_new", "pages");

        //reset the portal texts language
        class_carrier::getInstance()->getObjLang()->setStrTextLanguage($strPortalLanguage);

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
     *
     * @param string $strCacheAddon
     */
    public function setStrCacheAddon($strCacheAddon) {
        $this->strCacheAddon .= $strCacheAddon;
    }


    /**
     * Pre-check to indicate if a portal-element provides possible navigation entries.
     * This method has to be static since it is evaluated before the real object instantiation.
     * You have to overwrite this method in order to have getNavigationEntries() queried, otherwise the methode is ignores completely.
     *
     * @return bool
     */
    public static function providesNavigationEntries() {
        return false;
    }


    /**
     * This method may be used, if the current module is able to
     * register own levels in the navigation.
     * See the module mediamanager (gallery, downloads) on how to use
     * this special feature.
     * The array returned by this method should be structured like:
     * array(
     *    node => class_module_navigation_point ,
     *    subnodes => array(
     *        array( node => class_module_navigation_point, subnodes => array(...)),
     *        array( node => class_module_navigation_point, subnodes => array(...))
     *    )
     * )
     * If you don't want to create additional navigation entries, don't overwrite this method.
     * Otherwise you have to override the method providesNavigationEntries() and return true.
     * This method is only queried if the static providesNavigationEntries is true since the number of queries
     * could be reduced drastically due to this pre-check.
     *
     * @see class_module_navigation_tree::getCompleteNaviStructure()
     * @see class_module_navigation_point::getDynamicNaviLayer()
     * @return array|bool
     * @since 4.0
     */
    public function getNavigationEntries() {
        return false;
    }

    /**
     * @return \class_module_pages_pageelement
     */
    public function getObjElementData() {
        return $this->objElementData;
    }

}

