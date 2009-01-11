<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/

//base class
include_once(_portalpath_."/class_portal.php");
//model
include_once(_systempath_."/class_modul_pages_element.php");
include_once(_systempath_."/class_modul_pages_page.php");
include_once(_systempath_."/class_modul_pages_pageelement.php");
include_once(_systempath_."/class_http_statuscodes.php");

/**
 * Handles the loading of the pages - loads the elements, passes control to them and returns the complete
 * page ready for output
 *
 * @package modul_pages
 */
class class_modul_pages_portal extends class_portal {

    private $objPagecache;
    
    private static $strAdditionalHeader = "";

	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 			= "modul_pages";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_modul_id_;
		$arrModul["modul"]			= "pages";

		parent::__construct($arrModul);

		//in nearly every case, we'll need a pagecache-object
		include_once(_systempath_."/class_modul_pages_pagecache.php");
		$this->objPagecache = new class_modul_pages_pagecache();
	}

	/**
	 * Handles the loading of a page, more in a functional than in an oop style
	 *
	 */
	public function generatePage() {
		//Determin the pagename
		$strPagename = $this->getPagename();

		//At first: look up the pagecache or check, if its a preview
		$strPageFromCache = "";
		if(_pages_cacheenabled_ == "true")
		    $strPageFromCache = $this->objPagecache->loadPageFromCache($strPagename, $this->objSession->getUserID());

		//if using the pe, the cache shouldn't be used, otherwise strange things might happen.
		//the system could frighten your cat or eat up all your cheese with marshmellows...
		$bitPeRequested = false;
		if(_pages_portaleditor_ == "true" && $this->objSession->isAdmin()) {
		    //Load the data of the page
		    $objPageData = class_modul_pages_page::getPageByName($strPagename);
		    if($objPageData->rightEdit()) {
		        $bitPeRequested = true;
		    }
		}

		if(_pages_cacheenabled_ == "true" && $this->getParam("preview") != "1" && uniStrlen($strPageFromCache) != 0 && $bitPeRequested === false) {
		   $this->strOutput = $strPageFromCache;
		   return;
		}

		//If we reached up till here, no cached page was found, so generate it from scratch.
		//Keep the max cachetime to save the Page to cache.
		$intMaxCachetime = _pages_maxcachetime_;


		//Load the data of the page
		$objPageData = class_modul_pages_page::getPageByName($strPagename);
		//check, if the page is enabled and if the rights are given, or if we want to load a preview of a page
		$bitErrorpage = false;
		if($objPageData->getStrName() == "" || ($objPageData->getStatus() != 1 || !$this->objRights->rightView($objPageData->getSystemid())))
			$bitErrorpage = true;

		//but: if count != 0 && preview && rights:
		if($bitErrorpage && $objPageData->getStrName() != "" && $this->getParam("preview") == "1" && $this->objRights->rightView($objPageData->getSystemid()))
			$bitErrorpage = false;

		//check, if the template could be loaded
		try {
		    $strTemplateID = $this->objTemplate->readTemplate("/modul_pages/".$objPageData->getStrTemplate(), "", false, true);
		}
		catch (class_exception $objException) {
            $bitErrorpage = true;
		}

		if($bitErrorpage) {
			//Unfortunately, we have to load the errorpage

			//try to send the correct header
			//page not found
            if($objPageData->getStrName() == "" || $objPageData->getStatus() != 1)
			    header(class_http_status_codes::$strSC_NOT_FOUND);

			//user is not allowed to view the page
			if($objPageData->getStrName() != "" && !$this->objRights->rightView($objPageData->getSystemid()))
			    header(class_http_status_codes::$strSC_FORBIDDEN);

			//and load the errorpage itself
			$strFirstPagename = $strPagename;
			$strPagename = _pages_fehlerseite_;
			$objPageData = class_modul_pages_page::getPageByName($strPagename);

			//check, if the page is enabled and if the rights are given, too
			if($objPageData->getStrName() == "" || ($objPageData->getStatus() != 1 || !$this->objRights->rightView($objPageData->getSystemid()))) {
				//Whoops. Nothing to output here
				throw new class_exception("Requested Page ".$strFirstPagename." not existing, no errorpage created or set!", class_exception::$level_FATALERROR);
				return;
			}
		}

		//react on portaleditor commands
        //pe to display, or pe to diable?
        if($this->getParam("pe") == "false") {
            $this->objSession->setSession("pe_disable", "true");
        }
        if($this->getParam("pe") == "true") {
            $this->objSession->setSession("pe_disable", "false");
        }

		//If we reached up till here, we can begin loading the elements to fill
		$arrElementsOnPage = array();

	    $arrElementsOnPage = class_modul_pages_pageelement::getElementsOnPage($objPageData->getSystemid(), true, $this->getPortalLanguage());
		//If theres a master-page, load elements on that, too
		$objMasterData = class_modul_pages_page::getPageByName("master");
		if($objMasterData->getStrName() != "") {
			$arrElementsOnMaster = class_modul_pages_pageelement::getElementsOnPage($objMasterData->getSystemid(), true, $this->getPortalLanguage());
			//and merge them
			$arrElementsOnPage = array_merge($arrElementsOnPage, $arrElementsOnMaster);
		}
		
		//Load the template from the filesystem to get the placeholders
        $strTemplateID = $this->objTemplate->readTemplate("/modul_pages/".$objPageData->getStrTemplate(), "", false, true);
        //bit include the masters-elements!!
        $arrRawPlaceholders = array_merge($this->objTemplate->getElements($strTemplateID, 0), $this->objTemplate->getElements($strTemplateID, 1));
        
        $arrPlaceholders = array();
        //and retransform
        foreach ($arrRawPlaceholders as $arrOneRawPlaceholder)
            $arrPlaceholders[] = $arrOneRawPlaceholder["placeholder"];

        //copy for the portaleditor
        $arrPlaceholdersFilled = array();
		
		//Iterate over all elements and pass control to them
		//Get back the filled element
		//Build the array to fill the template
		$arrTemplate = array();

		foreach($arrElementsOnPage as $objOneElementOnPage) {
			//element really available on the template?
			if(!in_array($objOneElementOnPage->getStrPlaceholder(), $arrPlaceholders)) {
				//next one, plz
				continue;
			}
            else {
                //create a protocol of placeholders filled
                //remove from pe-additional-array, pe code is injected by element directly
                $arrPlaceholdersFilled[] = array("placeholder" => $objOneElementOnPage->getStrPlaceholder(), "name" => $objOneElementOnPage->getStrName(), "element" => $objOneElementOnPage->getStrElement());
            }
		    //Check if the max-cachetime is lower than the current one set
		    //include the "please hide the element" time
		    if($objOneElementOnPage->getIntCachetime() != 0) {
		        $intMaxCachetimeToCompare = (int)$objOneElementOnPage->getIntCachetime();

		        //check the max display time
                if((int)($objOneElementOnPage->getEndDate() - time() ) > 0 && ($objOneElementOnPage->getEndDate()-time()) < $intMaxCachetimeToCompare )
                    $intMaxCachetimeToCompare = ($objOneElementOnPage->getEndDate()-time());

		        if((int)$intMaxCachetime > (int)$intMaxCachetimeToCompare)
		           $intMaxCachetime = $intMaxCachetimeToCompare;
		    }
			//Include the portal-class of the element
			include_once(_portalpath_."/elemente/".$objOneElementOnPage->getStrClassPortal());
			//Build the class-name for the object
			$strClassname = uniSubstr($objOneElementOnPage->getStrClassPortal(), 0, -4);
			$objElement = new $strClassname($objOneElementOnPage);
			//let the element do the work and earn the output
			if(!isset($arrTemplate[$objOneElementOnPage->getStrPlaceholder()]))
				$arrTemplate[$objOneElementOnPage->getStrPlaceholder()] = "";

			$strElementOutput = $objElement->getElementOutput();

			//any string to highlight?
    		if($this->getParam("highlight") != "") {
    		    $strHighlight = strtolower($this->getParam("highlight"));
    		    //search for matches, but exclude tags
    		    $strElementOutput = preg_replace("#(?!<.*)(?<!\w)(".$strHighlight.")(?!\w|[^<>]*>)#i", "<span class=\"searchHighlight\"><a name=\"$1\">$1</a></span>", $strElementOutput);
    		}

			$arrTemplate[$objOneElementOnPage->getStrPlaceholder()] .= $strElementOutput;
		}

        //pe-code to add new elements on unfilled placeholders --> only if pe is visible?
        if(_pages_portaleditor_ == "true" && $objPageData->rightEdit() && $this->objSession->isAdmin() && $this->objSession->getSession("pe_disable") != "true" ) {
            //loop placeholders on template in order to remove already filled ones
            $arrRawPlaceholdersForPe = $arrRawPlaceholders;
            foreach($arrPlaceholdersFilled as $arrOnePlaceholder) {
                foreach($arrRawPlaceholdersForPe as &$arrOneRawPlaceholder) {
                    if($arrOneRawPlaceholder["placeholder"] == $arrOnePlaceholder["placeholder"]) {
                        foreach($arrOneRawPlaceholder["elementlist"] as $intElementKey => $arrOneRawElement) {
                            if($arrOneRawElement["name"] == $arrOnePlaceholder["name"] && $arrOneRawElement["element"] == $arrOnePlaceholder["element"]) {
                                $arrOneRawPlaceholder["elementlist"][$intElementKey] = null;
                            }
                        }
                    }
                }
            }
            
            //array is now set up. loop again to create new-buttons
            //var_dump($arrRawPlaceholdersForPe);
            $arrPePlaceholdersDone = array();
            foreach($arrRawPlaceholdersForPe as $arrOneRawPlaceholderForPe) {
                $strPeNewPlaceholder = $arrOneRawPlaceholderForPe["placeholder"];
                foreach($arrOneRawPlaceholderForPe["elementlist"] as $arrOnePeNewElement) {
                    if($arrOnePeNewElement != null) {
                        
                        //check if the linked element exists
                        $objPeNewElement = class_modul_pages_element::getElement($arrOnePeNewElement["element"]);
                        if($objPeNewElement != null) {
                            //placeholder processed before?
                            $strArrayKey = $strPeNewPlaceholder.$objPeNewElement->getStrName();
                            if(in_array($strArrayKey, $arrPePlaceholdersDone))
                                continue;
                            else
                                $arrPePlaceholdersDone[] = $strArrayKey;

                            //create and register the button to add a new element. Therefore generate an image-tag.
                            if(!isset($arrTemplate[$strPeNewPlaceholder]))
                                $arrTemplate[$strPeNewPlaceholder] = "";

                            $strLink = class_element_portal::getPortaleditorNewCode($objPageData->getSystemid(), $strPeNewPlaceholder, $objPeNewElement->getStrName());
                            
                            $arrTemplate[$strPeNewPlaceholder] .= $strLink;

                        }
                    }
                }
            }
        }

		$arrTemplate["description"] = $objPageData->getStrDesc();
		$arrTemplate["keywords"] = $objPageData->getStrKeywords();
		$arrTemplate["title"] = $objPageData->getStrBrowsername();
		$arrTemplate["additionalTitle"] = self::$strAdditionalHeader;
		//Include the $arrGlobal Elements
		$arrGlobal = array();
		include(_portalpath_."/global_includes.php");
		$arrTemplate = array_merge($arrTemplate, $arrGlobal);
		//fill the template. the template was read before
		$strPageContent = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

        //add the portaleditor toolbar
        if(_pages_portaleditor_ == "true" && $objPageData->rightEdit() && $this->objSession->isAdmin()) {

    		if(!defined("skinwebpath_"))
    		    define("_skinwebpath_", _webpath_."/admin/skins/".$this->objSession->getAdminSkin());
    		    
    		//save back the current portal text language and set the admin-one
    		$strPortalLanguage = class_carrier::getInstance()->getObjText()->getStrTextLanguage();
    		class_carrier::getInstance()->getObjText()->setStrTextLanguage($this->objSession->getAdminLanguage());

            if($this->objSession->getSession("pe_disable") != "true" ) {
    		    $strPeToolbar = "";
    		    $arrPeContents = array();
    		    $arrPeContents["pe_status_page"] = $this->getText("pe_status_page", "pages", "admin");
    		    $arrPeContents["pe_status_status"] = $this->getText("pe_status_status", "pages", "admin");
    		    $arrPeContents["pe_status_autor"] = $this->getText("pe_status_autor", "pages", "admin");
    		    $arrPeContents["pe_status_time"] = $this->getText("pe_status_time", "pages", "admin");
                $arrPeContents["pe_status_page_val"] = $objPageData->getStrName();
    		    $arrPeContents["pe_status_status_val"] = ($objPageData->getStatus() == 1 ? "active" : "inactive" );
    		    $arrPeContents["pe_status_autor_val"] = $objPageData->getLastEditUser();
    		    $arrPeContents["pe_status_time_val"] = timeToString($objPageData->getEditDate(), false);

                //Add a iconbar
    		    $arrPeContents["pe_iconbar"] = "";
    		    $arrPeContents["pe_iconbar"] .= getLinkAdmin("pages_content", "list", "&systemid=".$objPageData->getSystemid(), $this->getText("pe_icon_edit"), $this->getText("pe_icon_edit", "pages", "admin"), "icon_pencil.gif");
    		    $arrPeContents["pe_iconbar"] .= "&nbsp;";
    		    $arrPeContents["pe_iconbar"] .= getLinkAdmin("pages", "newPage", "&systemid=".$objPageData->getSystemid(), $this->getText("pe_icon_page"), $this->getText("pe_icon_page", "pages", "admin"), "icon_page.gif");

    		    $arrPeContents["pe_disable"] = "<a href=\"#\" onclick=\"portalEditorDisable();\" title=\"\">".getNoticeAdminWithoutAhref($this->getText("pe_disable", "pages", "admin"), "icon_enabled.gif")."</a>";

    		    $strPeToolbar .= $this->objToolkit->getPeToolbar($arrPeContents);
    		    //Load tooltips
                $strPeToolbar .= "\n<script language=\"Javascript\" type=\"text/javascript\">function enableTooltipsWrapper() { enableTooltips(\"showTooltip\"); } addLoadEvent(enableTooltipsWrapper);</script>";
                //Load portaleditor styles
                $strPeToolbar .= "\n<script language=\"Javascript\" type=\"text/javascript\">addCss(\""._skinwebpath_."/styles_portaleditor.css\");</script>";
    		    //The toolbar has to be added right after the body-tag - to generate correct html-code
    		    $strTemp = uniSubstr($strPageContent, uniStrpos($strPageContent, "<body"));
    		    //find closing bracket
    		    $intTemp = uniStrpos($strTemp, ">")+1;
    		    //and insert the code
    		    $strPageContent = uniSubstr($strPageContent, 0, uniStrpos($strPageContent, "<body")+$intTemp) .$strPeToolbar.uniSubstr($strPageContent, uniStrpos($strPageContent, "<body")+$intTemp) ;
            }
            else {
                //Button to enable the toolbar & pe
                $strEnableButton = "<div id=\"peEnableButton\"><a href=\"#\" onclick=\"portalEditorEnable();\" title=\"\">".getNoticeAdminWithoutAhref($this->getText("pe_enable", "pages", "admin"), "icon_disabled.gif")."</a></div>";
                //Load tooltips
                $strEnableButton .= "\n<script language=\"Javascript\" type=\"text/javascript\">function enableTooltipsWrapper() { enableTooltips(\"showTooltip\"); } addLoadEvent(enableTooltipsWrapper);</script>";
                //Load portaleditor styles
                $strEnableButton .= "\n<script language=\"Javascript\" type=\"text/javascript\">addCss(\""._skinwebpath_."/styles_portaleditor.css\");</script>";
                //The toobar has to be added right after the body-tag - to generate correct html-code
    		    $strTemp = uniSubstr($strPageContent, uniStrpos($strPageContent, "<body"));
    		    //find closing bracket
    		    $intTemp = uniStrpos($strTemp, ">")+1;
    		    //and insert the code
    		    $strPageContent = uniSubstr($strPageContent, 0, uniStrpos($strPageContent, "<body")+$intTemp) .$strEnableButton.uniSubstr($strPageContent, uniStrpos($strPageContent, "<body")+$intTemp) ;
            }
            
            //reset the portal texts language
            class_carrier::getInstance()->getObjText()->setStrTextLanguage($strPortalLanguage);
        }

        //insert the copyright headers. Due to our licence, you are NOT allowed to remove those lines.
        $strHeader  = "<!--\n";
        $strHeader .= "Website powered by Kajona³ Open Source Content Management Framework\n";
        $strHeader .= "For more information about Kajona see http://www.kajona.de\n";
        $strHeader .= "-->\n";
        $strPageContent = $strHeader.$strPageContent;
        
		//save the generated Page to the cache
		if(_pages_cacheenabled_ == "true" && $this->getParam("preview") != "1" && !$bitErrorpage)
		   $this->objPagecache->savePageToCache($strPagename, $intMaxCachetime, $this->objSession->getUserID(), $strPageContent);

		$this->strOutput = $strPageContent;
	}


	public function cacheCleanup() {
	    $this->objPagecache->cacheCleanup();
	}

	/**
	 * Sets the passed text as an additional title-information.
	 * If set, the separator-placeholder from global-includes will be included, too.
	 * @param string $strTitle
	 * @return void
	 */
	public static function registerAdditionalTitle($strTitle) {
		self::$strAdditionalHeader = "%%kajonaTitleSeparator%%".$strTitle;
	}

} 
?>