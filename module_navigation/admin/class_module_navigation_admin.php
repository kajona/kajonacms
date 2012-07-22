<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/


/**
 * Admin-class to manage all navigations
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 */
class class_module_navigation_admin extends class_admin_simple implements interface_admin {

    private $strPeAddon = "";

    /**
     * Constructor
     *
     */
	public function __construct() {
        $this->setArrModuleEntry("modul", "navigation");
        $this->setArrModuleEntry("moduleId", _navigation_modul_id_);
		parent::__construct();

        if($this->getParam("pe") == "1")
            $this->strPeAddon = "&pe=1";
	}

    public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newNavi", "", $this->getLang("module_action_new"), "", "", true, "adminnavi"));
		return $arrReturn;
	}

    protected final function validateForm() {
        $arrReturn = array();

        if($this->getAction() == "saveNaviPoint") {
            if($this->getParam("navigation_folder_i_id") != "" && $this->getParam("navigation_page_i") != "")
                $this->arrValidationErrors["navigation_folder_i_id"] = $this->getLang("error_folder_and_page");
        }

        parent::validateForm();

        $this->arrValidationErrors = array_merge($this->arrValidationErrors, $arrReturn);
        return (count($this->arrValidationErrors) == 0);
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
        if($this->getSystemid() == "" || $this->getSystemid() == $this->getModuleSystemid($this->arrModule["modul"])) {

            $objIterator = new class_array_section_iterator(class_module_navigation_tree::getAllNavisCount());
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setArraySection(class_module_navigation_tree::getAllNavis($objIterator->calculateStartPos(), $objIterator->calculateEndPos()));
            return $this->renderList($objIterator);

        }
        else {

            $objIterator = new class_array_section_iterator(class_module_navigation_point::getNaviLayerCount($this->getSystemid()));
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setIntElementsPerPage($objIterator->getNumberOfElements());
            $objIterator->setArraySection(class_module_navigation_point::getNaviLayer($this->getSystemid()));
            $strReturn .= $this->renderList($objIterator, true, "naviPoints");

            if($this->strPeAddon == "")
                $strReturn = $this->generateTreeView($strReturn);

            return $strReturn;
        }

	}

    protected function renderLevelUpAction($strListIdentifier) {
        if($strListIdentifier == "naviPoints") {
            if($this->getSystemid() != "") {
                $objEditObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
                return getLinkAdmin("navigation", "list", "&systemid=".$objEditObject->getPrevId().$this->strPeAddon, $this->getLang("commons_one_level_up"), $this->getLang("commons_one_level_up"), "icon_treeLevelUp.gif");
            }
        }
        return parent::renderLevelUpAction($strListIdentifier);
    }


    protected function renderAdditionalActions(class_model $objListEntry) {
        $arrReturn = array();

        if($objListEntry instanceof class_module_navigation_tree) {
            if(validateSystemid($objListEntry->getStrFolderId()))
                $arrReturn[] = $this->objToolkit->listButton(getImageAdmin("icon_treeBranchOpenDisabled.gif", $this->getLang("navigation_show_disabled")));
            else
                $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "list", "&systemid=".$objListEntry->getSystemid().$this->strPeAddon, "", $this->getLang("navigation_anzeigen"), "icon_treeBranchOpen.gif"));

        }

        if($objListEntry instanceof class_module_navigation_point) {
            $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("navigation", "list", "&systemid=".$objListEntry->getSystemid().$this->strPeAddon, "", $this->getLang("navigationp_anzeigen"), "icon_treeBranchOpen.gif"));
        }

        return $arrReturn;
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($strListIdentifier == "naviPoints") {
            if($this->getObjModule()->rightEdit()) {
                return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newNaviPoint", "&systemid=".$this->getSystemid().$this->strPeAddon, $this->getLang("modul_anlegenpunkt"), $this->getLang("modul_anlegenpunkt"), "icon_new.gif"));
            }
        }
        else if($strListIdentifier == "browserList")
            return "";
        else
            return parent::getNewEntryAction($strListIdentifier);
    }


    /**
     * Renders the form to create a new entry
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        if($this->getSystemid() == "")
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "newNavi"));
    }

    /**
     * Renders the form to edit an existing entry
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $objEditObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objEditObject instanceof class_module_navigation_tree) {
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editNavi", "&systemid=".$objEditObject->getSystemid().$this->strPeAddon));
        }

        if($objEditObject instanceof class_module_navigation_point) {
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editNaviPoint", "&systemid=".$objEditObject->getSystemid().$this->strPeAddon));
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
     * @param class_admin_formgenerator|null $objForm
     * @return string
     * @autoTestable
     * @permissions edit
     */
	protected function actionNewNavi($strMode = "new", class_admin_formgenerator $objForm = null) {

        $objNavi = new class_module_navigation_tree();

        if($strMode == "edit") {
            $objNavi = new class_module_navigation_tree($this->getSystemid());
            if(!$objNavi->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getNaviAdminForm($objNavi);

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);

        return $objForm->renderForm(getLinkAdminHref($this->arrModule["modul"], "saveNavi"));

	}


    private function getNaviAdminForm(class_module_navigation_tree $objTree) {

        $strFolderBrowser = getLinkAdminDialog(
            "pages",
            "pagesFolderBrowser",
            "&form_element=navi_folder_i&folder=1",
            $this->getLang("commons_open_browser"),
            $this->getLang("commons_open_browser"),
            "icon_externalBrowser.gif",
            $this->getLang("commons_open_browser")
        );


        $objForm = new class_admin_formgenerator("navi", $objTree);

        $objFolder = new class_module_pages_folder($objTree->getStrFolderId());

        $objForm->addDynamicField("name")->setStrLabel($this->getLang("commons_name"));
        $objForm->addField(new class_formentry_text("navi", "folder_i", null))->setStrValue($objFolder->getStrName())->setBitReadonly(true)->setStrOpener($strFolderBrowser)->setStrLabel($this->getLang("navigation_folder_i"));
        $objForm->addField(new class_formentry_hidden("navi", "folder_i_id"))->setStrValue($objFolder->getSystemid());

        return $objForm;
    }

	/**
	 * Saves or updates a navigation
	 *
	 * @return string, "" in case of success
     * @permissions edit
	 */
	protected function actionSaveNavi() {
		$strReturn = "";

        $objNavi = new class_module_navigation_tree();
        if($this->getParam("mode") == "edit") {
            $objNavi = new class_module_navigation_tree($this->getSystemid());
            if(!$objNavi->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        $objForm = $this->getNaviAdminForm($objNavi);
        if(!$objForm->validateForm())
            return $this->actionNewNavi($this->getParam("mode"), $objForm);

        $objForm->updateSourceObject();
        $objNavi->setStrFolderId($this->getParam("navi_folder_i_id"));

        if(!$objNavi->updateObjectToDb())
            throw new class_exception("Error saving object to db", class_exception::$level_ERROR);


        $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));

		return $strReturn;
	}


    protected function actionEditNaviPoint() {
        return $this->actionNewNaviPoint("edit");
    }

    /**
     * Creates the form to edit / create a new navi-point
     *
     * @param string $strMode new || edit
     * @param class_admin_formgenerator|null $objForm
     * @return string
     * @permissions edit
     */
	protected function actionNewNaviPoint($strMode = "new", class_admin_formgenerator $objForm = null) {
		$strReturn = "";

        $strNodeBrowser = getLinkAdminDialog(
            $this->arrModule["modul"],
            "navigationPointBrowser",
            "&form_element=navigation_parent&systemid=".$this->getPrevId(),
            $this->getLang("commons_open_browser"),
            $this->getLang("commons_open_browser"),
            "icon_externalBrowser.gif",
            $this->getLang("commons_open_browser")
        );

        $strParentname = "";
        $objParentPoint = null;
        if(validateSystemid($this->getParam("navigation_parent_id"))) {
            $objParentPoint = new class_module_navigation_point($this->getParam("navigation_parent_id"));
            $strParentname = $objParentPoint->getStrName();
        }


        $objPoint = new class_module_navigation_point();
        if ($strMode == "edit") {
            //Load Point data
            $objPoint = new class_module_navigation_point($this->getSystemid());
        }

        if($objForm == null)
            $objForm = $this->getPointAdminForm($objPoint, $strMode);

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->arrModule["modul"], "saveNaviPoint"));
	}


    private function getPointAdminForm(class_module_navigation_point $objPoint, $strMode) {

        $arrTargets = array("_self" => $this->getLang("navigation_tagetself"), "_blank" => $this->getLang("navigation_tagetblank"));

        if(validateSystemid($this->getParam("point_parent_id")))
            $objParent = class_objectfactory::getInstance()->getObject($this->getParam("point_parent_id"));
        else
            $objParent = class_objectfactory::getInstance()->getObject($this->getSystemid());


        $objParentPoint = null;
        if(validateSystemid($this->getParam("point_parent_id"))) {
            $objParentPoint = new class_module_navigation_point($this->getParam("point_parent_id"));
        }
        else if($strMode == "new" ) {
            $objParentPoint = class_objectfactory::getInstance()->getObject($this->getSystemid());
        }
        else if($strMode == "edit") {
            $objParentPoint = new class_module_navigation_point($objParent->getPrevId());
        }

        $strNodeBrowser = getLinkAdminDialog(
                    $this->arrModule["modul"],
                    "navigationPointBrowser",
                    "&form_element=point_parent&systemid=".$objParent->getPrevId(),
                    $this->getLang("commons_open_browser"),
                    $this->getLang("commons_open_browser"),
                    "icon_externalBrowser.gif",
                    $this->getLang("commons_open_browser")
                );

        $objForm = new class_admin_formgenerator("point", $objPoint);

        $objForm->generateFieldsFromObject();

        $objForm->getField("name")->setStrLabel($this->getLang("commons_name"));
        $objForm->getField("pagei")->setStrLabel($this->getLang("navigation_page_i"));
        $objForm->getField("pagee")->setStrLabel($this->getLang("navigation_page_e"));;
        $objForm->getField("image")->setStrLabel($this->getLang("commons_image"));

        $objForm->addField(new class_formentry_text("point", "parent"))->setStrOpener($strNodeBrowser)->setBitReadonly(true)->setStrValue($objParentPoint->getStrName())->setStrLabel($this->getLang("navigation_parent"));
        $objForm->addField(new class_formentry_hidden("point", "parent_id"))->setStrValue($objParentPoint->getSystemid());

        $objForm->getField("target")->setArrKeyValues($arrTargets)->setStrLabel($this->getLang("navigation_target"));;

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
        $objPoint = new class_module_navigation_point();
        if($this->getParam("mode") == "edit") {
            $objPoint = new class_module_navigation_point($this->getSystemid());
        }

        $objForm = $this->getPointAdminForm($objPoint, $this->getParam("mode"));
        if(!$objForm->validateForm())
            return $this->actionNewNaviPoint($this->getParam("mode"), $objForm);

        $objForm->updateSourceObject();

        $strExternalLink = $objPoint->getStrPageE();
        $strExternalLink = uniStrReplace(_indexpath_, "_indexpath_", $strExternalLink);
        $strExternalLink = uniStrReplace(_webpath_, "_webpath_", $strExternalLink);
        $objPoint->setStrPageE($strExternalLink);

        $objPoint->updateObjectToDb($this->getParam("point_parent_id"));

        //Flush pages cache
        $this->flushCompletePagesCache();
        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$objPoint->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
        return $strReturn;
	}


	/**
	 * Invokes the deletion of navi-points
	 *
	 * @return string "" in case of success
     * @permissions delete
	 */
	protected function actionDelete() {
		$strReturn = "";
		//Check rights
        $objPoint = class_objectfactory::getInstance()->getObject($this->getSystemid());
        $this->flushCompletePagesCache();

        $strPrevId = $objPoint->getPrevId();
        if(($objPoint instanceof class_module_navigation_point || $objPoint instanceof  class_module_navigation_tree) && !$objPoint->deleteObject())
            throw new class_exception("Error deleting object from db. Needed rights given?", class_exception::$level_ERROR);

        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$strPrevId.($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));

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
		$intCounter = 1;
		//Load all navis

        $arrPoints = class_module_navigation_point::getNaviLayer($this->getSystemid());
        $strReturn .= $this->objToolkit->listHeader();

        $objPoint = class_objectfactory::getInstance()->getObject($this->getSystemid());

        //Link one level up
        $strPrevID = $objPoint->getPrevId();
        if($strPrevID != $this->getModuleSystemid($this->arrModule["modul"])){
            $strAction  = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "navigationPointBrowser", "&systemid=".$strPrevID."&form_element=".$this->getParam("form_element"), $this->getLang("commons_one_level_up"), $this->getLang("commons_one_level_up"), "icon_treeLevelUp.gif"));
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "..", getImageAdmin("icon_treeRoot.gif"), $strAction, $intCounter++);
        }
        else {
            $strAction  = $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("navigation_point_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$this->getParam("form_element")."', ''],['".$this->getParam("form_element")."_id', '".$this->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), ".", getImageAdmin("icon_treeLeaf.gif"), $strAction, $intCounter++);
        }
        if(count($arrPoints) > 0) {
            /** @var class_module_navigation_point $objSinglePoint */
            foreach($arrPoints as $objSinglePoint) {
                if($objSinglePoint->rightView()) {
                    $strAction = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "navigationPointBrowser", "&systemid=".$objSinglePoint->getSystemid()."&form_element=".$this->getParam("form_element"), $this->getLang("navigationp_anzeigen"), $this->getLang("navigationp_anzeigen"), "icon_treeBranchOpen.gif"));
                    $strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("navigation_point_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$this->getParam("form_element")."', '".$objSinglePoint->getStrName()."'],['".$this->getParam("form_element")."_id', '".$objSinglePoint->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
                    $strReturn .= $this->objToolkit->simpleAdminList($objSinglePoint, $strAction, $intCounter);
                }
            }
		}

        $strReturn .= $this->objToolkit->listFooter();
		return $strReturn;
	}

    /**
	 * Returns a list of available navigation
	 *
     * @return string
     * @autoTestable
     * @permissions view
     */
	protected function actionNavigationBrowser() {
		$strReturn = "";
		$intCounter = 1;
        $this->setArrModuleEntry("template", "/folderview.tpl");
		//Load all navis
		$arrNavis = class_module_navigation_tree::getAllNavis();


		$strReturn .= $this->objToolkit->listHeader();
        /** @var class_module_navigation_tree $objOnenavigation */
		foreach($arrNavis as $objOnenavigation) {
		    $strAction = $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("navigation_point_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['navigation_name', '".$objOnenavigation->getStrName()."'], ['navigation_id', '".$objOnenavigation->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif"));
            $strReturn .= $this->objToolkit->simpleAdminList($objOnenavigation, $strAction, $intCounter++);
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
            $objPoint = new class_module_navigation_point($strOneSystemid);
            $arrEntries[] = getLinkAdmin("navigation", "list", "&systemid=".$strOneSystemid, $objPoint->getStrName(), $objPoint->getStrName());
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
        $arrNodes = $this->getPathArray();
        $strReturn .= $this->objToolkit->getTreeview("KAJONA.admin.ajax.loadNavigationTreeViewNodes", $arrNodes[0], $arrNodes, $strSideContent, $this->getOutputModuleTitle(), getLinkAdminHref($this->arrModule["modul"], "list", "&systemid=".$arrNodes[0], false));
        return $strReturn;
    }



    /**
     * Fetches all child-nodes of the passed node.
     * Used by the tree-view in module-navigation admin view.
     *
     * @return string
     * @since 3.3.0
     * @xml
     * @permissions view
     */
    protected function actionGetChildNodes() {
        $strReturn = " ";

        $strReturn .= "<subnodes>";
        $arrNavigations = class_module_navigation_point::getNaviLayer($this->getSystemid());

        if(count($arrNavigations) > 0) {
            /** @var class_module_navigation_point $objSinglePoint */
            foreach ($arrNavigations as $objSinglePoint) {
                if($objSinglePoint->rightView()) {
                    $strReturn .= "<point>";
                    $strReturn .= "<name>".xmlSafeString($objSinglePoint->getStrName())."</name>";
                    $strReturn .= "<systemid>".$objSinglePoint->getSystemid()."</systemid>";
                    $strReturn .= "<link>".getLinkAdminHref("navigation", "list", "&systemid=".$objSinglePoint->getSystemid(), false)."</link>";
                    $strReturn .= "<isleaf>".(count(class_module_navigation_point::getNaviLayer($objSinglePoint->getSystemid())) == 0 ? "true" : "false")."</isleaf>";
                    $strReturn .= "</point>";
                }
            }
        }

        $strReturn .= "</subnodes>";
        return $strReturn;
    }


}

