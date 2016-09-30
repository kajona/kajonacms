<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Admin;

use Kajona\Pages\Admin\Elements\ElementBlockAdmin;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\Admin\AdminSimple;
use Kajona\System\Admin\LanguagesAdmin;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\Link;
use Kajona\System\System\Lockmanager;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmBase;
use Kajona\System\System\Reflection;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\ServiceProvider;
use Kajona\System\System\SystemModule;
use Kajona\System\System\Template;
use Kajona\System\System\TemplateBlockContainer;
use Kajona\System\System\TemplateBlocksParserException;
use Kajona\System\System\TemplateKajonaSections;

/**
 * This class is used to edit the content of a page. So, to create / delete / modify elements on a
 * given page.
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @module pages
 * @moduleId _pages_content_modul_id_
 */
class PagesContentAdmin extends AdminSimple implements AdminInterface
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        if (_xmlLoader_) {
            $this->setArrModuleEntry("modul", "pages_content");
        }

        //If there's anything to unlock, do it now
        if ($this->getParam("unlockid") != "") {
            $objLockmanager = new Lockmanager($this->getParam("unlockid"));
            $objLockmanager->unlockRecord();
        }
    }


    /**
     * Adds the current page-name to the module-title
     *
     * @return string
     */
    public function getOutputModuleTitle()
    {
        $objPage = new PagesPage($this->getSystemid());
        if ($objPage->getStrName() == "") {
            $objPage = new PagesPage($objPage->getPrevId());
        }
        return $this->getLang("modul_titel")." (".$objPage->getStrName().")";
    }


    /**
     * Tries to generate a quick-help button.
     * Tests for exisiting help texts
     *
     * @return string
     */
    protected function getQuickHelp()
    {
        $strReturn = "";
        $strText = "";
        $strTextname = "";

        //Text for the current action available?
        //different loading when editing page-elements
        if ($this->getParam("module") == "pages_content" && ($this->getParam("action") == "edit" || $this->getParam("action") == "new")) {
            $objElement = null;
            if ($this->getParam("action") == "edit") {
                $objElement = new PagesPageelement($this->getSystemid());
            }
            elseif ($this->getParam("action") == "new") {
                $strPlaceholderElement = $this->getParam("element");
                $objElement = PagesElement::getElement($strPlaceholderElement);
            }

            //and finally create the object
            $strFilename = $this->objResourceLoader->getPathForFile("/admin/elements/".$objElement->getStrClassAdmin());
            $objElement = $this->objClassLoader->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");

            //and finally create the object
            if ($objElement != null) {
                $strTextname = $this->objLang->stringToPlaceholder("quickhelp_".$objElement->getArrModule("name"));
                $strText = $this->objLang->getLang($strTextname, $objElement->getArrModule("modul"));
            }
        }
        else {
            $strTextname = $this->objLang->stringToPlaceholder("quickhelp_".$this->getAction());
            $strText = $this->getLang($strTextname);
        }

        if ($strText != "!".$strTextname."!") {
            //Text found, embed the quickhelp into the current skin
            $strReturn .= $this->objToolkit->getQuickhelp($strText);
        }

        return $strReturn;
    }


    private function getPageInfoBox(PagesPage $objPage)
    {
        $strReturn = "";
        //get infos about the page
        $arrToolbarEntries = array();
        $arrToolbarEntries[0] = "<a href=\"".Link::getLinkAdminHref("pages", "editPage", "&systemid=".$objPage->getSystemid())."\">".AdminskinHelper::getAdminImage("icon_edit").$this->getLang("contentToolbar_pageproperties")."</a>";
        $arrToolbarEntries[1] = "<a href=\"".Link::getLinkAdminHref("pages_content", "list", "&systemid=".$objPage->getSystemid())."\">".AdminskinHelper::getAdminImage("icon_page").$this->getLang("contentToolbar_content")."</a>";
        $arrToolbarEntries[2] = "<a href=\"".Link::getLinkPortalHref($objPage->getStrName(), "", "", "&preview=1", "", $this->getLanguageToWorkOn())."\" target=\"_blank\">".AdminskinHelper::getAdminImage("icon_lens").$this->getLang("contentToolbar_preview")."</a>";

        if ($objPage->getIntType() != PagesPage::$INT_TYPE_ALIAS) {
            $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 1);
        }

        $arrInfoRows = array(
            array($this->getLang("template"), $objPage->getStrTemplate()),
            array($this->getLang("lastuserTitle"), $objPage->getLastEditUser()),
            array($this->getLang("lasteditTitle"), timeToString($objPage->getIntLmTime()))
        );
        $strReturn .= $this->objToolkit->dataTable(array(), $arrInfoRows);
        $strReturn .= $this->objToolkit->divider();
        return $strReturn;
    }


    /**
     * Returns a list of available placeholders & elements on this page
     *
     * @return string
     * @permissions edit
     */
    protected function actionList()
    {
        $strReturn = "";
        LanguagesAdmin::enableLanguageSwitch();
        $objCurObject = Objectfactory::getInstance()->getObject($this->getSystemid());

        /** @var PagesPage $objPage */
        $objPage = $objCurObject;
        while (!$objPage instanceof PagesPage && validateSystemid($objPage->getSystemid())) {
            $objPage = Objectfactory::getInstance()->getObject($objPage->getStrPrevId());
        }

        $strReturn .= $this->getPageInfoBox($objPage);


        //parse the whole template
        try {
            $objParsedBlocks = $this->objTemplate->parsePageTemplate("/module_pages/".$objPage->getStrTemplate(), $objPage->getStrName() == "master" ? Template::INT_ELEMENT_MODE_MASTER : Template::INT_ELEMENT_MODE_REGULAR);
        }
        catch(TemplateBlocksParserException $objEx) {
            $objParsedBlocks = new TemplateBlockContainer(TemplateKajonaSections::ROOT, "", "", "", "");

            $strPath = $strName = Resourceloader::getInstance()->getTemplate("/module_pages/".$objPage->getStrTemplate(), false);
            $strReturn .= $this->objToolkit->warningBox($this->getLang("exception_template_parse", array($strPath, nl2br(htmlentities($objEx->getStrSectionWithError())))), "alert-danger");
        }


        $arrTabs = array();
        //start the top level elements
        //Language-dependant loading of elements, if installed
        $arrAllElementsOnPage = PagesPageelement::getElementsOnPage($this->getSystemid(), false, $this->getLanguageToWorkOn());
        $arrPageelementsOnPage = array_filter($arrAllElementsOnPage, function (PagesPageelement $objSingleElement) {
            return $objSingleElement->getStrElement() != "blocks";
        });

        $arrBlocksOnPage = array_filter($arrAllElementsOnPage, function (PagesPageelement $objSingleElement) {
            return $objSingleElement->getStrElement() == "blocks";
        });

        //trigger blocks rendering
        $arrGhostBlocks = array();
        $strBlocks = "";
        //loop the blocks found on the template
        foreach ($objParsedBlocks->getArrBlocks() as $objOneBlocks) {

            $arrGhostBlock = array();

            $strCurBlocks = "";
            $strNewBlocks = "";

            $strNewBlocksSystemid = $objOneBlocks->getStrName();

            //process existing blocks, try to find the matching blocks element on the page
            /** @var PagesPageelement $objOneBlocksOnPage */
            foreach ($arrBlocksOnPage as $objOneBlocksOnPage) {

                if(!array_key_exists($objOneBlocksOnPage->getSystemid(), $arrGhostBlocks)) {
                    $arrGhostBlocks[$objOneBlocksOnPage->getSystemid()] = $objOneBlocksOnPage;
                }

                //success: found a blocks element named like the blocks element from the database
                if ($objOneBlocksOnPage->getStrName() == $objOneBlocks->getStrName()) {

                    $arrGhostBlocks[$objOneBlocksOnPage->getSystemid()] = false;

                    $strNewBlocksSystemid = $objOneBlocksOnPage->getSystemid();

                    //load all block elements from the database and render them
                    $arrSubBlock = PagesPageelement::getElementsOnPage($objOneBlocksOnPage->getSystemid(), false, $this->getLanguageToWorkOn());

                    foreach ($arrSubBlock as $objOneBlockOnPage) {

                        if(!array_key_exists($objOneBlockOnPage->getSystemid(), $arrGhostBlock)) {
                            $arrGhostBlock[$objOneBlockOnPage->getSystemid()] = $objOneBlockOnPage;
                        }

                        $strExistingBlock = "";

                        //loop the block from the template to find the matching element definitions
                        foreach ($objOneBlocks->getArrBlocks() as $objOneBlock) {
                            if ($objOneBlockOnPage->getStrName() == $objOneBlock->getStrName()) {
                                $arrElementsInBlock = PagesPageelement::getElementsOnPage($objOneBlockOnPage->getSystemid(), false, $this->getLanguageToWorkOn());
                                $strExistingBlock .= $this->renderElementPlaceholderList($objOneBlock->getArrPlaceholder(), $arrElementsInBlock, true, $objOneBlocksOnPage->getSystemid(), $objOneBlockOnPage->getSystemid());

                                $arrGhostBlock[$objOneBlockOnPage->getSystemid()] = false;
                            }
                        }

                        if ($strExistingBlock != "") {

                            $strActions = $this->getActionIcons($objOneBlockOnPage);

                            $strCurBlocks .= $this->objToolkit->getFieldset(
                                $objOneBlockOnPage->getStrName()." ".$objOneBlockOnPage->getIntSort()."<span class='pull-right'>".$strActions."</span>",
                                $strExistingBlock,
                                "fieldset block",
                                $objOneBlockOnPage->getSystemid()
                            );
                        }
                    }

                }
            }

            foreach ($objOneBlocks->getArrBlocks() as $objOneBlock) {
                $strNewBlocks .= $this->renderNewBlockLinkRow($strNewBlocksSystemid, $objOneBlock);
            }

            $arrGhostBlock = array_filter($arrGhostBlock, function ($objElement) {
                return $objElement !== false;
            });
            if(count($arrGhostBlock) > 0) {
                //render ghosts
                $strCurBlocks .= $this->renderGhostElementList($arrGhostBlock);
            }

            $strBlocks .= $this->objToolkit->getFieldset($objOneBlocks->getStrName(), $strCurBlocks.$strNewBlocks, "fieldset blocks");

        }
        
        $arrGhostBlocks = array_filter($arrGhostBlocks, function ($objElement) {
            return $objElement !== false;
        });
        if(count($arrGhostBlocks) > 0) {
            //render ghosts
            $strBlocks .= $this->renderGhostElementList($arrGhostBlocks);
        }


        $strElements = $this->renderElementPlaceholderList($objParsedBlocks->getArrPlaceholder(), $arrPageelementsOnPage);

        if($strBlocks != "" && $strElements != "") {
            $arrTabs[$this->getLang("pages_content_tab_blocks")] = $strBlocks;
            $arrTabs[$this->getLang("pages_content_tab_elements")] = $strElements;
            $strReturn .= $this->objToolkit->getTabbedContent($arrTabs);
        }
        else {
            $strReturn .= $strBlocks.$strElements;
        }

        $strCore = Resourceloader::getInstance()->getCorePathForModule("module_pages");

        $strReturn .= <<<HTML
            <script type="text/javascript">
                KAJONA.admin.loader.loadFile('{$strCore}/module_pages/scripts/pages.js', function() {
                    KAJONA.admin.pages.initBlockSort();
                });

            </script>
HTML;

        return $strReturn;

    }

    private function renderNewBlockLinkRow($strBlocks, TemplateBlockContainer $objBlock)
    {
        //validate if all linked elements are present on the current system
        $arrNotPresent = PagesElement::getElementsNotInstalledFromBlock($objBlock);

        $strNewElementLink = "";
        $strBlockName = $objBlock->getStrName();

        if(count($arrNotPresent) == 0) {
            $strNewElementLink = Link::getLinkAdmin(
                "pages_content",
                "newBlock",
                "&blocks={$strBlocks}&block={$objBlock->getStrName()}&systemid={$this->getSystemid()}",
                "",
                $this->getLang("element_anlegen"),
                "icon_new"
            );
        }
        else {
            $strBlockName .= " <i class='fa fa-exclamation-triangle'></i> ".$this->getLang("element_in_block_missing").implode(", ", $arrNotPresent)."";
        }



        //So, the Row for a new element: element is repeatable or not yet created
        $strActions = $this->objToolkit->listButton($strNewElementLink);
        $strReturn = $this->objToolkit->listHeader();
        $strReturn .= $this->objToolkit->genericAdminList("", $strBlockName, "", $strActions);
        $strReturn .= $this->objToolkit->listFooter();
        return $strReturn;
    }

    /**
     * @param $arrElementsOnTemplate
     * @param PagesPageelement[] $arrElementsOnPage
     *
     * @return string
     */
    private function renderElementPlaceholderList($arrElementsOnTemplate, $arrElementsOnPage, $bitRenderCompact = false, $strBlocks = "", $strBlock = "")
    {

        $strReturn = "";
        //save a copy of the array to be able to check against all values later on
        $arrElementsOnPageCopy = $arrElementsOnPage;


        if (is_array($arrElementsOnTemplate) && count($arrElementsOnTemplate) > 0) {
            //Iterate over every single placeholder provided by the template
            foreach ($arrElementsOnTemplate as $arrOneElementOnTemplate) {

                $strOutputAtPlaceholder = "";
                //Do we have one or more elements already in db at this placeholder?
                $bitHit = false;

                //Iterate over every single element-type provided by the placeholder
                foreach ($arrElementsOnPage as $intArrElementsOnPageKey => $objOneElementOnPage) {
                    //Check, if its the same placeholder
                    $bitSamePlaceholder = false;
                    if ($arrOneElementOnTemplate["placeholder"] == $objOneElementOnPage->getStrPlaceholder()) {
                        $bitSamePlaceholder = true;
                    }

                    if ($bitSamePlaceholder) {
                        $bitHit = true;
                        //try to unlock the record
                        $objOneElementOnPage->getLockManager()->unlockRecord();
                        $strActions = $this->getActionIcons($objOneElementOnPage);
                        //Put all Output together
                        $strOutputAtPlaceholder .= $this->objToolkit->simpleAdminList($objOneElementOnPage, $strActions);

                        //remove the element from the array
                        unset($arrElementsOnPage[$intArrElementsOnPageKey]);
                    }

                }

                //Check, if one of the elements in the placeholder is allowed to be used multiple times
                foreach ($arrOneElementOnTemplate["elementlist"] as $arrSingleElementOnTemplateplaceholder) {

                    //Loading all Elements installed on the system ("RAW"-Elements)
                    /** @var PagesElement $objOnePossibleElementInSystem */
                    foreach (PagesElement::getObjectListFiltered() as $objOnePossibleElementInSystem) {
                        if ($objOnePossibleElementInSystem->getStrName() == $arrSingleElementOnTemplateplaceholder["element"]) {

                            $strNewElementLink = Link::getLinkAdmin(
                                "pages_content",
                                "new",
                                "&placeholder={$arrOneElementOnTemplate["placeholder"]}&blocks={$strBlocks}&block={$strBlock}&element={$arrSingleElementOnTemplateplaceholder["element"]}&systemid={$this->getSystemid()}",
                                "",
                                $this->getLang("element_anlegen"),
                                "icon_new"
                            );

                            if (($objOnePossibleElementInSystem->getIntRepeat() == 1 || $bitHit === false) && ($strBlock == "" && $strBlocks == "")) {
                                //So, the Row for a new element: element is repeatable or not yet created
                                $strActions = $this->objToolkit->listButton($strNewElementLink);
                                $strOutputAtPlaceholder .= $this->objToolkit->genericAdminList("", $objOnePossibleElementInSystem->getStrDisplayName(), "", $strActions, ($bitRenderCompact ? $arrOneElementOnTemplate["placeholder"] : ""));
                            }
                            else {
                                //element not repeatable.
                                //Is there already one element installed? if not, then it IS allowed to create a new one
                                $bitOneInstalled = false;
                                foreach ($arrElementsOnPageCopy as $objOneElementToCheck) {
                                    if ($arrOneElementOnTemplate["placeholder"] == $objOneElementToCheck->getStrPlaceholder() && $arrSingleElementOnTemplateplaceholder["element"] == $objOneElementToCheck->getStrElement()) {
                                        $bitOneInstalled = true;
                                    }
                                }
                                if (!$bitOneInstalled) {
                                    //So, the Row for a new element
                                    $strActions = $this->objToolkit->listButton($strNewElementLink);
                                    $strOutputAtPlaceholder .= $this->objToolkit->genericAdminList("", $objOnePossibleElementInSystem->getStrDisplayName(), "", $strActions, ($bitRenderCompact ? $arrOneElementOnTemplate["placeholder"] : ""));
                                }
                            }
                        }
                    }
                }

                if ((int)uniStrlen($strOutputAtPlaceholder) > 0) {
                    $arrSinglePlaceholder = explode("_", $arrOneElementOnTemplate["placeholder"]);
                    if (count($arrSinglePlaceholder) == 2 && !$bitRenderCompact) {
                        $strOutputAtPlaceholder .= $this->objToolkit->formHeadline($arrSinglePlaceholder[0]);
                    }


                    if ($bitRenderCompact) {
                        $strReturn .= $this->objToolkit->listHeader();
                        $strReturn .= $strOutputAtPlaceholder;
                        $strReturn .= $this->objToolkit->listFooter();
                    }
                    else {
                        $strListId = generateSystemid();
                        $strReturn .= $this->objToolkit->dragableListHeader($strListId, true);
                        $strReturn .= $strOutputAtPlaceholder;
                        $strReturn .= $this->objToolkit->dragableListFooter($strListId);
                    }

                }

            }

        }
        else {
            $strReturn .= $this->getLang("element_liste_leer");
        }


        //if there are any page-elements remaining, print a warning and print the elements row
        if (count($arrElementsOnPage) > 0) {
            $strReturn .= $this->renderGhostElementList($arrElementsOnPage);
        }

        if($strReturn != "") {
            $strReturn .= $this->objToolkit->getTableOfContents("h2");
        }

        return $strReturn;
    }

    /**
     * @param PagesPageelement[] $arrEntries
     */
    private function renderGhostElementList(array $arrEntries)
    {
        $strReturn = $this->objToolkit->divider();
        $strReturn .= $this->objToolkit->warningBox($this->getLang("warning_elementsremaining"));
        $strReturn .= $this->objToolkit->listHeader();

        //minimized actions now, plz. this ain't being a real element anymore!
        foreach ($arrEntries as $objOneElement) {
            $strActions = "";
            $strActions .= $this->objToolkit->listDeleteButton($objOneElement->getStrDisplayName(), $this->getLang("element_loeschen_frage"), Link::getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneElement->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));

            //Put all Output together
            $strPlaceholder = $objOneElement->getStrPlaceholder();

//            if($strPlaceholder == "block") {
//                $strPlaceholder = $objOneElement->getStrName();
//            }

            $strReturn .= $this->objToolkit->genericAdminList("", $objOneElement->getStrDisplayName()." ".$this->getLang("placeholder").$strPlaceholder, "", $strActions);
        }
        $strReturn .= $this->objToolkit->listFooter();
        $strReturn .= $this->objToolkit->divider();
        return $strReturn;
    }


    /**
     * @param \Kajona\System\System\Model|AdminListableInterface|\Kajona\System\System\ModelInterface|PagesPageelement $objOneIterable
     * @param string $strListIdentifier
     *
     * @return string
     */
    public function getActionIcons($objOneIterable, $strListIdentifier = "")
    {
        $strActions = "";

        if ($objOneIterable instanceof PagesPageelement) {
            $objLockmanager = $objOneIterable->getLockManager();

            $bitParentIsBlock = false;
            $objParent = Objectfactory::getInstance()->getObject($objOneIterable->getStrPrevId());
            if ($objParent instanceof PagesPageelement) {
                $bitParentIsBlock = $objParent->getConcreteAdminInstance() instanceof ElementBlockAdmin;
            }

            //Create a row to handle the element, check all necessary stuff such as locking etc
            $strActions = "";
            //First step - Record locked? Offer button to unlock? But just as admin! For the user, who locked the record, the unlock-button
            //won't be visible
            if (!$objLockmanager->isAccessibleForCurrentUser()) {
                //So, return a button, if we have an admin in front of us
                if ($objLockmanager->isUnlockableForCurrentUser()) {
                    $strActions .= $this->objToolkit->listButton(Link::getLinkAdmin("pages_content", "list", "&systemid=".$this->getSystemid()."&unlockid=".$objOneIterable->getSystemid(), "", $this->getLang("ds_entsperren"), "icon_lockerOpen"));
                }
                //If the Element is locked, then its not allowed to edit or delete the record, so disable the icons
                if ($objOneIterable->rightEdit() && !$objOneIterable->getConcreteAdminInstance() instanceof ElementBlockAdmin) {
                    $strActions .= $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_editLocked", $this->getLang("ds_gesperrt")));
                }
                if ($objOneIterable->rightDelete() && !$bitParentIsBlock) {
                    $strActions .= $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_deleteLocked", $this->getLang("ds_gesperrt")));
                }
            }
            else {

                if ($objOneIterable->rightEdit() && !$objOneIterable->getConcreteAdminInstance() instanceof ElementBlockAdmin) {
                    $strActions .= $this->objToolkit->listButton(Link::getLinkAdmin("pages_content", "edit", "&systemid=".$objOneIterable->getSystemid(), "", $this->getLang("element_bearbeiten"), "icon_edit"));
                }
                if ($objOneIterable->rightDelete() && !$bitParentIsBlock) {
                    $strActions .= $this->objToolkit->listDeleteButton($objOneIterable->getStrName().($objOneIterable->getConcreteAdminInstance()->getContentTitle() != "" ? " - ".$objOneIterable->getConcreteAdminInstance()->getContentTitle() : "").($objOneIterable->getStrTitle() != "" ? " - ".$objOneIterable->getStrTitle() : ""), $this->getLang("element_loeschen_frage"), Link::getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneIterable->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
                }
            }

            if (!$bitParentIsBlock) {
                //The Icons to sort the list and to copy the element
                $strActions .= $this->objToolkit->listButton(Link::getLinkAdminDialog("pages_content", "copyElement", "&systemid=".$objOneIterable->getSystemid(), "", $this->getLang("element_copy"), "icon_copy"));
                //The status-icons
                $strActions .= $this->objToolkit->listStatusButton($objOneIterable->getSystemid());
            }

        }
        elseif ($objOneIterable instanceof PagesElement) {
            $objAdminInstance = SystemModule::getModuleByName("pages")->getAdminInstanceOfConcreteModule();
            if ($objAdminInstance != null && $objAdminInstance instanceof AdminSimple) {
                return $objAdminInstance->getActionIcons($objOneIterable);
            }
        }


        return $strActions;
    }

    protected function actionNewBlock()
    {
        $strBlocks = $this->getParam("blocks");
        $strBlock = $this->getParam("block");
        $strPageId = $this->getSystemid();
        $strLanguage = $this->getLanguageToWorkOn();

        if ($strBlocks != "" && $strBlock != "") {


            if (validateSystemid($strBlocks) && validateSystemid($strBlock)) {
                //fetch the matching elements
                $objBlocks = new PagesPageelement($strBlocks);
                $objBlock = new PagesPageelement($strBlock);

                if ($objBlocks->getStrElement() == "blocks" && $objBlock->getStrElement() == "block") {
                    return $objBlock->getSystemid();
                }
            }

            $objBlocksElement = null;
            if (validateSystemid($strBlocks) && !validateSystemid($strBlock)) {
                $objBlocksElement = new PagesPageelement($strBlocks);
            }


            if ($objBlocksElement == null) {
                $objBlocksElement = new PagesPageelement();
                $objBlocksElement->setStrName($strBlocks);
                $objBlocksElement->setStrPlaceholder("blocks");
                $objBlocksElement->setStrElement("blocks");
                $objBlocksElement->setStrLanguage($strLanguage);
                if (!$objBlocksElement->updateObjectToDb($strPageId)) {
                    throw new Exception("Error saving new element-object to db", Exception::$level_ERROR);
                }
            }

            $objBlockElement = new PagesPageelement();
            $objBlockElement->setStrName($strBlock);
            $objBlockElement->setStrPlaceholder("block");
            $objBlockElement->setStrElement("block");
            $objBlockElement->setStrLanguage($strLanguage);
            if (!$objBlockElement->updateObjectToDb($objBlocksElement->getSystemid())) {
                throw new Exception("Error saving new element-object to db", Exception::$level_ERROR);
            }

            $strNewPrevId = $objBlockElement->getSystemid();


            //create dummy elements, therefore parse the template
            /** @var PagesPage $objPage */
            $objPage = Objectfactory::getInstance()->getObject($this->getSystemid());
            /** @var TemplateBlockContainer $objTemplate */
            $objTemplate = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_TEMPLATE)->parsePageTemplate("/module_pages/".$objPage->getStrTemplate());
            foreach ($objTemplate->getArrBlocks() as $objOneBlocks) {
                foreach ($objOneBlocks->getArrBlocks() as $objOneBlock) {
                    if ($objOneBlocks->getStrName() == $objBlocksElement->getStrName() && $objOneBlock->getStrName() == $objBlockElement->getStrName()) {

                        foreach ($objOneBlock->getArrPlaceholder() as $arrOnePlaceholder) {
                            foreach ($arrOnePlaceholder["elementlist"] as $arrElementList) {
                                //Create dummy elements
                                $strPlaceholder = $arrOnePlaceholder["placeholder"];

                                //validate if the passed element really exists
                                if(PagesElement::getElement($arrElementList["element"]) == null) {
                                    throw new Exception("Element of type ".$arrElementList["element"]." is not installed", Exception::$level_ERROR);
                                }


                                $objPageElement = new PagesPageelement();
                                $objPageElement->setStrName($arrElementList["name"]);
                                $objPageElement->setStrPlaceholder($strPlaceholder);
                                $objPageElement->setStrElement($arrElementList["element"]);
                                $objPageElement->setStrLanguage($strLanguage);
                                if (!$objPageElement->updateObjectToDb($strNewPrevId)) {
                                    throw new Exception("Error saving new element-object to db", Exception::$level_ERROR);
                                }

                                $objPageElement = new PagesPageelement($objPageElement->getSystemid());
                                /** @var $objElement ElementAdmin */
                                $objElement = $objPageElement->getConcreteAdminInstance();
                                $objElement->generateDummyContent();

                                $objElement->doBeforeSaveToDb();

                                //check, if we could save the data, so the element needn't to
                                //woah, we are soooo great
                                $objElement->updateForeignElement();
                            }
                        }
                    }
                }
            }


            if ($this->getParam("peClose") == "1") {

                //generate the elements' output
                $objBlockElement = new PagesPageelement($objBlockElement->getSystemid());
                $objPortalElement = $objBlockElement->getConcretePortalInstance();
                $strElementContent = $objPortalElement->getRenderedElementOutput(PagesPortaleditor::isActive());

                $strContent = json_encode($strElementContent, JSON_FORCE_OBJECT); //JSON_HEX_QUOT|JSON_HEX_APOS
                $strActions = PagesPortaleditor::getInstance()->convertToJs();

                $strReturn = <<<JS
                    parent.KAJONA.admin.portaleditor.changeElementData('blocks_{$objBlocksElement->getStrName()}', '{$objBlockElement->getSystemid()}', {$strContent});
                    parent.KAJONA.admin.portaleditor.RTE.init();
                    parent.KAJONA.admin.portaleditor.elementActionToolbar.injectPlaceholderActions({$strActions});
                    parent.KAJONA.admin.portaleditor.closeDialog(true);

JS;
                Carrier::getInstance()->setParam("peClose", null);
                return "<script type='text/javascript'>{$strReturn}</script>";

            }
            else {
                $this->adminReload(Link::getLinkAdminHref("pages_content", "list", "&systemid=".$strPageId));
            }

        }

        return "";
    }


    /**
     * Loads the form to create a new element
     *
     * @param bool $bitShowErrors
     *
     * @permissions edit
     *
     * @return string
     */
    protected function actionNew($bitShowErrors = false)
    {
        $strReturn = "";
        //OK, here we go. So, what information do we have?
        $strPlaceholderElement = $this->getParam("element");
        //Now, load all infos about the requested element
        $objElement = PagesElement::getElement($strPlaceholderElement);
        //Build the class-name
        //and finally create the object
        $strFilename = Resourceloader::getInstance()->getPathForFile("/admin/elements/".$objElement->getStrClassAdmin());
        $objElement = Classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");
        if ($bitShowErrors) {
            $objElement->setDoValidation(true);
        }

        $strReturn = $objElement->actionEdit("new");

        return $strReturn;
    }

    /**
     * Loads the form to edit the element
     *
     * @param bool $bitShowErrors
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit($bitShowErrors = false)
    {
        $strReturn = "";
        //check rights
        /** @var $objElement PagesElement */
        $objElement = Objectfactory::getInstance()->getObject($this->getSystemid());

        if ($objElement instanceof PagesElement) {
            $this->adminReload(Link::getLinkAdminHref("pages", "edit", "&systemid=".$objElement->getSystemid()));
            return "";
        }


        //Load the element data
        //check, if the element isn't locked
        if ($objElement->getLockManager()->isAccessibleForCurrentUser()) {
            $objElement->getLockManager()->lockRecord();

            //Load the class to create an object


            //and finally create the object
            $strFilename = Resourceloader::getInstance()->getPathForFile("/admin/elements/".$objElement->getStrClassAdmin());
            /** @var $objPageElement ElementAdmin */
            $objPageElement = Classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");
            if ($bitShowErrors) {
                $objPageElement->setDoValidation(true);
            }
            $strReturn .= $objPageElement->actionEdit("edit");

        }
        else {
            $strReturn .= $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
        }

        return $strReturn;
    }


    /**
     * Internal helper to resolve or create the matching blocks and block element for the current page-element
     *
     * @param $strPageId
     * @param $strBlocks
     * @param $strBlock
     *
     * @return string
     * @throws Exception
     */
    private function processBlocksDefinition($strPageId, $strBlocks, $strBlock)
    {
        $strNewPrevId = $strPageId;

        if ($strBlocks != "" && $strBlock != "") {


            if (validateSystemid($strBlocks) && validateSystemid($strBlock)) {
                //fetch the matching elements
                $objBlocks = new PagesPageelement($strBlocks);
                $objBlock = new PagesPageelement($strBlock);

                if ($objBlocks->getStrElement() == "blocks" && $objBlock->getStrElement() == "block") {
                    return $objBlock->getSystemid();
                }
            }

            $objBlocksElement = null;
            if (validateSystemid($strBlocks) && !validateSystemid($strBlock)) {
                $objBlocksElement = new PagesPageelement($strBlocks);
            }


            if ($objBlocksElement == null) {
                $objBlocksElement = new PagesPageelement();
                $objBlocksElement->setStrName($strBlocks);
                $objBlocksElement->setStrPlaceholder("blocks");
                $objBlocksElement->setStrElement("blocks");
                $objBlocksElement->setStrLanguage($this->getParam("page_element_ph_language"));
                if (!$objBlocksElement->updateObjectToDb($strNewPrevId)) {
                    throw new Exception("Error saving new element-object to db", Exception::$level_ERROR);
                }
            }

            $objBlockElement = new PagesPageelement();
            $objBlockElement->setStrName($strBlock);
            $objBlockElement->setStrPlaceholder("block");
            $objBlockElement->setStrElement("block");
            $objBlockElement->setStrLanguage($this->getParam("page_element_ph_language"));
            if (!$objBlockElement->updateObjectToDb($objBlocksElement->getSystemid())) {
                throw new Exception("Error saving new element-object to db", Exception::$level_ERROR);
            }

            $strNewPrevId = $objBlockElement->getSystemid();

        }


        return $strNewPrevId;
    }

    /**
     * Saves the passed Element to the database (edit or new modes)
     *
     * @throws Exception
     * @return string "" in case of success
     */
    protected function actionSaveElement()
    {
        $strReturn = "";
        //There are two modes - edit and new
        //The element itself just knows the edit mode, so in case of new, we have to create a dummy element - before
        //passing control to the element
        if ($this->getParam("mode") == "new") {

            //Using the passed placeholder-param to load the element and get the table
            $strPlaceholder = $this->getParam("placeholder");
            //Split up the placeholder
            $arrPlaceholder = explode("_", $strPlaceholder);
            $strPlaceholderName = $arrPlaceholder[0];
            $strPlaceholderElement = $this->getParam("element");
            //Now, load all infos about the requested element
            $objElement = PagesElement::getElement($strPlaceholderElement);
            //Load the class to create an object


            //and finally create the object
            $strFilename = Resourceloader::getInstance()->getPathForFile("/admin/elements/".$objElement->getStrClassAdmin());
            $objElement = Classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");

            //really continue? try to validate the passed data.
            if ($objElement->getAdminForm() !== null && !$objElement->getAdminForm()->validateForm()) {
                Carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionNew(true);
                return $strReturn;
            }
            elseif (!$objElement->validateForm()) {
                Carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionNew(true);
                return $strReturn;
            }


            $strCurrentParentId = $this->getSystemid();
            //update / create blocks and block element
            //TODO: the following row may be removed
            $strCurrentParentId = $this->processBlocksDefinition($strCurrentParentId, $this->getParam("blocks"), $this->getParam("block"));


            //So, lets do the magic - create the records
            $objPageElement = new PagesPageelement();
            $objPageElement->setStrName($strPlaceholderName);
            $objPageElement->setStrPlaceholder($strPlaceholder);
            $objPageElement->setStrElement($strPlaceholderElement);
            $objPageElement->setStrLanguage($this->getParam("page_element_ph_language"));
            if (!$objPageElement->updateObjectToDb($strCurrentParentId)) {
                throw new Exception("Error saving new element-object to db", Exception::$level_ERROR);
            }
            $strElementSystemId = $objPageElement->getSystemid();

            $objLockmanager = new Lockmanager($strElementSystemId);
            $objLockmanager->lockRecord();

            //To have the element working as expected, set the systemid
            $this->setSystemid($strElementSystemId);
        }


        // ************************************* Edit the current Element *******************************

        //check, if the element isn't locked
        $objTemp = Objectfactory::getInstance()->getObject($this->getSystemid());
        $strPageSystemid = $this->getSystemid();

        while (!$objTemp instanceof PagesPage && validateSystemid($objTemp->getStrPrevId())) {
            $objTemp = Objectfactory::getInstance()->getObject($objTemp->getStrPrevId());
            $strPageSystemid = $objTemp->getSystemid();
        }

        $objLockmanager = new Lockmanager($this->getSystemid());

        if ($objLockmanager->isLockedByCurrentUser()) {
            //Load the data of the current element
            $objElementData = new PagesPageelement($this->getSystemid());
            /** @var $objElement ElementAdmin */
            $objElement = $objElementData->getConcreteAdminInstance();

            //really continue? try to validate the passed data.
            if ($objElement->getAdminForm() !== null && !$objElement->getAdminForm()->validateForm()) {
                Carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionEdit(true);
                return $strReturn;
            }
            elseif (!$objElement->validateForm()) {
                Carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionEdit(true);
                return $strReturn;
            }

            //pass the data to the element, maybe the element wants to update some data
            $objElement->setArrParamData($this->getAllParams());

            if ($objElement->getAdminForm() !== null) {
                $objElement->getAdminForm()->updateSourceObject();
            }

            $objElement->doBeforeSaveToDb();

            //check, if we could save the data, so the element needn't to
            //woah, we are soooo great
            $objElement->updateForeignElement();

            //Edit Date of page & unlock
            $objPage = Objectfactory::getInstance()->getObject($strPageSystemid);
            $objPage->updateObjectToDb();
            $objLockmanager->unlockRecord();

            //And update the internal comment and language
            $objElementData->setStrTitle($this->getParam("page_element_ph_title"));
            $objElementData->setStrLanguage($this->getParam("page_element_ph_language"));
            //placeholder to update?
            if ($this->getParam("placeholder") != "") {
                $objElementData->setStrPlaceholder($this->getParam("placeholder"));
            }

            $objStartDate = new \Kajona\System\System\Date("0");
            $objEndDate = new \Kajona\System\System\Date("0");
            $objStartDate->generateDateFromParams("start", $this->getAllParams());
            $objEndDate->generateDateFromParams("end", $this->getAllParams());


            if ($objStartDate->getIntYear() == "0000") {
                $objElementData->setObjStartDate(null);
            }
            else {
                $objElementData->setObjStartDate($objStartDate);
            }

            if ($objEndDate->getIntYear() == "0000") {
                $objElementData->setObjEndDate(null);
            }
            else {
                $objElementData->setObjEndDate($objEndDate);
            }


            if (!$objElementData->updateObjectToDb()) {
                throw new Exception("Error updating object to db", Exception::$level_ERROR);
            }


            //allow the element to run actions after saving
            $objElement->doAfterSaveToDb();


            //Loading the data of the corresponding site
            $this->flushCompletePagesCache();

            if ($this->getParam("peClose") == "1") {

                //generate the elements' output
                $objPortalElement = $objElementData->getConcretePortalInstance();
                $strElementContent = $objPortalElement->getRenderedElementOutput(true);

                $strContent = json_encode($strElementContent, JSON_FORCE_OBJECT); //JSON_HEX_QUOT|JSON_HEX_APOS
                $strActions = PagesPortaleditor::getInstance()->convertToJs();

                $strReturn = <<<JS
                    parent.KAJONA.admin.portaleditor.changeElementData('{$objElementData->getStrPlaceholder()}', '{$objElementData->getSystemid()}', {$strContent});
                    parent.KAJONA.admin.portaleditor.RTE.init();
                    parent.KAJONA.admin.portaleditor.elementActionToolbar.injectPlaceholderActions({$strActions});
                    parent.KAJONA.admin.portaleditor.closeDialog(true);

JS;
                Carrier::getInstance()->setParam("peClose", null);
                return "<script type='text/javascript'>{$strReturn}</script>";

            }

            $this->adminReload(Link::getLinkAdminHref("pages_content", "list", "&systemid=".$strPageSystemid));

        }
        else {
            $strReturn = $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
        }
        return $strReturn;
    }

    /**
     * Deletes an Element
     *
     * @throws Exception
     * @return string , "" in case of success
     * @permissions delete
     */
    protected function actionDeleteElementFinal()
    {
        $strReturn = "";

        $objPageElement = new PagesPageelement($this->getSystemid());
        if ($objPageElement->rightDelete()) {
            //Locked?
            $objLockmanager = new Lockmanager($this->getSystemid());

            $objPage = Objectfactory::getInstance()->getObject($objPageElement->getStrPrevId());
            while (!$objPage instanceof PagesPage && validateSystemid($objPage->getStrPrevId())) {
                $objPage = Objectfactory::getInstance()->getObject($objPage->getStrPrevId());
            }

            if ($objLockmanager->isAccessibleForCurrentUser()) {
                //delete object
                if (!$objPageElement->deleteObject()) {
                    throw new Exception("Error deleting element from db", Exception::$level_ERROR);
                }

                if ($this->getParam("pe") == "1") {
                    $strReturn = <<<JS
                    parent.KAJONA.admin.portaleditor.deleteElementData('{$objPageElement->getSystemid()}');
                    parent.KAJONA.admin.portaleditor.closeDialog(true);
JS;
                    Carrier::getInstance()->setParam("peClose", null);
                    return "<script type='text/javascript'>{$strReturn}</script>";
                }

                $this->adminReload(Link::getLinkAdminHref("pages_content", "list", "systemid=".$objPage->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
            }
            else {
                $strReturn .= $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
            }
        }
        else {
            $strReturn = $this->getLang("commons_error_permissions");
        }

        return $strReturn;
    }


    /**
     * Deletes an Element
     *
     * @todo still required? the PE doesn't call this anymore! same to the matchin delete methods in all modules making use of the pe.
     * @throws Exception
     * @return string , "" in case of success
     * @permissions delete
     * @xml
     */
    protected function actionDeleteElementFinalXML()
    {

        $objPageElement = new PagesPageelement($this->getSystemid());
        if ($objPageElement->rightDelete()) {
            //Locked?
            $objLockmanager = new Lockmanager($this->getSystemid());

            if ($objLockmanager->isAccessibleForCurrentUser()) {
                //delete object
                if (!$objPageElement->deleteObject()) {
                    throw new Exception("Error deleting element from db", Exception::$level_ERROR);
                }


                return "<message><success></success></message>";
            }
        }
        ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
        return "<message><error>".$this->getLang('commons_error_permissions')."</error></message>";
    }


    /**
     * Provides a form to set up the params needed to copy a single element from one placeholder to another.
     * Collects the target language, the target page and the target placeholder, invokes the copy-procedure.
     *
     * @throws Exception
     * @return string , "" in case of success
     * @permissions edit
     */
    protected function actionCopyElement()
    {
        $strReturn = "";

        $this->setArrModuleEntry("template", "/folderview.tpl");

        $objSourceElement = new PagesPageelement($this->getSystemid());
        if ($objSourceElement->rightEdit($this->getSystemid())) {

            $objLang = null;
            if ($this->getParam("copyElement_language") != "") {
                $objLang = new LanguagesLanguage($this->getParam("copyElement_language"));
            }
            else {
                $objLang = LanguagesLanguage::getLanguageByName($this->getLanguageToWorkOn());
            }

            $objPage = null;
            if ($this->getParam("copyElement_page") != "") {
                $objPage = PagesPage::getPageByName($this->getParam("copyElement_page"));
                if ($objPage == null) {
                    throw new Exception("failed to load page ".$this->getParam("copyElement_page"), Exception::$level_ERROR);
                }
                $objPage->setStrLanguage($objLang->getStrName());
                $objPage->initObject();
            }
            else {
                $objPage = new PagesPage($objSourceElement->getPrevId());
            }

            //form header
            $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref("pages_content", "copyElement"), "formCopyElement");
            $strReturn .= $this->objToolkit->formInputHidden("copyElement_doCopy", 1);
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());

            $strReturn .= $this->objToolkit->formHeadline($this->getLang("copyElement_element")." ".$objSourceElement->getStrName()."_".$objSourceElement->getStrElement()." (".$objSourceElement->getStrTitle().")");


            //step one: language selection
            $arrLanguages = LanguagesLanguage::getObjectListFiltered(null, true);
            $arrLanguageDD = array();
            foreach ($arrLanguages as $objSingleLanguage) {
                $arrLanguageDD[$objSingleLanguage->getSystemid()] = $this->getLang("lang_".$objSingleLanguage->getStrName(), "languages");
            }

            $strReturn .= $this->objToolkit->formInputDropdown("copyElement_language", $arrLanguageDD, $this->getLang("copyElement_language"), $objLang->getSystemid());


            //step two: page selection
            $strReturn .= $this->objToolkit->formInputPageSelector("copyElement_page", $this->getLang("copyElement_page"), $objPage->getStrName(), "inputText", false);


            //step three: placeholder-selection
            //here comes the tricky part. load the template, analyze the placeholders and validate all those against things like repeatable and more...
            $strTemplate = $objPage->getStrTemplate();

            //load the placeholders
            $strTemplateId = $this->objTemplate->readTemplate("/module_pages/".$strTemplate);
            $arrPlaceholders = $this->objTemplate->getElements($strTemplateId);
            $arrPlaceholdersDD = array();

            foreach ($arrPlaceholders as $arrSinglePlaceholder) {

                foreach ($arrSinglePlaceholder["elementlist"] as $arrSinglePlaceholderlist) {
                    if ($objSourceElement->getStrElement() == $arrSinglePlaceholderlist["element"]) {
                        if ($objSourceElement->getIntRepeat() == 1) {
                            //repeatable, ok in every case
                            $arrPlaceholdersDD[$arrSinglePlaceholder["placeholder"]] = $arrSinglePlaceholder["placeholder"];
                        }
                        else {
                            //not repeatable - element already existing at placeholder?
                            $arrElementsOnPage = PagesPageelement::getElementsOnPage($objPage->getSystemid(), false, $objLang->getStrName());
                            //loop in order to find same element-types - other elements may be possible due to piped placeholders, too
                            $bitAdd = true;
                            //var_dump($arrElementsOnPage);
                            foreach ($arrElementsOnPage as $objSingleElementOnPage) {
                                if ($objSingleElementOnPage->getStrElement() == $objSourceElement->getStrElement()) {
                                    $bitAdd = false;
                                }
                            }

                            if ($bitAdd) {
                                $arrPlaceholdersDD[$arrSinglePlaceholder["placeholder"]] = $arrSinglePlaceholder["placeholder"];
                            }
                        }
                    }
                }
            }


            $bitCopyingAllowed = true;
            if (count($arrPlaceholdersDD) == 0) {
                $strReturn .= $this->objToolkit->formTextRow($this->getLang("copyElement_err_placeholder"));
                $bitCopyingAllowed = false;
            }
            else {
                $strReturn .= $this->objToolkit->formInputDropdown("copyElement_placeholder", $arrPlaceholdersDD, $this->getLang("copyElement_placeholder"));
            }
            $strReturn .= $this->objToolkit->formTextRow($this->getLang("copyElement_template")." ".$strTemplate);

            $strReturn .= $this->objToolkit->divider();

            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("copyElement_submit"), "Submit", "", "inputSubmit", $bitCopyingAllowed);
            $strReturn .= $this->objToolkit->formClose();


            $strReturn .= "
                <script type=\"text/javascript\">

                $(function() {
                        var reloadForm = function() {
                            setTimeout( function() {
                                document.getElementById('copyElement_doCopy').value = 0;
                                var formElement = document.getElementById('formCopyElement');
                                formElement.submit();
                            }, 100);

                        };

	                    KAJONA.admin.copyElement_page.bind('autocompleteselect', reloadForm);

	                    var languageField = document.getElementById('copyElement_language');
	                    languageField.onchange = reloadForm;

                        var pageField = document.getElementById('copyElement_page');
	                    pageField.onchange = reloadForm;
	             });

                </script>";

            //any actions to take?
            if ($this->getParam("copyElement_doCopy") == 1) {
                $objNewElement = $objSourceElement->copyObject($objPage->getSystemid());
                $objNewElement->setStrLanguage($objLang->getStrName());
                $objNewElement->setStrPlaceholder($this->getParam("copyElement_placeholder"));
                if ($objNewElement->updateObjectToDb()) {
                    $this->setSystemid($objNewElement->getSystemid());
                    $strReturn = "";

                    $this->adminReload(Link::getLinkAdminHref("pages_content", "list", "systemid=".$objNewElement->getPrevId()."&blockAction=1&peClose=1"));
                }
                else {
                    throw new Exception("Error copying the pageelement ".$objSourceElement->getSystemid(), Exception::$level_ERROR);
                }

            }

        }
        else {
            $strReturn = $this->getLang("commons_error_permissions");
        }
        return $strReturn;
    }


    /**
     * Helper to generate a small path-navigation
     *
     * @return array
     */
    protected function getArrOutputNaviEntries()
    {
        $arrPath = $this->getPathArray();

        $arrPathLinks = parent::getArrOutputNaviEntries();
        array_pop($arrPathLinks);
        $arrPathLinks[] = Link::getLinkAdmin("pages", "list", "&unlockid=".$this->getSystemid(), $this->getLang("modul_titel", "pages"));

        foreach ($arrPath as $strOneSystemid) {
            /** @var $objObject PagesFolder|PagesPage */
            $objObject = Objectfactory::getInstance()->getObject($strOneSystemid);
            //Skip Elements: No sense to show in path-navigation
            if ($objObject == null || $objObject->getIntModuleNr() == _pages_content_modul_id_) {
                continue;
            }

            if ($objObject instanceof PagesFolder) {
                $arrPathLinks[] = Link::getLinkAdmin("pages", "list", "&systemid=".$strOneSystemid."&unlockid=".$this->getSystemid(), $objObject->getStrName());
            }
            if ($objObject instanceof PagesPage) {
                $arrPathLinks[] = Link::getLinkAdmin("pages", "list", "&systemid=".$strOneSystemid."&unlockid=".$this->getSystemid(), $objObject->getStrBrowsername());
            }

        }
        return $arrPathLinks;
    }

    /**
     * Sorts the current element upwards
     *
     * @return void
     */
    protected function actionElementStatus()
    {
        //Create the object
        $objElement = new PagesPageelement($this->getSystemid());
        $objElement->setIntRecordStatus($objElement->getIntRecordStatus() == 0 ? 1 : 0);
        $objElement->updateObjectToDb();
        $this->adminReload(Link::getLinkAdminHref("pages_content", "list", "systemid=".$objElement->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
    }


    /**
     * Method to move an element from one placeholder to another
     * Expects the params
     * - systemid
     * - placeholder
     *
     * @permissions edit
     * @xml
     * @return string
     */
    protected function actionMoveElement()
    {
        $strReturn = "";
        //get the object to update
        /** @var $objObject PagesPageelement */
        $objObject = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objObject instanceof PagesPageelement && $objObject->rightEdit()) {

            $strPageSystemid = $objObject->getPrevId();
            $objLockmanager = new Lockmanager($objObject->getSystemid());

            $strPlaceholder = $this->getParam("placeholder");
            $arrParts = explode("_", $strPlaceholder);

            if (uniStrpos($arrParts[1], $objObject->getStrElement()) !== false) {

                if (!$objLockmanager->isLocked()) {
                    $objLockmanager->lockRecord();
                }

                if ($objLockmanager->isLockedByCurrentUser()) {

                    //ph_placeholder
                    $objObject->setStrPlaceholder($strPlaceholder);

                    //ph_name
                    $objObject->setStrName($arrParts[0]);

                    $objObject->updateObjectToDb();

                    //Edit Date of page & unlock
                    $objPage = Objectfactory::getInstance()->getObject($strPageSystemid);
                    $objPage->updateObjectToDb();
                    $objLockmanager->unlockRecord();

                    //Loading the data of the corresp site
                    $this->flushCompletePagesCache();

                    $strReturn = "<message><success>element update succeeded</success></message>";
                }
                else {
                    ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
                    $strReturn = "<message><error>element not allowed for target placeholder</error></message>";

                }
            }

        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
            $strReturn = "<message><error>".$this->getLang("ds_gesperrt").".".$this->getLang("commons_error_permissions")."</error></message>";
        }
        return $strReturn;
    }


    /**
     * @xml
     * @permissions edit
     * @return string
     */
    protected function actionUpdateObjectProperty()
    {
        $strReturn = "";
        //get the object to update
        /** @var $objObject PagesElement */
        $objObject = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objObject->rightEdit()) {
            //differ between two modes - page-elements or regular objects
            if ($objObject instanceof PagesPageelement) {

                $strPageSystemid = $objObject->getPrevId();
                $objLockmanager = new Lockmanager($objObject->getSystemid());

                if (!$objLockmanager->isLocked()) {
                    $objLockmanager->lockRecord();
                }

                if ($objLockmanager->isLockedByCurrentUser()) {
                    //and finally create the object
                    /** @var PagesPageelement $objElement */
                    //and finally create the object
                    $strFilename = Resourceloader::getInstance()->getPathForFile("/admin/elements/".$objObject->getStrClassAdmin());
                    $objElement = Classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");

                    //and finally create the object
                    /** @var $objElement ElementAdmin */
                    $objElement->setSystemid($this->getSystemid());


                    $arrElementData = $objElement->loadElementData();

                    //see if we could set the param to the element
                    if ($this->getParam("property") != "") {

                        $strProperty = null;

                        //try to fetch the matching setter
                        $objReflection = new Reflection($objElement);

                        //try to fetch the property based on the orm annotations
                        $strTargetTable = $objReflection->getAnnotationValuesFromClass(OrmBase::STR_ANNOTATION_TARGETTABLE);
                        if (count($strTargetTable) > 0) {
                            $strTargetTable = $strTargetTable[0];
                        }

                        $arrTable = explode(".", $strTargetTable);
                        if (count($arrTable) == 2) {
                            $strTargetTable = $arrTable[0];
                        }

                        $arrOrmProperty = $objReflection->getPropertiesWithAnnotation(OrmBase::STR_ANNOTATION_TABLECOLUMN);
                        foreach ($arrOrmProperty as $strCurProperty => $strValue) {
                            if ($strValue == $strTargetTable.".".$this->getParam("property")) {
                                $strProperty = $strCurProperty;
                            }
                        }

                        if ($strProperty == null) {
                            $strProperty = $this->getParam("property");
                        }

                        $strSetter = $objReflection->getSetter($strProperty);
                        if ($strSetter != null) {
                            $objElement->{$strSetter}($this->getParam("value"));
                        }
                        else {
                            $arrElementData[$this->getParam("property")] = $this->getParam("value");
                            $objElement->setArrParamData($arrElementData);
                        }
                    }

                    //pass the data to the element, maybe the element wants to update some data
                    $objElement->doBeforeSaveToDb();

                    //check, if we could save the data, so the element needn't to
                    //woah, we are soooo great
                    $objElement->updateForeignElement();

                    //Edit Date of page & unlock
                    $objPage = Objectfactory::getInstance()->getObject($strPageSystemid);
                    $objPage->updateObjectToDb();
                    $objLockmanager->unlockRecord();

                    //allow the element to run actions after saving
                    $objElement->doAfterSaveToDb();

                    //Loading the data of the corresp site
                    $this->flushCompletePagesCache();

                    $strReturn = "<message><success>element update succeeded</success></message>";
                }
            }
            else {
                //any other object - try to find the matching property and write the value
                if ($this->getParam("property") == "") {
                    ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);
                    return "<message><error>missing property param</error></message>";
                }

                $objReflection = new Reflection($objObject);
                $strSetter = $objReflection->getSetter($this->getParam("property"));
                if ($strSetter == null) {
                    ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);
                    return "<message><error>setter not found</error></message>";
                }

                $objObject->{$strSetter}($this->getParam("value"));
                $objObject->updateObjectToDb();
                $this->flushCompletePagesCache();

                $strReturn = "<message><success>object update succeeded</success></message>";

            }
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
            $strReturn = "<message><error>".$this->getLang("ds_gesperrt").".".$this->getLang("commons_error_permissions")."</error></message>";
        }
        return $strReturn;

    }

}
