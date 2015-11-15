<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Admin;

use class_admin_simple;
use class_adminskin_helper;
use class_carrier;
use class_date;
use class_exception;
use class_http_statuscodes;
use class_lang;
use class_link;
use class_lockmanager;
use class_model;
use class_module_languages_admin;
use class_module_languages_language;
use class_module_system_module;
use class_objectfactory;
use class_orm_base;
use class_reflection;
use class_resourceloader;
use class_response_object;
use class_template;
use interface_admin;
use interface_admin_listable;
use interface_model;
use Kajona\Pages\Admin\Elements\ElementBlockAdmin;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;

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
class PagesContentAdmin extends class_admin_simple implements interface_admin
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
            $objLockmanager = new class_lockmanager($this->getParam("unlockid"));
            $objLockmanager->unlockRecord();
        }
        if ($this->getParam("adminunlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("adminunlockid"));
            $objLockmanager->unlockRecord(true);
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


    private function getPageInfoBox(PagesPage $objPage)
    {
        $strReturn = "";
        //get infos about the page
        $arrToolbarEntries = array();
        $arrToolbarEntries[0] = "<a href=\"".class_link::getLinkAdminHref("pages", "editPage", "&systemid=".$objPage->getSystemid())."\">".class_adminskin_helper::getAdminImage("icon_edit").$this->getLang("contentToolbar_pageproperties")."</a>";
        $arrToolbarEntries[1] = "<a href=\"".class_link::getLinkAdminHref("pages_content", "list", "&systemid=".$objPage->getSystemid())."\">".class_adminskin_helper::getAdminImage("icon_page").$this->getLang("contentToolbar_content")."</a>";
        $arrToolbarEntries[2] = "<a href=\"".class_link::getLinkPortalHref($objPage->getStrName(), "", "", "&preview=1", "", $this->getLanguageToWorkOn())."\" target=\"_blank\">".class_adminskin_helper::getAdminImage("icon_lens").$this->getLang("contentToolbar_preview")."</a>";

        if ($objPage->getIntType() != PagesPage::$INT_TYPE_ALIAS) {
            $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 1);
        }

        $arrInfoRows = array(
            array($this->getLang("template"), $objPage->getStrTemplate()),
            array($this->getLang("lastuserTitle"), $objPage->getLastEditUser()),
            array($this->getLang("lasteditTitle"), timeToString($objPage->getIntLmTime()))
        );
        $strReturn .= $this->objToolkit->dataTable(null, $arrInfoRows);
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
        class_module_languages_admin::enableLanguageSwitch();
        $objCurObject = class_objectfactory::getInstance()->getObject($this->getSystemid());

        /** @var PagesPage $objPage */
        $objPage = $objCurObject;
        while (!$objPage instanceof PagesPage && validateSystemid($objPage->getSystemid())) {
            $objPage = class_objectfactory::getInstance()->getObject($objPage->getStrPrevId());
        }

        $strReturn .= $this->getPageInfoBox($objPage);


        //parse the whole template
        $objParsedBlocks = $this->objTemplate->parsePageTemplate("/module_pages/".$objPage->getStrTemplate(), $objPage->getStrName() == "master" ? class_template::INT_ELEMENT_MODE_MASTER : class_template::INT_ELEMENT_MODE_REGULAR);


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
        $strBlocks = "";
        foreach ($objParsedBlocks->getArrBlocks() as $objOneBlocks) {



            $strCurBlocks = "";
            $strNewBlocks = "";
            foreach ($objOneBlocks->getArrBlocks() as $objOneBlock) {

                $strNewBlocksSystemid = $objOneBlocks->getStrName();

                //process existing blocks
                /** @var PagesPageelement $objOneBlocksOnPage */
                foreach($arrBlocksOnPage as $objOneBlocksOnPage) {


                    if($objOneBlocksOnPage->getStrName() == $objOneBlocks->getStrName()) {
                        $strNewBlocksSystemid = $objOneBlocksOnPage->getSystemid();
                        $arrSubBlock = PagesPageelement::getElementsOnPage($objOneBlocksOnPage->getSystemid(), false, $this->getLanguageToWorkOn());

                        foreach($arrSubBlock as $objOneBlockOnPage) {

                            $strExistingBlock = "";

                            if($objOneBlockOnPage->getStrName() == $objOneBlock->getStrName()) {
                                $arrElementsInBlock = PagesPageelement::getElementsOnPage($objOneBlockOnPage->getSystemid(), false, $this->getLanguageToWorkOn());
                                $strExistingBlock .= $this->renderElementPlaceholderList($objOneBlock->getArrPlaceholder(), $arrElementsInBlock, true, $objOneBlocksOnPage->getSystemid(), $objOneBlockOnPage->getSystemid());
                            }

                            if($strExistingBlock != "") {

                                $strActions = $this->getActionIcons($objOneBlockOnPage);

                                $strCurBlocks .= $this->objToolkit->getFieldset(
                                    $objOneBlock->getStrName()."<span class='pull-right'>".$strActions."</span>",
                                    $strExistingBlock,
                                    "fieldset block",
                                    $objOneBlockOnPage->getSystemid()
                                );
                            }
                        }

                    }

                }


                $strNewBlocks .= $this->renderNewBlockLinkRow($strNewBlocksSystemid, $objOneBlock->getStrName());


            }

            $strBlocks .= $this->objToolkit->getFieldset($objOneBlocks->getStrName(), $strCurBlocks.$strNewBlocks, "fieldset blocks");
        }


        $arrTabs["blocks"] = $strBlocks;
        $arrTabs["elements"] = $this->renderElementPlaceholderList($objParsedBlocks->getArrPlaceholder(), $arrPageelementsOnPage);

        $strReturn .= $this->objToolkit->getTabbedContent($arrTabs);
        $strCore = class_resourceloader::getInstance()->getCorePathForModule("module_pages");

        $strReturn .= <<<HTML
            <script type="text/javascript">
                KAJONA.admin.loader.loadFile('{$strCore}/module_pages/admin/scripts/pages.js', function() {
                    KAJONA.admin.pages.initBlockSort();
                });

            </script>
HTML;

        return $strReturn;

    }

    private function renderNewBlockLinkRow($strBlocks, $strBlock)
    {
        $strNewElementLink = class_link::getLinkAdmin(
            "pages_content",
            "newBlock",
            "&blocks={$strBlocks}&block={$strBlock}&systemid={$this->getSystemid()}",
            "",
            $this->getLang("element_anlegen"),
            "icon_new"
        );

            //So, the Row for a new element: element is repeatable or not yet created
        $strActions = $this->objToolkit->listButton($strNewElementLink);
        $strReturn = $this->objToolkit->listHeader();
        $strReturn .= $this->objToolkit->genericAdminList("", $strBlock, "", $strActions, 0);
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
                        $strOutputAtPlaceholder .= $this->objToolkit->simpleAdminList($objOneElementOnPage, $strActions, 0);

                        //remove the element from the array
                        unset($arrElementsOnPage[$intArrElementsOnPageKey]);
                    }

                }

                //Check, if one of the elements in the placeholder is allowed to be used multiple times
                foreach ($arrOneElementOnTemplate["elementlist"] as $arrSingleElementOnTemplateplaceholder) {

                    //Loading all Elements installed on the system ("RAW"-Elements)
                    /** @var PagesElement $objOnePossibleElementInSystem */
                    foreach (PagesElement::getObjectList() as $objOnePossibleElementInSystem) {
                        if ($objOnePossibleElementInSystem->getStrName() == $arrSingleElementOnTemplateplaceholder["element"]) {

                            $strNewElementLink = class_link::getLinkAdmin(
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
                                $strOutputAtPlaceholder .= $this->objToolkit->genericAdminList("", $objOnePossibleElementInSystem->getStrDisplayName(), "", $strActions, 0, ($bitRenderCompact ? $arrOneElementOnTemplate["placeholder"] : ""));
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
                                    $strOutputAtPlaceholder .= $this->objToolkit->genericAdminList("", $objOnePossibleElementInSystem->getStrDisplayName(), "", $strActions, 0, ($bitRenderCompact ? $arrOneElementOnTemplate["placeholder"] : ""));
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


                    if($bitRenderCompact) {
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
            $strReturn .= $this->objToolkit->divider();
            $strReturn .= $this->objToolkit->warningBox($this->getLang("warning_elementsremaining"));
            $strReturn .= $this->objToolkit->listHeader();

            //minimized actions now, plz. this ain't being a real element anymore!
            foreach ($arrElementsOnPage as $objOneElement) {
                $strActions = "";
                $strActions .= $this->objToolkit->listDeleteButton($objOneElement->getStrDisplayName(), $this->getLang("element_loeschen_frage"), class_link::getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneElement->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));

                //Put all Output together
                $strReturn .= $this->objToolkit->genericAdminList("", $objOneElement->getStrDisplayName().$this->getLang("placeholder").$objOneElement->getStrPlaceholder(), "", $strActions, 0);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }

        $strReturn .= $this->objToolkit->getTableOfContents("h2");

        return $strReturn;
    }


    /**
     * @param class_model|interface_admin_listable|interface_model|PagesPageelement $objOneIterable
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
            $objParent = class_objectfactory::getInstance()->getObject($objOneIterable->getStrPrevId());
            if($objParent instanceof PagesPageelement) {
                $bitParentIsBlock = $objParent->getConcreteAdminInstance() instanceof ElementBlockAdmin;
            }

            //Create a row to handle the element, check all necessary stuff such as locking etc
            $strActions = "";
            //First step - Record locked? Offer button to unlock? But just as admin! For the user, who locked the record, the unlock-button
            //won't be visible
            if (!$objLockmanager->isAccessibleForCurrentUser()) {
                //So, return a button, if we have an admin in front of us
                if ($objLockmanager->isUnlockableForCurrentUser()) {
                    $strActions .= $this->objToolkit->listButton(class_link::getLinkAdmin("pages_content", "list", "&systemid=".$this->getSystemid()."&adminunlockid=".$objOneIterable->getSystemid(), "", $this->getLang("ds_entsperren"), "icon_lockerOpen"));
                }
                //If the Element is locked, then its not allowed to edit or delete the record, so disable the icons
                if ($objOneIterable->rightEdit() && !$objOneIterable->getConcreteAdminInstance() instanceof ElementBlockAdmin) {
                    $strActions .= $this->objToolkit->listButton(class_adminskin_helper::getAdminImage("icon_editLocked", $this->getLang("ds_gesperrt")));
                }
                if ($objOneIterable->rightDelete() && !$bitParentIsBlock) {
                    $strActions .= $this->objToolkit->listButton(class_adminskin_helper::getAdminImage("icon_deleteLocked", $this->getLang("ds_gesperrt")));
                }
            }
            else {

                if ($objOneIterable->rightEdit() && !$objOneIterable->getConcreteAdminInstance() instanceof ElementBlockAdmin) {
                    $strActions .= $this->objToolkit->listButton(class_link::getLinkAdmin("pages_content", "edit", "&systemid=".$objOneIterable->getSystemid(), "", $this->getLang("element_bearbeiten"), "icon_edit"));
                }
                if ($objOneIterable->rightDelete() && !$bitParentIsBlock) {
                    $strActions .= $this->objToolkit->listDeleteButton($objOneIterable->getStrName().($objOneIterable->getConcreteAdminInstance()->getContentTitle() != "" ? " - ".$objOneIterable->getConcreteAdminInstance()->getContentTitle() : "").($objOneIterable->getStrTitle() != "" ? " - ".$objOneIterable->getStrTitle() : ""), $this->getLang("element_loeschen_frage"), class_link::getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneIterable->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
                }
            }

            if(!$bitParentIsBlock) {
            //The Icons to sort the list and to copy the element
                $strActions .= $this->objToolkit->listButton(class_link::getLinkAdminDialog("pages_content", "copyElement", "&systemid=".$objOneIterable->getSystemid(), "", $this->getLang("element_copy"), "icon_copy"));
                //The status-icons
                $strActions .= $this->objToolkit->listStatusButton($objOneIterable->getSystemid());
            }


        }
        elseif ($objOneIterable instanceof PagesElement) {
            $objAdminInstance = class_module_system_module::getModuleByName("pages")->getAdminInstanceOfConcreteModule();
            if ($objAdminInstance != null && $objAdminInstance instanceof class_admin_simple) {
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

        if($strBlocks != "" && $strBlock != "") {


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
                    throw new class_exception("Error saving new element-object to db", class_exception::$level_ERROR);
                }
            }

            $objBlockElement = new PagesPageelement();
            $objBlockElement->setStrName($strBlock);
            $objBlockElement->setStrPlaceholder("block");
            $objBlockElement->setStrElement("block");
            $objBlockElement->setStrLanguage($strLanguage);
            if (!$objBlockElement->updateObjectToDb($objBlocksElement->getSystemid())) {
                throw new class_exception("Error saving new element-object to db", class_exception::$level_ERROR);
            }

            $strNewPrevId = $objBlockElement->getSystemid();


            //create dummy elements, therefore parse the template
            /** @var PagesPage $objPage */
            $objPage = class_objectfactory::getInstance()->getObject($this->getSystemid());
            $objTemplate = class_template::getInstance()->parsePageTemplate("/module_pages/".$objPage->getStrTemplate());
            foreach ($objTemplate->getArrBlocks() as $objOneBlocks) {
                foreach ($objOneBlocks->getArrBlocks() as $objOneBlock) {
                    if ($objOneBlocks->getStrName() == $objBlocksElement->getStrName() && $objOneBlock->getStrName() == $objBlockElement->getStrName()) {

                        foreach ($objOneBlock->getArrPlaceholder() as $arrOnePlaceholder) {
                            foreach ($arrOnePlaceholder["elementlist"] as $arrElementList) {
                                //Create dummy elements
                                $strPlaceholder = $arrOnePlaceholder["placeholder"];


                                $objPageElement = new PagesPageelement();
                                $objPageElement->setStrName($arrElementList["name"]);
                                $objPageElement->setStrPlaceholder($strPlaceholder);
                                $objPageElement->setStrElement($arrElementList["element"]);
                                $objPageElement->setStrLanguage($strLanguage);
                                if (!$objPageElement->updateObjectToDb($strNewPrevId)) {
                                    throw new class_exception("Error saving new element-object to db", class_exception::$level_ERROR);
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

                $strReturn = <<<JS
                    parent.KAJONA.admin.portaleditor.changeElementData('blocks_{$objBlocksElement->getStrName()}', '{$objBlockElement->getSystemid()}', {$strContent});
                    parent.KAJONA.admin.portaleditor.closeDialog(true);

JS;
                class_carrier::getInstance()->setParam("peClose", null);
                return "<script type='text/javascript'>{$strReturn}</script>";

            }
            else {
                $this->adminReload(class_link::getLinkAdminHref("pages_content", "list", "&systemid=".$strPageId));
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
        $strFilename = \class_resourceloader::getInstance()->getPathForFile("/admin/elements/".$objElement->getStrClassAdmin());
        $objElement = \class_classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");
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
        $objElement = class_objectfactory::getInstance()->getObject($this->getSystemid());

        if ($objElement instanceof PagesElement) {
            $this->adminReload(class_link::getLinkAdminHref("pages", "edit", "&systemid=".$objElement->getSystemid()));
            return "";
        }


        //Load the element data
        //check, if the element isn't locked
        if ($objElement->getLockManager()->isAccessibleForCurrentUser()) {
            $objElement->getLockManager()->lockRecord();

            //Load the class to create an object


            //and finally create the object
            $strFilename = \class_resourceloader::getInstance()->getPathForFile("/admin/elements/".$objElement->getStrClassAdmin());
            /** @var $objPageElement ElementAdmin */
            $objPageElement = \class_classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");
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
     * @param $strPageId
     * @param $strBlocks
     * @param $strBlock
     *
     * @return string
     * @throws class_exception
     */
    private function processBlocksDefinition($strPageId, $strBlocks, $strBlock)
    {
        $strNewPrevId = $strPageId;

        if($strBlocks != "" && $strBlock != "") {


            if(validateSystemid($strBlocks) && validateSystemid($strBlock)) {
                //fetch the matching elements
                $objBlocks = new PagesPageelement($strBlocks);
                $objBlock = new PagesPageelement($strBlock);

                if($objBlocks->getStrElement() == "blocks" && $objBlock->getStrElement() == "block") {
                    return $objBlock->getSystemid();
                }
            }

            $objBlocksElement = null;
            if(validateSystemid($strBlocks) && !validateSystemid($strBlock)) {
                $objBlocksElement = new PagesPageelement($strBlocks);
            }


            if($objBlocksElement == null) {
                $objBlocksElement = new PagesPageelement();
                $objBlocksElement->setStrName($strBlocks);
                $objBlocksElement->setStrPlaceholder("blocks");
                $objBlocksElement->setStrElement("blocks");
                $objBlocksElement->setStrLanguage($this->getParam("page_element_ph_language"));
                if (!$objBlocksElement->updateObjectToDb($strNewPrevId)) {
                    throw new class_exception("Error saving new element-object to db", class_exception::$level_ERROR);
                }
            }

            $objBlockElement = new PagesPageelement();
            $objBlockElement->setStrName($strBlock);
            $objBlockElement->setStrPlaceholder("block");
            $objBlockElement->setStrElement("block");
            $objBlockElement->setStrLanguage($this->getParam("page_element_ph_language"));
            if (!$objBlockElement->updateObjectToDb($objBlocksElement->getSystemid())) {
                throw new class_exception("Error saving new element-object to db", class_exception::$level_ERROR);
            }

            $strNewPrevId = $objBlockElement->getSystemid();

        }


        return $strNewPrevId;
    }

    /**
     * Saves the passed Element to the database (edit or new modes)
     *
     * @throws class_exception
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
            $strFilename = \class_resourceloader::getInstance()->getPathForFile("/admin/elements/".$objElement->getStrClassAdmin());
            $objElement = \class_classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");

            //really continue? try to validate the passed data.
            if ($objElement->getAdminForm() !== null && !$objElement->getAdminForm()->validateForm()) {
                class_carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionNew(true);
                return $strReturn;
            }
            elseif (!$objElement->validateForm()) {
                class_carrier::getInstance()->setParam("peClose", "");
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
                throw new class_exception("Error saving new element-object to db", class_exception::$level_ERROR);
            }
            $strElementSystemId = $objPageElement->getSystemid();

            $objLockmanager = new class_lockmanager($strElementSystemId);
            $objLockmanager->lockRecord();

            //To have the element working as expected, set the systemid
            $this->setSystemid($strElementSystemId);
        }


        // ************************************* Edit the current Element *******************************

        //check, if the element isn't locked
        $objTemp = class_objectfactory::getInstance()->getObject($this->getSystemid());
        $strPageSystemid = $this->getSystemid();

        while (!$objTemp instanceof PagesPage && validateSystemid($objTemp->getStrPrevId())) {
            $objTemp = class_objectfactory::getInstance()->getObject($objTemp->getStrPrevId());
            $strPageSystemid = $objTemp->getSystemid();
        }

        $objLockmanager = new class_lockmanager($this->getSystemid());

        if ($objLockmanager->isLockedByCurrentUser()) {
            //Load the data of the current element
            $objElementData = new PagesPageelement($this->getSystemid());
            /** @var $objElement ElementAdmin */
            $objElement = $objElementData->getConcreteAdminInstance();

            //really continue? try to validate the passed data.
            if ($objElement->getAdminForm() !== null && !$objElement->getAdminForm()->validateForm()) {
                class_carrier::getInstance()->setParam("peClose", "");
                $strReturn .= $this->actionEdit(true);
                return $strReturn;
            }
            elseif (!$objElement->validateForm()) {
                class_carrier::getInstance()->setParam("peClose", "");
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
            $objPage = class_objectfactory::getInstance()->getObject($strPageSystemid);
            $objPage->updateObjectToDb();
            $objLockmanager->unlockRecord();

            //And update the internal comment and language
            $objElementData->setStrTitle($this->getParam("page_element_ph_title"));
            $objElementData->setStrLanguage($this->getParam("page_element_ph_language"));
            //placeholder to update?
            if ($this->getParam("placeholder") != "") {
                $objElementData->setStrPlaceholder($this->getParam("placeholder"));
            }

            $objStartDate = new class_date("0");
            $objEndDate = new class_date("0");
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
                throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
            }


            //allow the element to run actions after saving
            $objElement->doAfterSaveToDb();


            //Loading the data of the corresponding site
            $this->flushCompletePagesCache();

            if ($this->getParam("peClose") == "1") {

                //generate the elements' output
                $objPortalElement = $objElementData->getConcretePortalInstance();
                $strElementContent = $objPortalElement->getRenderedElementOutput(false);

                $strContent = json_encode($strElementContent, JSON_FORCE_OBJECT); //JSON_HEX_QUOT|JSON_HEX_APOS

                $strReturn = <<<JS
                    parent.KAJONA.admin.portaleditor.changeElementData('{$objElementData->getStrPlaceholder()}', '{$objElementData->getSystemid()}', {$strContent});
                    parent.KAJONA.admin.portaleditor.closeDialog(true);

JS;
                class_carrier::getInstance()->setParam("peClose", null);
                return "<script type='text/javascript'>{$strReturn}</script>";

            }

            $this->adminReload(class_link::getLinkAdminHref("pages_content", "list", "&systemid=".$strPageSystemid));

        }
        else {
            $strReturn = $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
        }
        return $strReturn;
    }

    /**
     * Deletes an Element
     *
     * @throws class_exception
     * @return string , "" in case of success
     * @permissions delete
     */
    protected function actionDeleteElementFinal()
    {
        $strReturn = "";

        $objPageElement = new PagesPageelement($this->getSystemid());
        if ($objPageElement->rightDelete()) {
            //Locked?
            $objLockmanager = new class_lockmanager($this->getSystemid());

            $objPage = class_objectfactory::getInstance()->getObject($objPageElement->getStrPrevId());
            while(!$objPage instanceof PagesPage && validateSystemid($objPage->getStrPrevId())) {
                $objPage = class_objectfactory::getInstance()->getObject($objPage->getStrPrevId());
            }

            if ($objLockmanager->isAccessibleForCurrentUser()) {
                //delete object
                if (!$objPageElement->deleteObject()) {
                    throw new class_exception("Error deleting element from db", class_exception::$level_ERROR);
                }

                if ($this->getParam("pe") == "1") {
                    $strReturn = <<<JS
                    parent.KAJONA.admin.portaleditor.deleteElementData('{$objPageElement->getSystemid()}');
                    parent.KAJONA.admin.portaleditor.closeDialog(true);
JS;
                    class_carrier::getInstance()->setParam("peClose", null);
                    return "<script type='text/javascript'>{$strReturn}</script>";
                }

                $this->adminReload(class_link::getLinkAdminHref("pages_content", "list", "systemid=".$objPage->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
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
     * @throws class_exception
     * @return string , "" in case of success
     * @permissions delete
     * @xml
     */
    protected function actionDeleteElementFinalXML()
    {

        $objPageElement = new PagesPageelement($this->getSystemid());
        if ($objPageElement->rightDelete()) {
            //Locked?
            $objLockmanager = new class_lockmanager($this->getSystemid());

            if ($objLockmanager->isAccessibleForCurrentUser()) {
                //delete object
                if (!$objPageElement->deleteObject()) {
                    throw new class_exception("Error deleting element from db", class_exception::$level_ERROR);
                }


                return "<message><success></success></message>";
            }
        }
        class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_FORBIDDEN);
        return "<message><error>".$this->getLang('commons_error_permissions')."</error></message>";
    }


    /**
     * Provides a form to set up the params needed to copy a single element from one placeholder to another.
     * Collects the target language, the target page and the target placeholder, invokes the copy-procedure.
     *
     * @throws class_exception
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
                $objLang = new class_module_languages_language($this->getParam("copyElement_language"));
            }
            else {
                $objLang = class_module_languages_language::getLanguageByName($this->getLanguageToWorkOn());
            }

            $objPage = null;
            if ($this->getParam("copyElement_page") != "") {
                $objPage = PagesPage::getPageByName($this->getParam("copyElement_page"));
                if ($objPage == null) {
                    throw new class_exception("failed to load page ".$this->getParam("copyElement_page"), class_exception::$level_ERROR);
                }
                $objPage->setStrLanguage($objLang->getStrName());
                $objPage->initObject();
            }
            else {
                $objPage = new PagesPage($objSourceElement->getPrevId());
            }

            //form header
            $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref("pages_content", "copyElement"), "formCopyElement");
            $strReturn .= $this->objToolkit->formInputHidden("copyElement_doCopy", 1);
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());

            $strReturn .= $this->objToolkit->formHeadline($this->getLang("copyElement_element")." ".$objSourceElement->getStrName()."_".$objSourceElement->getStrElement()." (".$objSourceElement->getStrTitle().")");


            //step one: language selection
            $arrLanguages = class_module_languages_language::getObjectList(true);
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

                    $this->adminReload(class_link::getLinkAdminHref("pages_content", "list", "systemid=".$objNewElement->getPrevId()."&blockAction=1&peClose=1"));
                }
                else {
                    throw new class_exception("Error copying the pageelement ".$objSourceElement->getSystemid(), class_exception::$level_ERROR);
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
        $arrPathLinks[] = class_link::getLinkAdmin("pages", "list", "&unlockid=".$this->getSystemid(), $this->getLang("modul_titel", "pages"));

        foreach ($arrPath as $strOneSystemid) {
            /** @var $objObject PagesFolder|PagesPage */
            $objObject = class_objectfactory::getInstance()->getObject($strOneSystemid);
            //Skip Elements: No sense to show in path-navigation
            if ($objObject == null || $objObject->getIntModuleNr() == _pages_content_modul_id_) {
                continue;
            }

            if ($objObject instanceof PagesFolder) {
                $arrPathLinks[] = class_link::getLinkAdmin("pages", "list", "&systemid=".$strOneSystemid."&unlockid=".$this->getSystemid(), $objObject->getStrName());
            }
            if ($objObject instanceof PagesPage) {
                $arrPathLinks[] = class_link::getLinkAdmin("pages", "list", "&systemid=".$strOneSystemid."&unlockid=".$this->getSystemid(), $objObject->getStrBrowsername());
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
        $this->adminReload(class_link::getLinkAdminHref("pages_content", "list", "systemid=".$objElement->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
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
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objObject instanceof PagesPageelement && $objObject->rightEdit()) {

            $strPageSystemid = $objObject->getPrevId();
            $objLockmanager = new class_lockmanager($objObject->getSystemid());

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
                    $objPage = class_objectfactory::getInstance()->getObject($strPageSystemid);
                    $objPage->updateObjectToDb();
                    $objLockmanager->unlockRecord();

                    //Loading the data of the corresp site
                    $this->flushCompletePagesCache();

                    $strReturn = "<message><success>element update succeeded</success></message>";
                }
                else {
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                    $strReturn = "<message><error>element not allowed for target placeholder</error></message>";

                }
            }

        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
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
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objObject->rightEdit()) {
            //differ between two modes - page-elements or regular objects
            if ($objObject instanceof PagesPageelement) {

                $strPageSystemid = $objObject->getPrevId();
                $objLockmanager = new class_lockmanager($objObject->getSystemid());

                if (!$objLockmanager->isLocked()) {
                    $objLockmanager->lockRecord();
                }

                if ($objLockmanager->isLockedByCurrentUser()) {
                    //and finally create the object
                    /** @var PagesPageelement $objElement */
                    $strElementClass = str_replace(".php", "", $objObject->getStrClassAdmin());
                    //and finally create the object
                    $strFilename = \class_resourceloader::getInstance()->getPathForFile("/admin/elements/".$objObject->getStrClassAdmin());
                    $objElement = \class_classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\Pages\\Admin\\ElementAdmin");

                    //and finally create the object
                    /** @var $objElement ElementAdmin */
                    $objElement->setSystemid($this->getSystemid());


                    $arrElementData = $objElement->loadElementData();

                    //see if we could set the param to the element
                    if ($this->getParam("property") != "") {

                        $strProperty = null;

                        //try to fetch the matching setter
                        $objReflection = new class_reflection($objElement);

                        //try to fetch the property based on the orm annotations
                        $strTargetTable = $objReflection->getAnnotationValuesFromClass(class_orm_base::STR_ANNOTATION_TARGETTABLE);
                        if (count($strTargetTable) > 0) {
                            $strTargetTable = $strTargetTable[0];
                        }

                        $arrTable = explode(".", $strTargetTable);
                        if (count($arrTable) == 2) {
                            $strTargetTable = $arrTable[0];
                        }

                        $arrOrmProperty = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_TABLECOLUMN);
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
                            call_user_func(array($objElement, $strSetter), $this->getParam("value"));
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
                    $objPage = class_objectfactory::getInstance()->getObject($strPageSystemid);
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
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_BADREQUEST);
                    return "<message><error>missing property param</error></message>";
                }

                $objReflection = new class_reflection($objObject);
                $strSetter = $objReflection->getSetter($this->getParam("property"));
                if ($strSetter == null) {
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_BADREQUEST);
                    return "<message><error>setter not found</error></message>";
                }

                call_user_func(array($objObject, $strSetter), $this->getParam("value"));
                $objObject->updateObjectToDb();
                $this->flushCompletePagesCache();

                $strReturn = "<message><success>object update succeeded</success></message>";

            }
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn = "<message><error>".$this->getLang("ds_gesperrt").".".$this->getLang("commons_error_permissions")."</error></message>";
        }
        return $strReturn;

    }

}
