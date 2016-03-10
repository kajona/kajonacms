<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$								*
********************************************************************************************************/
namespace Kajona\Pages\Portal;

use Kajona\Navigation\System\NavigationPoint;
use Kajona\Pages\Admin\Elements\ElementBlockAdmin;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorPlaceholderAction;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;
use Kajona\System\Portal\PortalController;
use Kajona\System\System\CacheManager;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmBase;
use Kajona\System\System\Reflection;
use Kajona\System\System\ScriptletHelper;
use Kajona\System\System\ScriptletInterface;
use Kajona\System\System\ServiceProvider;
use Kajona\System\System\SystemSetting;

/**
 * Base Class for all portal-elements
 *
 * @author sidler@mulchprod.de
 * @abstract
 *
 * @module elements
 * @moduleId _pages_elemente_modul_id_
 */
abstract class ElementPortal extends PortalController
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
        $objAnnotations = new Reflection($this);
        $arrTargetTables = $objAnnotations->getAnnotationValuesFromClass(OrmBase::STR_ANNOTATION_TARGETTABLE);
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
            return Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strSystemid));
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
        catch (Exception $objEx) {
            //FIXME: error handling is currently disabled
            //An error occurred during content generation. redirect to error page
            //$objEx->processException();
            //if available, show the error-page. on debugging-environments, the exception processing already die()d the process.
//            if ($this->getPagename() != SystemSetting::getConfigValue("_pages_errorpage_")) {
//                $this->portalReload(Link::getLinkPortalHref(SystemSetting::getConfigValue("_pages_errorpage_")));
//            }

            $strReturn = $objEx->getMessage();
        }

        //add an anchor to jump to, but exclude navigation-elements
        $strReturn = $this->getAnchorTag().$strReturn;

        //apply element-based scriptlets
        $objScriptlets = new ScriptletHelper();
        $strReturn = $objScriptlets->processString($strReturn, ScriptletInterface::BIT_CONTEXT_PORTAL_ELEMENT);

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
        /** @var CacheManager $objCache */
        $objCache = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER);
        return $objCache->getValue($this->getCacheHashSum());
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
        $intCachetimeInSeconds = $this->getCachetimeInSeconds();
        if($intCachetimeInSeconds <= 0) {
            return;
        }

        //strip the data-editable values - no use case for regular page views
        $strElementOutput = preg_replace('/data-kajona-editable=\"([a-zA-Z0-9#_]*)\"/i', "", $strElementOutput);

        /** @var CacheManager $objCache */
        $objCache = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER);
        $objCache->addValue($this->getCacheHashSum(), $strElementOutput, $intCachetimeInSeconds);

    }


    public function getCacheHashSum()
    {
        $strGuestId = "";
        //when browsing the site as a guest, drop the userid
        if ($this->objSession->isLoggedin()) {
            $strGuestId = $this->objSession->getUserID();
        }

        return sha1(
            __CLASS__.
            $strGuestId.
            $this->getAction().
            $this->strCacheAddon.
            $this->getParam("pv").
            $this->getSystemid().
            $this->getParam("systemid").
            $this->getParam("highlight")
        );
    }

    /**
     * Returns the number of seconds the current element will be cached in the pagecache.
     * @return int
     */
    public function getCachetimeInSeconds()
    {
        return $this->objElementData->getIntCachetime();
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

        if (SystemSetting::getConfigValue("_pages_cacheenabled_") == "true" && $this->getParam("preview") != "1" && $this->getPageData()->getStrName() != SystemSetting::getConfigValue("_pages_errorpage_")) {
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


        if ($bitActivePortaleditor) {
            $strElementOutput = $this->addPortalEditorCode($strElementOutput);
        }
        else {
            $strElementOutput = $this->removePortalEditorTags($strElementOutput);
        }


        return $strElementOutput;
    }

    /**
     * Adds the portal-editor code to the current elements' content
     *
     * @param $strElementOutput
     *
     * @return string
     */
    protected function addPortalEditorCode($strElementOutput)
    {
        $this->getPortalEditorActions();
        return PagesPortaleditor::addPortaleditorContentWrapper($strElementOutput, $this->getSystemid(), $this->arrElementData["page_element_ph_element"]);
    }

    /**
     * Removes the portal-editor editable ids
     *
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

        $objParent = Objectfactory::getInstance()->getObject($objPageelement->getPrevId());
        if ($objParent instanceof PagesPageelement) {
            $objParent = $objParent->getConcreteAdminInstance();
        }


        //fetch the language to set the correct admin-lang
        $objLanguages = new LanguagesLanguage();
        $strAdminLangParam = $objLanguages->getPortalLanguage();


        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::EDIT(), Link::getLinkAdminHref("pages_content", "edit", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
        );

        if (!$objParent instanceof ElementBlockAdmin) {

            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::COPY(), Link::getLinkAdminHref("pages_content", "copyElement", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
            );
            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::DELETE(), Link::getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
            );
            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::MOVE(), "", $this->getSystemid())
            );


            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::SETINACTIVE(), Link::getLinkAdminHref("pages_content", "elementStatus", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
            );
            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::SETACTIVE(), Link::getLinkAdminHref("pages_content", "elementStatus", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
            );

        }
    }

    /**
     * Registers new-entry actions for a given placeholder
     *
     * @param $bitElementIsExistingAtPlaceholder
     * @param PagesElement $objElement
     * @param $strPlaceholder
     * @param PagesPage $objPage
     */
    public function getPortaleditorPlaceholderActions($bitElementIsExistingAtPlaceholder, PagesElement $objElement, $strPlaceholder, PagesPage $objPage)
    {
        //fetch the language to set the correct admin-lang
        $objLanguages = new LanguagesLanguage();
        $strAdminLangParam = $objLanguages->getPortalLanguage();


        if ($objElement->getIntRepeat() == 1 || !$bitElementIsExistingAtPlaceholder) {
            PagesPortaleditor::getInstance()->registerAction(
                new PagesPortaleditorPlaceholderAction(PagesPortaleditorActionEnum::CREATE(), Link::getLinkAdminHref("pages_content", "new", "&systemid={$objPage->getSystemid()}&language={$strAdminLangParam}&placeholder={$strPlaceholder}&element={$objElement->getStrName()}&pe=1"), $strPlaceholder, $objElement->getStrName())
            );
        }
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
     *    node => NavigationPoint ,
     *    subnodes => array(
     *        array( node => NavigationPoint, subnodes => array(...)),
     *        array( node => NavigationPoint, subnodes => array(...))
     *    )
     * )
     * If you don't want to create additional navigation entries, don't overwrite this method.
     * Otherwise you have to override the method providesNavigationEntries() and return true.
     * This method is only queried if the static providesNavigationEntries is true since the number of queries
     * could be reduced drastically due to this pre-check.
     * If you only want to return a flat list of nodes, you can return an array of NavigationPoint instances instead of wrapping them
     * into the way more complex node/subnode structure.
     *
     * @see NavigationTree::getCompleteNaviStructure()
     * @see NavigationPoint::getDynamicNaviLayer()
     * @return array|NavigationPoint[]|bool
     * @since 4.0
     */
    public function getNavigationEntries()
    {
        return false;
    }

    /**
     * @return PagesPageelement
     */
    public function getObjElementData()
    {
        return $this->objElementData;
    }

}

