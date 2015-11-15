<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$								*
********************************************************************************************************/
namespace Kajona\Pages\Portal;

use class_cache;
use class_carrier;
use class_exception;
use class_link;
use class_model;
use class_module_languages_language;
use class_module_navigation_point;
use class_module_system_setting;
use class_objectfactory;
use class_orm_base;
use class_portal_controller;
use class_reflection;
use class_scriptlet_helper;
use interface_scriptlet;
use Kajona\Pages\Admin\Elements\ElementBlockAdmin;
use Kajona\Pages\Admin\Elements\ElementBlocksAdmin;
use Kajona\Pages\Portal\Elements\ElementBlocksPortal;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorPlaceholderAction;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;

/**
 * Base Class for all portal-elements
 *
 * @author sidler@mulchprod.de
 * @abstract
 *
 * @module elements
 * @moduleId _pages_elemente_modul_id_
 */
abstract class ElementPortal extends class_portal_controller
{

    private $strCacheAddon = "";

    /**
     * @var PagesPageelement
     */
    private $objElementData;

    /**
     * Constructor
     *
     * @param PagesPageelement $objElementData
     */
    public function __construct($objElementData)
    {
        parent::__construct();

        $this->setSystemid($objElementData->getSystemid());
        //merge the attributes of $objElementData to the array
        $this->arrElementData["page_element_ph_placeholder"] = $objElementData->getStrPlaceholder();
        $this->arrElementData["page_element_ph_name"] = $objElementData->getStrName();
        $this->arrElementData["page_element_ph_element"] = $objElementData->getStrElement();
        $this->arrElementData["page_element_ph_title"] = $objElementData->getStrTitle(false);

        $this->objElementData = $objElementData;
    }


    /**
     * returns the table used by the element
     *
     * @return string
     */
    public function getTable()
    {
        $objAnnotations = new class_reflection($this);
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass(class_orm_base::STR_ANNOTATION_TARGETTABLE);
        if (count($arrTargetTables) != 0) {
            $arrTable = explode(".", $arrTargetTables[0]);
            return _dbprefix_.$arrTable[0];
        }

        //legacy code
        return $this->getArrModule("table");
    }


    /**
     * Loads the content out of the elements-table
     *
     * @param string $strSystemid
     *
     * @return mixed
     */
    public function getElementContent($strSystemid)
    {
        //table given?
        if ($this->getTable() != "") {
            $strQuery = "SELECT *
    						FROM ".$this->getTable()."
    						WHERE content_id = ? ";
            return class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strSystemid));
        }
        else {
            return array();
        }

    }

    /**
     * Invokes the element to do the work
     * If enabled, passes to addPortalEditorCode(). This adds the element-based pe-code.
     * If modules want to create pe code, they have to call the static method addPortalEditorCode
     * on their own!
     *
     * @return string
     */
    private function getElementOutput()
    {
        $strReturn = "";
        //load the data from the database
        $this->arrElementData = array_merge($this->getElementContent($this->objElementData->getSystemid()), $this->arrElementData);

        //wrap all in a try catch block
        try {
            $strReturn = $this->loadData();
        }
        catch (class_exception $objEx) {
            //FIXME: error handling is currently disabled
            //An error occurred during content generation. redirect to error page
            //$objEx->processException();
            //if available, show the error-page. on debugging-environments, the exception processing already die()d the process.
//            if ($this->getPagename() != class_module_system_setting::getConfigValue("_pages_errorpage_")) {
//                $this->portalReload(class_link::getLinkPortalHref(class_module_system_setting::getConfigValue("_pages_errorpage_")));
//            }

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
     * @see ElementPortal::getElementOutput()
     */
    private function getElementOutputFromCache()
    {
        $strReturn = false;

        //load the matching cache-entry
        $objCacheEntry = class_cache::getCachedEntry(__CLASS__, $this->getCacheHash1(), $this->getCacheHash2(), $this->getStrPortalLanguage());
        if ($objCacheEntry != null && $this->onLoadFromCache()) {
            $strReturn = $objCacheEntry->getStrContent();
        }

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
    public function onLoadFromCache()
    {
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
    private function saveElementToCache($strElementOutput)
    {

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
    private function getCacheHash2()
    {

        $strGuestId = "";
        //when browsing the site as a guest, drop the userid
        if ($this->objSession->isLoggedin()) {
            $strGuestId = $this->objSession->getUserID();
        }

        return sha1("".$strGuestId.$this->getAction().$this->strCacheAddon.$this->getParam("pv").$this->getSystemid().$this->getParam("systemid").$this->getParam("highlight"));
    }

    /**
     * Generates the hash1 sum of the cached entry
     *
     * @return string
     * @since 3.3.1
     */
    private function getCacheHash1()
    {
        return $this->getPagename();
    }


    private function getPageData()
    {
        return PagesPage::getPageByName($this->getPagename());
    }


    /**
     * Forces the rendering of the current portal element.
     * Takes care of loading the element from cache or regenerating the element.
     * If enabled, the portal-editor code is rendered, too.
     *
     * @param bool|false $bitActivePortaleditor
     *
     * @return string
     */
    public function getRenderedElementOutput($bitActivePortaleditor = false)
    {

        if (class_module_system_setting::getConfigValue("_pages_cacheenabled_") == "true" && $this->getParam("preview") != "1" && $this->getPageData()->getStrName() != class_module_system_setting::getConfigValue("_pages_errorpage_")) {
            $strElementOutput = "";
            //if the portaleditor is disabled, do the regular cache lookups in storage. otherwise regenerate again and again :)
            if ($bitActivePortaleditor) {
                $strElementOutput = $this->getElementOutput();
            }
            else {
                //pe not to be taken into account --> full support of caching
                $strElementOutput = $this->getElementOutputFromCache();

                if ($strElementOutput === false) {
                    $strElementOutput = $this->getElementOutput();
                    $this->saveElementToCache($strElementOutput);
                }
            }

        }
        else {
            $strElementOutput = $this->getElementOutput();
        }


        $objBaseElement = new PagesPageelement($this->getSystemid());


        //if element is disabled & the pe is requested, wrap the content
        if ($bitActivePortaleditor && $objBaseElement->getIntRecordStatus() == 0) {
            //TODO really render the whole disabled element?
            //$strElementOutput = $this->objToolkit->getPeInactiveElement(array("title" => $this->getLang("pe_inactiveElement", "pages")." (".$objBaseElement->getStrElement().")"));
            $strElementOutput = $this->objToolkit->getPeInactiveElement(array("title" => $this->getElementOutput()));
        }

        if($bitActivePortaleditor) {
            $this->getPortalEditorActions();
            $strElementOutput = PagesPortaleditor::addPortaleditorContentWrapper($strElementOutput, $this->getSystemid(), $this->arrElementData["page_element_ph_element"]);

        }
        else {
            $strElementOutput = $this->removePortalEditorTags($strElementOutput);
        }


        return $strElementOutput;
    }

    /**
     * Removes the portal-editor editable ids
     * @param $strElementOutput
     *
     * @return mixed
     */
    protected function removePortalEditorTags($strElementOutput)
    {
        return preg_replace('/data-kajona-editable=\"([a-zA-Z0-9#_]*)\"/i', "", $strElementOutput);
    }

    /**
     * Registers the default portaleditor actions for the current element
     */
    public function getPortalEditorActions()
    {

        $objPageelement = new PagesPageelement($this->getSystemid());
        if (!$objPageelement->rightEdit()) {
            return;
        }

        $objParent = class_objectfactory::getInstance()->getObject($objPageelement->getPrevId());
        if($objParent instanceof PagesPageelement) {
            $objParent  = $objParent->getConcreteAdminInstance();
        }


        //fetch the language to set the correct admin-lang
        $objLanguages = new class_module_languages_language();
        $strAdminLangParam = $objLanguages->getPortalLanguage();


        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::EDIT(), class_link::getLinkAdminHref("pages_content", "edit", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
        );

        if(!$objParent instanceof ElementBlockAdmin) {

            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::COPY(), class_link::getLinkAdminHref("pages_content", "copyElement", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
            );
            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::DELETE(), class_link::getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
            );
            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::MOVE(), "", $this->getSystemid())
            );


            if ($objPageelement->getIntRecordStatus() == 1) {
                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::SETINACTIVE(), class_link::getLinkAdminHref("pages_content", "elementStatus", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
                );
            }
            else {
                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::SETACTIVE(), class_link::getLinkAdminHref("pages_content", "elementStatus", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
                );
            }

            if(!$this instanceof ElementBlocksPortal) {
                PagesPortaleditor::getInstance()->registerAction(
                    new PagesPortaleditorPlaceholderAction(PagesPortaleditorActionEnum::CREATE(), class_link::getLinkAdminHref("pages_content", "new", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&placeholder={$objPageelement->getStrPlaceholder()}&element={$objPageelement->getStrName()}&pe=1"), $objPageelement->getStrPlaceholder(), $objPageelement->getStrName())
                );
            }

        }
    }

    /**
     * Registers new-entry actions for a given placeholder
     *
     * @param $bitElementIsExistingAtPlaceholder
     * @param PagesElement $objElement
     * @param $strPlaceholder
     *
     */
    public function getPortaleditorPlaceholderActions($bitElementIsExistingAtPlaceholder, PagesElement $objElement, $strPlaceholder)
    {
        //fetch the language to set the correct admin-lang
        $objLanguages = new class_module_languages_language();
        $strAdminLangParam = $objLanguages->getPortalLanguage();

        if ($objElement->getIntRepeat() == 1 || !$bitElementIsExistingAtPlaceholder) {
            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorPlaceholderAction(PagesPortaleditorActionEnum::CREATE(), class_link::getLinkAdminHref("pages_content", "new", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&placeholder={$strPlaceholder}&element={$objElement->getStrName()}&pe=1"), $strPlaceholder, $objElement->getStrName())
            );
        }
    }


    public static function addPortalEditorSetActiveCode($strContent, $strSystemid, $arrConfig)
    {
        $strReturn = "";

        if (class_module_system_setting::getConfigValue("_pages_portaleditor_") == "true" && class_carrier::getInstance()->getObjRights()->rightEdit($strSystemid) && class_carrier::getInstance()->getObjSession()->isAdmin()) {

            if (class_carrier::getInstance()->getObjSession()->getSession("pe_disable") != "true") {

                //switch the text-language temporary
                $strPortalLanguage = class_carrier::getInstance()->getObjLang()->getStrTextLanguage();
                class_carrier::getInstance()->getObjLang()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());

                //fetch the language to set the correct admin-lang
                $objLanguages = new class_module_languages_language();
                $strAdminLangParam = "&language=".$objLanguages->getPortalLanguage();


                $strModule = "pages_content";
                //param-inits ---------------------------------------
                //Generate url to the admin-area
                if (isset($arrConfig["pe_module"]) && $arrConfig["pe_module"] != "") {
                    $strModule = $arrConfig["pe_module"];
                }
                //---------------------------------------------------


                //---------------------------------------------------
                //link to set element active
                $strSetActiveLink = "";
                //standard: pages_content.
                if ($strModule == "pages_content") {
                    $strSetActiveUrl = class_link::getLinkAdminHref("pages_content", "elementStatus", "&systemid=".$strSystemid.$strAdminLangParam."&pe=1");
                    $strSetActiveLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strSetActiveUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_setactive", "pages")."</a>";
                }
                else {
                    //Use Module-config to generate link
                    if (isset($arrConfig["pe_action_setStatus"]) && $arrConfig["pe_action_setStatus"] != "") {
                        $strSetActiveUrl = class_link::getLinkAdminHref($strModule, $arrConfig["pe_action_setStatus"], $arrConfig["pe_action_setStatus_params"].$strAdminLangParam."&pe=1");
                        $strSetActiveLink = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strSetActiveUrl."'); return false;\">".class_carrier::getInstance()->getObjLang()->getLang("pe_setactive", "pages")."</a>";
                    }
                }

                //---------------------------------------------------
                // layout generation

                $strReturn .= class_carrier::getInstance()->getObjToolkit("portal")->getPeActionToolbar($strSystemid, array($strSetActiveLink), $strContent);

                //reset the portal texts language
                class_carrier::getInstance()->getObjLang()->setStrTextLanguage($strPortalLanguage);
            }
            else {
                $strReturn = $strContent;
            }
        }
        else {
            $strReturn = $strContent;
        }
        return $strReturn;
    }

    /**
     * Generates the link to create an element at a placeholder not yet existing
     *
     * @param string $strSystemid
     * @param string $strPlaceholder
     * @param PagesElement $objElement
     *
     * @return string
     * @static
     */
    public static function getPortaleditorNewCode($strSystemid, $strPlaceholder, PagesElement $objElement)
    {
        $strReturn = "";
        if (class_carrier::getInstance()->getObjRights()->rightEdit($strSystemid) && class_carrier::getInstance()->getObjSession()->isAdmin()) {
            //switch the text-language temporary
            $strPortalLanguage = class_carrier::getInstance()->getObjLang()->getStrTextLanguage();
            class_carrier::getInstance()->getObjLang()->setStrTextLanguage(class_carrier::getInstance()->getObjSession()->getAdminLanguage());

            //fetch the language to set the correct admin-lang
            $objLanguages = new class_module_languages_language();
            $strAdminLangParam = "&language=".$objLanguages->getPortalLanguage();

            $strElementHref = class_link::getLinkAdminHref("pages_content", "new", "&systemid=".$strSystemid.$strAdminLangParam."&placeholder=".$strPlaceholder."&element=".$objElement->getStrName()."&pe=1");

            $strReturn = class_carrier::getInstance()->getObjToolkit("portal")->getPeNewButton($strPlaceholder, $objElement->getStrDisplayName(), $strElementHref);

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
    public static function getPortaleditorNewWrapperCode($strPlaceholder, $strContentElements)
    {
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
    protected function loadData()
    {
        return "Element needs to overwrite loadData()!";
    }

    /**
     * Generates an anchor tag enabling navigation-points to jump to specific page-elements.
     * can be overwritten by subclasses
     *
     * @return string
     */
    protected function getAnchorTag()
    {
        return "<a name=\"".$this->getSystemid()."\" class=\"hiddenAnchor\"></a>";
    }

    /**
     * Use this method to set additional cache-key-addons.
     * E.g. if you want to cache depending on your own params like a rating history,
     * this is the place to go.
     *
     * @param string $strCacheAddon
     */
    public function setStrCacheAddon($strCacheAddon)
    {
        $this->strCacheAddon .= $strCacheAddon;
    }


    /**
     * Pre-check to indicate if a portal-element provides possible navigation entries.
     * This method has to be static since it is evaluated before the real object instantiation.
     * You have to overwrite this method in order to have getNavigationEntries() queried, otherwise the methode is ignores completely.
     *
     * @return bool
     */
    public static function providesNavigationEntries()
    {
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
     * If you only want to return a flat list of nodes, you can return an array of class_module_navigation_point instances instead of wrapping them
     * into the way more complex node/subnode structure.
     *
     * @see class_module_navigation_tree::getCompleteNaviStructure()
     * @see class_module_navigation_point::getDynamicNaviLayer()
     * @return array|class_module_navigation_point[]|bool
     * @since 4.0
     */
    public function getNavigationEntries()
    {
        return false;
    }

    /**
     * @return \class_module_pages_pageelement
     */
    public function getObjElementData()
    {
        return $this->objElementData;
    }

}

