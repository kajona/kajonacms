<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						    *
********************************************************************************************************/

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
class class_module_search_admin extends class_admin_simple implements interface_admin
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
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "search", "", $this->getLang("search_search"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * Renders the form to create a new entry
     *
     * @param string $strMode
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNew($strMode = "new", class_admin_formgenerator $objForm = null)
    {
        $objSearch = new class_module_search_search();
        if ($strMode == "edit") {
            $objSearch = new class_module_search_search($this->getSystemid());

            if (!$objSearch->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }
        }

        if ($objForm == null) {
            $objForm = $this->getSearchAdminForm($objSearch);
        }

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(class_link::getLinkAdminHref($this->getArrModule("modul"), "save"));
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
            $objSearch = new class_module_search_search();
        }

        else if ($this->getParam("mode") == "edit") {
            $objSearch = new class_module_search_search($this->getSystemid());
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

            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "", ($this->getParam("pe") != "" ? "&peClose=1&blockAction=1" : "")));
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
        $objArraySectionIterator = new class_array_section_iterator(class_module_search_search::getObjectCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_search_search::getObjectList(false, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

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

        $objSearch = new class_module_search_search($this->getParam("systemid"));
        $objForm = $this->getSearchAdminForm($objSearch);
        $objForm->updateSourceObject();


        if($this->getParam("filtermodules") == "" && $this->getParam("search_filter_all") == "") {
            $arrNrs = array_keys($objSearch->getPossibleModulesForFilter());
            $intSearch = array_search(class_module_system_module::getModuleByName("messaging")->getIntNr(), $arrNrs);
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
        $strReturn .= $objForm->renderForm(class_link::getLinkAdminHref($this->getArrModule("modul"), "search"), class_admin_formgenerator::BIT_BUTTON_SUBMIT);


        $strCore = class_resourceloader::getInstance()->getCorePathForModule("module_search");
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

        class_carrier::getInstance()->getObjSession()->sessionClose();

        $objSearch = new class_module_search_search();

        if ($this->getParam("search_query") != "") {
            $objSearch->setStrQuery(urldecode($this->getParam("search_query")));
        }
        if ($this->getParam("filtermodules") != "") {
            $objSearch->setStrInternalFilterModules(urldecode($this->getParam("filtermodules")));
        }

        if ($this->getParam("search_changestartdate") != "") {
            $objDate = new \Kajona\System\System\Date();
            $objDate->generateDateFromParams("search_changestartdate", class_carrier::getAllParams());
            $objSearch->setObjChangeStartdate($objDate);
        }

        if ($this->getParam("search_changeenddate") != "") {
            $objDate = new \Kajona\System\System\Date();
            $objDate->generateDateFromParams("search_changeenddate", class_carrier::getAllParams());
            $objSearch->setObjChangeEnddate($objDate);
        }

        if ($this->getParam("search_formfilteruser_id") != "") {
            $objSearch->setStrFormFilterUser(urldecode($this->getParam("search_formfilteruser_id")));
        }


        $objSearchCommons = new class_module_search_commons();
        $arrResult = $objSearchCommons->doIndexedSearch($objSearch, 0, self::INT_MAX_NR_OF_RESULTS_FULLSEARCH);

        $arrMappedObjects = array_map(function(class_search_result $objSearchResult) {
            return $objSearchResult->getObjObject();
        }, $arrResult);


        $strReturn = "<content><![CDATA[";

        if(count($arrMappedObjects) > 20) {
            $strReturn .= $this->objToolkit->warningBox($this->getLang("search_reduce_hits_link"));
        }

        if(count($arrMappedObjects) > 0) {
            $strReturn .= $this->objToolkit->listHeader();
            foreach ($arrMappedObjects as $objOneObject) {
                $strReturn .= $this->objToolkit->simpleAdminList($objOneObject, $this->getActionIcons($objOneObject, "searchResultList"), 0);
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
     * @param \Kajona\System\System\Model|interface_admin_listable|\Kajona\System\System\ModelInterface $objOneIterable
     * @param string $strListIdentifier
     *
     * @return string
     */
    public function getActionIcons($objOneIterable, $strListIdentifier = "")
    {
        if ($strListIdentifier == "searchResultList") {
            //call the original module to render the action-icons
            $objAdminInstance = class_module_system_module::getModuleByName($objOneIterable->getArrModule("modul"))->getAdminInstanceOfConcreteModule();
            if ($objAdminInstance != null && $objAdminInstance instanceof class_admin_simple) {
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

        class_carrier::getInstance()->getObjSession()->sessionClose();

        $strSearchterm = "";
        if ($this->getParam("search_query") != "") {
            $strSearchterm = htmlToString(urldecode($this->getParam("search_query")), false);
        }

        $objSearch = new class_module_search_search();
        $objSearch->setStrQuery($strSearchterm);

        $arrResult = array();
        $objSearchCommons = new class_module_search_commons();
        if ($strSearchterm != "") {
            $arrResult = $objSearchCommons->doAdminSearch($objSearch, 0, self::INT_MAX_NR_OF_RESULTS_AUTOCOMPLETE);
        }

        $intSteps = 1;
        //try to load more entries if there's no hit
        while(count($arrResult) == 0 && $intSteps < 10) {
            $arrResult = $objSearchCommons->doAdminSearch($objSearch, self::INT_MAX_NR_OF_RESULTS_AUTOCOMPLETE*$intSteps, self::INT_MAX_NR_OF_RESULTS_AUTOCOMPLETE*++$intSteps);
        }

        $objSearchFunc = function (class_search_result $objA, class_search_result $objB) {
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
     * @param class_search_result[] $arrResults
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
            if ($objOneResult->getObjObject() instanceof interface_admin_listable) {
                $strIcon = $objOneResult->getObjObject()->getStrIcon();
                if (is_array($strIcon)) {
                    $strIcon = $strIcon[0];
                }
            }

            $strLink = $objOneResult->getStrPagelink();
            if ($strLink == "") {
                $strLink = class_link::getLinkAdminHref($objOneResult->getObjObject()->getArrModule("modul"), "edit", "&systemid=".$objOneResult->getStrSystemid());
            }

            $arrItem["module"] = class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $objOneResult->getObjObject()->getArrModule("modul"));
            $arrItem["systemid"] = $objOneResult->getStrSystemid();
            $arrItem["icon"] = class_adminskin_helper::getAdminImage($strIcon, "", true);
            $arrItem["score"] = $objOneResult->getStrSystemid();
            $arrItem["description"] = uniStrTrim($objOneResult->getObjObject()->getStrDisplayName(), 200);
            $arrItem["link"] = html_entity_decode($strLink);

            $arrItems[] = $arrItem;
        }

        $objResult = $arrItems;
        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($objResult);
    }


    /**
     * @param string $strSearchterm
     * @param class_search_result[] $arrResults
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
            if ($objOneResult->getObjObject() instanceof interface_admin_listable) {
                $strIcon = $objOneResult->getObjObject()->getStrIcon();
                if (is_array($strIcon)) {
                    $strIcon = $strIcon[0];
                }
            }

            $strLink = $objOneResult->getStrPagelink();
            if ($strLink == "") {
                $strLink = class_link::getLinkAdminHref($objOneResult->getObjObject()->getArrModule("modul"), "edit", "&systemid=".$objOneResult->getStrSystemid());
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
     * @param class_module_search_search $objSearch
     *
     * @return class_admin_formgenerator
     */
    public function getSearchAdminForm($objSearch)
    {

        $objForm = new class_admin_formgenerator("search", $objSearch);
        $objForm->generateFieldsFromObject();

        // Load filterable modules
        $arrFilterModules = $objSearch->getPossibleModulesForFilter();
        $objForm->getField("formfiltermodules")->setArrKeyValues($arrFilterModules);

        $objForm->addField(new class_formentry_checkbox("search", "filter_all"))
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
        $strCore = class_resourceloader::getInstance()->getCorePathForModule("module_search");
        $strJS = <<<JS
            KAJONA.admin.loader.loadFile('{$strCore}/module_search/admin/scripts/search.js', function() {
                    KAJONA.admin.search.switchFilterAllModules();
                    $('#search_filter_all').click(function() {KAJONA.admin.search.switchFilterAllModules()});

                });
JS;
        $strPlain = "<script type='text/javascript'>".$strJS."</script>";
        $objForm->addField(new class_formentry_plaintext())->setStrValue($strPlain);


        return $objForm;
    }

    /**
     * @param \Kajona\System\System\Model $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry)
    {
        if ($objListEntry instanceof class_module_search_search) {
            return array(
                $this->objToolkit->listButton(class_link::getLinkAdmin($this->getArrModule("modul"), "search", "&systemid=".$objListEntry->getSystemid(), $this->getLang("action_execute_search"), $this->getLang("action_execute_search"), "icon_lens")),
            );
        }
        else {
            return array();
        }
    }

}
