<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\News\Admin;

use Kajona\News\System\NewsCategory;
use Kajona\News\System\NewsCategoryFilter;
use Kajona\News\System\NewsNews;
use Kajona\News\System\NewsNewsFilter;
use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminFormgeneratorFilter;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\LanguagesLanguageset;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;

/**
 * Admin class of the news-module. Responsible for editing news, organizing them in categories and creating feeds
 *
 * @author sidler@mulchprod.de
 *
 * @objectListNews Kajona\News\System\NewsNews
 * @objectFilterNews Kajona\News\System\NewsNewsFilter
 * @objectNewNews Kajona\News\System\NewsNews
 * @objectEditNews Kajona\News\System\NewsNews
 * @objectEdit Kajona\News\System\NewsNews
 *
 * @objectListCategory Kajona\News\System\NewsCategory
 * @objectFilterCategory Kajona\News\System\NewsCategoryFilter
 * @objectNewCategory Kajona\News\System\NewsCategory
 * @objectEditCategory Kajona\News\System\NewsCategory
 *
 * @objectListFeed Kajona\News\System\NewsFeed
 * @objectNewFeed Kajona\News\System\NewsFeed
 * @objectEditFeed Kajona\News\System\NewsFeed
 *
 * @autoTestable listNews,newNews,listCategory,newCategory,listFeed,newFeed
 *
 * @module news
 * @moduleId _news_module_id_
 */
class NewsAdmin extends AdminEvensimpler implements AdminInterface
{
    const STR_CAT_LIST = "STR_CAT_LIST";
    const STR_NEWS_LIST = "STR_NEWS_LIST";

    const STR_CALENDAR_FILTER_NEWS = "STR_CALENDAR_FILTER_NEWS";


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->getAction() == "list") {
            $this->setAction("listNewsAndCategories");
        }

    }

    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "listNewsAndCategories", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right2", Link::getLinkAdmin($this->getArrModule("modul"), "listFeed", "", $this->getLang("modul_titel_feed"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * @param \Kajona\System\System\Model $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry)
    {
        if ($objListEntry instanceof NewsCategory) {
            return array(
                $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "listNewsAndCategories", "&filterId=".$objListEntry->getSystemid(), "", $this->getLang("kat_anzeigen"), "icon_lens"))
            );
        }

        if ($objListEntry instanceof NewsNews && $objListEntry->rightEdit()) {
            if (LanguagesLanguage::getNumberOfLanguagesAvailable() > 1) {
                return array(
                    $this->objToolkit->listButton(
                        Link::getLinkAdminDialog(
                            $this->getArrModule("modul"),
                            "editLanguageset",
                            "&systemid=".$objListEntry->getSystemid(),
                            "",
                            $this->getLang("news_languageset"),
                            "icon_language",
                            $this->getLang("languageset_addtolanguage")
                        )
                    )
                );
            }
        }

        return array();
    }

    /**
     * @param string $strAction
     * @param \Kajona\System\System\ModelInterface $objInstance
     *
     * @return string
     */
    protected function getActionNameForClass($strAction, $objInstance)
    {
        if ($strAction == "list" && ($objInstance instanceof NewsNews || $objInstance instanceof NewsCategory)) {
            return "listNewsAndCategories";
        }

        return parent::getActionNameForClass($strAction, $objInstance);
    }

    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return array|string
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        if ($strListIdentifier == NewsAdmin::STR_CAT_LIST) {
            return $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "newCategory", "", $this->getLang("commons_create_category"), $this->getLang("commons_create_category"), "icon_new"));
        } elseif ($strListIdentifier == NewsAdmin::STR_NEWS_LIST) {
            return $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "newNews", "", $this->getLang("action_new_news"), $this->getLang("action_new_news"), "icon_new"));
        }

        return parent::getNewEntryAction($strListIdentifier, $bitDialog);
    }


    /**
     * Returns a list of all categories and all news
     * The list could be filtered by categories
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionListNewsAndCategories()
    {

        /* Category Filter and List */

        /** @var  NewsCategoryFilter $objFilter */
        $objFilter = NewsCategoryFilter::getOrCreateFromSession();
        $strFilterForm = $this->renderFilter($objFilter);
        if ($strFilterForm === AdminFormgeneratorFilter::STR_FILTER_REDIRECT) {
            return "";
        }

        $strReturn = $strFilterForm;
        $objIterator = new ArraySectionIterator(NewsCategory::getObjectCountFiltered($objFilter));
        $objIterator->setIntElementsPerPage(NewsCategory::getObjectCountFiltered($objFilter));
        $objIterator->setPageNumber(1);
        $objIterator->setArraySection(NewsCategory::getObjectListFiltered($objFilter, "", $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objIterator, false, NewsAdmin::STR_CAT_LIST);


        /* News Filter and List */

        /** @var  NewsNewsFilter $objFilter */
        $objFilter = null;// NewsNewsFilter::getOrCreateFromSession();
//        $strFilterForm = $this->renderFilter($objFilter);
//        if ($strFilterForm === AdminFormgeneratorFilter::STR_FILTER_REDIRECT) {
//            return "";
//        }
//        $strReturn .= $strFilterForm;

        $objIterator = new ArraySectionIterator(NewsNews::getObjectCountFiltered($objFilter, $this->getParam("filterId")));
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(NewsNews::getObjectListFiltered($objFilter, $this->getParam("filterId"), $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objIterator, false, NewsAdmin::STR_NEWS_LIST, false, "&filterId=".$this->getParam("filterId"));

        return $strReturn;
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEditLanguageset()
    {
        $strReturn = "";
        $objNews = Objectfactory::getInstance()->getObject($this->getSystemid());
        $this->setArrModuleEntry("template", "/folderview.tpl");
        if ($objNews->rightEdit()) {

            $objLanguageset = LanguagesLanguageset::getLanguagesetForSystemid($this->getSystemid());
            if ($objLanguageset == null) {
                $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "assignToLanguageset"));
                $strReturn .= $this->objToolkit->warningBox($this->getLang("languageset_notmaintained"));
                $strReturn .= $this->objToolkit->formTextRow($objNews->getStrDisplayName());

                $arrLanguages = LanguagesLanguage::getObjectListFiltered(null);
                $arrDropdown = array();
                foreach ($arrLanguages as $objOneLanguage) {
                    $arrDropdown[$objOneLanguage->getSystemid()] = $this->getLang("lang_".$objOneLanguage->getStrName(), "languages");
                }

                $strReturn .= $this->objToolkit->formInputDropdown("languageset_language", $arrDropdown, $this->getLang("commons_language_field"));
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                $strReturn .= $this->objToolkit->formClose();
            } else {

                $objLanguage = new LanguagesLanguage($objLanguageset->getLanguageidForSystemid($this->getSystemid()));

                $strReturn .= $this->objToolkit->warningBox($this->getLang("languageset_currentlanguage", array($this->getLang("lang_".$objLanguage->getStrName(), "languages"))));

                $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_maintainlanguages"));

                $arrLanguages = LanguagesLanguage::getObjectListFiltered(null);

                $strReturn .= $this->objToolkit->listHeader();
                $intNrOfUnassigned = 0;
                $arrMaintainedLanguages = array();
                foreach ($arrLanguages as $objOneLanguage) {

                    $strNewsid = $objLanguageset->getSystemidForLanguageid($objOneLanguage->getSystemid());
                    $strActions = "";
                    if ($strNewsid != null) {
                        $arrMaintainedLanguages[] = $objOneLanguage->getSystemid();
                        $objNews = new NewsNews($strNewsid);
                        $strNewsName = $objNews->getStrTitle();
                        $strActions .= $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "removeFromLanguageset", "&systemid=".$objNews->getSystemid(), "", $this->getLang("languageset_remove"), "icon_delete"));
                        $strReturn .= $this->objToolkit->genericAdminList($objOneLanguage->getSystemid(), $this->getLang("lang_".$objOneLanguage->getStrName(), "languages").": ".$strNewsName, getImageAdmin("icon_language"), $strActions);
                    } else {
                        $intNrOfUnassigned++;
                        $strReturn .= $this->objToolkit->genericAdminList(
                            $objOneLanguage->getSystemid(),
                            $this->getLang("lang_".$objOneLanguage->getStrName(), "languages").": ".$this->getLang("languageset_news_na"),
                            AdminskinHelper::getAdminImage("icon_language"),
                            $strActions
                        );
                    }

                }
                $strReturn .= $this->objToolkit->listFooter();

                //provide a form to add further news-items
                if ($intNrOfUnassigned > 0) {
                    $strReturn .= $this->objToolkit->formHeadline($this->getLang("languageset_addnewstolanguage"));

                    $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "addNewsToLanguageset"));
                    $arrLanguages = LanguagesLanguage::getObjectListFiltered(null);
                    $arrDropdown = array();
                    foreach ($arrLanguages as $objOneLanguage) {
                        if (!in_array($objOneLanguage->getSystemid(), $arrMaintainedLanguages)) {
                            $arrDropdown[$objOneLanguage->getSystemid()] = $this->getLang("lang_".$objOneLanguage->getStrName(), "languages");
                        }
                    }

                    $strReturn .= $this->objToolkit->formInputDropdown("languageset_language", $arrDropdown, $this->getLang("commons_language_field"), array_keys($arrDropdown)[0]);


                    $arrNews = NewsNews::getObjectListFiltered();
                    $arrDropdown = array();
                    foreach ($arrNews as $objOneNews) {
                        if (LanguagesLanguageset::getLanguagesetForSystemid($objOneNews->getSystemid()) == null) {
                            $arrDropdown[$objOneNews->getSystemid()] = $objOneNews->getStrTitle();
                        }
                    }

                    $strReturn .= $this->objToolkit->formInputDropdown("languageset_news", $arrDropdown, $this->getLang("languageset_news"), array_keys($arrDropdown)[0]);

                    $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                    $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                    $strReturn .= $this->objToolkit->formClose();
                }
            }
        } else {
            $strReturn .= $this->getLang("commons_error_permissions");
        }

        return $strReturn;
    }


    /**
     * @return void
     */
    protected function actionAddNewsToLanguageset()
    {
        $objNews = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objNews->rightEdit()) {
            $objLanguageset = LanguagesLanguageset::getLanguagesetForSystemid($this->getSystemid());
            //load the languageset for the current systemid
            $objTargetLanguage = new LanguagesLanguage($this->getParam("languageset_language"));
            if ($objLanguageset != null && $objTargetLanguage->getStrName() != "") {
                $objLanguageset->setSystemidForLanguageid($this->getParam("languageset_news"), $objTargetLanguage->getSystemid());
            }

            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editLanguageset", "&systemid=".$this->getSystemid()));
        }
    }

    /**
     * @return void
     */
    protected function actionAssignToLanguageset()
    {
        $objNews = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objNews->rightEdit()) {
            $objLanguageset = LanguagesLanguageset::getLanguagesetForSystemid($this->getSystemid());
            $objTargetLanguage = new LanguagesLanguage($this->getParam("languageset_language"));
            if ($objLanguageset == null && $objTargetLanguage->getStrName() != "") {
                $objLanguageset = new LanguagesLanguageset();
                $objLanguageset->setSystemidForLanguageid($this->getSystemid(), $objTargetLanguage->getSystemid());
            }

            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editLanguageset", "&systemid=".$this->getSystemid()));
        }
    }

    /**
     * @return void
     */
    protected function actionRemoveFromLanguageset()
    {
        $objNews = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objNews->rightEdit()) {
            $objLanguageset = LanguagesLanguageset::getLanguagesetForSystemid($this->getSystemid());
            if ($objLanguageset != null) {
                $objLanguageset->removeSystemidFromLanguageeset($this->getSystemid());
            }

            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editLanguageset", "&systemid=".$this->getSystemid()));
        }
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
     */
    protected function actionListCategories()
    {
        $strReturn = "";
        if ($this->getObjModule()->rightView()) {
            /** @var NewsCategory[] $arrCategories */
            $arrCategories = NewsCategory::getObjectListFiltered();
            $strReturn .= "<categories>\n";
            foreach ($arrCategories as $objOneCategory) {
                if ($objOneCategory->rightView()) {
                    $strReturn .= " <category>\n";
                    $strReturn .= "   <title>".xmlSafeString($objOneCategory->getStrTitle())."</title>";
                    $strReturn .= "   <systemid>".$objOneCategory->getSystemid()."</systemid>";
                    $strReturn .= " </category>\n";
                }
            }
            $strReturn .= "</categories>\n";
        } else {
            $strReturn = "<error>".$this->getLang("commons_error_permissions")."</error>";
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
     */
    protected function actionListNews()
    {
        $strReturn = "";
        if ($this->getObjModule()->rightView()) {
            $arrNews = NewsNews::getObjectListFiltered();
            $strReturn .= "<newslist>\n";
            foreach ($arrNews as $objOneNews) {
                if ($objOneNews->rightView()) {
                    $strReturn .= " <news>\n";
                    $strReturn .= "   <title>".xmlSafeString($objOneNews->getStrTitle())."</title>";
                    $strReturn .= "   <systemid>".$objOneNews->getSystemid()."</systemid>";
                    $strReturn .= " </news>\n";
                }
            }
            $strReturn .= "</newslist>\n";
        } else {
            $strReturn = "<error>".$this->getLang("commons_error_permissions")."</error>";
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
     */
    protected function actionNewsDetails()
    {
        $strReturn = "";
        $objNews = new NewsNews($this->getSystemid());
        $arrCats = NewsCategory::getNewsMember($objNews->getSystemid());

        array_walk($arrCats, function (NewsCategory &$objValue) {
            $objValue = $objValue->getSystemid();
        });


        if ($objNews->rightView()) {
            $strReturn .= " <news>\n";
            $strReturn .= "   <title>".xmlSafeString($objNews->getStrTitle())."</title>";
            $strReturn .= "   <systemid>".$objNews->getSystemid()."</systemid>";
            $strReturn .= "   <intro>".xmlSafeString($objNews->getStrIntro())."</intro>";
            $strReturn .= "   <text>".xmlSafeString($objNews->getStrText())."</text>";
            $strReturn .= "   <image>".xmlSafeString($objNews->getStrImage())."</image>";
            $strReturn .= "   <categories>".xmlSafeString(implode(",", $arrCats))."</categories>";
            $strReturn .= "   <startdate>".xmlSafeString($objNews->getObjStartDate() != null ? $objNews->getObjStartDate()->getTimeInOldStyle() : "")."</startdate>";
            $strReturn .= "   <enddate>".xmlSafeString($objNews->getObjEndDate() != null ? $objNews->getObjEndDate()->getTimeInOldStyle() : "")."</enddate>";
            $strReturn .= "   <archivedate>".xmlSafeString($objNews->getObjDateSpecial() != null ? $objNews->getObjDateSpecial()->getTimeInOldStyle() : "")."</archivedate>";
            $strReturn .= " </news>\n";
        } else {
            $strReturn = "<error>".$this->getLang("commons_error_permissions")."</error>";
        }

        return $strReturn;
    }

    /**
     * Saves newscontent as passed by post-paras via an xml-request.
     * Params expected are: newstitle, newsintro, newsimage, newstext, categories, startdate, enddate, archivedate
     *
     * @return string
     */
    protected function actionUpdateNewsXml()
    {
        $objNews = new NewsNews($this->getSystemid());
        if ($objNews->rightEdit() || $this->getSystemid() == "") {

            $arrCats = array();
            foreach (explode(",", $this->getParam("categories")) as $strCatId) {
                $arrCats[$strCatId] = "c";
            }

            $objNews->setStrTitle($this->getParam("newstitle"));
            $objNews->setStrIntro($this->getParam("newsintro"));
            $objNews->setStrImage($this->getParam("newsimage"));
            $objNews->setStrText($this->getParam("newstext"));

            if ($this->getParam("startdate") > 0) {
                $objDate = new \Kajona\System\System\Date($this->getParam("startdate"));
                $objNews->setObjDateStart($objDate);
            }

            if ($this->getParam("enddate") > 0) {
                $objDate = new \Kajona\System\System\Date($this->getParam("enddate"));
                $objNews->setObjDateEnd($objDate);
            }

            if ($this->getParam("archivedate") > 0) {
                $objDate = new \Kajona\System\System\Date($this->getParam("archivedate"));
                $objNews->setObjDateSpecial($objDate);
            }

            $objNews->setArrCats($arrCats);
            if ($objNews->updateObjectToDb()) {
                $strReturn = "<success></success>";
            } else {
                $strReturn = "<error></error>";
            }

        } else {
            $strReturn = "<error>".$this->getLang("commons_error_permissions")."</error>";
        }

        return $strReturn;
    }

}

