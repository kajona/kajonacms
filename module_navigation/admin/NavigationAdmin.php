<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/

namespace Kajona\Navigation\Admin;

use Kajona\Navigation\System\NavigationJStreeNodeLoader;
use Kajona\Navigation\System\NavigationPoint;
use Kajona\Navigation\System\NavigationTree;
use Kajona\Pages\System\PagesFolder;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\Admin\AdminSimple;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\Formentries\FormentryText;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemJSTreeBuilder;
use Kajona\System\System\SystemJSTreeConfig;
use Kajona\System\System\SystemModule;

/**
 * Admin-class to manage all navigations
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 *
 * @module navigation
 * @moduleId _navigation_modul_id_
 */
class NavigationAdmin extends AdminSimple implements AdminInterface {

    protected  $strPeAddon = "";

    /**
     * Constructor
     *
     */
    public function __construct() {
        parent::__construct();

        if($this->getParam("pe") == "1")
            $this->strPeAddon = "&pe=1";
    }

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * Returns a list of the current level
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList() {
        $strReturn = "";

        //Decide, whether to return the list of navigation or the layer of a navigation
        if($this->getSystemid() == "" || $this->getSystemid() == $this->getObjModule()->getSystemid()) {

            $objIterator = new ArraySectionIterator(NavigationTree::getObjectCountFiltered(null, SystemModule::getModuleIdByNr(_navigation_modul_id_)));
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setArraySection(NavigationTree::getObjectListFiltered(null, "", $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));
            return $this->renderList($objIterator);

        }
        else {

            $objIterator = new ArraySectionIterator(NavigationPoint::getObjectCountFiltered(null, $this->getSystemid()));
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setArraySection(NavigationPoint::getNaviLayer($this->getSystemid(), false, $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));
            $strReturn .= $this->renderList($objIterator, false, "naviPoints", false);

            if($this->strPeAddon == "")
                $strReturn = $this->generateTreeView($strReturn);

            return $strReturn;
        }

    }

    protected function renderLevelUpAction($strListIdentifier) {
        if($strListIdentifier == "naviPoints") {
            if($this->getSystemid() != "") {
                $objEditObject = Objectfactory::getInstance()->getObject($this->getSystemid());
                return Link::getLinkAdmin(
                    "navigation",
                    "list",
                    "&systemid=".$objEditObject->getPrevId().$this->strPeAddon,
                    $this->getLang("commons_one_level_up"),
                    $this->getLang("commons_one_level_up"),
                    "icon_treeLevelUp"
                );
            }
        }
        return parent::renderLevelUpAction($strListIdentifier);
    }


    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry) {
        $arrReturn = array();

        if($objListEntry instanceof NavigationTree) {
            if(validateSystemid($objListEntry->getStrFolderId()))
                $arrReturn[] = $this->objToolkit->listButton(getImageAdmin("icon_treeBranchOpenDisabled", $this->getLang("navigation_show_disabled")));
            else
                $arrReturn[] = $this->objToolkit->listButton(
                    Link::getLinkAdmin($this->getArrModule("modul"), "list", "&systemid=".$objListEntry->getSystemid().$this->strPeAddon, "", $this->getLang("navigation_anzeigen"), "icon_treeBranchOpen")
                );

        }

        if($objListEntry instanceof NavigationPoint) {
            $arrReturn[] = $this->objToolkit->listButton(
                Link::getLinkAdmin("navigation", "list", "&systemid=".$objListEntry->getSystemid().$this->strPeAddon, "", $this->getLang("navigationp_anzeigen"), "icon_treeBranchOpen")
            );
        }

        return $arrReturn;
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($strListIdentifier == "naviPoints") {
            if($this->getObjModule()->rightEdit()) {
                return $this->objToolkit->listButton(
                    Link::getLinkAdmin(
                        $this->getArrModule("modul"),
                        "newNaviPoint",
                        "&systemid=".$this->getSystemid().$this->strPeAddon,
                        $this->getLang("modul_anlegenpunkt"),
                        $this->getLang("modul_anlegenpunkt"),
                        "icon_new"
                    )
                );
            }
        }
        else if($strListIdentifier == "browserList")
            return "";
        else
            return parent::getNewEntryAction($strListIdentifier);

        return "";
    }


    /**
     * Renders the form to create a new entry
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        if($this->getSystemid() == "")
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "newNavi"));
    }

    /**
     * Renders the form to edit an existing entry
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $objEditObject = Objectfactory::getInstance()->getObject($this->getSystemid());
        if($objEditObject instanceof NavigationTree) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editNavi", "&systemid=".$objEditObject->getSystemid().$this->strPeAddon));
        }

        if($objEditObject instanceof NavigationPoint) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "editNaviPoint", "&systemid=".$objEditObject->getSystemid().$this->strPeAddon));
        }
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEditNavi() {
        return $this->actionNewNavi("edit");
    }

    /**
     * Creates the form to edit / create a navi
     *
     * @param string $strMode
     * @param AdminFormgenerator|null $objForm
     * @return string
     * @autoTestable
     * @permissions edit
     */
    protected function actionNewNavi($strMode = "new", AdminFormgenerator $objForm = null) {

        $objNavi = new NavigationTree();

        if($strMode == "edit") {
            $objNavi = new NavigationTree($this->getSystemid());
            if(!$objNavi->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getNaviAdminForm($objNavi);

        $objForm->addField(new FormentryHidden("", "mode"))->setStrValue($strMode);

        return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "saveNavi"));

    }


    private function getNaviAdminForm(NavigationTree $objTree) {

        $strFolderBrowser = Link::getLinkAdminDialog(
            "pages",
            "pagesFolderBrowser",
            "&form_element=navi_folder_i&folder=1",
            $this->getLang("commons_open_browser"),
            $this->getLang("commons_open_browser"),
            "icon_externalBrowser",
            $this->getLang("commons_open_browser")
        );


        $objForm = new AdminFormgenerator("navi", $objTree);

        $objFolder = new PagesFolder($objTree->getStrFolderId());

        $objForm->addDynamicField("strName");
        $objForm->addField(new FormentryText("navi", "folder_i", null))->setStrValue($objFolder->getStrName())->setBitReadonly(true)->setStrOpener($strFolderBrowser)->setStrLabel($this->getLang("navigation_folder_i"));
        $objForm->addField(new FormentryHidden("navi", "folder_i_id"))->setStrValue($objFolder->getSystemid());

        return $objForm;
    }

    /**
     * Saves or updates a navigation
     *
     * @throws Exception
     * @return string, "" in case of success
     * @permissions edit
     */
    protected function actionSaveNavi() {
        $strReturn = "";

        $objNavi = new NavigationTree();
        if($this->getParam("mode") == "edit") {
            $objNavi = new NavigationTree($this->getSystemid());
            if(!$objNavi->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        $objForm = $this->getNaviAdminForm($objNavi);
        if(!$objForm->validateForm())
            return $this->actionNewNavi($this->getParam("mode"), $objForm);

        $objForm->updateSourceObject();
        $objNavi->setStrFolderId($this->getParam("navi_folder_i_id"));

        if(!$objNavi->updateObjectToDb())
            throw new Exception("Error saving object to db", Exception::$level_ERROR);


        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul")));

        return $strReturn;
    }


    protected function actionEditNaviPoint() {
        return $this->actionNewNaviPoint("edit");
    }

    /**
     * Creates the form to edit / create a new navi-point
     *
     * @param string $strMode new || edit
     * @param AdminFormgenerator|null $objForm
     * @return string
     * @permissions edit
     */
    protected function actionNewNaviPoint($strMode = "new", AdminFormgenerator $objForm = null) {

        $objPoint = new NavigationPoint();
        if($strMode == "edit") {
            //Load Point data
            $objPoint = new NavigationPoint($this->getSystemid());
        }
        else
            $objPoint->setSystemid($this->getSystemid());

        if($objForm == null)
            $objForm = $this->getPointAdminForm($objPoint, $strMode);

        $objForm->addField(new FormentryHidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "saveNaviPoint"));
    }


    private function getPointAdminForm(NavigationPoint $objPoint) {

        $objForm = new AdminFormgenerator("point", $objPoint);

        $objForm->generateFieldsFromObject();


        return $objForm;
    }

    /**
     * Saves or updates a navi-point
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveNaviPoint() {
        $strReturn = "";
        $objPoint = new NavigationPoint();
        if($this->getParam("mode") == "edit") {
            $objPoint = new NavigationPoint($this->getSystemid());
        }

        $objForm = $this->getPointAdminForm($objPoint);
        if(!$objForm->validateForm())
            return $this->actionNewNaviPoint($this->getParam("mode"), $objForm);

        $objForm->updateSourceObject();

        $strExternalLink = $objPoint->getStrPageE();
        $strExternalLink = StringUtil::replace(_indexpath_, "_indexpath_", $strExternalLink);
        $strExternalLink = StringUtil::replace(_webpath_, "_webpath_", $strExternalLink);
        $objPoint->setStrPageE($strExternalLink);

        if($this->getParam("mode") == "new")
            $objPoint->updateObjectToDb($this->getSystemid());
        else
            $objPoint->updateObjectToDb();


        //Flush pages cache
        $this->flushCompletePagesCache();
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "systemid=".$objPoint->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
        return $strReturn;
    }


    /**
     * Invokes the deletion of navi-points
     *
     * @throws Exception
     * @return string "" in case of success
     * @permissions delete
     */
    protected function actionDelete() {
        $strReturn = "";
        //Check rights
        $objPoint = Objectfactory::getInstance()->getObject($this->getSystemid());
        $this->flushCompletePagesCache();

        $strPrevId = $objPoint->getPrevId();
        if(($objPoint instanceof NavigationPoint || $objPoint instanceof  NavigationTree) && !$objPoint->deleteObjectFromDatabase())
            throw new Exception("Error deleting object from db. Needed rights given?", Exception::$level_ERROR);

        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "systemid=".$strPrevId.($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));

        return $strReturn;
    }

    /**
     * Returns a list of available navigation-points
     *
     * @return string
     * @permissions view
     */
    protected function actionNavigationPointBrowser() {
        $strReturn = "";
        $this->setArrModuleEntry("template", "/folderview.tpl");
        //Load all navis

        $arrPoints = NavigationPoint::getNaviLayer($this->getSystemid());
        $strReturn .= $this->objToolkit->listHeader();

        $objPoint = Objectfactory::getInstance()->getObject($this->getSystemid());

        //Link one level up
        $strPrevID = $objPoint->getPrevId();
        if($strPrevID != $this->getObjModule()->getSystemid()) {
            $strAction = $this->objToolkit->listButton(
                Link::getLinkAdmin(
                    $this->getArrModule("modul"),
                    "navigationPointBrowser",
                    "&systemid=".$strPrevID."&form_element=".$this->getParam("form_element"),
                    $this->getLang("commons_one_level_up"),
                    $this->getLang("commons_one_level_up"),
                    "icon_treeLevelUp"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "..", getImageAdmin("icon_treeRoot"), $strAction);
        }
        else {
            $strAction = $this->objToolkit->listButton(
                "<a href=\"#\" title=\"".$this->getLang("navigation_point_accept")."\" rel=\"tooltip\" onclick=\"require('folderview').selectCallback([['".$this->getParam("form_element")."', ''],['".$this->getParam("form_element")."_id', '".$this->getSystemid()."']]);\">".getImageAdmin("icon_accept")."</a>"
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), ".", getImageAdmin("icon_treeLeaf"), $strAction);
        }
        if(count($arrPoints) > 0) {
            /** @var NavigationPoint $objSinglePoint */
            foreach($arrPoints as $objSinglePoint) {
                if($objSinglePoint->rightView()) {
                    $strAction = $this->objToolkit->listButton(
                        Link::getLinkAdmin(
                            $this->getArrModule("modul"),
                            "navigationPointBrowser",
                            "&systemid=".$objSinglePoint->getSystemid()."&form_element=".$this->getParam("form_element"),
                            $this->getLang("navigationp_anzeigen"),
                            $this->getLang("navigationp_anzeigen"),
                            "icon_treeBranchOpen"
                        )
                    );
                    $strAction .= $this->objToolkit->listButton(
                        "<a href=\"#\" title=\"".$this->getLang("navigation_point_accept")."\" rel=\"tooltip\" onclick=\"require('folderview').selectCallback([['".$this->getParam("form_element")."', '".$objSinglePoint->getStrName()."'],['".$this->getParam("form_element")."_id', '".$objSinglePoint->getSystemid()."']]);\">".getImageAdmin("icon_accept")."</a>"
                    );
                    $strReturn .= $this->objToolkit->simpleAdminList($objSinglePoint, $strAction);
                }
            }
        }

        $strReturn .= $this->objToolkit->listFooter();
        return $strReturn;
    }

    /**
     * Helper to generate a small path-navigation
     *
     * @return array
     */
    protected function getArrOutputNaviEntries() {
        $arrEntries = parent::getArrOutputNaviEntries();

        $arrPath = $this->getPathArray();

        foreach($arrPath as $strOneSystemid) {
            $objPoint = new NavigationPoint($strOneSystemid);
            $arrEntries[] = Link::getLinkAdmin("navigation", "list", "&systemid=".$strOneSystemid, $objPoint->getStrName(), $objPoint->getStrName());
        }

        return $arrEntries;
    }


    /**
     * Generates the code needed to render the nodes as a tree-view element.
     * The elements themselves are loaded via ajax, so only the root-node and the initial
     * folding-params are generated right here.
     *
     * @param string $strSideContent
     * @return string
     */
    private function generateTreeView($strSideContent) {
        $strReturn = "";

        //generate the array of ids to expand initially
        $arrNodesToExpand = $this->getPathArray();

        $objTreeConfig = new SystemJSTreeConfig( );
        $objTreeConfig->setStrRootNodeId($arrNodesToExpand[0]);
        $objTreeConfig->setStrNodeEndpoint(Link::getLinkAdminXml($this->getArrModule("modul"), "getChildNodes"));
        $objTreeConfig->setArrNodesToExpand($arrNodesToExpand);

        $strReturn .= $this->objToolkit->getTreeview($objTreeConfig, $strSideContent);
        return $strReturn;
    }



    /**
     * Fetches all child-nodes of the passed node.
     * Used by the tree-view in module-navigation admin view.
     *
     * @return string
     * @since 3.3.0
     * @permissions view
     * @responseType json
     */
    protected function actionGetChildNodes() {

        $objJsTreeLoader = new SystemJSTreeBuilder(
            new NavigationJStreeNodeLoader()
        );

        $arrSystemIdPath = $this->getParam(SystemJSTreeBuilder::STR_PARAM_INITIALTOGGLING);
        $bitInitialLoading = is_array($arrSystemIdPath);
        if(!$bitInitialLoading) {
            $arrSystemIdPath = array($this->getSystemid());
        }

        $arrReturn = $objJsTreeLoader->getJson($arrSystemIdPath, $bitInitialLoading, $this->getParam(SystemJSTreeBuilder::STR_PARAM_LOADALLCHILDNOES) === "true");
        return $arrReturn;
    }
}

