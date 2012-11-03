<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
 */
class class_module_search_admin extends class_admin_simple implements interface_admin {

    private static $INT_MAX_NR_OF_RESULTS = 30;

    /**
     * Constructor

     */
    public function __construct() {
        $this->setArrModuleEntry("modul", "search");
        $this->setArrModuleEntry("moduleId", _search_module_id_);
        parent::__construct();
    }

    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "search", "", $this->getLang("search_search"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("view", getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->getArrModule("modul"), "new", "", $this->getLang("action_new"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=" . $this->arrModule["modul"], $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));

        return $arrReturn;
    }

    /**
     * Renders the form to create a new entry
     * @return string
     * @permissions edit
     */
    protected function actionNew($strMode = "new", class_admin_formgenerator $objForm = null)
    {
        $objSearch = new class_module_search_search();
        if($strMode == "edit") {
            $objSearch = new class_module_search_search($this->getSystemid());

            if(!$objSearch->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getSearchAdminForm($objSearch);

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "save"));
    }

    /**
     * Renders the form to edit an existing entry
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
    protected function actionSave() {
        $objSearch = null;

        if($this->getParam("mode") == "new")
            $objSearch = new class_module_search_search();

        else if($this->getParam("mode") == "edit")
            $objSearch = new class_module_search_search($this->getSystemid());

        if($objSearch != null) {
            $objForm = $this->getSearchAdminForm($objSearch);
            if(!$objForm->validateForm())
                return $this->actionNew($this->getParam("mode"), $objForm);

            $objForm->updateSourceObject();
            $objSearch->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "", ($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }

    /**
     * Renders the general list of records
     * @return string
     * @permissions view
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
     * @permissions view
     * @return string
     */
    protected function actionSearch() {

        $strReturn = "";

        $objSearch = new class_module_search_search($this->getParam("systemid"));

        if($this->getParam("search_query") != "") {
            $objSearch->setStrQuery(htmlToString(urldecode($this->getParam("search_query")), true));
        }
        if($this->getParam("search_filter_modules") != "") {
            $objSearch->setStrFilterModules(htmlToString(urldecode($this->getParam("search_filter_modules")), true));
        }

        // Search Form
        $objForm = $this->getSearchAdminForm($objSearch);
        $strReturn .= $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "search"), class_admin_formgenerator::BIT_BUTTON_SUBMIT);

        // Execute Search
        $arrResult = array();
        $objSearchCommons = new class_module_search_commons();
        if($objSearch->getStrQuery() != "") {
            $arrResult = $objSearchCommons->doAdminSearch($objSearch);
        }

        $objArrayIterator = new class_array_iterator($arrResult);
        $objArrayIterator->getElementsOnPage((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));

        $objArraySectionIterator = new class_array_section_iterator(count($arrResult));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objArrayIterator->getElementsOnPage((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1)));

        $arrResult = $objArraySectionIterator->getArrayExtended(true);

        $arrObjects = array();
        /** @var $objSearchResult class_search_result */
        foreach ($arrResult as $objSearchResult){
            $arrObjects[] =  $objSearchResult->getObjObject();
        }

        $objArrayIterator->setArrElements($arrObjects);
        $objArraySectionIterator->setArraySection($objArrayIterator->getElementsOnPage(1));

        $strReturn.= $this->renderList($objArraySectionIterator, false, "searchResultList", false, "&search_query=".$objSearch->getStrQuery());
        return $strReturn;
    }

    public function getActionIcons($objOneIterable, $strListIdentifier = "") {
        if($strListIdentifier == "searchResultList") {
            //call the original module to render the action-icons
            $objAdminInstance = class_module_system_module::getModuleByName($objOneIterable->getArrModule("modul"))->getAdminInstanceOfConcreteModule();
            if($objAdminInstance != null && $objAdminInstance instanceof class_admin_simple) {
                return $objAdminInstance->getActionIcons($objOneIterable);
            }
        }

        return parent::getActionIcons($objOneIterable, $strListIdentifier);
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        if ($strListIdentifier != "searchResultList")
            return parent::getNewEntryAction($strListIdentifier, $bitDialog);
    }

    /**
     * Searches for a passed string
     *
     * @return string
     * @permissions view
     * @xml
     */
    protected function actionSearchXml() {
        $strReturn = "";

        $strSearchterm = "";
        if($this->getParam("search_query") != "") {
            $strSearchterm = htmlToString(urldecode($this->getParam("search_query")), true);
        }

        $objSearch = new class_module_search_search();
        $objSearch->setStrQuery($strSearchterm);

        $arrResult = array();
        $objSearchCommons = new class_module_search_commons();
        if($strSearchterm != "") {
            $arrResult = $objSearchCommons->doAdminSearch($objSearch);
        }

        if($this->getParam("asJson") != "")
            $strReturn .= $this->createSearchJson($strSearchterm, $arrResult);
        else
            $strReturn .= $this->createSearchXML($strSearchterm, $arrResult);

        return $strReturn;
    }

    /**
     * @param $strSearchterm
     * @param class_search_result[] $arrResults
     *
     * @return string
     */
    private function createSearchJson($strSearchterm, $arrResults) {

        $arrItems = array();
        $intI = 0;
        foreach($arrResults as $objOneResult) {
            $arrItem = array();
            //create a correct link
            if($objOneResult->getObjObject() == null || !$objOneResult->getObjObject()->rightView())
                continue;

            if(++$intI > self::$INT_MAX_NR_OF_RESULTS)
                break;

            $strIcon = "";
            if($objOneResult->getObjObject() instanceof interface_admin_listable) {
                $strIcon = $objOneResult->getObjObject()->getStrIcon();
                if(is_array($strIcon))
                    $strIcon = $strIcon[0];
            }

            $strLink = $objOneResult->getStrPagelink();
            if($strLink == "")
                $strLink = getLinkAdminHref($objOneResult->getObjObject()->getArrModule("modul"), "edit", "&systemid=".$objOneResult->getStrSystemid()."&source=search");

            $arrItem["module"] = class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $objOneResult->getObjObject()->getArrModule("modul"));
            $arrItem["systemid"] = $objOneResult->getStrSystemid();
            $arrItem["icon"] = _skinwebpath_."/pics/".$strIcon;
            $arrItem["score"] = $objOneResult->getStrSystemid();
            $arrItem["description"] = uniStrTrim($objOneResult->getObjObject()->getStrDisplayName(), 200);
            $arrItem["link"] = html_entity_decode($strLink);

            $arrItems[] = $arrItem;
        }

        $objResult = $arrItems;
        class_response_object::getInstance()->setStResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($objResult);
    }


    /**
     * @param $strSearchterm
     * @param class_search_result[] $arrResults
     *
     * @return string
     */
    private function createSearchXML($strSearchterm, $arrResults) {
        $strReturn = "";

        $strReturn .=
            "<search>\n"
                ."  <searchterm>".xmlSafeString($strSearchterm)."</searchterm>\n"
                ."  <nrofresults>".count($arrResults)."</nrofresults>\n";


        //And now all results
        $intI = 0;
        $strReturn .= "    <resultset>\n";
        foreach($arrResults as $objOneResult) {

            //create a correct link
            if($objOneResult->getObjObject() == null || !$objOneResult->getObjObject()->rightView())
                continue;

            if(++$intI > self::$INT_MAX_NR_OF_RESULTS)
                break;

            $strIcon = "";
            if($objOneResult->getObjObject() instanceof interface_admin_listable) {
                $strIcon = $objOneResult->getObjObject()->getStrIcon();
                if(is_array($strIcon))
                    $strIcon = $strIcon[0];
            }

            $strLink = $objOneResult->getStrPagelink();
            if($strLink == "")
                $strLink = getLinkAdminHref($objOneResult->getObjObject()->getArrModule("modul"), "edit", "&systemid=".$objOneResult->getStrSystemid()."&source=search");

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
    public function getSearchAdminForm($objSearch){

        $objForm = new class_admin_formgenerator("search", $objSearch);
        $objForm->generateFieldsFromObject();

        // Load filterable modules
        $arrModules = $this->objDB->getPArray("SELECT DISTINCT system_module_nr as module_nr,  module_name
            FROM kajona_system INNER JOIN kajona_system_module
            on system_module_nr = module_nr
            ORDER BY module_name ASC", array());


        foreach ($arrModules as $arrEntry){
            $arrFilterModules[$arrEntry["module_nr"]] = $arrEntry["module_name"];
        }


        $arrFilterModules = array_merge(array(implode(",",array_keys( $arrFilterModules))=>$this->getLang("select_all")), $arrFilterModules);

        $objForm->addField(new class_formentry_dropdown("search", "filter_modules"))->setArrKeyValues($arrFilterModules)->setStrValue($this->getParam("search_filter_modules"))->setStrLabel($this->getLang("search_modules"));

        return $objForm;
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_search_search) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "search", "&systemid=" . $objListEntry->getSystemid(), $this->getLang("actionExecuteSearch"), $this->getLang("actionExecuteSearch"), "icon_lens.png")),
            );
        }
        else {
            return array();
        }
    }

}
