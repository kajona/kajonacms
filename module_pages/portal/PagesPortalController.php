<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal;

use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorPlaceholderAction;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\CacheManager;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\Link;
use Kajona\System\System\Logger;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\Template;

/**
 * Handles the loading of the pages - loads the elements, passes control to them and returns the complete
 * page ready for output
 *
 * @author sidler@mulchprod.de
 *
 * @module pages
 * @moduleId _pages_modul_id_
 */
class PagesPortalController extends PortalController implements PortalInterface
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
    public function __construct($arrElementData = array(), $strSystemid = "")
    {
        parent::__construct($arrElementData, $strSystemid);
        $this->setAction("generatePage");
    }

    /**
     * Handles the loading of a page, more in a functional than in an oop style
     *
     * @throws Exception
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


        if (!PagesPortaleditor::isActive()) {
            /** @var CacheManager $objCache */
            $objCache = Carrier::getInstance()->getContainer()->offsetGet("cache_manager");
            $strPageContent = $objCache->getValue($this->getPageCacheHashSum());
            if ($strPageContent !== false) {
                return $strPageContent;
            }
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
        if ($objMasterData != null) {
            if (PagesPortaleditor::isActive()) {
                $arrElementsOnMaster = PagesPageelement::getElementsOnPage($objMasterData->getSystemid(), false, $this->getStrPortalLanguage());
            }
            else {
                $arrElementsOnMaster = PagesPageelement::getElementsOnPage($objMasterData->getSystemid(), true, $this->getStrPortalLanguage());
            }

            //and merge them
            $arrElementsOnPage = array_merge($arrElementsOnPage, $arrElementsOnMaster);
        }

        //load the merged placeholder-list
        $objPlaceholders = $this->objTemplate->parsePageTemplate("/module_pages/".$objPageData->getStrTemplate(), Template::INT_ELEMENT_MODE_MASTER);


        //Load the template from the filesystem to get the placeholders
        //bit include the masters-elements!!
        $arrRawPlaceholders = $objPlaceholders->getArrPlaceholder();

        $arrPlaceholders = array();
        //and retransform
        foreach ($arrRawPlaceholders as $arrOneRawPlaceholder) {
            $arrPlaceholders[] = $arrOneRawPlaceholder["placeholder"];
        }

        $arrPlaceholderWithElements = array();

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

        //pe-code to add new elements on unfilled block --> only if pe is visible
        if (PagesPortaleditor::isActive()) {

            foreach ($objPlaceholders->getArrBlocks() as $objOneBlocks) {
                foreach ($objOneBlocks->getArrBlocks() as $objOneBlock) {

                    //register a new-action per block-element
                    if (PagesPortaleditor::isActive()) {
                        $strId = $objOneBlocks->getStrName();
                        if (isset($arrBlocksIds[$objOneBlocks->getStrName()])) {
                            $strId = $arrBlocksIds[$objOneBlocks->getStrName()];
                        }

                        PagesPortaleditor::getInstance()->registerAction(
                            new PagesPortaleditorPlaceholderAction(
                                PagesPortaleditorActionEnum::CREATE(),
                                Link::getLinkAdminHref("pages_content", "newBlock", "&blocks={$strId}&block={$objOneBlock->getStrName()}&systemid={$objPageData->getSystemid()}&peClose=1"), "blocks_".$objOneBlocks->getStrName(),
                                $objOneBlock->getStrName()
                            )
                        );
                    }
                }
                if (!isset($arrBlocks[$objOneBlocks->getStrName()])) {
                    $arrBlocks[$objOneBlocks->getStrName()] = "";
                }
                $arrBlocks[$objOneBlocks->getStrName()] = PagesPortaleditor::getPlaceholderWrapper("blocks_".$objOneBlocks->getStrName(), $arrBlocks[$objOneBlocks->getStrName()]);

            }

            foreach ($objPlaceholders->getArrPlaceholder() as $arrOnePlaceholder) {

                foreach ($arrOnePlaceholder["elementlist"] as $arrSinglePlaceholder) {
                    /** @var PagesElement $objElement */
                    $objElement = PagesElement::getElement($arrSinglePlaceholder["element"]);
                    if ($objElement == null) {
                        continue;
                    }

                    //see if the element was rendered at the same placeholder and skip it if not repeatable

                    $objPortalElement = $objElement->getPortalElementInstance();

                    $objPortalElement->getPortaleditorPlaceholderActions(isset($arrPlaceholderWithElements[$arrSinglePlaceholder["name"].$arrSinglePlaceholder["element"]]), $objElement, $arrOnePlaceholder["placeholder"], $objPageData);

                }

                if (!isset($arrTemplate[$arrOnePlaceholder["placeholder"]])) {
                    $arrTemplate[$arrOnePlaceholder["placeholder"]] = "";
                }
                $arrTemplate[$arrOnePlaceholder["placeholder"]] = PagesPortaleditor::getPlaceholderWrapper($arrOnePlaceholder["placeholder"], $arrTemplate[$arrOnePlaceholder["placeholder"]]);
            }

        }


        $arrTemplate["description"] = $objPageData->getStrDesc();
        $arrTemplate["keywords"] = $objPageData->getStrKeywords();
        $arrTemplate["title"] = $objPageData->getStrBrowsername();
        $arrTemplate["additionalTitle"] = self::$strAdditionalTitle;
        $arrTemplate["canonicalUrl"] = Link::getLinkPortalHref($objPageData->getStrName(), "", $this->getParam("action"), "", $this->getParam("systemid"));

        //Include the $arrGlobal Elements
        $arrGlobal = array();
        $strPath = Resourceloader::getInstance()->getPathForFile("/portal/global_includes.php");
        if ($strPath !== false) {
            if (is_file($strPath)) {
                include($strPath);
            }
            else {
                include(_realpath_.$strPath);
            }
        }

        $arrTemplate = array_merge($arrTemplate, $arrGlobal);
        //fill the template. the template was read before
        $strPageContent = $this->objTemplate->fillTemplateFile($arrTemplate, "/module_pages/".$objPageData->getStrTemplate(), "", true);
        $strPageContent = $this->objTemplate->fillBlocksToTemplateFile($arrBlocks, $strPageContent);
        $strPageContent = $this->objTemplate->deleteBlocksFromTemplate($strPageContent);

        //add portaleditor main code
        $strPageContent = PagesPortaleditor::injectPortalEditorPageCode($objPageData, $strPageContent);

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

        //and cache the whole page
        $intPageCacheTime = $this->getPageCacheTime($arrElementsOnPage);
        if ($intPageCacheTime > 0) {
            /** @var CacheManager $objCache */
            $objCache = Carrier::getInstance()->getContainer()->offsetGet("cache_manager");
            $objCache->addValue($this->getPageCacheHashSum(), $strPageContent, $intPageCacheTime);
        }

        return $strPageContent;
    }

    /**
     * Determines the page-data to load.
     * This includes the evaluation of the current page-data and the fallback to another language or even the error-page
     *
     * @throws Exception
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
        catch (Exception $objException) {
            $bitErrorpage = true;
        }

        if ($bitErrorpage) {
            //Unfortunately, we have to load the errorpage

            //try to send the correct header
            //page not found
            if ($objPageData == null || $objPageData->getIntRecordStatus() != 1) {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_NOT_FOUND);
            }

            //user is not allowed to view the page
            if ($objPageData != null && !$objPageData->rightView()) {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
            }

            //check, if the page may be loaded using the default-language
            $strPreviousLang = $this->getStrPortalLanguage();
            $objDefaultLang = LanguagesLanguage::getDefaultLanguage();
            if ($this->getStrPortalLanguage() != $objDefaultLang->getStrName()) {
                Logger::getInstance()->addLogRow("Requested page ".$strPagename." not existing in language ".$this->getStrPortalLanguage().", switch to fallback lang", Logger::$levelWarning);
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
                catch (Exception $objException) {
                    $bitErrorpage = true;
                }

                if ($bitErrorpage) {
                    $strPagename = SystemSetting::getConfigValue("_pages_errorpage_");
                    $this->setParam("page", SystemSetting::getConfigValue("_pages_errorpage_"));
                    //revert to the old language - fallback didn't work
                    $objDefaultLang->setStrPortalLanguage($strPreviousLang);
                }
            }
            else {
                $strPagename = SystemSetting::getConfigValue("_pages_errorpage_");
                $this->setParam("page", SystemSetting::getConfigValue("_pages_errorpage_"));
            }

            $objPageData = PagesPage::getPageByName($strPagename);

            //check, if the page is enabled and if the rights are given, too
            if ($objPageData == null || ($objPageData->getIntRecordStatus() != 1 || !$objPageData->rightView())) {
                //Whoops. Nothing to output here
                throw new Exception("Requested Page ".$strPagename." not existing, no errorpage created or set!", Exception::$level_FATALERROR);
            }

        }

        return $objPageData;
    }


    /**
     * @param PagesPageelement[] $arrElementsOnPage
     *
     * @return int|null
     */
    private function getPageCacheTime(array $arrElementsOnPage)
    {
        $intCachetime = null;

        foreach ($arrElementsOnPage as $objOneElement) {
            /** @var  ElementPortal $objElement */
            $objElement = $objOneElement->getConcretePortalInstance();

            $intElementCachetime = $objElement->getCachetimeInSeconds();
            if ($intCachetime === null || $intElementCachetime < $intCachetime) {
                $intCachetime = $intElementCachetime;
            }
        }

        return $intCachetime !== null ? $intCachetime : 0;
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
    private function getPageCacheHashSum()
    {
        $strGuestId = "";
        //when browsing the site as a guest, drop the userid
        if ($this->objSession->isLoggedin()) {
            $strGuestId = $this->objSession->getUserID();
        }

        return sha1(
            __CLASS__.
            $strGuestId.
            $this->getPagename().
            $this->getAction().
            $this->getParam("pv").
            $this->getSystemid().
            $this->getParam("systemid").
            $this->getParam("highlight")
        );
    }

}
