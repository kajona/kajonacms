<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
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


	protected function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("commons_list"), "", "", true, "adminnavi"));
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newNavi", "", $this->getText("module_action_new"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


	public function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "saveNavi") {
            $arrReturn["navigation_name"] = "string";
        }
        if($strAction == "saveNaviPoint") {
            $arrReturn["navigation_name"] = "string";
        }

        return $arrReturn;
    }

    protected final function validateForm() {
        $arrReturn = array();

        if($this->getAction() == "saveNaviPoint") {
            if($this->getParam("navigation_folder_i_id") != "" && $this->getParam("navigation_page_i") != "")
                $this->arrValidationErrors["navigation_folder_i_id"] = $this->getText("error_folder_and_page");
        }

        parent::validateForm();

        $this->arrValidationErrors = array_merge($this->arrValidationErrors, $arrReturn);
        return (count($this->arrValidationErrors) == 0);
    }


    protected function actionNaviPointMoveUp() {
        $this->setPositionAndReload($this->getSystemid(), "upwards");
    }

    protected function actionNaviPointMoveDown() {
        $this->setPositionAndReload($this->getSystemid(), "downwards");
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

        $intI = 0;
        //Decide, whether to return the list of navigation or the layer of a navigation
        if($this->getSystemid() == "" || $this->getSystemid() == $this->getModuleSystemid($this->arrModule["modul"]))  {

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

            if($this->strPeAddon != "")
                $strReturn = $this->getPathNavigation().$strReturn;
            else
                $strReturn = $this->getPathNavigation().$this->generateTreeView($strReturn);

            return $strReturn;
        }

	}

    protected function renderLevelUpAction($strListIdentifier) {
        if($strListIdentifier == "naviPoints") {
            if($this->getSystemid() != "") {
                $objEditObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
                return getLinkAdmin("navigation", "list", "&systemid=".$objEditObject->getPrevId().$this->strPeAddon, $this->getText("commons_one_level_up"), $this->getText("commons_one_level_up"), "icon_treeLevelUp.gif");
            }
        }
        return parent::renderLevelUpAction($strListIdentifier);
    }


    protected function renderAdditionalActions(class_model $objListEntry) {
        $arrReturn = array();

        if($objListEntry instanceof class_module_navigation_tree) {
            if(validateSystemid($objListEntry->getStrFolderId()))
                $arrReturn[] = $this->objToolkit->listButton(getImageAdmin("icon_treeBranchOpenDisabled.gif", $this->getText("navigation_show_disabled")));
            else
                $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "list", "&systemid=".$objListEntry->getSystemid().$this->strPeAddon, "", $this->getText("navigation_anzeigen"), "icon_treeBranchOpen.gif"));

        }

        if($objListEntry instanceof class_module_navigation_point) {
            $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("navigation", "list", "&systemid=".$objListEntry->getSystemid().$this->strPeAddon, "", $this->getText("navigationp_anzeigen"), "icon_treeBranchOpen.gif"));
        }

        return $arrReturn;
    }

    protected function getNewEntryAction($strListIdentifier) {
        if($strListIdentifier == "naviPoints") {
            if($this->getObjModule()->rightEdit()) {
                return getLinkAdmin($this->getArrModule("modul"), "newNaviPoint", "&systemid=".$this->getSystemid().$this->strPeAddon, $this->getText("modul_anlegenpunkt"), $this->getText("modul_anlegenpunkt"), "icon_new.gif");
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
	 * @return string
     * @autoTestable
     * @permissions edit
	 */
	protected function actionNewNavi($strMode = "new") {
		$strReturn = "";

        $strFolderBrowser = getLinkAdminDialog("pages",
                                               "pagesFolderBrowser",
                                               "&form_element=navigation_folder_i&folder=1",
                                               $this->getText("commons_open_browser"),
                                               $this->getText("commons_open_browser"),
                                               "icon_externalBrowser.gif",
                                               $this->getText("commons_open_browser"));

        if($strMode == "edit")
            $objNavi = new class_module_navigation_tree($this->getSystemid());
        else
            $objNavi = new class_module_navigation_tree("");

        $strFoldername = "";
        $strFolderid = "";
        if(validateSystemid($objNavi->getStrFolderId())) {
            $objFolder = new class_module_pages_folder($objNavi->getStrFolderId());
            $strFoldername = $objFolder->getStrName();
            $strFolderid = $objFolder->getSystemid();
        }

        //Build the form
        $strReturn .= $this->objToolkit->getValidationErrors($this, "saveNavi");
        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveNavi"));
        $strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("commons_name"), ($objNavi->getStrName() != "" ? $objNavi->getStrName() : ""));
        $strReturn .= $this->objToolkit->formInputText("navigation_folder_i", $this->getText("navigation_folder_i"), $strFoldername, "inputText", $strFolderBrowser, true);
        $strReturn .= $this->objToolkit->formInputHidden("navigation_folder_i_id", $strFolderid);
        $strReturn .= $this->objToolkit->formInputHidden("mode", $strMode);
        $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
        $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
        $strReturn .= $this->objToolkit->formClose();

        $strReturn .= $this->objToolkit->setBrowserFocus("navigation_name");

		return $strReturn;
	}

	/**
	 * Saves or updates a navigation
	 *
	 * @return string, "" in case of success
     * @permissions edit
	 */
	protected function actionSaveNavi() {
		$strReturn = "";

        if(!$this->validateForm())
            return $this->actionNewNavi($this->getParam("mode"));

        // new navi or edit exising?
        if($this->getParam("mode") == "new") {
            $objNavi = new class_module_navigation_tree("");
            $objNavi->setStrName($this->getParam("navigation_name"));
            $objNavi->setStrFolderId($this->getParam("navigation_folder_i_id"));
            if(!$objNavi->updateObjectToDb())
                throw new class_exception("Error saving object to db", class_exception::$level_ERROR);

        }
        elseif($this->getParam("mode") == "edit") {
            //Just update the record
            $objNavi = new class_module_navigation_tree($this->getSystemid());
            $objNavi->setStrName($this->getParam("navigation_name"));
            $objNavi->setStrFolderId($this->getParam("navigation_folder_i_id"));
            if(!$objNavi->updateObjectToDb())
                throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
        }

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
	 * @return string
     * @permissions edit
	 */
	protected function actionNewNaviPoint($strMode = "new") {
		$strReturn = "";

        $strNodeBrowser = getLinkAdminDialog(  $this->arrModule["modul"],
                                               "navigationPointBrowser",
                                               "&form_element=navigation_parent&systemid=".$this->getPrevId(),
                                               $this->getText("commons_open_browser"),
                                               $this->getText("commons_open_browser"),
                                               "icon_externalBrowser.gif",
                                               $this->getText("commons_open_browser"));

        $strFoldername = "";
        if(validateSystemid($this->getParam("navigation_folder_i_id"))) {
            $objFolder = new class_module_pages_folder($this->getParam("navigation_folder_i_id"));
            $strFoldername = $objFolder->getStrName();
        }

        $strParentname = "";
        $objParentPoint = null;
        if(validateSystemid($this->getParam("navigation_parent_id"))) {
            $objParentPoint = new class_module_navigation_point($this->getParam("navigation_parent_id"));
            $strParentname = $objParentPoint->getStrName();
        }

		if($strMode == "new") {
            //Build the form
            $strReturn .= $this->objToolkit->getValidationErrors($this, "saveNaviPoint");
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveNaviPoint"));
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
            $strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("commons_name"), $this->getParam("navigation_name"));
            $strReturn .= $this->objToolkit->formInputPageSelector("navigation_page_i", $this->getText("navigation_page_i"), $this->getParam("navigation_page_i"));
            $strReturn .= $this->objToolkit->formInputFileSelector("navigation_page_e", $this->getText("navigation_page_e"), $this->getParam("navigation_page_e"), _filemanager_default_filesrepoid_);
            $strReturn .= $this->objToolkit->formInputImageSelector("navigation_image", $this->getText("commons_image"), $this->getParam("navigation_image"));
            $arrTargets = array("_self" => $this->getText("navigation_tagetself"), "_blank" => $this->getText("navigation_tagetblank"));
            $strReturn .= $this->objToolkit->formInputDropdown("navigation_target", $arrTargets, $this->getText("navigation_target"), $this->getParam("navigation_target"));
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("navigation_name");
		}
		elseif ($strMode == "edit") {
            //Load Point data
            $objPoint = new class_module_navigation_point($this->getSystemid());

            if($strParentname == "" && validateSystemid($objPoint->getPrevId())) {
                $objParentPoint = new class_module_navigation_point($objPoint->getPrevId());
                $strParentname = $objParentPoint->getStrName();
            }

            //Build the form
            $strReturn .= $this->objToolkit->getValidationErrors($this, "saveNaviPoint");
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveNaviPoint"));
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
            $strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("commons_name"), $objPoint->getStrName());
            $strReturn .= $this->objToolkit->formInputPageSelector("navigation_page_i", $this->getText("navigation_page_i"), $objPoint->getStrPageI());
            $strReturn .= $this->objToolkit->formInputFileSelector("navigation_page_e", $this->getText("navigation_page_e"), $objPoint->getStrPageE(), _filemanager_default_filesrepoid_);
            $strReturn .= $this->objToolkit->formInputImageSelector("navigation_image", $this->getText("commons_image"), $objPoint->getStrImage());

            $strReturn .= $this->objToolkit->formInputText("navigation_parent", $this->getText("navigation_parent"), $strParentname, "inputText", $strNodeBrowser, true);
            $strReturn .= $this->objToolkit->formInputHidden("navigation_parent_id", $objParentPoint->getSystemid());

            $arrTargets = array("_self" => $this->getText("navigation_tagetself"), "_blank" => $this->getText("navigation_tagetblank"));
            $strReturn .= $this->objToolkit->formInputDropdown("navigation_target", $arrTargets, $this->getText("navigation_target"), $objPoint->getStrTarget());
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("navigation_name");
		}
		return $strReturn;
	}

	/**
	 * Saves or updates a navi-point
	 *
	 * @return string "" in case of success
     * @permissions edit
	 */
	protected function actionSaveNaviPoint() {
		$strReturn = "";

        if(!$this->validateForm())
            return $this->actionNewNaviPoint($this->getParam("mode"));

        $strExternalLink = $this->getParam("navigation_page_e");
        $strExternalLink = uniStrReplace(_indexpath_, "_indexpath_", $strExternalLink);
        $strExternalLink = uniStrReplace(_webpath_, "_webpath_", $strExternalLink);

		//Insert or update?
		if($this->getParam("mode") == "new") {
            $objPoint = new class_module_navigation_point("");
            //and the navigation-table
            $objPoint->setStrImage($this->getParam("navigation_image"));
            $objPoint->setStrName($this->getParam("navigation_name"));
            $objPoint->setStrPageE($strExternalLink);
            $objPoint->setStrPageI($this->getParam("navigation_page_i"));
            $objPoint->setStrTarget($this->getParam("navigation_target"));
            if(!$objPoint->updateObjectToDb($this->getSystemid()))
                throw new class_exception("Error saving point-object to db", class_exception::$level_ERROR);
            //To load a correct list, set the points id as current id
            $this->setSystemid($objPoint->getSystemid());
		}
		elseif ($this->getParam("mode") == "edit") {
            $objPoint = new class_module_navigation_point($this->getSystemid());
            //and the navigation-table
            $objPoint->setStrImage($this->getParam("navigation_image"));
            $objPoint->setStrName($this->getParam("navigation_name"));
            $objPoint->setStrPageE($strExternalLink);
            $objPoint->setStrPageI($this->getParam("navigation_page_i"));
            $objPoint->setStrTarget($this->getParam("navigation_target"));

            $strPrevid = $objPoint->getPrevId();
            if(validateSystemid($this->getParam("navigation_parent_id")) && $this->getParam("navigation_parent_id") != $this->getSystemid())
                $strPrevid = $this->getParam("navigation_parent_id");

            if(!$objPoint->updateObjectToDb($strPrevid))
                throw new class_exception("Error updating point-object to db", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
		}
		//Flush pages cache
		$this->flushCompletePagesCache();
        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
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
            $strAction  = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "navigationPointBrowser", "&systemid=".$strPrevID."&form_element=".$this->getParam("form_element"), $this->getText("commons_one_level_up"), $this->getText("commons_one_level_up"), "icon_treeLevelUp.gif"));
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "..", getImageAdmin("icon_treeRoot.gif"), $strAction, $intCounter++);
        }
        else {
            $strAction  = $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("navigation_point_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$this->getParam("form_element")."', ''],['".$this->getParam("form_element")."_id', '".$this->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), ".", getImageAdmin("icon_treeLeaf.gif"), $strAction, $intCounter++);
        }
        if(count($arrPoints) > 0) {
            /** @var class_module_navigation_point $objSinglePoint */
            foreach($arrPoints as $objSinglePoint) {
                if($objSinglePoint->rightView()) {
                    $strAction = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "navigationPointBrowser", "&systemid=".$objSinglePoint->getSystemid()."&form_element=".$this->getParam("form_element"), $this->getText("navigationp_anzeigen"), $this->getText("navigationp_anzeigen"), "icon_treeBranchOpen.gif"));
                    $strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("navigation_point_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$this->getParam("form_element")."', '".$objSinglePoint->getStrName()."'],['".$this->getParam("form_element")."_id', '".$objSinglePoint->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
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
		    $strAction = $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("navigation_point_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['navigation_name', '".$objOnenavigation->getStrName()."'], ['navigation_id', '".$objOnenavigation->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif"));
            $strReturn .= $this->objToolkit->simpleAdminList($objOnenavigation, $strAction, $intCounter++);
		}
        $strReturn .= $this->objToolkit->listFooter();
		return $strReturn;
	}



	/**
	 * Helper to generate a small path-navigation
	 *
	 * @return string
	 */
	private function getPathNavigation() {
		$arrPath = $this->getPathArray();
		$arrPathLinks = array();

		foreach($arrPath as $strOneSystemid) {
			$objPoint = new class_module_navigation_point($strOneSystemid);
			$arrPathLinks[] = getLinkAdmin("navigation", "list", "&systemid=".$strOneSystemid, $objPoint->getStrName(), $objPoint->getStrName());
		}
		return $this->objToolkit->getPathNavigation($arrPathLinks);
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

