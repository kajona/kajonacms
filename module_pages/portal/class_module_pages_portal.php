<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Handles the loading of the pages - loads the elements, passes control to them and returns the complete
 * page ready for output
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @module pages
 * @moduleId _pages_modul_id_
 */
class class_module_pages_portal extends class_portal_controller implements interface_portal {

    /**
     * Static field storing the last registered page-title. Modules may register additional page-titles in order
     * to have them places as the current page-title. Since this is a single field, the last module wins in case of
     * multiple entries.
     *
     * @var string
     */
    private static $strAdditionalTitle = "";

    /**
     * @param array|mixed $arrElementData
     */
    public function __construct($arrElementData) {
        parent::__construct($arrElementData);
        $this->setAction("generatePage");
    }

    /**
     * Handles the loading of a page, more in a functional than in an oop style
     *
     * @throws class_exception
     * @return string the generated page
     * @permissions view
     */
    protected function actionGeneratePage() {

        //Determine the pagename
        $objPageData = $this->getPageData();

        //react on portaleditor commands
        //pe to display, or pe to disable?
        if($this->getParam("pe") == "false") {
            $this->objSession->setSession("pe_disable", "true");
        }
        if($this->getParam("pe") == "true") {
            $this->objSession->setSession("pe_disable", "false");
        }

        //if using the pe, the cache shouldn't be used, otherwise strange things might happen.
        //the system could frighten your cat or eat up all your cheese with marshmallows...
        //get the current state of the portal editor
        $bitPeRequested = false;
        if(class_module_system_setting::getConfigValue("_pages_portaleditor_") == "true" && $this->objSession->getSession("pe_disable") != "true" && $this->objSession->isAdmin() && $objPageData->rightEdit()) {
            $bitPeRequested = true;
        }

        //If we reached up till here, we can begin loading the elements to fill
        if($bitPeRequested)
            $arrElementsOnPage = class_module_pages_pageelement::getElementsOnPage($objPageData->getSystemid(), false, $this->getStrPortalLanguage());
        else
            $arrElementsOnPage = class_module_pages_pageelement::getElementsOnPage($objPageData->getSystemid(), true, $this->getStrPortalLanguage());

        //If there's a master-page, load elements on that, too
        $objMasterData = class_module_pages_page::getPageByName("master");
        $bitEditPermissionOnMasterPage = false;
        if($objMasterData != null) {
            if($bitPeRequested)
                $arrElementsOnMaster = class_module_pages_pageelement::getElementsOnPage($objMasterData->getSystemid(), false, $this->getStrPortalLanguage());
            else
                $arrElementsOnMaster = class_module_pages_pageelement::getElementsOnPage($objMasterData->getSystemid(), true, $this->getStrPortalLanguage());

            //and merge them
            $arrElementsOnPage = array_merge($arrElementsOnPage, $arrElementsOnMaster);
            if($objMasterData->rightEdit())
                $bitEditPermissionOnMasterPage = true;

        }

        //load the merged placeholder-list
        $objPlaceholders = $this->objTemplate->parsePageTemplate("/module_pages/".$objPageData->getStrTemplate(), class_template::INT_ELEMENT_MODE_MASTER);




        //Load the template from the filesystem to get the placeholders
        $strTemplateID = $this->objTemplate->readTemplate("/module_pages/".$objPageData->getStrTemplate(), "", false, true);
        //bit include the masters-elements!!
        $arrRawPlaceholders = $objPlaceholders->getArrPlaceholder();

        $arrPlaceholders = array();
        //and retransform
        foreach($arrRawPlaceholders as $arrOneRawPlaceholder)
            $arrPlaceholders[] = $arrOneRawPlaceholder["placeholder"];


        //Initialize the caches internal cache :)
        class_cache::fillInternalCache("class_element_portal", $this->getPagename(), null, $this->getStrPortalLanguage());


        //try to load the additional title from cache
        $strAdditionalTitleFromCache = "";
        $intMaxCacheDuration = 0;
        $objCachedTitle = class_cache::getCachedEntry(__CLASS__, $this->getPagename(), $this->generateHash2Sum(), $this->getStrPortalLanguage());
        if($objCachedTitle != null) {
            $strAdditionalTitleFromCache = $objCachedTitle->getStrContent();
            self::$strAdditionalTitle = $strAdditionalTitleFromCache;
        }


        //copy for the portaleditor
        $arrPlaceholdersFilled = array();

        //Iterate over all elements and pass control to them
        //Get back the filled element
        //Build the array to fill the template
        $arrTemplate = array();
        $arrBlocks = array();

        /** @var class_module_pages_pageelement $objOneElementOnPage */
        foreach($arrElementsOnPage as $objOneElementOnPage) {


            //element really available on the template?
            if($objOneElementOnPage->getStrElement() != "block" && $objOneElementOnPage->getStrElement() != "blocks" && !in_array($objOneElementOnPage->getStrPlaceholder(), $arrPlaceholders)) {
                //next one, plz
                continue;
            }
//            elseif($objOneElementOnPage->getStrName() == "block" || $objOneElementOnPage->getStrName() == "blocks") {
//
//            }
            else {
                //create a protocol of placeholders filled
                //remove from pe-additional-array, pe code is injected by element directly
                $arrPlaceholdersFilled[] = array(
                    "placeholder" => $objOneElementOnPage->getStrPlaceholder(),
                    "name"        => $objOneElementOnPage->getStrName(),
                    "element"     => $objOneElementOnPage->getStrElement(),
                    "repeatable"  => $objOneElementOnPage->getIntRepeat()
                );
            }

            //Build the class-name for the object
            /** @var  class_element_portal $objElement  */
            $objElement = $objOneElementOnPage->getConcretePortalInstance();
            //let the element do the work and earn the output
            if(!isset($arrTemplate[$objOneElementOnPage->getStrPlaceholder()]))
                $arrTemplate[$objOneElementOnPage->getStrPlaceholder()] = "";


            //cache-handling. load element from cache.
            //if the element is re-generated, save it back to cache.
            if(class_module_system_setting::getConfigValue("_pages_cacheenabled_") == "true" && $this->getParam("preview") != "1" && $objPageData->getStrName() != class_module_system_setting::getConfigValue("_pages_errorpage_")) {
                $strElementOutput = "";
                //if the portaleditor is disabled, do the regular cache lookups in storage. otherwise regenerate again and again :)
                if($bitPeRequested) {
                    $strElementOutput = $objElement->getElementOutput();
                }
                else {
                    //pe not to be taken into account --> full support of caching
                    $strElementOutput = $objElement->getElementOutputFromCache();

                    if($objOneElementOnPage->getIntCachetime() > $intMaxCacheDuration)
                        $intMaxCacheDuration = $objOneElementOnPage->getIntCachetime();

                    if($strElementOutput === false) {
                        $strElementOutput = $objElement->getElementOutput();

                        $objElement->saveElementToCache($strElementOutput);
                    }
                }

            }
            else
                $strElementOutput = $objElement->getElementOutput();

            //if element is disabled & the pe is requested, wrap the content
            if($bitPeRequested && $objOneElementOnPage->getIntRecordStatus() == 0) {
                $arrPeElement = array();
                $arrPeElement["title"] = $this->getLang("pe_inactiveElement", "pages")." (".$objOneElementOnPage->getStrElement().")";
                $strElementOutput = $this->objToolkit->getPeInactiveElement($arrPeElement);
                $strElementOutput = class_element_portal::addPortalEditorSetActiveCode($strElementOutput, $objElement->getSystemid(), array());
            }

            if($objOneElementOnPage->getStrElement() == "blocks") {
                //try to fetch the whole block as a placeholder
                foreach($objPlaceholders->getArrBlocks() as $objOneBlock) {
                    if($objOneBlock->getStrName() == $objOneElementOnPage->getStrName()) {
                        if(!isset($arrBlocks[$objOneBlock->getStrFullSection()])) {
                            $arrBlocks[$objOneBlock->getStrName()] = "";
                        }
                        $arrBlocks[$objOneBlock->getStrName()] .= $strElementOutput;
                    }
                }
            }
            else {
                $arrTemplate[$objOneElementOnPage->getStrPlaceholder()] .= $strElementOutput;

            }

        }

        //pe-code to add new elements on unfilled placeholders --> only if pe is visible?
        if($bitPeRequested) {
            //loop placeholders on template in order to remove already filled ones not being repeatable
            $arrRawPlaceholdersForPe = $arrRawPlaceholders;
            foreach($arrPlaceholdersFilled as $arrOnePlaceholder) {

                foreach($arrRawPlaceholdersForPe as &$arrOneRawPlaceholder) {

                    if($arrOneRawPlaceholder["placeholder"] == $arrOnePlaceholder["placeholder"]) {

                        foreach($arrOneRawPlaceholder["elementlist"] as $intElementKey => $arrOneRawElement) {

                            if($arrOnePlaceholder["element"] == $arrOneRawElement["element"]) {
                                if(uniSubstr($arrOneRawElement["name"], 0, 5) == "master") {
                                    $arrOneRawPlaceholder["elementlist"][$intElementKey] = null;
                                }
                                else if($arrOnePlaceholder["repeatable"] == "0") {
                                    $arrOneRawPlaceholder["elementlist"][$intElementKey] = null;
                                }
                            }

                        }
                    }
                }
            }

            //array is now set up. loop again to create new-buttons
            $arrPePlaceholdersDone = array();
            $arrPeNewButtons = array();
            foreach($arrRawPlaceholdersForPe as $arrOneRawPlaceholderForPe) {
                $strPeNewPlaceholder = $arrOneRawPlaceholderForPe["placeholder"];
                foreach($arrOneRawPlaceholderForPe["elementlist"] as $arrOnePeNewElement) {

                    if($arrOnePeNewElement == null)
                        continue;

                    //check if the linked element exists
                    $objPeNewElement = class_module_pages_element::getElement($arrOnePeNewElement["element"]);
                    if($objPeNewElement == null)
                        continue;

                    //placeholder processed before?
                    $strArrayKey = $strPeNewPlaceholder.$objPeNewElement->getStrName();

                    if(in_array($strArrayKey, $arrPePlaceholdersDone))
                        continue;
                    else
                        $arrPePlaceholdersDone[] = $strArrayKey;

                    //create and register the button to add a new element
                    if(!isset($arrPeNewButtons[$strPeNewPlaceholder]))
                        $arrPeNewButtons[$strPeNewPlaceholder] = "";

                    if(uniStripos($strArrayKey, "master") !== false) {
                        $strLink = "";
                        if($objMasterData !== null)
                            $strLink = class_element_portal::getPortaleditorNewCode($objMasterData->getSystemid(), $strPeNewPlaceholder, $objPeNewElement);
                    }
                    else {
                        $strLink = class_element_portal::getPortaleditorNewCode($objPageData->getSystemid(), $strPeNewPlaceholder, $objPeNewElement);
                    }

                    $arrPeNewButtons[$strPeNewPlaceholder] .= $strLink;

                }
            }

            //loop pe-new code in order to add the wrappers and assign the code to the matching placeholder
            foreach($arrPeNewButtons as $strPlaceholderName => $strNewButtons) {

                if(!isset($arrTemplate[$strPlaceholderName]))
                    $arrTemplate[$strPlaceholderName] = "";

                if($strNewButtons != "")
                    $strNewButtons = class_element_portal::getPortaleditorNewWrapperCode($strPlaceholderName, $strNewButtons);

                $arrTemplate[$strPlaceholderName] .= $strNewButtons;
            }

            // add placeholder wrapping
            foreach($arrTemplate as $strPlaceholder => $strContent) {
                $arrTemplate[$strPlaceholder] = class_carrier::getInstance()->getObjToolkit("portal")->getPePlaceholderWrapper($strPlaceholder, $strContent);
            }
        }


        //check if the additional title has to be saved to the cache
        if(self::$strAdditionalTitle != "" && self::$strAdditionalTitle != $strAdditionalTitleFromCache) {
            $objCacheEntry = class_cache::getCachedEntry(__CLASS__, $this->getPagename(), $this->generateHash2Sum(), $this->getStrPortalLanguage(), true);
            $objCacheEntry->setStrContent(self::$strAdditionalTitle);
            $objCacheEntry->setIntLeasetime(time() + $intMaxCacheDuration);

            $objCacheEntry->updateObjectToDb();
        }


        $arrTemplate["description"] = $objPageData->getStrDesc();
        $arrTemplate["keywords"] = $objPageData->getStrKeywords();
        $arrTemplate["title"] = $objPageData->getStrBrowsername();
        $arrTemplate["additionalTitle"] = self::$strAdditionalTitle;
        $arrTemplate["canonicalUrl"] = class_link::getLinkPortalHref($objPageData->getStrName(), "", $this->getParam("action"), "", $this->getParam("systemid"));

        //Include the $arrGlobal Elements
        $arrGlobal = array();
        $strPath = class_resourceloader::getInstance()->getPathForFile("/portal/global_includes.php");
        if($strPath !== false)
            include(_realpath_.$strPath);

        $arrTemplate = array_merge($arrTemplate, $arrGlobal);
        //fill the template. the template was read before
        $strPageContent = $this->objTemplate->fillTemplateFile($arrTemplate, "/module_pages/".$objPageData->getStrTemplate(), "", true);
        $strPageContent = $this->objTemplate->fillBlocksToTemplateFile($arrBlocks, $strPageContent);

        //add portaleditor main code
        $strPageContent = $this->renderPortalEditorCode($objPageData, $bitEditPermissionOnMasterPage, $strPageContent);

        //insert the copyright headers. Due to our licence, you are NOT allowed to remove those lines.
        $strHeader = "<!--\n";
        $strHeader .= "Website powered by Kajona Open Source Content Management Framework\n";
        $strHeader .= "For more information about Kajona see http://www.kajona.de\n";
        $strHeader .= "-->\n";

        $intBodyPos = uniStripos($strPageContent, "</head>");
        $intPosXml = uniStripos($strPageContent, "<?xml");
        if($intBodyPos !== false) {
            $intBodyPos += 0;
            $strPageContent = uniSubstr($strPageContent, 0, $intBodyPos).$strHeader.uniSubstr($strPageContent, $intBodyPos);
        }
        elseif($intPosXml !== false) {
            $intBodyPos = uniStripos($strPageContent, "?>");
            $intBodyPos += 2;
            $strPageContent = uniSubstr($strPageContent, 0, $intBodyPos).$strHeader.uniSubstr($strPageContent, $intBodyPos);
        }
        else {
            $strPageContent = $strHeader.$strPageContent;
        }

        return $strPageContent;
    }

    /**
     * Determines the page-data to load.
     * This includes the evaluation of the current page-data and the fallback to another language or even the error-page
     *
     * @throws class_exception
     * @return class_module_pages_page
     */
    private function getPageData() {
        $strPagename = $this->getPagename();

        //Load the data of the page
        $objPageData = class_module_pages_page::getPageByName($strPagename);

        //check, if the page is enabled and if the rights are given, or if we want to load a preview of a page
        $bitErrorpage = false;
        if($objPageData == null || ($objPageData->getIntRecordStatus() != 1 || !$objPageData->rightView()))
            $bitErrorpage = true;

        //but: if count != 0 && preview && rights:
        if($bitErrorpage && $objPageData != null && $this->getParam("preview") == "1" && $objPageData->rightEdit())
            $bitErrorpage = false;

        //check, if the template could be loaded
        try {
            if(!$bitErrorpage)
                $this->objTemplate->readTemplate("/module_pages/".$objPageData->getStrTemplate(), "", false, true);
        }
        catch(class_exception $objException) {
            $bitErrorpage = true;
        }

        if($bitErrorpage) {
            //Unfortunately, we have to load the errorpage

            //try to send the correct header
            //page not found
            if($objPageData == null || $objPageData->getIntRecordStatus() != 1)
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_NOT_FOUND);

            //user is not allowed to view the page
            if($objPageData != null && !$objPageData->rightView())
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_FORBIDDEN);

            //check, if the page may be loaded using the default-language
            $strPreviousLang = $this->getStrPortalLanguage();
            $objDefaultLang = class_module_languages_language::getDefaultLanguage();
            if($this->getStrPortalLanguage() != $objDefaultLang->getStrName()) {
                class_logger::getInstance()->addLogRow("Requested page ".$strPagename." not existing in language ".$this->getStrPortalLanguage().", switch to fallback lang", class_logger::$levelWarning);
                $objDefaultLang->setStrPortalLanguage($objDefaultLang->getStrName());
                $objPageData = class_module_pages_page::getPageByName($strPagename);

                $bitErrorpage = false;

                try {
                    if($objPageData != null)
                        $this->objTemplate->readTemplate("/module_pages/".$objPageData->getStrTemplate(), "", false, true);
                    else
                        $bitErrorpage = true;
                }
                catch(class_exception $objException) {
                    $bitErrorpage = true;
                }

                if($bitErrorpage) {
                    $strPagename = class_module_system_setting::getConfigValue("_pages_errorpage_");
                    $this->setParam("page", class_module_system_setting::getConfigValue("_pages_errorpage_"));
                    //revert to the old language - fallback didn't work
                    $objDefaultLang->setStrPortalLanguage($strPreviousLang);
                }
            }
            else {
                $strPagename = class_module_system_setting::getConfigValue("_pages_errorpage_");
                $this->setParam("page", class_module_system_setting::getConfigValue("_pages_errorpage_"));
            }

            $objPageData = class_module_pages_page::getPageByName($strPagename);

            //check, if the page is enabled and if the rights are given, too
            if($objPageData == null || ($objPageData->getIntRecordStatus() != 1 || !$objPageData->rightView())) {
                //Whoops. Nothing to output here
                throw new class_exception("Requested Page ".$strPagename." not existing, no errorpage created or set!", class_exception::$level_FATALERROR);
            }

        }

        return $objPageData;
    }


    /**
     * Adds the portal-editor code to the current page-output - if all requirements are given
     *
     * @param class_module_pages_page $objPageData
     * @param bool $bitEditPermissionOnMasterPage
     * @param string $strPageContent
     *
     * @return string
     */
    private function renderPortalEditorCode(class_module_pages_page $objPageData, $bitEditPermissionOnMasterPage, $strPageContent) {
        //add the portaleditor toolbar
        if(class_module_system_setting::getConfigValue("_pages_portaleditor_") == "false")
            return $strPageContent;

        if(!$this->objSession->isAdmin())
            return $strPageContent;

        if(!$objPageData->rightEdit() && !$bitEditPermissionOnMasterPage)
            return $strPageContent;

        class_adminskin_helper::defineSkinWebpath();

        //save back the current portal text language and set the admin-one
        $strPortalLanguage = class_carrier::getInstance()->getObjLang()->getStrTextLanguage();
        class_carrier::getInstance()->getObjLang()->setStrTextLanguage($this->objSession->getAdminLanguage());

        if($this->objSession->getSession("pe_disable") != "true") {
            $strPeToolbar = "";
            $arrPeContents = array();
            $arrPeContents["pe_status_page_val"] = $objPageData->getStrName();
            $arrPeContents["pe_status_status_val"] = ($objPageData->getIntRecordStatus() == 1 ? "active" : "inactive");
            $arrPeContents["pe_status_autor_val"] = $objPageData->getLastEditUser();
            $arrPeContents["pe_status_time_val"] = timeToString($objPageData->getIntLmTime(), false);
            $arrPeContents["pe_dialog_close_warning"] = $this->getLang("pe_dialog_close_warning", "pages");

            //Add an iconbar
            $arrPeContents["pe_iconbar"] = "";
            $arrPeContents["pe_iconbar"] .= class_link::getLinkAdmin(
                "pages_content", "list", "&systemid=".$objPageData->getSystemid()."&language=".$strPortalLanguage, $this->getLang("pe_icon_edit"),
                $this->getLang("pe_icon_edit", "pages"),
                "icon_page"
            );
            $arrPeContents["pe_iconbar"] .= "&nbsp;";

            $strEditUrl = class_link::getLinkAdminHref("pages", "editPage", "&systemid=".$objPageData->getSystemid()."&language=".$strPortalLanguage."&pe=1");
            $arrPeContents["pe_iconbar"] .= "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strEditUrl."'); return false;\">"
                .class_adminskin_helper::getAdminImage("icon_edit", $this->getLang("pe_icon_page", "pages"))."</a>";

            $arrPeContents["pe_iconbar"] .= "&nbsp;";
            $strEditUrl = class_link::getLinkAdminHref("pages", "newPage", "&systemid=".$objPageData->getSystemid()."&language=".$strPortalLanguage."&pe=1");
            $arrPeContents["pe_iconbar"] .= "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.openDialog('".$strEditUrl."'); return false;\">"
                .class_adminskin_helper::getAdminImage("icon_new", $this->getLang("pe_icon_new", "pages"))."</a>";

            $arrPeContents["pe_disable"] = "<a href=\"#\" onclick=\"KAJONA.admin.portaleditor.switchEnabled(false); return false;\" title=\"\">"
                .class_adminskin_helper::getAdminImage("icon_enabled", $this->getLang("pe_disable", "pages"))."</a>";


            //Load portaleditor javascript (even if it's maybe already loaded in portal and init the ckeditor)
            $strTemplateInitID = $this->objTemplate->readTemplate("/elements.tpl", "wysiwyg_ckeditor_inits");
            $strSkinInit = $this->objTemplate->fillTemplate(array(), $strTemplateInitID);

            $strConfigFile = "'config_kajona_standard.js'";
            if(is_file(_realpath_."/project/admin/scripts/ckeditor/config_kajona_standard.js"))
                $strConfigFile = "KAJONA_WEBPATH+'/project/admin/scripts/ckeditor/config_kajona_standard.js'";

            $strPeToolbar .= "<script type='text/javascript'>
                KAJONA.admin.lang.pe_rte_unsavedChanges = '" . $this->getLang("pe_rte_unsavedChanges", "pages") . "';

                if($) {
                    KAJONA.portal.loader.loadFile([
                        '/core/module_pages/admin/scripts/kajona_portaleditor.js',
                        '/core/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js',
                        '/core/module_system/admin/scripts/jqueryui/css/smoothness/jquery-ui.custom.css'
                    ], function() {
                        KAJONA.admin.portaleditor.RTE.config = {
                            language : '".(class_session::getInstance()->getAdminLanguage() != "" ? class_session::getInstance()->getAdminLanguage() : "en")."',
                            filebrowserBrowseUrl : '".uniStrReplace("&amp;", "&", class_link::getLinkAdminHref("folderview", "browserChooser", "&form_element=ckeditor"))."',
                            filebrowserImageBrowseUrl : '".uniStrReplace("&amp;", "&", class_link::getLinkAdminHref("mediamanager", "folderContentFolderviewMode", "systemid=".class_module_system_setting::getConfigValue("_mediamanager_default_imagesrepoid_")."&form_element=ckeditor&bit_link=1"))."',
                            customConfig : {$strConfigFile},
                            ".$strSkinInit."
                        }
                        $(KAJONA.admin.portaleditor.initPortaleditor);
                    });
                }
                else {
                    KAJONA.portal.loader.loadFile([
                        '/core/module_system/admin/scripts/jquery/jquery.min.js',
                        '/core/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js',
                        '/core/module_pages/admin/scripts/kajona_portaleditor.js',
                        '/core/module_system/admin/scripts/jqueryui/css/smoothness/jquery-ui.custom.css'
                    ], function() {
                        KAJONA.admin.portaleditor.RTE.config = {
                            language : '".(class_session::getInstance()->getAdminLanguage() != "" ? class_session::getInstance()->getAdminLanguage() : "en")."',
                            filebrowserBrowseUrl : '".uniStrReplace("&amp;", "&", class_link::getLinkAdminHref("folderview", "browserChooser", "&form_element=ckeditor"))."',
                            filebrowserImageBrowseUrl : '".uniStrReplace("&amp;", "&", class_link::getLinkAdminHref("mediamanager", "folderContentFolderviewMode", "systemid=".class_module_system_setting::getConfigValue("_mediamanager_default_imagesrepoid_")."&form_element=ckeditor&bit_link=1"))."',
                            ".$strSkinInit."
                        }
                        $(KAJONA.admin.portaleditor.initPortaleditor);
                    });
                }
            </script>";
            //Load portaleditor styles
            $strPeToolbar .= $this->objToolkit->getPeBasicData();
            $strPeToolbar .= $this->objToolkit->getPeToolbar($arrPeContents);

            $objScriptlets = new class_scriptlet_helper();
            $strPeToolbar = $objScriptlets->processString($strPeToolbar, interface_scriptlet::BIT_CONTEXT_ADMIN);

            //The toolbar has to be added right after the body-tag - to generate correct html-code
            $strTemp = uniSubstr($strPageContent, uniStrpos($strPageContent, "<body"));
            //find closing bracket
            $intTemp = uniStrpos($strTemp, ">") + 1;
            //and insert the code
            $strPageContent = uniSubstr($strPageContent, 0, uniStrpos($strPageContent, "<body") + $intTemp).$strPeToolbar.uniSubstr($strPageContent, uniStrpos($strPageContent, "<body") + $intTemp);
        }
        else {
            //Button to enable the toolbar & pe
            $strEnableButton = "<div id=\"peEnableButton\" style=\"z-index: 1000; position: fixed; top: 0px; right: 0px;\"><a href=\"#\" onclick=\"KAJONA.admin.portaleditor.switchEnabled(true); return false;\" title=\"\">"
                .getImageAdmin("icon_disabled", $this->getLang("pe_enable", "pages"))."</a></div>";
            //Load portaleditor javascript
            $strEnableButton .= "\n<script type=\"text/javascript\" src=\""._webpath_."/core/module_pages/admin/scripts/kajona_portaleditor.js?".class_module_system_setting::getConfigValue("_system_browser_cachebuster_")."\"></script>";
            $strEnableButton .= $this->objToolkit->getPeBasicData();
            //Load portaleditor styles
            //The toobar has to be added right after the body-tag - to generate correct html-code
            $strTemp = uniSubstr($strPageContent, uniStripos($strPageContent, "<body"));
            //find closing bracket
            $intTemp = uniStripos($strTemp, ">") + 1;
            //and insert the code
            $strPageContent = uniSubstr($strPageContent, 0, uniStrpos($strPageContent, "<body") + $intTemp).$strEnableButton.uniSubstr($strPageContent, uniStrpos($strPageContent, "<body") + $intTemp);
        }

        //reset the portal texts language
        class_carrier::getInstance()->getObjLang()->setStrTextLanguage($strPortalLanguage);


        return $strPageContent;
    }




    /**
     * Sets the passed text as an additional title information.
     * If set, the separator placeholder from global_includes.php will be included, too.
     * Modules may register additional page-titles in order to have them places as the current page-title.
     * Since this is a single field, the last module wins in case of multiple entries.
     *
     * @param string $strTitle
     *
     * @return void
     */
    public static function registerAdditionalTitle($strTitle) {
        self::$strAdditionalTitle = $strTitle."%%kajonaTitleSeparator%%";
    }

    /**
     * @return string
     */
    private function generateHash2Sum() {
        $strGuestId = "";
        //when browsing the site as a guest, drop the userid
        if($this->objSession->isLoggedin())
            $strGuestId = $this->objSession->getUserID();

        return sha1("".$strGuestId.$this->getAction().$this->getParam("pv").$this->getSystemid().$this->getParam("systemid").$this->getParam("highlight"));
    }

}
