<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal;

use class_adminskin_helper;
use class_cache;
use class_carrier;
use class_exception;
use class_http_statuscodes;
use class_link;
use class_logger;
use class_module_languages_language;
use class_module_system_setting;
use class_portal_controller;
use class_resourceloader;
use class_response_object;
use class_scriptlet_helper;
use class_session;
use class_template;
use interface_portal;
use interface_scriptlet;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorPlaceholderAction;

/**
 * Handles the loading of the pages - loads the elements, passes control to them and returns the complete
 * page ready for output
 *
 * @author sidler@mulchprod.de
 *
 * @module pages
 * @moduleId _pages_modul_id_
 */
class PagesPortalController extends class_portal_controller implements interface_portal
{

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
    public function __construct($arrElementData = array(), $strSystemid = "") {
        parent::__construct($arrElementData, $strSystemid);
        $this->setAction("generatePage");
    }

    /**
     * Handles the loading of a page, more in a functional than in an oop style
     *
     * @throws class_exception
     * @return string the generated page
     * @permissions view
     */
    protected function actionGeneratePage()
    {

        //Determine the pagename
        $objPageData = $this->getPageData();

        //react on portaleditor commands
        //pe to display, or pe to disable?
        if ($this->getParam("pe") == "false") {
            $this->objSession->setSession("pe_disable", "true");
        }
        if ($this->getParam("pe") == "true") {
            $this->objSession->setSession("pe_disable", "false");
        }

        //If we reached up till here, we can begin loading the elements to fill
        if (PagesPortaleditor::isActive()) {
            $arrElementsOnPage = PagesPageelement::getElementsOnPage($objPageData->getSystemid(), false, $this->getStrPortalLanguage());
        }
        else {
            $arrElementsOnPage = PagesPageelement::getElementsOnPage($objPageData->getSystemid(), true, $this->getStrPortalLanguage());
        }

        //If there's a master-page, load elements on that, too
        $objMasterData = PagesPage::getPageByName("master");
        $bitEditPermissionOnMasterPage = false;
        if ($objMasterData != null) {
            if (PagesPortaleditor::isActive()) {
                $arrElementsOnMaster = PagesPageelement::getElementsOnPage($objMasterData->getSystemid(), false, $this->getStrPortalLanguage());
            }
            else {
                $arrElementsOnMaster = PagesPageelement::getElementsOnPage($objMasterData->getSystemid(), true, $this->getStrPortalLanguage());
            }

            //and merge them
            $arrElementsOnPage = array_merge($arrElementsOnPage, $arrElementsOnMaster);
            if ($objMasterData->rightEdit()) {
                $bitEditPermissionOnMasterPage = true;
            }

        }

        //load the merged placeholder-list
        $objPlaceholders = $this->objTemplate->parsePageTemplate("/module_pages/".$objPageData->getStrTemplate(), class_template::INT_ELEMENT_MODE_MASTER);


        //Load the template from the filesystem to get the placeholders
        //bit include the masters-elements!!
        $arrRawPlaceholders = $objPlaceholders->getArrPlaceholder();

        $arrPlaceholders = array();
        //and retransform
        foreach ($arrRawPlaceholders as $arrOneRawPlaceholder) {
            $arrPlaceholders[] = $arrOneRawPlaceholder["placeholder"];
        }


        //Initialize the caches internal cache :)
        class_cache::fillInternalCache("class_element_portal", $this->getPagename(), null, $this->getStrPortalLanguage());


        //try to load the additional title from cache
        $strAdditionalTitleFromCache = "";
        $intMaxCacheDuration = 0; //TODO find a better cache sum, in v4 determined by the elements
        $objCachedTitle = class_cache::getCachedEntry(__CLASS__, $this->getPagename(), $this->generateHash2Sum(), $this->getStrPortalLanguage());
        if ($objCachedTitle != null) {
            $strAdditionalTitleFromCache = $objCachedTitle->getStrContent();
            self::$strAdditionalTitle = $strAdditionalTitleFromCache;
        }

        $arrPlaceholderWithElements = array();

        //copy for the portaleditor

        //Iterate over all elements and pass control to them
        //Get back the filled element
        //Build the array to fill the template
        $arrTemplate = array();
        $arrBlocks = array();

        $arrBlocksIds = array();

        /** @var PagesPageelement $objOneElementOnPage */
        foreach ($arrElementsOnPage as $objOneElementOnPage) {

            //element really available on the template?
            if ($objOneElementOnPage->getStrElement() != "block" && $objOneElementOnPage->getStrElement() != "blocks" && !in_array($objOneElementOnPage->getStrPlaceholder(), $arrPlaceholders)) {
                //next one, plz
                continue;
            }


            $arrPlaceholderWithElements[$objOneElementOnPage->getStrName().$objOneElementOnPage->getStrElement()] = true;

            //Build the class-name for the object
            /** @var  ElementPortal $objElement */
            $objElement = $objOneElementOnPage->getConcretePortalInstance();
            //let the element do the work and earn the output
            if (!isset($arrTemplate[$objOneElementOnPage->getStrPlaceholder()])) {
                $arrTemplate[$objOneElementOnPage->getStrPlaceholder()] = "";
            }


            //cache-handling. load element from cache.
            //if the element is re-generated, save it back to cache.
            $strElementOutput = $objElement->getRenderedElementOutput(PagesPortaleditor::isActive());

            if ($objOneElementOnPage->getStrElement() == "blocks") {
                //try to fetch the whole block as a placeholder
                foreach ($objPlaceholders->getArrBlocks() as $objOneBlock) {
                    if ($objOneBlock->getStrName() == $objOneElementOnPage->getStrName()) {
                        if (!isset($arrBlocks[$objOneBlock->getStrName()])) {
                            $arrBlocks[$objOneBlock->getStrName()] = "";
                        }
                        $arrBlocks[$objOneBlock->getStrName()] .= $strElementOutput;
                        $arrBlocksIds[$objOneBlock->getStrName()] = $objOneElementOnPage->getSystemid();
                    }
                }
            }
            else {
                $arrTemplate[$objOneElementOnPage->getStrPlaceholder()] .= $strElementOutput;

            }

        }

        //pe-code to add new elements on unfilled placeholders --> only if pe is visible
        if (PagesPortaleditor::isActive()) {

            foreach($objPlaceholders->getArrBlocks() as $objOneBlocks) {
                foreach($objOneBlocks->getArrBlocks() as $objOneBlock) {

                    //register a new-action per block-element
                    if (PagesPortaleditor::isActive()) {
                        $strId = $objOneBlocks->getStrName();
                        if(isset($arrBlocksIds[$objOneBlocks->getStrName()])) {
                            $strId = $arrBlocksIds[$objOneBlocks->getStrName()];
                        }

                        PagesPortaleditor::getInstance()->registerAction(
                            new PagesPortaleditorPlaceholderAction(
                                PagesPortaleditorActionEnum::CREATE(),
                                class_link::getLinkAdminHref("pages_content", "newBlock", "&blocks={$strId}&block={$objOneBlock->getStrName()}&systemid={$objPageData->getSystemid()}&peClose=1"), "blocks_".$objOneBlocks->getStrName(),
                                $objOneBlock->getStrName()
                            )
                        );
                    }
                }
                if(!isset($arrBlocks[$objOneBlocks->getStrName()])) {
                    $arrBlocks[$objOneBlocks->getStrName()] = "";
                }
                $arrBlocks[$objOneBlocks->getStrName()] .= PagesPortaleditor::getPlaceholderWrapper("blocks_".$objOneBlocks->getStrName());

            }

            foreach ($objPlaceholders->getArrPlaceholder() as $arrOnePlaceholder) {

                foreach ($arrOnePlaceholder["elementlist"] as $arrSinglePlaceholder) {
                    /** @var PagesElement $objElement */
                    $objElement = PagesElement::getElement($arrSinglePlaceholder["element"]);
                    if ($objElement == null) {
                        continue;
                    }

                    $objPortalElement = $objElement->getPortalElementInstance();

                    $objPortalElement->getPortaleditorPlaceholderActions(isset($arrPlaceholderWithElements[$arrSinglePlaceholder["name"].$arrSinglePlaceholder["element"]]), $objElement, $arrOnePlaceholder["placeholder"]);

                }

                if (!isset($arrTemplate[$arrOnePlaceholder["placeholder"]])) {
                    $arrTemplate[$arrOnePlaceholder["placeholder"]] = "";
                }
                $arrTemplate[$arrOnePlaceholder["placeholder"]] .= PagesPortaleditor::getPlaceholderWrapper($arrOnePlaceholder["placeholder"]);
            }

        }


        //check if the additional title has to be saved to the cache
        if (self::$strAdditionalTitle != "" && self::$strAdditionalTitle != $strAdditionalTitleFromCache) {
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
        if ($strPath !== false) {
            if (is_file($strPath)) {
                include($strPath);
            } else {
                include(_realpath_.$strPath);
            }
        }

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
        if ($intBodyPos !== false) {
            $intBodyPos += 0;
            $strPageContent = uniSubstr($strPageContent, 0, $intBodyPos).$strHeader.uniSubstr($strPageContent, $intBodyPos);
        }
        elseif ($intPosXml !== false) {
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
     * @return PagesPage
     */
    private function getPageData()
    {
        $strPagename = $this->getPagename();

        //Load the data of the page
        $objPageData = PagesPage::getPageByName($strPagename);

        //check, if the page is enabled and if the rights are given, or if we want to load a preview of a page
        $bitErrorpage = false;
        if ($objPageData == null || ($objPageData->getIntRecordStatus() != 1 || !$objPageData->rightView())) {
            $bitErrorpage = true;
        }

        //but: if count != 0 && preview && rights:
        if ($bitErrorpage && $objPageData != null && $this->getParam("preview") == "1" && $objPageData->rightEdit()) {
            $bitErrorpage = false;
        }

        //check, if the template could be loaded
        try {
            if (!$bitErrorpage) {
                $this->objTemplate->readTemplate("/module_pages/".$objPageData->getStrTemplate(), "", false, true);
            }
        }
        catch (class_exception $objException) {
            $bitErrorpage = true;
        }

        if ($bitErrorpage) {
            //Unfortunately, we have to load the errorpage

            //try to send the correct header
            //page not found
            if ($objPageData == null || $objPageData->getIntRecordStatus() != 1) {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_NOT_FOUND);
            }

            //user is not allowed to view the page
            if ($objPageData != null && !$objPageData->rightView()) {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_FORBIDDEN);
            }

            //check, if the page may be loaded using the default-language
            $strPreviousLang = $this->getStrPortalLanguage();
            $objDefaultLang = class_module_languages_language::getDefaultLanguage();
            if ($this->getStrPortalLanguage() != $objDefaultLang->getStrName()) {
                class_logger::getInstance()->addLogRow("Requested page ".$strPagename." not existing in language ".$this->getStrPortalLanguage().", switch to fallback lang", class_logger::$levelWarning);
                $objDefaultLang->setStrPortalLanguage($objDefaultLang->getStrName());
                $objPageData = PagesPage::getPageByName($strPagename);

                $bitErrorpage = false;

                try {
                    if ($objPageData != null) {
                        $this->objTemplate->readTemplate("/module_pages/".$objPageData->getStrTemplate(), "", false, true);
                    }
                    else {
                        $bitErrorpage = true;
                    }
                }
                catch (class_exception $objException) {
                    $bitErrorpage = true;
                }

                if ($bitErrorpage) {
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

            $objPageData = PagesPage::getPageByName($strPagename);

            //check, if the page is enabled and if the rights are given, too
            if ($objPageData == null || ($objPageData->getIntRecordStatus() != 1 || !$objPageData->rightView())) {
                //Whoops. Nothing to output here
                throw new class_exception("Requested Page ".$strPagename." not existing, no errorpage created or set!", class_exception::$level_FATALERROR);
            }

        }

        return $objPageData;
    }


    /**
     * Adds the portal-editor code to the current page-output - if all requirements are given
     *
     * @param PagesPage $objPageData
     * @param bool $bitEditPermissionOnMasterPage
     * @param string $strPageContent
     *
     * @return string
     * @todo move this to an external class
     */
    private function renderPortalEditorCode(PagesPage $objPageData, $bitEditPermissionOnMasterPage, $strPageContent)
    {
        //add the portaleditor toolbar
        if (class_module_system_setting::getConfigValue("_pages_portaleditor_") == "false") {
            return $strPageContent;
        }

        if (!$this->objSession->isAdmin()) {
            return $strPageContent;
        }

        if (!$objPageData->rightEdit() && !$bitEditPermissionOnMasterPage) {
            return $strPageContent;
        }

        class_adminskin_helper::defineSkinWebpath();

        //save back the current portal text language and set the admin-one
        $strPortalLanguage = class_carrier::getInstance()->getObjLang()->getStrTextLanguage();
        class_carrier::getInstance()->getObjLang()->setStrTextLanguage($this->objSession->getAdminLanguage());

        if ($this->objSession->getSession("pe_disable") != "true") {
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
            if (is_file(_realpath_."/project/admin/scripts/ckeditor/config_kajona_standard.js")) {
                $strConfigFile = "KAJONA_WEBPATH+'/project/admin/scripts/ckeditor/config_kajona_standard.js'";
            }

            $strPeToolbar .= "<script type='text/javascript'>
                KAJONA.admin.lang.pe_rte_unsavedChanges = '".$this->getLang("pe_rte_unsavedChanges", "pages")."';

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

                KAJONA.admin.actions = ".PagesPortaleditor::getInstance()->convertToJs().";
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
            $strEnableButton = "<div id=\"peEnableButton\" style=\"z-index: 1000; position: fixed; top: 0; right: 0;\"><a href=\"#\" onclick=\"KAJONA.admin.portaleditor.switchEnabled(true); return false;\" title=\"\">"
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
    public static function registerAdditionalTitle($strTitle)
    {
        self::$strAdditionalTitle = $strTitle."%%kajonaTitleSeparator%%";
    }

    /**
     * @return string
     */
    private function generateHash2Sum()
    {
        $strGuestId = "";
        //when browsing the site as a guest, drop the userid
        if ($this->objSession->isLoggedin()) {
            $strGuestId = $this->objSession->getUserID();
        }

        return sha1("".$strGuestId.$this->getAction().$this->getParam("pv").$this->getSystemid().$this->getParam("systemid").$this->getParam("highlight"));
    }

}
