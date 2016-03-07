<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						    *
********************************************************************************************************/

namespace Kajona\Search\Admin;
use Kajona\Search\System\SearchCommons;
use Kajona\Search\System\SearchResult;
use Kajona\Search\System\SearchSearch;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\Admin\AdminSimple;
use Kajona\System\Admin\Formentries\FormentryCheckbox;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\Formentries\FormentryPlaintext;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\SystemModule;


/**
 * Portal-class of the search.
 * Serves xml-requests, e.g. generates search results
 *
 * @package module_search
 * @author sidler@mulchprod.de
 *
 * @module search
 * @moduleId _search_module_id_
 */
class SearchAdmin extends AdminSimple implements AdminInterface
{

    /**
     * The maximum number of records to return on xml/json requests
     */
    const INT_MAX_NR_OF_RESULTS_AUTOCOMPLETE = 30;

    const INT_MAX_NR_OF_RESULTS_FULLSEARCH = 100;

    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "search", "", $this->getLang("search_search"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * Renders the form to create a new entry
     *
     * @param string $strMode
     * @param AdminFormgenerator $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNew($strMode = "new", AdminFormgenerator $objForm = null)
    {
        $objSearch = new SearchSearch();
        if ($strMode == "edit") {
            $objSearch = new SearchSearch($this->getSystemid());

            if (!$objSearch->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }
        }

        if ($objForm == null) {
            $objForm = $this->getSearchAdminForm($objSearch);
        }

        $objForm->addField(new FormentryHidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "save"));
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit()
    {
        return $this->actionNew("edit");
    }

    /**
     * Saves the passed values as a new category to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSave()
    {
        $objSearch = null;

        if ($this->getParam("mode") == "new") {
            $objSearch = new SearchSearch();
        }

        else if ($this->getParam("mode") == "edit") {
            $objSearch = new SearchSearch($this->getSystemid());
        }

        if ($objSearch != null) {
            $objForm = $this->getSearchAdminForm($objSearch);

            if (!$objForm->validateForm()) {
                return $this->actionNew($this->getParam("mode"), $objForm);
            }

            $objForm->updateSourceObject();
            if ($this->getParam("search_filter_all") != "") {
                $objSearch->setStrInternalFilterModules("-1");
            }

            $objSearch->updateObjectToDb();

            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "", ($this->getParam("pe") != "" ? "&peClose=1&blockAction=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }

    /**
     * Renders the general list of records
     *
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList()
    {
        $objArraySectionIterator = new ArraySectionIterator(SearchSearch::getObjectCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(SearchSearch::getObjectList(false, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
    }

    /**
     * Renders the search form with results
     *
     * @permissions view
     * @return string
     * @autoTestable
     */
    protected function actionSearch()
    {

        $strReturn = "";

        $objSearch = new SearchSearch($this->getParam("systemid"));
        $objForm = $this->getSearchAdminForm($objSearch);
        $objForm->updateSourceObject();


        if($this->getParam("filtermodules") == "" && $this->getParam("search_filter_all") == "") {
            $arrNrs = array_keys($objSearch->getPossibleModulesForFilter());
            $intSearch = array_search(SystemModule::getModuleByName("messaging")->getIntNr(), $arrNrs);
            if($intSearch !== false) {
                unset($arrNrs[$intSearch]);
            }

            $objSearch->setArrFormFilterModules($arrNrs);
        }

        if ($this->getParam("filtermodules") != "") {
            $objSearch->setStrInternalFilterModules($this->getParam("filtermodules"));
        }

        if ($this->getParam("search_filter_all") != "") {
            $objSearch->setStrInternalFilterModules("-1");
        }

        // Search Form
        $objForm = $this->getSearchAdminForm($objSearch);

        $objForm->setStrOnSubmit('KAJONA.admin.search.triggerFullSearch(); return false;');
        $strReturn .= $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "search"), AdminFormgenerator::BIT_BUTTON_SUBMIT);


        $strCore = Resourceloader::getInstance()->getCorePathForModule("module_search");
        $strReturn .= "

        <script type=\"text/javascript\">
        KAJONA.admin.loader.loadFile('{$strCore}/module_search/admin/scripts/search.js', function() {
            KAJONA.admin.search.triggerFullSearch();
        });


        </script>";
        $strReturn .= "<div id=\"search_container\" ></div>";

        return $strReturn;




    }

    /**
     * Decoupled rendering of search results
     * @permissions view
     * @xml
     */
    public function actionRenderSearch() {

        Carrier::getInstance()->getObjSession()->sessionClose();

        $objSearch = new SearchSearch();

        if ($this->getParam("search_query") != "") {
            $objSearch->setStrQuery(urldecode($this->getParam("search_query")));
        }
        if ($this->getParam("filtermodules") != "") {
            $objSearch->setStrInternalFilterModules(urldecode($this->getParam("filtermodules")));
        }

        if ($this->getParam("search_changestartdate") != "") {
            $objDate = new \Kajona\System\System\Date();
            $objDate->generateDateFromParams("search_changestartdate", Carrier::getAllParams());
            $objSearch->setObjChangeStartdate($objDate);
        }

        if ($this->getParam("search_changeenddate") != "") {
            $objDate = new \Kajona\System\System\Date();
            $objDate->generateDateFromParams("search_changeenddate", Carrier::getAllParams());
            $objSearch->setObjChangeEnddate($objDate);
        }

        if ($this->getParam("search_formfilteruser_id") != "") {
            $objSearch->setStrFormFilterUser(urldecode($this->getParam("search_formfilteruser_id")));
        }


        $objSearchCommons = new SearchCommons();
        $arrResult = $objSearchCommons->doIndexedSearch($objSearch, 0, self::INT_MAX_NR_OF_RESULTS_FULLSEARCH);

        $arrMappedObjects = array_map(function(SearchResult $objSearchResult) {
            return $objSearchResult->getObjObject();
        }, $arrResult);


        $strReturn = "<content><![CDATA[";

        if(count($arrMappedObjects) > 20) {
            $strReturn .= $this->objToolkit->warningBox($this->getLang("search_reduce_hits_link"));
        }

        if(count($arrMappedObjects) > 0) {
            $strReturn .= $this->objToolkit->listHeader();
            foreach ($arrMappedObjects as $objOneObject) {
                $strReturn .= $this->objToolkit->simpleAdminList($objOneObject, $this->getActionIcons($objOneObject, "searchResultList"));
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            $strReturn .= $this->getLang("commons_list_empty");
        }


        $strReturn .= "]]></content>";

        return $strReturn;
    }


    /**
     * @param Model|AdminListableInterface|ModelInterface $objOneIterable
     * @param string $strListIdentifier
     *
     * @return string
     */
    public function getActionIcons($objOneIterable, $strListIdentifier = "")
    {
        if ($strListIdentifier == "searchResultList") {
            //call the original module to render the action-icons
            $objAdminInstance = SystemModule::getModuleByName($objOneIterable->getArrModule("modul"))->getAdminInstanceOfConcreteModule();
            if ($objAdminInstance != null && $objAdminInstance instanceof AdminSimple) {
                return $objAdminInstance->getActionIcons($objOneIterable);
            }
        }

        return parent::getActionIcons($objOneIterable, $strListIdentifier);
    }


    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return array|string
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        if ($strListIdentifier != "searchResultList") {
            return parent::getNewEntryAction($strListIdentifier, $bitDialog);
        }
    }

    /**
     * Searches for a passed string
     *
     * @return string
     * @permissions view
     * @xml
     */
    protected function actionSearchXml()
    {
        $strReturn = "";

        Carrier::getInstance()->getObjSession()->sessionClose();

        $strSearchterm = "";
        if ($this->getParam("search_query") != "") {
            $strSearchterm = htmlToString(urldecode($this->getParam("search_query")), false);
        }

        $objSearch = new SearchSearch();
        $objSearch->setStrQuery($strSearchterm);

        $arrResult = array();
        $objSearchCommons = new SearchCommons();
        if ($strSearchterm != "") {
            $arrResult = $objSearchCommons->doAdminSearch($objSearch, 0, self::INT_MAX_NR_OF_RESULTS_AUTOCOMPLETE);
        }

        $intSteps = 1;
        //try to load more entries if there's no hit
        while(count($arrResult) == 0 && $intSteps < 10) {
            $arrResult = $objSearchCommons->doAdminSearch($objSearch, self::INT_MAX_NR_OF_RESULTS_AUTOCOMPLETE*$intSteps, self::INT_MAX_NR_OF_RESULTS_AUTOCOMPLETE*++$intSteps);
        }

        $objSearchFunc = function (SearchResult $objA, SearchResult $objB) {
            //first by module, second by score
            if ($objA->getObjObject() instanceof \Kajona\System\System\Model && $objB->getObjObject() instanceof \Kajona\System\System\Model) {
                $intCmp = strcmp($objA->getObjObject()->getArrModule("modul"), $objB->getObjObject()->getArrModule("modul"));

                if ($intCmp != 0) {
                    return $intCmp;
                }
                else {
                    return $objA->getIntScore() < $objB->getIntScore();
                }
            }
            //fallback: score only
            return $objA->getIntScore() < $objB->getIntScore();
        };

        uasort($arrResult, $objSearchFunc);

        if ($this->getParam("asJson") != "") {
            $strReturn .= $this->createSearchJson($strSearchterm, $arrResult);
        }
        else {
            $strReturn .= $this->createSearchXML($strSearchterm, $arrResult);
        }

        return $strReturn;
    }

    /**
     * @param string $strSearchterm
     * @param SearchResult[] $arrResults
     *
     * @return string
     */
    private function createSearchJson($strSearchterm, $arrResults)
    {

        $arrItems = array();
        foreach ($arrResults as $objOneResult) {
            $arrItem = array();
            //create a correct link
            if ($objOneResult->getObjObject() == null || !$objOneResult->getObjObject()->rightView()) {
                continue;
            }

            $strIcon = "";
            if ($objOneResult->getObjObject() instanceof AdminListableInterface) {
                $strIcon = $objOneResult->getObjObject()->getStrIcon();
                if (is_array($strIcon)) {
                    $strIcon = $strIcon[0];
                }
            }

            $strLink = $objOneResult->getStrPagelink();
            if ($strLink == "") {
                $strLink = Link::getLinkAdminHref($objOneResult->getObjObject()->getArrModule("modul"), "edit", "&systemid=".$objOneResult->getStrSystemid());
            }

            $arrItem["module"] = Carrier::getInstance()->getObjLang()->getLang("modul_titel", $objOneResult->getObjObject()->getArrModule("modul"));
            $arrItem["systemid"] = $objOneResult->getStrSystemid();
            $arrItem["icon"] = AdminskinHelper::getAdminImage($strIcon, "", true);
            $arrItem["score"] = $objOneResult->getStrSystemid();
            $arrItem["description"] = uniStrTrim($objOneResult->getObjObject()->getStrDisplayName(), 200);
            $arrItem["link"] = html_entity_decode($strLink);

            $arrItems[] = $arrItem;
        }

        $objResult = $arrItems;
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
        return json_encode($objResult);
    }


    /**
     * @param string $strSearchterm
     * @param SearchResult[] $arrResults
     *
     * @return string
     */
    private function createSearchXML($strSearchterm, $arrResults)
    {
        $strReturn = "";

        $strReturn .=
            "<search>\n"
            ."  <searchterm>".xmlSafeString($strSearchterm)."</searchterm>\n"
            ."  <nrofresults>".count($arrResults)."</nrofresults>\n";


        //And now all results
        $strReturn .= "    <resultset>\n";
        foreach ($arrResults as $objOneResult) {

            //create a correct link
            if ($objOneResult->getObjObject() == null || !$objOneResult->getObjObject()->rightView()) {
                continue;
            }

            $strIcon = "";
            if ($objOneResult->getObjObject() instanceof AdminListableInterface) {
                $strIcon = $objOneResult->getObjObject()->getStrIcon();
                if (is_array($strIcon)) {
                    $strIcon = $strIcon[0];
                }
            }

            $strLink = $objOneResult->getStrPagelink();
            if ($strLink == "") {
                $strLink = Link::getLinkAdminHref($objOneResult->getObjObject()->getArrModule("modul"), "edit", "&systemid=".$objOneResult->getStrSystemid());
            }

            $strReturn .=
                "        <item>\n"
                ."            <systemid>".$objOneResult->getStrSystemid()."</systemid>\n"
                ."            <icon>".xmlSafeString($strIcon)."</icon>\n"
                ."            <score>".$objOneResult->getIntHits()."</score>\n"
                ."            <description>".xmlSafeString(uniStrTrim($objOneResult->getObjObject()->getStrDisplayName(), 200))."</description>\n"
                ."            <link>".xmlSafeString($strLink)."</link>\n"
                ."        </item>\n";
        }

        $strReturn .= "    </resultset>\n";
        $strReturn .= "</search>";
        return $strReturn;
    }

    /**
     * @param SearchSearch $objSearch
     *
     * @return AdminFormgenerator
     */
    public function getSearchAdminForm($objSearch)
    {

        $objForm = new AdminFormgenerator("search", $objSearch);
        $objForm->generateFieldsFromObject();

        // Load filterable modules
        $arrFilterModules = $objSearch->getPossibleModulesForFilter();
        $objForm->getField("formfiltermodules")->setArrKeyValues($arrFilterModules);

        $objForm->addField(new FormentryCheckbox("search", "filter_all"))
            ->setStrLabel($this->getLang("select_all"))
            ->setStrValue($objSearch->getStrInternalFilterModules() == "-1" || $objSearch->getStrInternalFilterModules() == "");
        $objForm->setFieldToPosition("search_filter_all", 3);

        $bitVisible = $objSearch->getObjChangeEnddate() != null || $objSearch->getObjChangeStartdate() != null;

        $objForm->setStrHiddenGroupTitle($this->getLang("form_additionalheader"));
        $objForm->addFieldToHiddenGroup($objForm->getField("formfiltermodules"));
        $objForm->addFieldToHiddenGroup($objForm->getField("formfilteruser"));
        $objForm->addFieldToHiddenGroup($objForm->getField("search_filter_all"));
        $objForm->addFieldToHiddenGroup($objForm->getField("changestartdate"));
        $objForm->addFieldToHiddenGroup($objForm->getField("changeenddate"));
        $objForm->setBitHiddenElementsVisible($bitVisible);


        //add js-code for enabling and disabling multiselect box for modules
        $strCore = Resourceloader::getInstance()->getCorePathForModule("module_search");
        $strJS = <<<JS
            KAJONA.admin.loader.loadFile('{$strCore}/module_search/admin/scripts/search.js', function() {
                    KAJONA.admin.search.switchFilterAllModules();
                    $('#search_filter_all').click(function() {KAJONA.admin.search.switchFilterAllModules()});

                });
JS;
        $strPlain = "<script type='text/javascript'>".$strJS."</script>";
        $objForm->addField(new FormentryPlaintext())->setStrValue($strPlain);


        return $objForm;
    }

    /**
     * @param \Kajona\System\System\Model $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry)
    {
        if ($objListEntry instanceof SearchSearch) {
            return array(
                $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "search", "&systemid=".$objListEntry->getSystemid(), $this->getLang("action_execute_search"), $this->getLang("action_execute_search"), "icon_lens")),
            );
        }
        else {
            return array();
        }
    }

}
