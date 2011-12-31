<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/


/**
 * Portal-part of the navigation. Creates the different navigation-views as sitemap or tree.
 * This class was refactored for Kajona 3.4. Since 3.4 it's possible to mix regular navigation and
 * page/folder structures within a single tree.
 *
 * Therefore only the nodes in $arrTempNodes may be used. A instantiation via new class_module_navigation_point()
 * is not recommend since a node created out of the pages will fail to load this way!
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 */
class class_module_navigation_portal extends class_portal implements interface_portal {

	private $strCurrentSite = "";
	private $arrTree = array();
	private $intLevelMax = 0;

    private $arrNodeTempHelper = array();

    /**
     * Internal structure for all nodes within a single navigation, permissions are evaluated.
     * The structure is a multi-dim array:
     * [navigation-id] = array("node" => $objNode, "subnodes" => array(
     *                                 array("node", "subnodes" => array(...) ),
     *                                 array("node", "subnodes" => array(...) ),
     *                                  ... )
     *                   );
     *
     * @var array
     */
    private $arrTempNodes = array();

    /**
     * Constructor
     * @param $arrElementData
     */
	public function __construct($arrElementData) {
		parent::__construct($arrElementData);

        $this->setArrModuleEntry("modul", "navigation");
        $this->setArrModuleEntry("moduleId", _navigation_modul_id_);

		//Determine the current site to load
		$this->strCurrentSite = $this->getPagename();

        //init with the current navigation, required in all cases
        $objNavigation = new class_module_navigation_tree($this->arrElementData["navigation_id"]);
        $this->arrTempNodes[$this->arrElementData["navigation_id"]] = $objNavigation->getCompleteNaviStructure();


        //Which kind of navigation do we want to load?
		if($this->arrElementData["navigation_mode"] == "tree")
			$this->setAction("navigationTree");
		if($this->arrElementData["navigation_mode"] == "sitemap")
			$this->setAction("navigationSitemap");
	}

    /**
     * Adds the code to load the portaleditor
     *
     * @param string $strReturn
     * @return string
     */
	private function addPortaleditorCode($strReturn) {

        //        foreach($this->arrTempNodes[$this->arrElementData["navigation_id"]]["subnodes"] as $objOneNode)
        //                $this->printTreeLevel(1, $objOneNode);


        //Add pe code
        $arrPeConfig = array(
                              "pe_module" => "navigation",
                              "pe_action_edit" => "list",
                              "pe_action_edit_params" => "&systemid=".$this->arrElementData["navigation_id"],
                              "pe_action_new" => "",
                              "pe_action_new_params" => "",
                              "pe_action_delete" => "",
                              "pe_action_delete_params" => ""
                            );

        //only add the code, if not auto-generated
        $objNavigation = new class_module_navigation_tree($this->arrElementData["navigation_id"]);
        if(!validateSystemid($objNavigation->getStrFolderId()))
            $strReturn = class_element_portal::addPortalEditorCode($strReturn, $this->arrElementData["navigation_id"], $arrPeConfig);

		return $strReturn;
	}


    // --- Tree-Functions -----------------------------------------------------------------------------------

	/**
	 * Creates a common tree-view of the navigation
	 *
	 * @return string
	 */
	protected function actionNavigationTree() {
		$strReturn = "";
        $objPagePointData = $this->searchPageInNavigationTree($this->strCurrentSite, $this->arrElementData["navigation_id"]);
        $strStack = $this->getActiveIdStack($objPagePointData);

		//path created, build the tree using recursion
        $objNavi = new class_module_navigation_tree($this->arrElementData["navigation_id"]);
        if($objNavi->rightView() && $objNavi->getIntRecordStatus() == 1)
            $this->createTree($strStack, 0, $this->arrTempNodes[$this->arrElementData["navigation_id"]]);

		//Create the tree
		$intCounter = -1;
		$arrTree = array();
		foreach($this->arrTree as $arrContents) {
			$arrTree[++$intCounter] = (isset($arrContents) ? $arrContents : "");
		}
		//Create tree from bottom
		$arrTemp = array();
		while($intCounter > 1) {
			$strLevel = $arrTree[$intCounter];

			//include into a wrapper?
			$strLevelTemplateID = $this->objTemplate->readTemplate("/module_navigation/".$this->arrElementData["navigation_template"], "level_".$intCounter."_wrapper");
			$strWrappedLevel = $this->fillTemplate(array("level".$intCounter => $strLevel), $strLevelTemplateID);
			if(uniStrlen($strWrappedLevel) > 0)
			    $strLevel = $strWrappedLevel;

			$arrTemp["level".$intCounter] = $strLevel;

			$this->objTemplate->setTemplate($arrTree[$intCounter-1]);
			$arrTree[$intCounter-1] = $this->objTemplate->fillCurrentTemplate($arrTemp);
			$intCounter--;
		}

		//and add level 1 wrapper
        if($intCounter != -1) {
            $strLevelTemplateID = $this->objTemplate->readTemplate("/module_navigation/".$this->arrElementData["navigation_template"], "level_".$intCounter."_wrapper");
            $strWrappedLevel = $this->fillTemplate(array("level".$intCounter => $arrTree[$intCounter]), $strLevelTemplateID);
            if(uniStrlen($strWrappedLevel) > 0)
                $arrTree[$intCounter] = $strWrappedLevel;


            $this->objTemplate->setTemplate($arrTree[$intCounter]);
            $this->objTemplate->deletePlaceholder();
            $strReturn = $this->objTemplate->getTemplate();
        }

        $strReturn = $this->addPortaleditorCode($strReturn);
		return $strReturn;

	}



	/**
	 * Creates the tree recursive
	 *
	 * @param string $strStack
	 * @param int $intLevel
	 * @param array $arrNodes
	 * @param bool $bitFirst
	 * @param bool $bitLast
	 */
	private function createTree($strStack, $intLevel, $arrNodes, $bitFirst = false, $bitLast = false) {

		//build an array out of the stack
		$arrStack = explode(",", $strStack);

		//Hold the level
		if($intLevel > $this->intLevelMax)
			$this->intLevelMax = $intLevel;

        //any childs?
        $arrChilds = $arrNodes["subnodes"];

		//Add the current point
		//active or inactive
        if($arrNodes["node"] != null) {
            if(in_array($arrNodes["node"]->getSystemid(), $arrStack)) {
                if(!isset($this->arrTree[$intLevel]))
                    $this->arrTree[$intLevel] = "";

                $this->arrTree[$intLevel] .= $this->createNavigationPoint($arrNodes["node"], true, $intLevel, $bitFirst, $bitLast);
            }
            else {
                if(!isset($this->arrTree[$intLevel]))
                    $this->arrTree[$intLevel] = "";

                $this->arrTree[$intLevel] .= $this->createNavigationPoint($arrNodes["node"], false, $intLevel, $bitFirst, $bitLast);
            }
        }
        else {
            $this->arrTree[$intLevel] = "";
        }

		//Let the childs present themselfes
		$intNumberOfChilds = count($arrChilds);

		if($intNumberOfChilds > 0) {
			//First and last are handled special
			$intJ = 1;
			foreach($arrChilds as $arrOneChild) {

				if($intLevel == 0 || in_array($arrNodes["node"]->getSystemid(), $arrStack)) {

    				if($intJ == 1)                           // first node
    					$this->createTree($strStack, $intLevel+1, $arrOneChild, true, false);
    				elseif ($intJ == $intNumberOfChilds)     // last node
    					$this->createTree($strStack, $intLevel+1, $arrOneChild, false, true);
    				else                                     // regualar node
    					$this->createTree($strStack, $intLevel+1, $arrOneChild);
    		    }

				$intJ++;
			}
		}
	}


	/**
	 * creates the code for a sitemap
	 *
	 * @return string
	 */
	protected function actionNavigationSitemap() {
		$strReturn = "";
		//check rights on the navigation
        $objNavi = new class_module_navigation_tree($this->arrElementData["navigation_id"]);
		if($objNavi->rightView() && $objNavi->getIntRecordStatus() == 1) {
            //create a stack to highlight the points being active
            $strStack = $this->getActiveIdStack($this->searchPageInNavigationTree($this->strCurrentSite, $this->arrElementData["navigation_id"]));

            //build the navigation
            $strReturn = $this->sitemapRecursive(1, $this->arrTempNodes[$this->arrElementData["navigation_id"]], $strStack);
		}

        $strReturn = $this->addPortaleditorCode($strReturn);
		return $strReturn;
	}


    /**
     * Creates a sitemap recursive level by level
     *
     * @param int $intLevel
     * @param $objStartEntry
     * @param $strStack
     * @internal param string $strSystemid
     * @return string
     */
	private function sitemapRecursive($intLevel, $objStartEntry, $strStack) {
		$strReturn = "";
		$arrChilds = $objStartEntry["subnodes"];

        $intNrOfChilds = count($arrChilds);
		//Anything to do right here?
		if($intNrOfChilds == 0)
			return "";

		//Iterate over every child
		for($intI = 0; $intI < $intNrOfChilds; $intI++) {
		    $arrOneChild = $arrChilds[$intI];
			//Check the rights
			if($arrOneChild["node"]->rightView()) {
                //current point active?
                $bitActive = false;
                if(uniStripos($strStack, $arrOneChild["node"]->getSystemid()) !== false)
                    $bitActive = true;

			    //Create the navigation point
			    if($intI == 0)
				    $strCurrentPoint = $this->createNavigationPoint($arrOneChild["node"], $bitActive, $intLevel, true);
				elseif ($intI == $intNrOfChilds-1)
				    $strCurrentPoint = $this->createNavigationPoint($arrOneChild["node"], $bitActive, $intLevel, false, true);
				else
				    $strCurrentPoint = $this->createNavigationPoint($arrOneChild["node"], $bitActive, $intLevel);

				//And load all points below
				$strChilds = $this->sitemapRecursive($intLevel+1, $arrOneChild, $strStack);

				//Put the childs below into the current template
				$this->objTemplate->setTemplate($strCurrentPoint);
				$arrTemp = array("level".($intLevel+1) => $strChilds);
				$strTemplate = $this->objTemplate->fillCurrentTemplate($arrTemp);

				$strReturn .= $strTemplate;
			}
		}

		//wrap into the wrapper-section
        $strLevelTemplateID = $this->objTemplate->readTemplate("/module_navigation/".$this->arrElementData["navigation_template"], "level_".$intLevel."_wrapper");
        $strWrappedLevel = $this->fillTemplate(array("level".$intLevel => $strReturn), $strLevelTemplateID);
        if(uniStrlen($strWrappedLevel) > 0)
            $strReturn = $strWrappedLevel;

		return $strReturn;
	}


    /**
     * Builds a string of concatenated systemids. Those ids are the systemids of the page-points
     * being active. The delimiter is the ','-char.
     * Ths ids are sorted top to bottom. So the first one is current active one,
     * the last one is the last parent being active, so in most cases the node on the
     * first level of the navigation
     *
     * @param class_module_navigation_point $objActivePoint
     * @return string
     */
    private function getActiveIdStack($objActivePoint) {
        //Loading the points above
        $objTemp = $objActivePoint;

		if($objTemp == null) {
		    //Special case: no active point found --> load the first level inactive
            return $this->arrElementData["navigation_id"];
		}

        $arrStacks = array();
        foreach($this->arrTempNodes[$this->arrElementData["navigation_id"]]["subnodes"] as $arrOneNode) {
            $strStack = $this->createActiveIdStackHelper($objActivePoint, $arrOneNode);
            if($strStack != null)
                $arrStacks[] = $strStack.",".$this->arrElementData["navigation_id"];
        }

        $strStack = "";
        //search the deepest stack
        foreach($arrStacks as $strOneStack)
            if(uniStrlen($strOneStack) > uniStrlen($strStack))
                $strStack = $strOneStack;


        return $strStack;
    }

    /**
     * Traverses the internal node-structure in order to build a stack of active nodes
     *
     * @param class_module_navigation_point $objNodeToSearch
     * @param array $arrNodes
     * @return string
     */
    private function createActiveIdStackHelper($objNodeToSearch, $arrNodes) {
        $strReturn = null;

        if($arrNodes["node"]->getSystemid() == $objNodeToSearch->getSystemid()) {
            $strReturn = $objNodeToSearch->getSystemid();
            return $strReturn;
        }


        foreach($arrNodes["subnodes"] as $arrOneSubnode) {
            $strReturnTemp = $this->createActiveIdStackHelper($objNodeToSearch, $arrOneSubnode);
            if($strReturnTemp != null) {
                $strReturn = $strReturnTemp.",".$arrNodes["node"]->getSystemid();
                return $strReturn;
            }
        }

        return $strReturn;
    }

    /**
     * Invokes the search of a page inside a navigation tree.
     * Loads the navigation structure if not yet present.
     *
     * Triggers the usage of a fallback-node and manages the handling of fallback nodes.
     *
     * @param string $strPagename
     * @param string $strNavigationId
     * @return class_module_navigation_point or null
     */
    private function searchPageInNavigationTree($strPagename, $strNavigationId) {

        $this->arrNodeTempHelper = array();

        //nodestructure given?
        if(!isset($this->arrTempNodes[$strNavigationId])) {
            $objNavigation = new class_module_navigation_tree($strNavigationId);
            $this->arrTempNodes[$strNavigationId] = $objNavigation->getCompleteNaviStructure();
        }

        //process the hierarchy
        $arrNodes = $this->arrTempNodes[$strNavigationId];
        foreach($arrNodes["subnodes"] as $arrOneNode)
            $this->searchPageInNavigationTreeHelper(1, $strPagename, $arrOneNode);

        //process the nodes found
        $intMaxLevel = 0;
        $objEntry = null;
        foreach($this->arrNodeTempHelper as $intLevel => $arrNodes) {
            if(count($arrNodes)> 0 && $intLevel > $intMaxLevel) {
                $intMaxLevel = $intLevel;
                $objEntry = $arrNodes[0];
            }
        }

        //if not found, check for links in other navigations - or use the fallback
        if($objEntry == null) {
            //not visible, so load fallback from session - if given
            if(!$this->isPageVisibleInOtherNavigation()) {
			    $strFallbackPage = $this->objSession->getSession("navi_fallback_page_".$this->arrElementData["navigation_id"]);

                //use the fallback page
			    if($strFallbackPage !== false) {
                    $this->arrNodeTempHelper = array();
                    foreach($this->arrTempNodes[$strNavigationId]["subnodes"] as $arrOneNode)
                        $this->searchPageInNavigationTreeHelper(1, $strFallbackPage, $arrOneNode);

                    $intMaxLevel = 0;
                    $objEntry = null;
                    foreach($this->arrNodeTempHelper as $intLevel => $arrNodes) {
                        if(count($arrNodes)> 0 && $intLevel > $intMaxLevel) {
                            $intMaxLevel = $intLevel;
                            $objEntry = $arrNodes[0];
                        }
                    }
			    }
            }
        }
        else {
            //save this page as a fallback page, dependant of the navigation_id / navigation_tree
		    $this->objSession->setSession("navi_fallback_page_".$this->arrElementData["navigation_id"], $this->strCurrentSite);
        }

        return $objEntry;
    }

    /**
     * Internal recursion helper, processes a single level of nodes in oder to
     * search a matching node.
     *
     * @param int $intLevel
     * @param string $strPage page to search
     * @param array $arrNodes
     */
    private function searchPageInNavigationTreeHelper($intLevel, $strPage, $arrNodes) {
        if(!isset($this->arrNodeTempHelper[$intLevel]))
            $this->arrNodeTempHelper[$intLevel] = array();

        if($arrNodes["node"]->getStrPageI() == $strPage)
            $this->arrNodeTempHelper[$intLevel][] = $arrNodes["node"];

        foreach($arrNodes["subnodes"] as $arrOneSubnode) {
            $this->searchPageInNavigationTreeHelper($intLevel+1, $strPage, $arrOneSubnode);
        }
    }


	/**
	 * Searches the current page in other navigation-trees found on the current page.
	 * This can be usefull to avoid a session-based "opening" of the current tree.
	 * The user may find it confusing, if the current tree remains opened but he clicked
	 * a navigation-point of another tree.
	 *
	 * @return bool
	 */
	private function isPageVisibleInOtherNavigation() {

	    //load the placeholders placed on the current page-template. therefore, instantiate a page-object
        $objPageData = class_module_pages_page::getPageByName($this->getPagename());
        $objMasterPageData = class_module_pages_page::getPageByName("master");
        if($objPageData != null) {
            //analyze the placeholders on the page, faster than iterating the the elements available in the db
            $strTemplateId = $this->objTemplate->readTemplate("/module_pages/".$objPageData->getStrTemplate());
            $arrElementsTemplate = array_merge($this->objTemplate->getElements($strTemplateId, 0), $this->objTemplate->getElements($strTemplateId, 1));

            //loop elements to remove navigation-elements. to do so, get the current elements-name (maybe the user renamed the default "navigation")
            foreach($arrElementsTemplate as $arrPlaceholder) {
                if($arrPlaceholder["placeholder"] != $this->arrElementData["page_element_ph_placeholder"]) {
                    //loop the elements-list
                    foreach($arrPlaceholder["elementlist"] as $arrOneElement) {
                        if($arrOneElement["element"] == $this->arrElementData["page_element_ph_element"]) {

                            //seems as we have a navigation-element different than the current one.
                            //check, if the element is installed on the current page
                            $objElement = class_module_pages_pageelement::getElementByPlaceholderAndPage($objPageData->getSystemid(), $arrPlaceholder["placeholder"], $this->getPortalLanguage());
                            //maybe on the masters-page?
                            if($objElement == null)
                                $objElement = class_module_pages_pageelement::getElementByPlaceholderAndPage($objMasterPageData->getSystemid(), $arrPlaceholder["placeholder"], $this->getPortalLanguage());

                            if($objElement != null) {
                                //wohooooo, an element was found.
                                //check, if the current point is in the tree linked by the navigation - if it's a different navigation....
                          	    //load the real-pageelement
                                $objRealElement = new class_element_navigation_portal($objElement);
                          	    $arrContent = $objRealElement->getElementContent($objElement->getSystemid());

                          	    if($arrContent["navigation_mode"] == "tree") {

                                    //navigation found. trigger loading of nodes if not yet happend
                                    if(!isset($this->arrTempNodes[$arrContent["navigation_id"]])) {
                                        $objNavigation = new class_module_navigation_tree($arrContent["navigation_id"]);

                                        if($objNavigation->getStatus() == 0)
                                            $this->arrTempNodes[$arrContent["navigation_id"]] = array("node" => null, "subnodes" => array());
                                        else
                                            $this->arrTempNodes[$arrContent["navigation_id"]] = $objNavigation->getCompleteNaviStructure();
                                    }

                                    //search navigation tree
                                    $this->arrNodeTempHelper = array();
                                    foreach($this->arrTempNodes[$arrContent["navigation_id"]]["subnodes"] as $objOneNodeToScan)
                                        $this->searchPageInNavigationTreeHelper(0, $this->strCurrentSite, $objOneNodeToScan);

                                    $intMaxLevel = 0;
                                    $objEntry = null;
                                    foreach($this->arrNodeTempHelper as $intLevel => $arrNodes) {
                                        if(count($arrNodes)> 0 && $intLevel >= $intMaxLevel) {
                                            $intMaxLevel = $intLevel;
                                            $objEntry = $arrNodes[0];
                                        }
                                    }

                                    //jepp, page found in another tree, so return true
                                    if($objEntry != null)
                                        return true;

                          	    }
                            }
                        }
                    }
                }
            }
        }
	    return false;
	}

	/**
	 * Creates the html-code for one single navigationpoint. The check if the user has the needed rights should have been made before!
	 *
	 * @param class_module_navigation_point $objPointData
	 * @param bool $bitActive
	 * @param int $intLevel
	 * @param bool $bitFirst
	 * @param bool $bitLast
	 * @return string
	 */
	private function createNavigationPoint($objPointData, $bitActive, $intLevel, $bitFirst= false, $bitLast = false) {
		//and start to create a link and all needed stuff
        $arrTemp = array();
		$arrTemp["page_intern"] = $objPointData->getStrPageI();
		$arrTemp["page_extern"] = $objPointData->getStrPageE();
		$arrTemp["text"] = $objPointData->getStrName();
		$arrTemp["link"] = getLinkPortal($arrTemp["page_intern"], $arrTemp["page_extern"], $objPointData->getStrTarget(), $arrTemp["text"]);
		$arrTemp["href"] = getLinkPortalHref($arrTemp["page_intern"], $arrTemp["page_extern"], "", "", "");
		$arrTemp["target"] = $objPointData->getStrTarget();
		if($objPointData->getStrImage() != "") {
			$arrTemp["image"] = getLinkPortal($arrTemp["page_intern"], $arrTemp["page_extern"], $objPointData->getStrTarget(), "<img src=\""._webpath_.$objPointData->getStrImage()."\" border=\"0\" alt=\"".$arrTemp["text"]."\"/>");
            $arrTemp["image_src"] = $objPointData->getStrImage();
        }

        if($objPointData->getStrPageI() != "") {
            $objPage = class_module_pages_page::getPageByName($objPointData->getStrPageI());
            if($objPage->getIntLmTime() != "")
                $arrTemp["lastmodified"] = strftime("%Y-%m-%dT%H:%M:%S", $objPage->getIntLmTime());
        }

		//Load the correct template
		$strSection = "level_".$intLevel."_".($bitActive ? "active" : "inactive").($bitFirst ? "_first" : "").($bitLast ? "_last" : "");
		$strTemplateId = $this->objTemplate->readTemplate("/module_navigation/".$this->arrElementData["navigation_template"], $strSection);
		//Fill the template
		$strReturn = $this->objTemplate->fillTemplate($arrTemp, $strTemplateId, false);
		//BUT: if we received an empty string and are in the situation of a first or last point, then maybe the template
		//     didn't supply a first / last section. so we'll try to load a regular point
		if($strReturn == "" && ($bitFirst || $bitLast)) {
			$strSection = "level_".$intLevel."_".($bitActive ? "active" : "inactive");
			$strTemplateId = $this->objTemplate->readTemplate("/module_navigation/".$this->arrElementData["navigation_template"], $strSection);
			//And fill it once more
			$strReturn = $this->objTemplate->fillTemplate($arrTemp, $strTemplateId, false);
		}

		return $strReturn;
	}

    /**
     * INTERNAL DEBUG
     * @deprecated
     * @param $intLevel
     * @param $arrNodes
     */
    private function printTreeLevel($intLevel, $arrNodes) {
        for($intI=0; $intI<$intLevel; $intI++) {
            echo "  ";
        }
        echo $arrNodes["node"]->getStrName()."/".$arrNodes["node"]->getSystemid()."/".$arrNodes["node"]->getStrPageI()."<br />\n";

        foreach($arrNodes["subnodes"] as $arrOneNode)
            $this->printTreeLevel($intLevel+1, $arrOneNode);
    }
}

