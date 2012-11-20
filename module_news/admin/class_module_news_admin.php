<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * Admin class of the news-module. Responsible for editing news, organizing them in categories and creating feeds
 *
 * @package module_news
 * @author sidler@mulchprod.de
 */
class class_module_news_admin extends class_admin_simple implements interface_admin, interface_calendarsource_admin {

    const STR_CAT_LIST = "STR_CAT_LIST";
    const STR_NEWS_LIST = "STR_NEWS_LIST";
    const STR_FEED_LIST = "STR_FEED_LIST";

    const STR_CALENDAR_FILTER_NEWS = "STR_CALENDAR_FILTER_NEWS";


    /**
     * Constructor

     */
    public function __construct() {
        $this->setArrModuleEntry("moduleId", _news_module_id_);
        $this->setArrModuleEntry("modul", "news");
        parent::__construct();
    }

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newNews", "", $this->getLang("actionNew"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newCat", "", $this->getLang("commons_create_category"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "listNewsFeed", "", $this->getLang("actionListNewsFeed"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "newNewsFeed", "", $this->getLang("actionNewNewsFeed"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=" . $this->arrModule["modul"], $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    /**
     * Renders the form to create a new entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        return $this->actionNewNews();
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());

        if($objObject instanceof class_module_news_category && $objObject->rightEdit()) {
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editCat", "&systemid=" . $objObject->getSystemid()));
        }

        if($objObject instanceof class_module_news_news && $objObject->rightEdit()) {
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editNews", "&systemid=" . $objObject->getSystemid()));
        }

        if($objObject instanceof class_module_news_feed && $objObject->rightEdit()) {
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editNewsFeed", "&systemid=" . $objObject->getSystemid()));
        }

        return "";
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($strListIdentifier == class_module_news_admin::STR_CAT_LIST) {
            return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newCat", "", $this->getLang("commons_create_category"), $this->getLang("commons_create_category"), "icon_new.png"));
        }

        if($strListIdentifier == class_module_news_admin::STR_FEED_LIST) {
            return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newNewsFeed", "", $this->getLang("actionNewNewsFeed"), $this->getLang("actionNewNewsFeed"), "icon_new.png"));
        }

        if($strListIdentifier == class_module_news_admin::STR_NEWS_LIST) {
            return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "new", "", $this->getLang("actionNew"), $this->getLang("actionNew"), "icon_new.png"));
        }


        return parent::getNewEntryAction($strListIdentifier, $bitDialog);
    }

    protected function renderDeleteAction(interface_model $objListEntry) {

        if($objListEntry instanceof class_module_news_category && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(),
                $this->getLang("commons_delete_category_question"),
                getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=" . $objListEntry->getSystemid())
            );
        }

        if($objListEntry instanceof class_module_news_feed && $objListEntry->rightDelete()) {
            return $this->objToolkit->listDeleteButton(
                $objListEntry->getStrDisplayName(),
                $this->getLang("feed_delete_question"),
                getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=" . $objListEntry->getSystemid())
            );
        }

        return parent::renderDeleteAction($objListEntry);
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_news_category) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "list", "&filterId=" . $objListEntry->getSystemid(), "", $this->getLang("kat_anzeigen"), "icon_lens.png"))
            );
        }

        if($objListEntry instanceof class_module_news_news && $objListEntry->rightEdit()) {
            if(class_module_languages_language::getNumberOfLanguagesAvailable() > 1) {
                return array(
                    $this->objToolkit->listButton(
                        getLinkAdmin($this->arrModule["modul"], "editLanguageset", "&systemid=" . $objListEntry->getSystemid(), "", $this->getLang("news_languageset"), "icon_language.png")
                    )
                );
            }
        }

        return array();
    }

    /**
     * Returns a list of all categories and all news
     * The list could be filtered by categories
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList() {

        $objIterator = new class_array_section_iterator(class_module_news_category::getObjectCount());
        $objIterator->setIntElementsPerPage(class_module_news_category::getObjectCount());
        $objIterator->setPageNumber(1);
        $objIterator->setArraySection(class_module_news_category::getObjectList("", $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn = $this->renderList($objIterator, false, class_module_news_admin::STR_CAT_LIST);

        $objIterator = new class_array_section_iterator(class_module_news_news::getObjectCount($this->getParam("filterId")));
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(class_module_news_news::getObjectList($this->getParam("filterId"), $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objIterator, false, class_module_news_admin::STR_NEWS_LIST);

        return $strReturn;
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEditLanguageset() {
        $strReturn = "";
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objNews->rightEdit()) {

            $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            if($objLanguageset == null) {
                $strReturn .= $this->objToolkit->formTextRow($this->getLang("languageset_notmaintained"));
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_addtolanguage"));

                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "assignToLanguageset"));
                $arrLanguages = class_module_languages_language::getObjectList();
                $arrDropdown = array();
                foreach($arrLanguages as $objOneLanguage) {
                    $arrDropdown[$objOneLanguage->getSystemid()] = $this->getLang("lang_" . $objOneLanguage->getStrName(), "languages");
                }

                $strReturn .= $this->objToolkit->formInputDropdown("languageset_language", $arrDropdown, $this->getLang("commons_language_field"));
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                $strReturn .= $this->objToolkit->formClose();
            }
            else {

                $objLanguage = new class_module_languages_language($objLanguageset->getLanguageidForSystemid($this->getSystemid()));
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_addtolanguage"));
                $strReturn .= $this->objToolkit->formTextRow($this->getLang("languageset_currentlanguage"));
                $strReturn .= $this->objToolkit->formTextRow($this->getLang("lang_" . $objLanguage->getStrName(), "languages"));

                $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_maintainlanguages"));

                $arrLanguages = class_module_languages_language::getObjectList();

                $strReturn .= $this->objToolkit->listHeader();
                $intI = 0;
                $intNrOfUnassigned = 0;
                $arrMaintainedLanguages = array();
                foreach($arrLanguages as $objOneLanguage) {

                    $strNewsid = $objLanguageset->getSystemidForLanguageid($objOneLanguage->getSystemid());
                    $strActions = "";
                    if($strNewsid != null) {
                        $arrMaintainedLanguages[] = $objOneLanguage->getSystemid();
                        $objNews = new class_module_news_news($strNewsid);
                        $strNewsName = $objNews->getStrTitle();
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "removeFromLanguageset", "&systemid=" . $objNews->getSystemid(), "", $this->getLang("languageset_remove"), "icon_delete.png"));
                        $strReturn .= $this->objToolkit->genericAdminList(
                            $objOneLanguage->getSystemid(), $this->getLang("lang_" . $objOneLanguage->getStrName(), "languages") . ": " . $strNewsName, getImageAdmin("icon_language.png"), $strActions, $intI++
                        );
                    }
                    else {
                        $intNrOfUnassigned++;
                        $strReturn .= $this->objToolkit->genericAdminList(
                            $objOneLanguage->getSystemid(), $this->getLang("lang_" . $objOneLanguage->getStrName(), "languages") . ": " . $this->getLang("languageset_news_na"), getImageAdmin("icon_language.png"), $strActions, $intI++
                        );
                    }

                }

                $strReturn .= $this->objToolkit->listFooter();

                //provide a form to add further news-items
                if($intNrOfUnassigned > 0) {
                    $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_addnewstolanguage"));

                    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "addNewsToLanguageset"));
                    $arrLanguages = class_module_languages_language::getObjectList();
                    $arrDropdown = array();
                    foreach($arrLanguages as $objOneLanguage) {
                        if(!in_array($objOneLanguage->getSystemid(), $arrMaintainedLanguages)) {
                            $arrDropdown[$objOneLanguage->getSystemid()] = $this->getLang("lang_" . $objOneLanguage->getStrName(), "languages");
                        }
                    }

                    $strReturn .= $this->objToolkit->formInputDropdown("languageset_language", $arrDropdown, $this->getLang("commons_language_field"));


                    $arrNews = class_module_news_news::getObjectList();
                    $arrDropdown = array();
                    foreach($arrNews as $objOneNews) {
                        if(class_module_languages_languageset::getLanguagesetForSystemid($objOneNews->getSystemid()) == null) {
                            $arrDropdown[$objOneNews->getSystemid()] = $objOneNews->getStrTitle();
                        }
                    }

                    $strReturn .= $this->objToolkit->formInputDropdown("languageset_news", $arrDropdown, $this->getLang("languageset_news"));

                    $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                    $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                    $strReturn .= $this->objToolkit->formClose();
                }
            }
        }
        else {
            $strReturn .= $this->getLang("commons_error_permissions");
        }

        return $strReturn;
    }

    protected function actionAddNewsToLanguageset() {
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objNews->rightEdit()) {
            $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            //load the languageset for the current systemid
            $objTargetLanguage = new class_module_languages_language($this->getParam("languageset_language"));
            if($objLanguageset != null && $objTargetLanguage->getStrName() != "") {
                $objLanguageset->setSystemidForLanguageid($this->getParam("languageset_news"), $objTargetLanguage->getSystemid());
            }

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "editLanguageset", "&systemid=" . $this->getSystemid()));
        }
    }

    protected function actionAssignToLanguageset() {
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objNews->rightEdit()) {
            $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            $objTargetLanguage = new class_module_languages_language($this->getParam("languageset_language"));
            if($objLanguageset == null && $objTargetLanguage->getStrName() != "") {
                $objLanguageset = new class_module_languages_languageset();
                $objLanguageset->setSystemidForLanguageid($this->getSystemid(), $objTargetLanguage->getSystemid());
            }

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "editLanguageset", "&systemid=" . $this->getSystemid()));
        }
    }

    protected function actionRemoveFromLanguageset() {
        $objNews = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objNews->rightEdit()) {
            $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            if($objLanguageset != null) {
                $objLanguageset->removeSystemidFromLanguageeset($this->getSystemid());
            }

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "editLanguageset", "&systemid=" . $this->getSystemid()));
        }
    }


    protected function actionEditCat() {
        return $this->actionNewCat("edit");
    }


    /**
     * Show the form to create or edit a news cat
     *
     * @param string $strMode
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNewCat($strMode = "new", class_admin_formgenerator $objForm = null) {

        $objCategory = new class_module_news_category();
        if($strMode == "edit") {
            $objCategory = new class_module_news_category($this->getSystemid());

            if(!$objCategory->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }
        }

        if($objForm == null) {
            $objForm = $this->getCatAdminForm($objCategory);
        }

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveCat"));
    }


    private function getCatAdminForm(class_module_news_category $objCat) {
        $objForm = new class_admin_formgenerator("cat", $objCat);
        $objForm->generateFieldsFromObject();
        return $objForm;
    }

    /**
     * Saves the passed values as a new category to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveCat() {
        $objCat = null;

        if($this->getParam("mode") == "new") {
            $objCat = new class_module_news_category();
        }

        else if($this->getParam("mode") == "edit") {
            $objCat = new class_module_news_category($this->getSystemid());
        }

        if($objCat != null) {

            $objForm = $this->getCatAdminForm($objCat);
            if(!$objForm->validateForm()) {
                return $this->actionNewCat($this->getParam("mode"), $objForm);
            }

            $objForm->updateSourceObject();
            $objCat->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "", ($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }


    protected function actionEditNews() {
        return $this->actionNewNews("edit");
    }

    /**
     * Shows the form to edit or create news
     *
     * @param string $strMode new || edit
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     */
    protected function actionNewNews($strMode = "new", class_admin_formgenerator $objForm = null) {
        $strReturn = "";
        $objNews = new class_module_news_news();
        if($strMode == "edit") {
            $objNews = new class_module_news_news($this->getSystemid());
            $objNews->getLockManager()->lockRecord();

            if(!$objNews->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }

            //search the languages maintained
            $objLanguageManager = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
            if($objLanguageManager != null) {

                $arrMaintained = $objLanguageManager->getArrLanguageSet();
                $arrDD = array();
                foreach($arrMaintained as $strLanguageId => $strSystemid) {
                    $objLanguage = new class_module_languages_language($strLanguageId);
                    $arrDD[$strSystemid] = $this->getLang("lang_" . $objLanguage->getStrName(), "languages");
                }

                class_module_languages_admin::enableLanguageSwitch();
                class_module_languages_admin::setArrLanguageSwitchEntries($arrDD);
                class_module_languages_admin::setStrOnChangeHandler("window.location='" . getLinkAdminHref("news", "editNews") . (_system_mod_rewrite_ == "true" ? "?" : "&") . "systemid='+this.value+'&pe=" . $this->getParam("pe") . "';");
                class_module_languages_admin::setStrActiveKey($this->getSystemid());
            }
        }

        if($objForm == null) {
            $objForm = $this->getNewsAdminForm($objNews);
        }

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        $strReturn .= $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveNews"));
        return $strReturn;
    }


    private function getNewsAdminForm(class_module_news_news $objNews) {
        $objForm = new class_admin_formgenerator("news", $objNews);
        $objForm->generateFieldsFromObject();

        $arrCats = class_module_news_category::getObjectList();
        if(count($arrCats) > 0) {
            $objForm->addField(new class_formentry_headline())->setStrValue($this->getLang("commons_categories"));
        }

        $arrFaqsMember = class_module_news_category::getNewsMember($this->getSystemid());

        foreach($arrCats as $objOneCat) {
            $bitChecked = false;
            foreach($arrFaqsMember as $objOneMember) {
                if($objOneMember->getSystemid() == $objOneCat->getSystemid()) {
                    $bitChecked = true;
                }
            }

            $objForm->addField(new class_formentry_checkbox("news", "cat[" . $objOneCat->getSystemid() . "]"))->setStrLabel($objOneCat->getStrTitle())->setStrValue($bitChecked);

        }

        return $objForm;
    }

    /**
     * Saves the passed values as a new category to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveNews() {
        $objNews = null;

        if($this->getParam("mode") == "new") {
            $objNews = new class_module_news_news();
        }

        else if($this->getParam("mode") == "edit") {
            $objNews = new class_module_news_news($this->getSystemid());
        }

        if($objNews != null) {

            $objForm = $this->getNewsAdminForm($objNews);
            if(!$objForm->validateForm()) {
                return $this->actionNewNews($this->getParam("mode"), $objForm);
            }

            $objForm->updateSourceObject();




            $arrParams = $this->getAllParams();
            $arrCats = array();
            if(isset($arrParams["news_cat"])) {
                foreach($arrParams["news_cat"] as $strCatID => $strValue) {
                    $arrCats[$strCatID] = $strValue;
                }
            }
            $objNews->setArrCats($arrCats);

            $objNews->setBitUpdateMemberships(true);
            $objNews->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "", ($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }


    /**
     * Shows a list of all views currently available
     *
     * @return string
     * @autotestable
     * @permissions right3
     */
    protected function actionListNewsFeed() {
        $objIterator = new class_array_section_iterator(class_module_news_feed::getObjectCount());
        $objIterator->setPageNumber(1);
        $objIterator->setArraySection(class_module_news_feed::getObjectList("", $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        return $this->renderList($objIterator, false, class_module_news_admin::STR_FEED_LIST);
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEditNewsFeed() {
        return $this->actionNewNewsFeed("edit");
    }


    /**
     * Show the form to create or edit a news feed
     *
     * @param string $strMode
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNewNewsFeed($strMode = "new", class_admin_formgenerator $objForm = null) {

        $objFeed = new class_module_news_feed();
        if($strMode == "edit") {
            $objFeed = new class_module_news_feed($this->getSystemid());

            if(!$objFeed->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }
        }

        if($objForm == null) {
            $objForm = $this->getFeedAdminForm($objFeed);
        }

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveFeed"));
    }


    private function getFeedAdminForm(class_module_news_feed $objFeed) {
        $objForm = new class_admin_formgenerator("feed", $objFeed);
        $objForm->generateFieldsFromObject();

        $arrNewsCats = class_module_news_category::getObjectList();
        $arrCatsDD = array();
        foreach($arrNewsCats as $objOneCat) {
            $arrCatsDD[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();
        }
        $arrCatsDD["0"] = $this->getLang("commons_all_categories");
        $objForm->getField("cat")->setArrKeyValues($arrCatsDD);

        return $objForm;
    }

    /**
     * Saves the passed values as a new category to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveFeed() {
        $objFeed = null;

        if($this->getParam("mode") == "new") {
            $objFeed = new class_module_news_feed();
        }

        else if($this->getParam("mode") == "edit") {
            $objFeed = new class_module_news_feed($this->getSystemid());
        }

        if($objFeed != null) {

            $objForm = $this->getFeedAdminForm($objFeed);
            if(!$objForm->validateForm()) {
                return $this->actionNewNewsFeed($this->getParam("mode"), $objForm);
            }

            $objForm->updateSourceObject();
            $objFeed->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listNewsFeed", ($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }


    /**
     * Returns a xml-based representation of all categories available
     * Return format:
     * <categories>
     *    <category>
     *        <title></title>
     *        <systemid></systemid>
     *    </category>
     * </categories>
     *
     * @return string
     * @xml
     */
    protected function actionListCategories() {
        $strReturn = "";
        if($this->getObjModule()->rightView()) {
            $arrCategories = class_module_news_category::getObjectList();
            $strReturn .= "<categories>\n";
            foreach($arrCategories as $objOneCategory) {
                if($objOneCategory->rightView()) {
                    $strReturn .= " <category>\n";
                    $strReturn .= "   <title>" . xmlSafeString($objOneCategory->getStrTitle()) . "</title>";
                    $strReturn .= "   <systemid>" . $objOneCategory->getSystemid() . "</systemid>";
                    $strReturn .= " </category>\n";
                }
            }
            $strReturn .= "</categories>\n";
        }
        else {
            $strReturn = "<error>" . $this->getLang("commons_error_permissions") . "</error>";
        }

        return $strReturn;
    }

    /**
     * Returns a xml-based representation of all news available.
     * In this case only a limited set of attributes is returned, namely the title and the
     * systemid of each entry.
     * Return format:
     * <newslist>
     *    <news>
     *        <title></title>
     *        <systemid></systemid>
     *    </news>
     * </newslist>
     *
     * @return string
     * @xml
     */
    protected function actionListNews() {
        $strReturn = "";
        if($this->getObjModule()->rightView()) {
            $arrNews = class_module_news_news::getObjectList();
            $strReturn .= "<newslist>\n";
            foreach($arrNews as $objOneNews) {
                if($objOneNews->rightView()) {
                    $strReturn .= " <news>\n";
                    $strReturn .= "   <title>" . xmlSafeString($objOneNews->getStrTitle()) . "</title>";
                    $strReturn .= "   <systemid>" . $objOneNews->getSystemid() . "</systemid>";
                    $strReturn .= " </news>\n";
                }
            }
            $strReturn .= "</newslist>\n";
        }
        else {
            $strReturn = "<error>" . $this->getLang("commons_error_permissions") . "</error>";
        }

        return $strReturn;
    }

    /**
     * Returns a xml-based representation of a single news.
     * Return format:
     *    <news>
     *        <title></title>
     *        <systemid></systemid>
     *        <intro></intro>
     *        <text></text>
     *        <image></image>
     *        <categories></categories>
     *        <startdate></startdate>
     *        <enddate></enddate>
     *        <archivedate></archivedate>
     *    </news>
     *
     * @return string
     * @xml
     */
    protected function actionNewsDetails() {
        $strReturn = "";
        $objNews = new class_module_news_news($this->getSystemid());
        $arrCats = class_module_news_category::getNewsMember($objNews->getSystemid());

        array_walk($arrCats, function (&$objValue) {
            $objValue = $objValue->getSystemid();
        });


        if($objNews->rightView()) {
            $strReturn .= " <news>\n";
            $strReturn .= "   <title>" . xmlSafeString($objNews->getStrTitle()) . "</title>";
            $strReturn .= "   <systemid>" . $objNews->getSystemid() . "</systemid>";
            $strReturn .= "   <intro>" . xmlSafeString($objNews->getStrIntro()) . "</intro>";
            $strReturn .= "   <text>" . xmlSafeString($objNews->getStrText()) . "</text>";
            $strReturn .= "   <image>" . xmlSafeString($objNews->getStrImage()) . "</image>";
            $strReturn .= "   <categories>" . xmlSafeString(implode(",", $arrCats)) . "</categories>";
            $strReturn .= "   <startdate>" . xmlSafeString($objNews->getObjStartDate() != null ? $objNews->getObjStartDate()->getTimeInOldStyle() : "") . "</startdate>";
            $strReturn .= "   <enddate>" . xmlSafeString($objNews->getObjEndDate() != null ? $objNews->getObjEndDate()->getTimeInOldStyle() : "") . "</enddate>";
            $strReturn .= "   <archivedate>" . xmlSafeString($objNews->getObjDateSpecial() != null ? $objNews->getObjDateSpecial()->getTimeInOldStyle() : "") . "</archivedate>";
            $strReturn .= " </news>\n";
        }
        else {
            $strReturn = "<error>" . $this->getLang("commons_error_permissions") . "</error>";
        }

        return $strReturn;
    }

    /**
     * Saves newscontent as passed by post-paras via an xml-request.
     * Params expected are: newstitle, newsintro, newsimage, newstext, categories, startdate, enddate, archivedate
     *
     * @return string
     * @xml
     */
    protected function actionUpdateNewsXml() {
        $strReturn = "";
        $objNews = new class_module_news_news($this->getSystemid());
        if($objNews->rightEdit() || $this->getSystemid() == "") {

            $arrCats = array();
            foreach(explode(",", $this->getParam("categories")) as $strCatId) {
                $arrCats[$strCatId] = "c";
            }

            $objNews->setStrTitle($this->getParam("newstitle"));
            $objNews->setStrIntro($this->getParam("newsintro"));
            $objNews->setStrImage($this->getParam("newsimage"));
            $objNews->setStrText($this->getParam("newstext"));

            if($this->getParam("startdate") > 0) {
                $objDate = new class_date($this->getParam("startdate"));
                $objNews->setObjDateStart($objDate);
            }

            if($this->getParam("enddate") > 0) {
                $objDate = new class_date($this->getParam("enddate"));
                $objNews->setObjDateEnd($objDate);
            }

            if($this->getParam("archivedate") > 0) {
                $objDate = new class_date($this->getParam("archivedate"));
                $objNews->setObjDateSpecial($objDate);
            }

            $objNews->setArrCats($arrCats);
            if($objNews->updateObjectToDb()) {
                $strReturn = "<success></success>";
            }
            else {
                $strReturn = "<error></error>";
            }

        }
        else {
            $strReturn = "<error>" . $this->getLang("commons_error_permissions") . "</error>";
        }

        return $strReturn;
    }


    /**
     * @see interface_calendarsource_admin::getArrCalendarEntries()
     */
    public function getArrCalendarEntries(class_date $objStartDate, class_date $objEndDate) {
        $arrEntries = array();

        if($this->objSession->getSession(self::STR_CALENDAR_FILTER_NEWS) != "disabled") {

            $arrNews = class_module_news_news::getObjectList("", null, null, $objStartDate, $objEndDate);

            foreach($arrNews as $objOneNews) {

                $objEntry = new class_calendarentry();
                $objEntry->setStrClass("calendarEvent calendarNews");
                $strAlt = $this->getLang("calendar_type_news");

                $strTitle = $objOneNews->getStrTitle();
                if(uniStrlen($strTitle) > 15) {
                    $strAlt = $strTitle . "<br />" . $strAlt;
                    $strTitle = uniStrTrim($strTitle, 14);
                }

                $strName = getLinkAdmin($this->arrModule["modul"], "edit", "&systemid=" . $objOneNews->getSystemid(), $strTitle, $strAlt);
                $objEntry->setStrName($strName);
                $arrEntries[] = $objEntry;
            }
        }


        return $arrEntries;
    }

    /**
     * @see interface_calendarsource_admin::getArrLegendEntries()
     */
    public function getArrLegendEntries() {
        return array($this->getLang("calendar_type_news") => "calendarEvent calendarNews");
    }

    /**
     * @see interface_calendarsource_admin::getArrFilterEntries()
     */
    public function getArrFilterEntries() {
        return array(
            self::STR_CALENDAR_FILTER_NEWS => $this->getLang("calendar_filter_news"),
        );
    }

}

