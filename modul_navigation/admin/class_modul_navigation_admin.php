<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/


/**
 * Admin-class to manage all navigations
 *
 * @package modul_navigation
 */
class class_modul_navigation_admin extends class_admin implements interface_admin {
    private $strAction;

    /**
     * Constructor
     *
     */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 				= "modul_navigation";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _navigation_modul_id_;
		$arrModul["table"]     			= _dbprefix_."navigation";
		$arrModul["modul"]				= "navigation";
		parent::__construct($arrModul);
	}

	/**
	 * Action block to decide which action to perform
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
	    $strReturn = "";
		if($strAction == "")
			$strAction ="list";

		$this->strAction = $strAction;

		try {

    		if($strAction == "list")
    			$strReturn = $this->actionList();
    		if($strAction == "newNavi")
    			$strReturn = $this->actionNewNavi("new");
    		if($strAction == "editNavi")
    			$strReturn = $this->actionNewNavi("edit");
    		if($strAction == "saveNavi") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSaveNavi();
    			    if($strReturn == "")
    	               $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
    		    }
    		    else {
    		        if($this->getParam("mode") == "new")
    		            $strReturn = $this->actionNewNavi("new");
    		        else
    		            $strReturn = $this->actionNewNavi("edit");
    		    }
    		}
    		if($strAction == "newNaviPoint")
    			$strReturn = $this->actionNewNaviPoint("new");
    		if($strAction == "editNaviPoint")
    			$strReturn = $this->actionNewNaviPoint("edit");
    		if($strAction == "saveNaviPoint") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSaveNaviPoint();
    			    if($strReturn == "")
    			       $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
    		    }
    		    else {
                    if($this->getParam("mode") == "new")
                        $strReturn = $this->actionNewNaviPoint("new");
                    else
                        $strReturn = $this->actionNewNaviPoint("edit");
    		    }
    		}
    		if($strAction == "deleteNaviFinal") {
    			$strReturn = $this->actionDeleteNaviFinal();
    			if($strReturn == "")
    			   $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
    		}
    		if($strAction == "naviPointMoveUp") {
    			$strReturn = $this->actionMovePoint("upwards");
    			$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$this->getPrevId()));
    		}
    		if($strAction == "naviPointMoveDown") {
    			$strReturn = $this->actionMovePoint("downwards");
    			$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", "systemid=".$this->getPrevId()));
    		}

		}
		catch (class_exception $objException) {
		    $objException->processException();
		    $strReturn = "An internal error occured: ".$objException->getMessage();
		}

		$this->strOutput = $strReturn;
	}


	public function getOutputContent() {
		return $this->strOutput;
	}

	public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("modul_rechte"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("modul_liste"), "", "", true, "adminnavi"));
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newNavi", "", $this->getText("modul_anlegen"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
	    $arrReturn[] = array("edit", ($this->getSystemid() != "" && ($this->strAction == "list" || $this->strAction== "saveNaviPoint") ? getLinkAdmin($this->arrModule["modul"], "newNaviPoint", "&systemid=".$this->getSystemid()."", $this->getText("modul_anlegenpunkt"), "", "", true, "adminnavi")  : "" ));
		return $arrReturn;
	}


	protected function getRequiredFields() {
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

// --- List-Functions -----------------------------------------------------------------------------------

	/**
	 * Returns a list of the current level
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";

		//rights
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
		    $intI = 0;
    		//Decide, whether to return the list of navigations or the layer of a navigation
    		if($this->getSystemid() == "") {
    			//Return a list of available navigations
    			$arrNavigations = class_modul_navigation_tree::getAllNavis();
				//Print all navigations
				foreach($arrNavigations as $objOneNavigation) {
					//Correct Rights?
					if($this->objRights->rightView($objOneNavigation->getSystemid())) {
						$strAction = "";
						if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
			    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "editNavi", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigation_bearbeiten"), "icon_pencil.gif"));
			    		if($this->objRights->rightView($objOneNavigation->getSystemid()))
			    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "list", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigation_anzeigen"), "icon_treeBranchOpen.gif"));
			    		if($this->objRights->rightDelete($objOneNavigation->getSystemid()))
			    		    $strAction .= $this->objToolkit->listDeleteButton($objOneNavigation->getStrName(), $this->getText("navigation_loeschen_frage"), getLinkAdminHref("navigation", "deleteNaviFinal", "&systemid=".$objOneNavigation->getSystemid()));
			    		if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
			    		    $strAction .= $this->objToolkit->listStatusButton($objOneNavigation->getSystemid());
			    		if($this->objRights->rightRight($objOneNavigation->getSystemid()))
			    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigation_rechte"), getRightsImageAdminName($objOneNavigation->getSystemid())));
			  			$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_treeRoot.gif"), $objOneNavigation->getStrName(), $strAction, $intI++);
					}
				}
				if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
				    $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newNavi", "", $this->getText("modul_anlegen"), $this->getText("modul_anlegen"), "icon_blank.gif"), $intI++);

                if(uniStrlen($strReturn) != 0)
	  			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

	  			if(count($arrNavigations) == 0)
				    $strReturn .= $this->getText("liste_leer");
    		}
    		else {
    			//Load a sublevel of elements
    			//start with a path-navigation
    			$strReturn .= $this->getPathNavigation();
                $arrNavigations = class_modul_navigation_point::getNaviLayer($this->getSystemid());
                $strListID = generateSystemid();
    			$strReturn .= $this->objToolkit->dragableListHeader($strListID);
    			//Link one level up
    			$strPrevID = $this->getPrevId($this->getSystemid());
    			$strAction = $this->objToolkit->listButton(getLinkAdmin("navigation", "list", "&systemid=".$strPrevID, $this->getText("navigation_ebene"), $this->getText("navigation_ebene"), "icon_treeLevelUp.gif"));
    			$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_treeRoot.gif"), "..", $strAction, $intI++);
                //And loop through the regular points
    			foreach($arrNavigations as $objOneNavigation) {
    				//check rights
    				if($this->objRights->rightView($objOneNavigation->getSystemid())) {
    					$strName = $objOneNavigation->getStrName() . " (".$objOneNavigation->getStrPageI().($objOneNavigation->getStrPageE() != "" ? " ".$objOneNavigation->getStrPageE() : "").") ";
    					$strAction = "";
    					if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
    		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "editNaviPoint", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigationp_bearbeiten"), "icon_pencil.gif"));
    		    		if($this->objRights->rightView($objOneNavigation->getSystemid()))
    		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "list", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigationp_anzeigen"), "icon_treeBranchOpen.gif"));
    		    		if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
    		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "naviPointMoveUp", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigationp_hoch"), "icon_arrowUp.gif"));
    		    		if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
    					    $strAction .= $this->objToolkit->listButton(getLinkAdmin("navigation", "naviPointMoveDown", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigationp_runter"), "icon_arrowDown.gif"));
    					if($this->objRights->rightDelete($objOneNavigation->getSystemid()))
    					    $strAction .= $this->objToolkit->listDeleteButton($objOneNavigation->getStrName(), $this->getText("navigation_loeschen_frage"), getLinkAdminHref("navigation", "deleteNaviFinal", "&systemid=".$objOneNavigation->getSystemid()));
    		    		if($this->objRights->rightEdit($objOneNavigation->getSystemid()))
    		    		    $strAction .= $this->objToolkit->listStatusButton($objOneNavigation->getSystemid());
    		    		if($this->objRights->rightRight($objOneNavigation->getSystemid()))
    		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneNavigation->getSystemid(), "", $this->getText("navigationp_recht"), getRightsImageAdminName($objOneNavigation->getSystemid())));

    		  			$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_treeLeaf.gif"), $strName, $strAction, $intI++, "" , $objOneNavigation->getSystemid());
    				}
    	  		}
    	  		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
    	  		    $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newNaviPoint", "&systemid=".$this->getSystemid()."", $this->getText("modul_anlegenpunkt"), $this->getText("modul_anlegenpunkt"), "icon_blank.gif"), $intI++);
    	  		$strReturn .= $this->objToolkit->dragableListFooter($strListID);
    		}
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Creates the form to edit / create a navi
	 *
	 * @param string $strMode
	 * @return string
	 */
	private function actionNewNavi($strMode = "new") {
		$strReturn = "";
		//check Rights & mode
		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            $arrPoint = array();
            if($strMode == "edit")
                $objNavi = new class_modul_navigation_tree($this->getSystemid());
            else
                $objNavi = new class_modul_navigation_tree("");

		    //Build the form
		    $strReturn .= $this->objToolkit->getValidationErrors($this);
		    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveNavi"));
            $strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("navigation_name"), ($objNavi->getStrName() != "" ? $objNavi->getStrName() : ""));
            $strReturn .= $this->objToolkit->formInputHidden("mode", $strMode);
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
		    $strReturn .= $this->objToolkit->formClose();

		    $strReturn .= $this->objToolkit->setBrowserFocus("navigation_name");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Saves or updates a navigation
	 *
	 * @return string, "" in case of success
	 */
	private function actionSaveNavi() {
		$strReturn = "";
		//Check rights
		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			// new navi or edit exising?
			if($this->getParam("mode") == "new") {
				$objNavi = new class_modul_navigation_tree("");
				$objNavi->setStrName($this->getParam("navigation_name"));
				if(!$objNavi->updateObjectToDb())
				    throw new class_exception("Error saving object to db", class_exception::$level_ERROR);

			}
			elseif($this->getParam("mode") == "edit") {
				//Just update the record
				$objNavi = new class_modul_navigation_tree($this->getSystemid());
				$objNavi->setStrName($this->getParam("navigation_name"));
				if(!$objNavi->updateObjectToDb())
				    throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
			}

		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Creates the form to edit / create a new navi-point
	 *
	 * @param string $strMode new || edit
	 * @return string
	 */
	private function actionNewNaviPoint($strMode = "new") {
		$strReturn = "";
		if($strMode == "new") {
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    //Build the form
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveNaviPoint"));
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
                $strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("navigation_name"), $this->getParam("navigation_name"));
                $strReturn .= $this->objToolkit->formInputPageSelector("navigation_page_i", $this->getText("navigation_page_i"), $this->getParam("navigation_page_i"));
                $strReturn .= $this->objToolkit->formInputText("navigation_page_e", $this->getText("navigation_page_e"), $this->getParam("navigation_page_e"), "inputText", getLinkAdminPopup("folderview", "list", "&bit_link=1&form_element=navigation_page_e", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
                $strReturn .= $this->objToolkit->formInputText("navigation_image", $this->getText("navigation_image"), $this->getParam("navigation_image"), "inputText", getLinkAdminPopup("folderview", "list", "&form_element=navigation_image", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
                $arrTargets = array("_self" => $this->getText("navigation_tagetself"), "_blank" => $this->getText("navigation_tagetblank"));
                $strReturn .= $this->objToolkit->formInputDropdown("navigation_target", $arrTargets, $this->getText("navigation_target"), $this->getParam("navigation_target"));
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
                if($this->getParam("pe") == "1")
                    $strReturn .= $this->objToolkit->formInputHidden("pe", $this->getParam("pe"));
			    $strReturn .= $this->objToolkit->formClose();

			    $strReturn .= $this->objToolkit->setBrowserFocus("navigation_name");
			}
			else
				$strReturn .= $this->obj_texte->get_text($this->modul["modul"], "fehler_recht");
		}
		elseif ($strMode == "edit") {
			if($this->objRights->rightEdit($this->getSystemid())) {
			    //Load Point data
			    $objPoint = new class_modul_navigation_point($this->getSystemid());
			    //Build the form
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
			    $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveNaviPoint"));
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputText("navigation_name", $this->getText("navigation_name"), $objPoint->getStrName());
                $strReturn .= $this->objToolkit->formInputPageSelector("navigation_page_i", $this->getText("navigation_page_i"), $objPoint->getStrPageI() );
                $strReturn .= $this->objToolkit->formInputText("navigation_page_e", $this->getText("navigation_page_e"), $objPoint->getStrPageE(), "inputText", getLinkAdminPopup("folderview", "list", "&bit_link=1&form_element=navigation_page_e", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
                $strReturn .= $this->objToolkit->formInputText("navigation_image", $this->getText("navigation_image"), $objPoint->getStrImage(), "inputText", getLinkAdminPopup("folderview", "list", "&form_element=navigation_image", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
                $arrTargets = array("_self" => $this->getText("navigation_tagetself"), "_blank" => $this->getText("navigation_tagetblank"));
                $strReturn .= $this->objToolkit->formInputDropdown("navigation_target", $arrTargets, $this->getText("navigation_target"), $objPoint->getStrTarget());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
                if($this->getParam("pe") == "1")
                    $strReturn .= $this->objToolkit->formInputHidden("pe", $this->getParam("pe"));
			    $strReturn .= $this->objToolkit->formClose();

			    $strReturn .= $this->objToolkit->setBrowserFocus("navigation_name");
			}
			else
				$strReturn .= $this->obj_texte->get_text($this->modul["modul"], "fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Saves or updates a navi-point
	 *
	 * @return string "" in case of success
	 */
	private function actionSaveNaviPoint() {
		$strReturn = "";
		//Insert or update?
		if($this->getParam("mode") == "new") {
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $objPoint = new class_modul_navigation_point("");
				//and the navigation-table
				$objPoint->setStrImage($this->getParam("navigation_image"));
				$objPoint->setStrName($this->getParam("navigation_name"));
				$objPoint->setStrPageE($this->getParam("navigation_page_e"));
				$objPoint->setStrPageI($this->getParam("navigation_page_i"));
				$objPoint->setStrTarget($this->getParam("navigation_target"));
				if(!$objPoint->updateObjectToDb($this->getSystemid()))
				    throw new class_exception("Error saving point-object to db", class_exception::$level_ERROR);
				//To load a correct list, set the points id as current id
				$this->setSystemid($objPoint->getSystemid());
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		elseif ($this->getParam("mode") == "edit") {
			if($this->objRights->rightEdit($this->getSystemid())) {
				$objPoint = new class_modul_navigation_point($this->getSystemid());
				//and the navigation-table
				$objPoint->setStrImage($this->getParam("navigation_image"));
				$objPoint->setStrName($this->getParam("navigation_name"));
				$objPoint->setStrPageE($this->getParam("navigation_page_e"));
				$objPoint->setStrPageI($this->getParam("navigation_page_i"));
				$objPoint->setStrTarget($this->getParam("navigation_target"));
				if(!$objPoint->updateObjectToDb())
					throw new class_exception("Error updating point-object to db", class_exception::$level_ERROR);
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		//Flush pages cache
		$this->flushCompletePagesCache();
		return $strReturn;
	}


	/**
	 * Invokes the deletion of navi-points
	 *
	 * @return string "" in case of success
	 */
	private function actionDeleteNaviFinal() {
		$strReturn = "";
		//Check rights
		if($this->objRights->rightDelete($this->getSystemid())) {
		    $this->flushCompletePagesCache();

		    //small trick: call prevID() now, to get the result lateron from the cache ;)
		    $this->getPrevId();
            $objNavi = new class_modul_navigation_point($this->getSystemid());

		    if(!$objNavi->deleteNaviPoint())
		        throw new class_exception("Error deleting object from db. Needed rights given?", class_exception::$level_ERROR);

		}
		else
			$strReturn = $this->getText("fehler_recht");
		return $strReturn;
	}


	/**
	 * Shifts a point one position up or downwards
	 *
	 * @param string $strMode upwards || downwards
	 * @return void
	 */
	private function actionMovePoint($strMode = "upwards") {
	    if($this->objRights->rightEdit($this->getSystemid())) {
	        $this->setPosition($this->getSystemid(), $strMode);
	        $this->flushCompletePagesCache();
	    }
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


} //class_modul_navigation_admin

?>