<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * Portal-part of the navigation. Creates the different navigation-views as sitemap or tree.
 * This class was refactored for Kajona 3.4. Since 3.4 it's possible to mix regular navigation and
 * page/folder structures within a single tree.
 * Therefore only the nodes in $arrTempNodes may be used. A instantiation via new class_module_navigation_point()
 * is not recommend since a node created out of the pages will fail to load this way!
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 *
 * @module navigation
 * @moduleId _navigation_modul_id_
 */
class class_module_navigation_portal extends class_portal_controller implements interface_portal {

    private $strCurrentSite = "";

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


    private static $arrStaticNodes = array();

    /**
     * Constructor
     *
     * @param array $arrElementData
     */
    public function __construct($arrElementData = array(), $strSystemid = "") {
        parent::__construct($arrElementData, $strSystemid);

        //Determine the current site to load
        $this->strCurrentSite = $this->getPagename();

        //init with the current navigation, required in all cases
        if(isset($this->arrElementData["navigation_id"])) {

            if(!isset(self::$arrStaticNodes[$this->arrElementData["navigation_id"]])) {
                /** @var class_module_navigation_tree $objNavigation */
                $objNavigation = class_objectfactory::getInstance()->getObject($this->arrElementData["navigation_id"]);
                self::$arrStaticNodes[$this->arrElementData["navigation_id"]] = $objNavigation->getCompleteNaviStructure();
            }

            $this->arrTempNodes[$this->arrElementData["navigation_id"]] = self::$arrStaticNodes[$this->arrElementData["navigation_id"]];

        }
        //set the default navigation mode
        $this->setAction("navigationSitemap");

        //foreach($this->arrTempNodes[$this->arrElementData["navigation_id"]]["subnodes"] as $objOneNode)
        //    $this->printTreeLevel(1, $objOneNode);

    }

    /**
     * Adds the code to load the portaleditor
     *
     * @param string $strReturn
     *
     * @return string
     */
    private function addPortaleditorCode($strReturn) {

        $objNavigation = new class_module_navigation_tree($this->arrElementData["navigation_id"]);

        //Add pe code
        $arrPeConfig = array(
            "pe_module"               => "navigation",
            "pe_action_edit"          => "list",
            "pe_action_edit_params"   => "&systemid=".$this->arrElementData["navigation_id"],
            "pe_action_new"           => "",
            "pe_action_new_params"    => "",
            "pe_action_delete"        => "",
            "pe_action_delete_params" => ""
        );

        $arrPeConfigAutoNavigation = array(
            "pe_module"               => "pages",
            "pe_action_edit"          => "list",
            "pe_action_edit_params"   => "&systemid=".$objNavigation->getStrFolderId(),
            "pe_action_new"           => "",
            "pe_action_new_params"    => "",
            "pe_action_delete"        => "",
            "pe_action_delete_params" => ""
        );


        //only add the code, if not auto-generated
        if(!validateSystemid($objNavigation->getStrFolderId()))
            $strReturn = class_element_portal::addPortalEditorCode($strReturn, $this->arrElementData["navigation_id"], $arrPeConfig);
        else
            $strReturn = class_element_portal::addPortalEditorCode($strReturn, $this->arrElementData["navigation_id"], $arrPeConfigAutoNavigation);

        return $strReturn;
    }


    /**
     * creates the code for a sitemap
     *
     * @return string
     * @permissions view
     */
    protected function actionNavigationSitemap() {


        $strReturn = "";
        //check rights on the navigation
        $objNavi = new class_module_navigation_tree($this->arrElementData["navigation_id"]);
        if($objNavi->rightView() && $objNavi->getIntRecordStatus() == 1) {
            //create a stack to highlight the points being active
            $objActivePoint = $this->searchPageInNavigationTree($this->strCurrentSite, $this->arrElementData["navigation_id"], $this->getParam("systemid"), $this->getParam("action"));
            $strStack = $this->getActiveIdStack($objActivePoint);

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
     * @param array $objStartEntry
     * @param string $strStack
     *
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

                //check if it's a foreign node and whether foreign nodes should be included
                if($arrOneChild["node"]->getBitIsForeignNode() && $this->arrElementData["navigation_foreign"] === 0)
                    continue;

                //current point active?
                $bitActive = false;
                if(uniStripos($strStack, $arrOneChild["node"]->getSystemid()) !== false)
                    $bitActive = true;

                //Create the navigation point
                if($intI == 0)
                    $strCurrentPoint = $this->createNavigationPoint($arrOneChild["node"], $bitActive, $intLevel, true);
                elseif($intI == $intNrOfChilds - 1)
                    $strCurrentPoint = $this->createNavigationPoint($arrOneChild["node"], $bitActive, $intLevel, false, true);
                else
                    $strCurrentPoint = $this->createNavigationPoint($arrOneChild["node"], $bitActive, $intLevel);

                //And load all points below
                $strChilds = "";
                if(uniStrpos($strCurrentPoint, "level".($intLevel + 1)) !== false)
                    $strChilds = $this->sitemapRecursive($intLevel + 1, $arrOneChild, $strStack);

                //Put the childs below into the current template
                $this->objTemplate->setTemplate($strCurrentPoint);
                $arrTemp = array("level".($intLevel + 1) => $strChilds);
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
     *
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
     *
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
     * Triggers the usage of a fallback-node and manages the handling of fallback nodes.
     *
     * @param string $strPagename
     * @param string $strNavigationId
     * @param string $strCheckId systemid to check, only used to get active id stack
     * @param string $strCheckAction action to check, only used to get active id stack
     *
     * @return class_module_navigation_point or null
     */
    private function searchPageInNavigationTree($strPagename, $strNavigationId, $strCheckId = "", $strCheckAction = "") {

        $this->arrNodeTempHelper = array();

        //nodestructure given?
        if(!isset($this->arrTempNodes[$strNavigationId])) {
            $objNavigation = new class_module_navigation_tree($strNavigationId);
            $this->arrTempNodes[$strNavigationId] = $objNavigation->getCompleteNaviStructure();
        }

        //process the hierarchy
        $arrNodes = $this->arrTempNodes[$strNavigationId];
        foreach($arrNodes["subnodes"] as $arrOneNode)
            $this->searchPageInNavigationTreeHelper(1, $strPagename, $arrOneNode, $strCheckId, $strCheckAction);

        //process the nodes found
        $intMaxLevel = 0;
        $objEntry = null;
        foreach($this->arrNodeTempHelper as $intLevel => $arrNodes) {
            if(count($arrNodes) > 0 && $intLevel > $intMaxLevel) {
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
                        $this->searchPageInNavigationTreeHelper(1, $strFallbackPage, $arrOneNode, $strCheckId, $strCheckAction);

                    $intMaxLevel = 0;
                    $objEntry = null;
                    foreach($this->arrNodeTempHelper as $intLevel => $arrNodes) {
                        if(count($arrNodes) > 0 && $intLevel > $intMaxLevel) {
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
     * @param string $strCheckId systemid to check, only used to get active id stack
     * @param string $strCheckAction action to check, only used to get active id stack
     */
    private function searchPageInNavigationTreeHelper($intLevel, $strPage, $arrNodes, $strCheckId = "", $strCheckAction = "") {
        if(!isset($this->arrNodeTempHelper[$intLevel]))
            $this->arrNodeTempHelper[$intLevel] = array();

        if($arrNodes["node"]->getStrPageI() == $strPage) {

            //systemid & ation given
            if(validateSystemid($arrNodes["node"]->getStrLinkSystemid()) && $arrNodes["node"]->getStrLinkAction() != "") {
                if($arrNodes["node"]->getStrLinkSystemid() == $strCheckId && $arrNodes["node"]->getStrLinkAction() == $strCheckAction)
                    $this->arrNodeTempHelper[$intLevel][] = $arrNodes["node"];
            }
            //only systemid given
            else if(validateSystemid($arrNodes["node"]->getStrLinkSystemid())) {
                if($arrNodes["node"]->getStrLinkSystemid() == $strCheckId)
                    $this->arrNodeTempHelper[$intLevel][] = $arrNodes["node"];
            }
            //nothing given
            else
                $this->arrNodeTempHelper[$intLevel][] = $arrNodes["node"];
        }

        foreach($arrNodes["subnodes"] as $arrOneSubnode) {
            $this->searchPageInNavigationTreeHelper($intLevel + 1, $strPage, $arrOneSubnode, $strCheckId, $strCheckAction);
        }
    }


    /**
     * Searches the current page in other navigation-trees found on the current page.
     * This can be useful to avoid a session-based "opening" of the current tree.
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
                            $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objPageData->getSystemid(), $arrPlaceholder["placeholder"], $this->getStrPortalLanguage());
                            //maybe on the masters-page?
                            if(count($arrElements) == 0 && $objMasterPageData != null)
                                $arrElements = class_module_pages_pageelement::getElementsByPlaceholderAndPage($objMasterPageData->getSystemid(), $arrPlaceholder["placeholder"], $this->getStrPortalLanguage());

                            if(count($arrElements) > 0) {
                                foreach($arrElements as $objElement) {
                                    //wohooooo, an element was found.
                                    //check, if the current point is in the tree linked by the navigation - if it's a different navigation....
                                    //load the real-pageelement
                                    $objRealElement = new class_element_navigation_portal($objElement);
                                    $arrContent = $objRealElement->getElementContent($objElement->getSystemid());
                                    if(count($arrContent) == 0)
                                        continue;

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
                                        if(count($arrNodes) > 0 && $intLevel >= $intMaxLevel) {
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
     *
     * @return string
     */
    private function createNavigationPoint(class_module_navigation_point $objPointData, $bitActive, $intLevel, $bitFirst = false, $bitLast = false) {
        //and start to create a link and all needed stuff
        $arrTemp = array();
        $arrTemp["page_intern"] = $objPointData->getStrPageI();
        $arrTemp["page_extern"] = $objPointData->getStrPageE();
        $arrTemp["systemid"] = $objPointData->getSystemid();
        $arrTemp["text"] = $objPointData->getStrName();
        $arrTemp["link"] = getLinkPortal($arrTemp["page_intern"], $arrTemp["page_extern"], $objPointData->getStrTarget(), $arrTemp["text"], $objPointData->getStrLinkAction(), "", $objPointData->getStrLinkSystemid());
        $arrTemp["href"] = getLinkPortalHref($arrTemp["page_intern"], $arrTemp["page_extern"], $objPointData->getStrLinkAction(), "", $objPointData->getStrLinkSystemid());
        $arrTemp["target"] = $objPointData->getStrTarget();
        if($objPointData->getStrImage() != "") {
            $arrTemp["image"] = getLinkPortal(
                $arrTemp["page_intern"],
                $arrTemp["page_extern"],
                $objPointData->getStrTarget(),
                "<img src=\""._webpath_.$objPointData->getStrImage()."\" border=\"0\" alt=\"".$arrTemp["text"]."\"/>",
                $objPointData->getStrLinkAction(),
                "",
                $objPointData->getStrSystemid()
            );
            $arrTemp["image_src"] = $objPointData->getStrImage();
        }

        if($objPointData->getStrPageI() != "") {
            $objPage = class_module_pages_page::getPageByName($objPointData->getStrPageI());
            if($objPage != null && $objPage->getIntLmTime() != "")
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
     *
     * @deprecated
     *
     * @param $intLevel
     * @param $arrNodes
     */
    private function printTreeLevel($intLevel, $arrNodes) {
        for($intI = 0; $intI < $intLevel; $intI++) {
            echo "  ";
        }
        echo $arrNodes["node"]->getStrName()."/".$arrNodes["node"]->getSystemid()."/".$arrNodes["node"]->getStrPageI()."<br />\n";

        foreach($arrNodes["subnodes"] as $arrOneNode)
            $this->printTreeLevel($intLevel + 1, $arrOneNode);
    }
}

