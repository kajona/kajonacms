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
 * @package modul_navigation
 * @author sidler@mulchprod.de
 */
class class_modul_navigation_admin extends class_admin implements interface_admin {

    private $strPeAddon = "";

    /**
     * Constructor
     *
     */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 				= "modul_navigation";
		$arrModul["moduleId"] 			= _navigation_modul_id_;
		$arrModul["table"]     			= _dbprefix_."navigation";
		$arrModul["modul"]				= "navigation";
		parent::__construct($arrModul);

        if($this->getParam("pe") == "1")
            $this->strPeAddon = "&pe=1";
	}


	protected function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("commons_list"), "", "", true, "adminnavi"));
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newNavi", "", $this->getText("modul_anlegen"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
	    $arrReturn[] = array("edit", ($this->getSystemid() != "" && ($this->getAction() == "list" || $this->getAction()== "saveNaviPoint") ? getLinkAdmin($this->arrModule["modul"], "newNaviPoint", "&systemid=".$this->getSystemid()."", $this->getText("modul_anlegenpunkt"), "", "", true, "adminnavi")  : "" ));
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

    // --- List-Functions -----------------------------------------------------------------------------------

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
	 */
	protected function actionList() {
		$strReturn = "";

		//rights
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
		    $intI = 0;
    		//Decide, whether to return the list of navigations or the layer of a navigation
    		if($this->getSystemid() == "" || $this->getSystemid() == $this->getModuleSystemid($this->arrModule["modul"]))  {
    			//Return a list of available navigations
    			$arrNavigations = class_modul_navigation_tree::getAllNavis();
				//Print all navigations
				foreach($arrNavigations as $objOneNavigation) {
					//Correct Rights?
					if($this->objRights->rightView($objOneNavigation->getSystemid())) {
						$strAction = "";
						if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
			    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "editNavi", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigation_bearbeiten"), "icon_pencil.gif"));
			    		if($this->objRights->rightView($objOneNavigation->getSystemid())) {
                            if(validateSystemid($objOneNavigation->getStrFolderId()))
                                $strAction .= $this->objToolkit->listButton(getImageAdmin("icon_treeBranchOpenDisabled.gif", $this->getText("navigation_show_disabled")));
                            else
                                $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "list", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigation_anzeigen"), "icon_treeBranchOpen.gif"));
                        }
			    		if($this->objRights->rightDelete($objOneNavigation->getSystemid()))
			    		    $strAction .= $this->objToolkit->listDeleteButton($objOneNavigation->getStrName(), $this->getText("navigation_loeschen_frage"), getLinkAdminHref("navigation", "deleteNaviFinal", "&systemid=".$objOneNavigation->getSystemid()));
			    		if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
			    		    $strAction .= $this->objToolkit->listStatusButton($objOneNavigation->getSystemid());
			    		if($this->objRights->rightRight($objOneNavigation->getSystemid()))
			    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOneNavigation->getSystemid())));
			  			$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_treeRoot.gif"), $objOneNavigation->getStrName(), $strAction, $intI++);
					}
				}
				if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
				    $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newNavi", "", $this->getText("modul_anlegen"), $this->getText("modul_anlegen"), "icon_new.gif"), $intI++);

                if(uniStrlen($strReturn) != 0)
	  			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

	  			if(count($arrNavigations) == 0)
				    $strReturn .= $this->getText("liste_leer");
    		}
    		else {
    			//Load a sublevel of elements
                $strNaviReturn = "";
                $arrNavigations = class_modul_navigation_point::getNaviLayer($this->getSystemid());
                $strListID = generateSystemid();
    			$strNaviReturn .= $this->objToolkit->dragableListHeader($strListID);
    			//Link one level up
                $objCommons = new class_modul_system_common($this->getSystemid());
                $strPrevID = $objCommons->getStrPrevId();
                
    			$strAction = $this->objToolkit->listButton(getLinkAdmin("navigation", "list", "&systemid=".$strPrevID.$this->strPeAddon, $this->getText("commons_one_level_up"), $this->getText("commons_one_level_up"), "icon_treeLevelUp.gif"));
    			$strNaviReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_treeRoot.gif"), "..", $strAction, $intI++);
                //And loop through the regular points
    			foreach($arrNavigations as $objOneNavigation) {
    				//check rights
    				if($this->objRights->rightView($objOneNavigation->getSystemid())) {
                        $strNameInternal = $objOneNavigation->getStrPageI();
                        $strNameExternal = $objOneNavigation->getStrPageE();
                        $strNameFolder = "";
                        if(validateSystemid($objOneNavigation->getStrFolderI())) {
                            $objFolder = new class_modul_pages_folder($objOneNavigation->getStrFolderI());
                            $strNameFolder = $objFolder->getStrName();
                        }

    					$strName = $objOneNavigation->getStrName() . " (".$strNameInternal.$strNameExternal.$strNameFolder.") ";
    					$strAction = "";
    					if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
    		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "editNaviPoint", "&systemid=".$objOneNavigation->getSystemid().$this->strPeAddon, "", $this->getText("navigationp_bearbeiten"), "icon_pencil.gif"));
    		    		if($this->objRights->rightView($objOneNavigation->getSystemid()))
    		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "list", "&systemid=".$objOneNavigation->getSystemid().$this->strPeAddon, "", $this->getText("navigationp_anzeigen"), "icon_treeBranchOpen.gif"));
    		    		/*if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
    		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "naviPointMoveUp", "&systemid=".$objOneNavigation->getSystemid().$this->strPeAddon, "", $this->getText("navigationp_hoch"), "icon_arrowUp.gif"));
    		    		if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
    					    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "naviPointMoveDown", "&systemid=".$objOneNavigation->getSystemid().$this->strPeAddon, "", $this->getText("navigationp_runter"), "icon_arrowDown.gif"));
    					*/
    		    		if($this->objRights->rightDelete($objOneNavigation->getSystemid()))
    					    $strAction .= $this->objToolkit->listDeleteButton($objOneNavigation->getStrName(), $this->getText("navigation_loeschen_frage"), getLinkAdminHref("navigation", "deleteNaviFinal", "&systemid=".$objOneNavigation->getSystemid().$this->strPeAddon));
    		    		if($this->objRights->rightEdit($objOneNavigation->getSystemid()) && $this->strPeAddon == "")
    		    		    $strAction .= $this->objToolkit->listStatusButton($objOneNavigation->getSystemid());
    		    		if($this->objRights->rightRight($objOneNavigation->getSystemid()) && $this->strPeAddon == "")
    		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOneNavigation->getSystemid())));

    		  			$strNaviReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_treeLeaf.gif"), $strName, $strAction, $intI++, "" , $objOneNavigation->getSystemid());
    				}
    	  		}
    	  		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
    	  		    $strNaviReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newNaviPoint", "&systemid=".$this->getSystemid().$this->strPeAddon."", $this->getText("modul_anlegenpunkt"), $this->getText("modul_anlegenpunkt"), "icon_new.gif"), $intI++);
    	  		$strNaviReturn .= $this->objToolkit->dragableListFooter($strListID);

                if($this->strPeAddon != "")
                    $strReturn .= $this->getPathNavigation().$strNaviReturn;
                else
                    $strReturn .= $this->getPathNavigation().$this->generateTreeView($strNaviReturn);
    		}
		}
		else
			$strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
	}

	protected function actionEditNavi() {
        return $this->actionNewNavi("edit");
    }

    /**
	 * Creates the form to edit / create a navi
	 *
	 * @param string $strMode
	 * @return string
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

		//check Rights & mode
		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($strMode == "edit")
                $objNavi = new class_modul_navigation_tree($this->getSystemid());
            else
                $objNavi = new class_modul_navigation_tree("");

            $strFoldername = "";
            $strFolderid = "";
            if(validateSystemid($objNavi->getStrFolderId())) {
                $objFolder = new class_modul_pages_folder($objNavi->getStrFolderId());
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
		}
		else
			$strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
	}

	/**
	 * Saves or updates a navigation
	 *
	 * @return string, "" in case of success
	 */
	protected function actionSaveNavi() {
		$strReturn = "";

        if(!$this->validateForm())
            return $this->actionNewNavi($this->getParam("mode"));

		//Check rights
		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			// new navi or edit exising?
			if($this->getParam("mode") == "new") {
				$objNavi = new class_modul_navigation_tree("");
				$objNavi->setStrName($this->getParam("navigation_name"));
                $objNavi->setStrFolderId($this->getParam("navigation_folder_i_id"));
				if(!$objNavi->updateObjectToDb())
				    throw new class_exception("Error saving object to db", class_exception::$level_ERROR);

			}
			elseif($this->getParam("mode") == "edit") {
				//Just update the record
				$objNavi = new class_modul_navigation_tree($this->getSystemid());
				$objNavi->setStrName($this->getParam("navigation_name"));
                $objNavi->setStrFolderId($this->getParam("navigation_folder_i_id"));
				if(!$objNavi->updateObjectToDb())
				    throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
			}

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));

		}
		else
			$strReturn .= $this->getText("commons_error_permissions");

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
            $objFolder = new class_modul_pages_folder($this->getParam("navigation_folder_i_id"));
            $strFoldername = $objFolder->getStrName();
        }

        $strParentname = "";
        $objParentPoint = null;
        if(validateSystemid($this->getParam("navigation_parent_id"))) {
            $objParentPoint = new class_modul_navigation_point($this->getParam("navigation_parent_id"));
            $strParentname = $objParentPoint->getStrName();
        }

		if($strMode == "new") {
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    //Build the form
			    $strReturn .= $this->objToolkit->getValidationErrors($this, "saveNaviPoint");
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveNaviPoint"));
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
                $strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("commons_name"), $this->getParam("navigation_name"));
                $strReturn .= $this->objToolkit->formInputPageSelector("navigation_page_i", $this->getText("navigation_page_i"), $this->getParam("navigation_page_i"));
//                $strReturn .= $this->objToolkit->formInputText("navigation_folder_i", $this->getText("navigation_folder_i"), $strFoldername, "inputText", $strFolderBrowser, true);
//                $strReturn .= $this->objToolkit->formInputHidden("navigation_folder_i_id", $this->getParam("navigation_folder_i_id"));
                $strReturn .= $this->objToolkit->formInputFileSelector("navigation_page_e", $this->getText("navigation_page_e"), $this->getParam("navigation_page_e"), _filemanager_default_filesrepoid_);
                $strReturn .= $this->objToolkit->formInputImageSelector("navigation_image", $this->getText("commons_image"), $this->getParam("navigation_image"));
                $arrTargets = array("_self" => $this->getText("navigation_tagetself"), "_blank" => $this->getText("navigation_tagetblank"));
                $strReturn .= $this->objToolkit->formInputDropdown("navigation_target", $arrTargets, $this->getText("navigation_target"), $this->getParam("navigation_target"));
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
			    $strReturn .= $this->objToolkit->formClose();

			    $strReturn .= $this->objToolkit->setBrowserFocus("navigation_name");
			}
			else
				$strReturn .= $this->getText("commons_error_permissions");
		}
		elseif ($strMode == "edit") {
			if($this->objRights->rightEdit($this->getSystemid())) {
			    //Load Point data
			    $objPoint = new class_modul_navigation_point($this->getSystemid());

                if($strFoldername == "" && validateSystemid($objPoint->getStrFolderI())) {
                    $objFolder = new class_modul_pages_folder($objPoint->getStrFolderI());
                    $strFoldername = $objFolder->getStrName();
                }

                if($strParentname == "" && validateSystemid($objPoint->getPrevId())) {
                    $objParentPoint = new class_modul_navigation_point($objPoint->getPrevId());
                    $strParentname = $objParentPoint->getStrName();
                }

			    //Build the form
			    $strReturn .= $this->objToolkit->getValidationErrors($this, "saveNaviPoint");
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveNaviPoint"));
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("commons_name"), $objPoint->getStrName());
                $strReturn .= $this->objToolkit->formInputPageSelector("navigation_page_i", $this->getText("navigation_page_i"), $objPoint->getStrPageI());
//                $strReturn .= $this->objToolkit->formInputText("navigation_folder_i", $this->getText("navigation_folder_i"), $strFoldername, "inputText", $strFolderBrowser, true);
//                $strReturn .= $this->objToolkit->formInputHidden("navigation_folder_i_id", $objPoint->getStrFolderI());
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
			else
				$strReturn .= $this->getText($this->modul["modul"], "commons_error_permissions");
		}
		return $strReturn;
	}

	/**
	 * Saves or updates a navi-point
	 *
	 * @return string "" in case of success
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
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $objPoint = new class_modul_navigation_point("");
				//and the navigation-table
				$objPoint->setStrImage($this->getParam("navigation_image"));
				$objPoint->setStrName($this->getParam("navigation_name"));
				$objPoint->setStrPageE($strExternalLink);
				$objPoint->setStrPageI($this->getParam("navigation_page_i"));
//				$objPoint->setStrFolderI($this->getParam("navigation_folder_i_id"));
				$objPoint->setStrTarget($this->getParam("navigation_target"));
				if(!$objPoint->updateObjectToDb($this->getSystemid()))
				    throw new class_exception("Error saving point-object to db", class_exception::$level_ERROR);
				//To load a correct list, set the points id as current id
				$this->setSystemid($objPoint->getSystemid());
			}
			else
				$strReturn = $this->getText("commons_error_permissions");
		}
		elseif ($this->getParam("mode") == "edit") {
			if($this->objRights->rightEdit($this->getSystemid())) {
				$objPoint = new class_modul_navigation_point($this->getSystemid());
				//and the navigation-table
				$objPoint->setStrImage($this->getParam("navigation_image"));
				$objPoint->setStrName($this->getParam("navigation_name"));
				$objPoint->setStrPageE($strExternalLink);
				$objPoint->setStrPageI($this->getParam("navigation_page_i"));
//                $objPoint->setStrFolderI($this->getParam("navigation_folder_i_id"));
				$objPoint->setStrTarget($this->getParam("navigation_target"));

                $strPrevid = $objPoint->getPrevId();
                if(validateSystemid($this->getParam("navigation_parent_id")) && $this->getParam("navigation_parent_id") != $this->getSystemid())
                    $strPrevid = $this->getParam("navigation_parent_id");

				if(!$objPoint->updateObjectToDb($strPrevid))
					throw new class_exception("Error updating point-object to db", class_exception::$level_ERROR);
			}
			else
				$strReturn = $this->getText("commons_error_permissions");

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
	 */
	protected function actionDeleteNaviFinal() {
		$strReturn = "";
		//Check rights
		if($this->objRights->rightDelete($this->getSystemid())) {
		    $this->flushCompletePagesCache();

		    //small trick: call prevID() now, to get the result lateron from the cache ;)
		    $this->getPrevId();
            $objNavi = new class_modul_navigation_point($this->getSystemid());

		    if(!$objNavi->deleteNaviPoint())
		        throw new class_exception("Error deleting object from db. Needed rights given?", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));

		}
		else
			$strReturn = $this->getText("commons_error_permissions");
		return $strReturn;
	}

    /**
	 * Returns a list of available navigations
	 *
	 */
	protected function actionNavigationPointBrowser() {
		$strReturn = "";
        $this->setArrModuleEntry("template", "/folderview.tpl");
		$intCounter = 1;
		//Load all navis

        $arrPoints = class_modul_navigation_point::getNaviLayer($this->getSystemid());
        $strReturn .= $this->objToolkit->listHeader();

        //Link one level up
        $strPrevID = $this->getPrevId();
        if($strPrevID != $this->getModuleSystemid($this->arrModule["modul"])){
            $strAction  = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "navigationPointBrowser", "&systemid=".$strPrevID."&form_element=".$this->getParam("form_element"), $this->getText("commons_one_level_up"), $this->getText("commons_one_level_up"), "icon_treeLevelUp.gif"));
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_treeRoot.gif"), "..", $strAction, $intCounter++);
        }
        else {
            $strAction  = $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("navigation_point_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$this->getParam("form_element")."', ''],['".$this->getParam("form_element")."_id', '".$this->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_treeLeaf.gif"), ".", $strAction, $intCounter++);
        }
        if(count($arrPoints) > 0) {
            foreach($arrPoints as $objSinglePoint) {
                if($objSinglePoint->rightView()) {
                    $strAction = $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "navigationPointBrowser", "&systemid=".$objSinglePoint->getSystemid()."&form_element=".$this->getParam("form_element"), $this->getText("navigationp_anzeigen"), $this->getText("navigationp_anzeigen"), "icon_treeBranchOpen.gif"));
                    $strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("navigation_point_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$this->getParam("form_element")."', '".$objSinglePoint->getStrName()."'],['".$this->getParam("form_element")."_id', '".$objSinglePoint->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
                    $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_treeLeaf.gif"), $objSinglePoint->getStrName(), $strAction, $intCounter++);
                }
            }
		}

        $strReturn .= $this->objToolkit->listFooter();
		return $strReturn;
	}
    
    /**
	 * Returns a list of available navigations
	 *
	 */
	protected function actionNavigationBrowser() {
		$strReturn = "";
		$intCounter = 1;
        $this->setArrModuleEntry("template", "/folderview.tpl");
		//Load all navis
		$arrNavis = class_modul_navigation_tree::getAllNavis();


		$strReturn .= $this->objToolkit->listHeader();
		foreach($arrNavis as $objOnenavigation) {
		    $strAction = $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("navigation_point_accept")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['navigation_name', '".$objOnenavigation->getStrName()."'], ['navigation_id', '".$objOnenavigation->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif"));
			$strReturn .= $this->objToolkit->listRow2($objOnenavigation->getStrName(), $strAction, $intCounter++);
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
			$objPoint = new class_modul_navigation_point($strOneSystemid);
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
        //array_unshift($arrNodes, $this->getModuleSystemid($this->arrModule["modul"]));
        $strReturn .= $this->objToolkit->getTreeview("KAJONA.admin.ajax.loadNavigationTreeViewNodes", $arrNodes[0], $arrNodes, $strSideContent, $this->getOutputModuleTitle(), getLinkAdminHref($this->arrModule["modul"], "list", "&systemid=".$arrNodes[0]));
        return $strReturn;
    }



    /**
     * Fetches all child-nodes of the passed node.
     * Used by the tree-view in module-navigation admin view.
     *
     * @return string
     * @since 3.3.0
     * @xml
     */
    protected function actionGetChildNodes() {
        $strReturn = " ";

        $strReturn .= "<subnodes>";
        $arrNavigations = class_modul_navigation_point::getNaviLayer($this->getSystemid());

        if(count($arrNavigations) > 0) {
            foreach ($arrNavigations as $objSinglePoint) {
                if($objSinglePoint->rightView()) {
                    $strReturn .= "<point>";
                    $strReturn .= "<name>".xmlSafeString($objSinglePoint->getStrName())."</name>";
                    $strReturn .= "<systemid>".$objSinglePoint->getSystemid()."</systemid>";
                    $strReturn .= "<link>".getLinkAdminHref("navigation", "list", "&systemid=".$objSinglePoint->getSystemid())."</link>";
                    $strReturn .= "<isleaf>".(count(class_modul_navigation_point::getNaviLayer($objSinglePoint->getSystemid())) == 0 ? "true" : "false")."</isleaf>";
                    $strReturn .= "</point>";
                }
            }
        }

        $strReturn .= "</subnodes>";
        return $strReturn;
    }



}

?>