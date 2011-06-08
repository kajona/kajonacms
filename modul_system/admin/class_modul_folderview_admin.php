<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/


/**
 * This class provides a list-view of the folders created in the database / filesystem
 *
 * @package modul_system
 */
class class_modul_folderview_admin extends class_admin  implements interface_admin {

	/**
	 * Constructor, doin nothing but a few inits
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 					= "class_modul_folderview_admin";
		$arrModule["author"] 				= "sidler@mulchprod.de";
		$arrModule["moduleId"]				= _filesystem_modul_id_;
		$arrModule["modul"]					= "folderview";
		$arrModule["template"] 				= "/folderview.tpl";

		parent::__construct($arrModule);
		$this->setStrTextBase("filemanager");
	}

	/**
	 * Action block
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
		$strReturn = "";

		//The common list. Used to select files or folders.
		if($strAction == "list") {
            $strElement = "bild";
			if($this->getParam("form_element") != "")
				$strElement = $this->getParam("form_element");

			$strReturn = $this->getListFilemanager($strElement);
		}

		else if($strAction == "folderList") 	{
            $strFolder = "/portal/pics";
			if($this->getParam("folder") != "")
				$strFolder = $this->getParam("folder");

            $arrSuffix = array();
			if($this->getParam("suffix") != "")
				$arrSuffix = explode("|", $this->getParam("suffix"));

            $arrExclude = array();
			if($this->getParam("exclude") != "")
				$arrExclude = explode("|", $this->getParam("exclude"));

            $bitFolder = true;
			if($this->getParam("bit_folder") != "")
				$bitFolder = $this->getParam("bit_folder");

            $bitFile = true;
			if($this->getParam("bit_file") != "")
				$bitFile = $this->getParam("bit_dateien");

            $arrExcludeFolder = array(0 => ".", 1 => "..");
			if($this->getParam("exclude_folder") != "")
				$arrExcludeFolder = explode("|", $this->getParam("exclude_folder"));

            $strElement = "bild";
			if($this->getParam("form_element") != "")
				$strElement = $this->getParam("form_element");

			$strReturn = $this->getFolderlist($strFolder, $arrSuffix, $arrExclude, $arrExcludeFolder, $bitFolder, $bitFile, $strElement, $this->getParam("detail"));
		}

		else if($strAction == "pagesFolderBrowser") {
			$bitPages = ($this->getParam("pages") != "" ? true : false);
			$bitPageAliases = ($this->getParam("pagealiases") != "" ? true : false);
			$bitElements = ($this->getParam("elements") == "false" ? false : true);
			$bitFolder = ($this->getParam("folder") != "" ? true : false);
			$strFolderid = ($this->getParam("folderid") != "" ? $this->getParam("folderid") : $this->getModuleSystemid("pages") );
			$strElement = ($this->getParam("form_element") != "" ? $this->getParam("form_element") : "ordner_name");
            $strPageid = ($this->getParam("pageid") != "" ? $this->getParam("pageid") : "0" );

			$strReturn = $this->pagesFolderBrowser($strFolderid, $bitPages, $strElement, $strPageid, $bitElements, $bitFolder, $bitPageAliases);
		}


        else
            $strReturn = parent::action($strAction);

        //if set, save CKEditors CKEditorFuncNum parameter to read it again in KAJONA.admin.folderview.selectCallback()
        //so we don't have to pass through the param with all requests
	    if ($this->getParam("CKEditorFuncNum") != "") {
            $strReturn .= "<script type=\"text/javascript\">window.opener.KAJONA.admin.folderview.selectCallbackCKEditorFuncNum = ".(int)$this->getParam("CKEditorFuncNum").";</script>";
        }

		$this->strOutput = $strReturn;
	}



    protected function getOutputModuleTitle() {
        return $this->getText("moduleFolderviewTitle");
    }

	/**
	 * Opens the filemanager to browse files
	 *
	 * @param string $strTargetfield
	 * @return string
	 */
	public function getListFilemanager($strTargetfield) {
        $objFilemanager = new class_modul_filemanager_admin();
        return $objFilemanager->actionFolderContentFolderviewMode($strTargetfield);
	}


	/**
	* @return void
	* @param string $verzeichnis
	* @param mixed $arr_endung
	* @param mixed $arr_ausschluss
	* @param mixed $arr_ausschluss_ordner
	* @param bool $bit_ordner
	* @param bool $bit_dateien
	* @desc Laedt eine Liste der Ordner im Dateisystem
	*/
	public function getFolderlist($strFolder, $arrSuffix = array(), $arrExclude = array(), $arrExcludeFolder = array(), $bitFolder = true, $bitFiles = false, $strFormElement = "bild", $strDetail = "") {
	    $strReturn = "";
		$objFilesystem = new class_filesystem();
		$arrContent = $objFilesystem->getCompleteList($strFolder, $arrSuffix, $arrExclude, $arrExcludeFolder, $bitFolder, false);

		$strReturn .= $this->objToolkit->listHeader();
		$strReturn .= $this->objToolkit->listRow2($this->getText("pfad"), $strFolder, 1);
		$strReturn .= $this->objToolkit->listRow2($this->getText("ordner_anz"), $arrContent["nrFolders"], 1);
		$strReturn .= $this->objToolkit->listFooter();
		$strReturn .= $this->objToolkit->divider();

        $intCounter = 0;
		//Show Folders
		//Folder to jump one back up
		$arrFolderStart = array("/portal");
		$bitHit = false;
		if(!in_array($strFolder, $arrFolderStart) && $bitHit == false) {
			$strReturn .= $this->objToolkit->listHeader();
			$strAction = $this->objToolkit->listButton(getLinkAdmin("folderview", "folderList", "&folder=".uniSubstr($strFolder, 0, uniStrrpos($strFolder, "/"))."&suffix=".implode("|", $arrSuffix)."&exclude=".implode("|", $arrExclude)."&bit_folder=".$bitFolder."&bit_file=".$bitFiles."&form_element=".$strFormElement, $this->getText("ordner_hoch"), $this->getText("ordner_hoch"), "icon_folderActionLevelup.gif"));
			$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), "..", $strAction, $intCounter++);
			$bitHit = true;
		}
		if($arrContent["nrFolders"] != 0) {
			if(!$bitHit)
				$strReturn .= $this->objToolkit->listHeader();
			$bitHit = true;
			foreach($arrContent["folders"] as $strFolderCur) {
				$strAction = $this->objToolkit->listButton(getLinkAdmin("folderview", "folderList", "&folder=".$strFolder."/".$strFolderCur."&suffix=".implode("|", $arrSuffix)."&exclude=".implode("|", $arrExclude)."&bit_folder=".$bitFolder."&bit_file=".$bitFiles."&form_element=".$strFormElement, $this->getText("ordner_oeffnen"), $this->getText("ordner_oeffnen"), "icon_folderActionOpen.gif"));
				$strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("ordner_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strFormElement."', '".$strFolder."/".$strFolderCur."']]);\">".getImageAdmin("icon_accept.gif"));
				$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif", "Ordner"), $strFolderCur, $strAction, $intCounter++);
			}
		}
		if($bitHit)
		  $strReturn .= $this->objToolkit->listFooter();
        return $strReturn;
	}


	/**
	 * Returns a list of folders in the pages-database
	 *
	 * @param string $strFolder
	 * @param bool $bitPages
	 * @param bool $strElement
	 * @param string $strPageid
     * @param bool $bitFolderselect
     * @param bool $bitPageelements
     * @param bool $bitPageAliases
	 * @return String
	 */
	private function pagesFolderBrowser($strFolder, $bitPages, $strElement, $strPageid, $bitPageelements, $bitFolder, $bitPageAliases) {
		$strReturn = "";
		$intCounter = 1;


		$arrFolder = class_modul_pages_folder::getFolderList($strFolder);
        $objFolder = new class_modul_pages_folder($strFolder);
		$strLevelUp = "";

		if(validateSystemid($strFolder) && $strFolder != $this->getModuleSystemid("pages"))
			$strLevelUp = $objFolder->getPrevId();
		//but: when browsing pages the current level should be kept
		iF($strPageid != "0")
		   $strLevelUp = $strFolder;

		$strReturn .= $this->objToolkit->listHeader();
		//Folder to jump one level up
		if(!$bitPages || $strLevelUp != "" || $bitFolder) {
			$strAction = $this->objToolkit->listButton(($strFolder != "0" && $strLevelUp!= "") || $strPageid != "0" ? getLinkAdmin("folderview", "pagesFolderBrowser", "&folderid=".$strLevelUp.($bitFolder ? "&folder=1" : "").($bitPages ? "&pages=1" : "").(!$bitPageelements ? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : "")."&form_element=".$strElement.($this->getParam("bit_link")  != "" ? "&bit_link=1" : ""), $this->getText("ordner_hoch"), $this->getText("ordner_hoch"), "icon_folderActionLevelup.gif") :  " " );
			if($strFolder == $this->getModuleSystemid("pages") && (!$bitPages || $bitFolder))
				$strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("ordner_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$this->getModuleSystemid("pages")."'], ['".$strElement."', '']]);\">".getImageAdmin("icon_accept.gif"));

			$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), "..", $strAction, $intCounter++);
		}

		if(count($arrFolder) > 0 && $strPageid == "0") {
			foreach($arrFolder as $objSingleFolder) {
				if($bitPages && !$bitFolder) {
					$strAction = $this->objToolkit->listButton(getLinkAdmin("folderview", "pagesFolderBrowser", "&folderid=".$objSingleFolder->getSystemid().($bitPages ? "&pages=1" : "")."&form_element=".$strElement.($bitFolder ? "&folder=1" : "").(!$bitPageelements? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : "").($this->getParam("bit_link")  != "" ? "&bit_link=1" : "")."", $this->getText("ordner_oeffnen"), $this->getText("ordner_oeffnen"), "icon_folderActionOpen.gif"));
					$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), $objSingleFolder->getStrName(), $strAction, $intCounter++);
				}
				else {
				    $strAction = $this->objToolkit->listButton(getLinkAdmin("folderview", "pagesFolderBrowser", "&folderid=".$objSingleFolder->getSystemid()."&form_element=".$strElement.($bitPages ? "&pages=1" : "").($bitFolder ? "&folder=1" : "").($this->getParam("bit_link")  != "" ? "&bit_link=1" : "").(!$bitPageelements? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : ""), $this->getText("ordner_oeffnen"), $this->getText("ordner_oeffnen"), "icon_folderActionOpen.gif"));
					$strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("ordner_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSingleFolder->getSystemid()."'], ['".$strElement."', '".$objSingleFolder->getStrName()."']]); \">".getImageAdmin("icon_accept.gif"));
					$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), $objSingleFolder->getStrName(), $strAction, $intCounter++);
				}
			}

		}
		$strReturn .= $this->objToolkit->listFooter();

		//Pages could be sent too
		if($bitPages && $strPageid == "0") {
			$strReturn .= $this->objToolkit->divider();
			$arrPages = class_modul_pages_folder::getPagesInFolder($strFolder);
			if(count($arrPages) > 0) {
				$strReturn .= $this->objToolkit->listHeader();
				foreach($arrPages as $objSinglePage) {
                    $arrSinglePage = array();
					//Should we generate a link ?
					if($this->getParam("bit_link") != "")
						$arrSinglePage["name2"] = getLinkPortalHref($objSinglePage->getStrName(), "", "", "", "", $this->getLanguageToWorkOn());
					else
						$arrSinglePage["name2"] = $objSinglePage->getStrName();

                    $strAction = "";
                    if ($objSinglePage->getIntType() == class_modul_pages_page::$INT_TYPE_ALIAS) {
	                    $strAction = $this->objToolkit->listButton(getLinkAdmin("folderview", "pagesFolderBrowser", "&folderid=".$objSinglePage->getSystemid()."&form_element=".$strElement.($bitPages ? "&pages=1" : "").($bitFolder ? "&folder=1" : "").($this->getParam("bit_link")  != "" ? "&bit_link=1" : "").(!$bitPageelements? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : ""), $this->getText("page_sublist"), $this->getText("page_sublist"), "icon_treeBranchOpen.gif"));
	                    if ($bitPageAliases) {
	                    	$strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("seite_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSinglePage->getSystemid()."'],['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
	                    }
						$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_page_alias.gif"), $objSinglePage->getStrBrowsername()." (-> ".uniStrTrim($objSinglePage->getStrAlias(), 20).")", $strAction, $intCounter++);
                    } else {
                        $strAction = $this->objToolkit->listButton(getLinkAdmin("folderview", "pagesFolderBrowser", "&folderid=".$objSinglePage->getSystemid()."&form_element=".$strElement.($bitPages ? "&pages=1" : "").($bitFolder ? "&folder=1" : "").($this->getParam("bit_link")  != "" ? "&bit_link=1" : "").(!$bitPageelements? "&elements=false" : "").($bitPageAliases ? "&pagealiases=1" : ""), $this->getText("page_sublist"), $this->getText("page_sublist"), "icon_treeBranchOpen.gif"));
                        if($bitPageelements)
                            $strAction .= $this->objToolkit->listButton(getLinkAdmin("folderview", "pagesFolderBrowser", "&folderid=".$strFolder."&form_element=".$strElement."&pageid=".$objSinglePage->getSystemid().($this->getParam("bit_link")  != "" ? "&bit_link=1" : "").($bitPages ? "&pages=1" : "").($bitPageAliases ? "&pagealiases=1" : ""), $this->getText("seite_oeffnen"), $this->getText("seite_oeffnen"), "icon_folderActionOpen.gif"));
                        $strAction .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("seite_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."_id', '".$objSinglePage->getSystemid()."'],['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
                        $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_page.gif"), $objSinglePage->getStrBrowsername()." (".$objSinglePage->getStrName().")", $strAction, $intCounter++);

                    }
				}
				$strReturn .= $this->objToolkit->listFooter();
			}
		}

		//Load the list of pagelements available on the page
		if($strPageid != "0") {
		    $strReturn .= $this->objToolkit->divider();
            $arrPageelements = class_modul_pages_pageelement::getElementsOnPage($strPageid, true, $this->objSession->getAdminLanguage());
            $objPage = new class_modul_pages_page($strPageid);
            if(count($arrPageelements) > 0) {
                $strReturn .= $this->objToolkit->listHeader();
                foreach($arrPageelements as $objOnePageelement) {
                    $arrSinglePage = array();
                    //Should we generate a link ?
					if($this->getParam("bit_link") != "")
						$arrSinglePage["name2"] = getLinkPortalHref($objPage->getStrName(), "", "", "", "", $this->getLanguageToWorkOn())."#".$objOnePageelement->getSystemid();
					else
						$arrSinglePage["name2"] = $objPage->getStrName()."#".$objOnePageelement->getSystemid();

					$strAction = $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("seite_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strElement."', '".$arrSinglePage["name2"]."']]);\">".getImageAdmin("icon_accept.gif")."</a>");
					$strReturn .= $this->objToolkit->listRow2($objOnePageelement->getStrTitle()." (".$objOnePageelement->getStrName().")", $strAction, $intCounter++);
                }
                $strReturn .= $this->objToolkit->listFooter();
            }
		}

		return $strReturn;
	}

	/**
	 * Returns a list of available navigations
	 *
	 */
	protected function actionNavigationBrowser() {
		$strReturn = "";
		$intCounter = 1;
		//Load all navis
		$arrNavis = class_modul_navigation_tree::getAllNavis();


		$strReturn .= $this->objToolkit->listHeader();
		foreach($arrNavis as $objOnenavigation) {
		    $strAction = $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getText("ordner_uebernehmen")."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" onclick=\"KAJONA.admin.folderview.selectCallback([['navigation_name', '".$objOnenavigation->getStrName()."'], ['navigation_id', '".$objOnenavigation->getSystemid()."']]);\">".getImageAdmin("icon_accept.gif"));
			$strReturn .= $this->objToolkit->listRow2($objOnenavigation->getStrName(), $strAction, $intCounter++);
		}
        $strReturn .= $this->objToolkit->listFooter();
		return $strReturn;
	}

    protected function actionBrowserChooser() {
        $strReturn = "";

		$intCounter = 1;
        $strReturn .= $this->objToolkit->listHeader();

        $strAction = $this->objToolkit->listButton(getLinkAdmin("folderview", "pagesFolderBrowser", "&pages=1&form_element=".$this->getParam("form_element")."&bit_link=1", $this->getText("wysiwygPagesBrowser"), $this->getText("wysiwygPagesBrowser"), "icon_folderActionOpen.gif"));
        $strReturn .= $this->objToolkit->listRow2($this->getText("wysiwygPagesBrowser"), $strAction, $intCounter++);

        $strAction = $this->objToolkit->listButton(getLinkAdmin("folderview", "list", "&systemid="._filemanager_default_filesrepoid_."&form_element=".$this->getParam("form_element")."&bit_link=1", $this->getText("wysiwygFilesBrowser"), $this->getText("wysiwygFilesBrowser"), "icon_folderActionOpen.gif"));
        $strReturn .= $this->objToolkit->listRow2($this->getText("wysiwygFilesBrowser"), $strAction, $intCounter++);

        $strReturn .= $this->objToolkit->listFooter();
		return $strReturn;
    }

}
?>