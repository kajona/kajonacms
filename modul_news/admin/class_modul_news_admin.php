<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_news_admin.php																			*
* 	Admin-class of the news          																	*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

//Base class
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_admin.php");
//model
include_once(_systempath_."/class_modul_news_category.php");
include_once(_systempath_."/class_modul_news_feed.php");
include_once(_systempath_."/class_modul_news_news.php");

/**
 * Admin-Class of the news-module. Responsible for editing news, organizing them in categories and creating feeds
 *
 * @package modul_news
 */
class class_modul_news_admin extends class_admin implements interface_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 				= "modul_news";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _news_modul_id_;
		$arrModul["table"] 			    = _dbprefix_."news";
		$arrModul["table2"]			    = _dbprefix_."news_category";
		$arrModul["table3"]			    = _dbprefix_."news_member";
		$arrModul["table4"]			    = _dbprefix_."news_feed";
		$arrModul["modul"]				= "news";

		//Base class
		parent::__construct($arrModul);

	}

	/**
	 * Action block to control the class
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
	    $strReturn = "";
	    if($strAction == "")
	       $strAction = "list";

	    try {

    		if($strAction == "list")
    			$strReturn = $this->actionList();
    		if($strAction == "newCat")
    			$strReturn = $this->actionNewCat("new");
    		if($strAction == "editCat")
    			$strReturn = $this->actionNewCat("edit");
    		if($strAction == "saveCat") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSaveCat();
    			    if($strReturn == "")
                        $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		    }
    		    else {
    		        if($this->getParam("mode") == "new")
    		            $strReturn = $this->actionNewCat("new");
    		        else
    		            $strReturn = $this->actionNewCat("edit");
    		    }
    		}
    		if($strAction == "deleteCat") {
    			$strReturn = $this->actionDeleteCategory();
    			if($strReturn == "")
                    $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		}

    		if($strAction == "newNews")
    			$strReturn = $this->actionNewNews("new");
    		if($strAction == "editNews")
    			$strReturn = $this->actionNewNews("edit");
    		if($strAction == "saveNews") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSaveNews();
    			    if($strReturn == "")
                       $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		    }
    		    else  {
    		        if($this->getParam("mode") == "new")
    		            $strReturn = $this->actionNewNews("new");
    		        else
    		            $strReturn = $this->actionNewNews("edit");
    		    }
    		}
    		if($strAction == "deleteNews") {
    			$strReturn = $this->actionDeleteNews();
    			if($strReturn == "")
                    $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		}

    		if($strAction == "editNewscontent")
    			$strReturn = $this->actionEditNewscontent();
    		if($strAction == "saveNewscontent") {
    			$strReturn = $this->actionSaveNewscontent();
    			if($strReturn == "")
                    $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		}

    		if($strAction == "newsFeed")
    		    $strReturn = $this->actionListNewsFeed();

    		if($strAction == "newNewsFeed") {
    		    $strReturn .= $this->actionCreateNewsFeed();
    		    if($strReturn == "")
    		        $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]."&action=newsFeed");
    		}

    		if($strAction == "editNewsFeed") {
    		    $strReturn .= $this->actionEditNewsFeed();
    		    if($strReturn == "")
    		        $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]."&action=newsFeed");
    		}

    		if($strAction == "deleteNewsFeed") {
    		    $strReturn .= $this->actionDeleteNewsFeed();
    		    if($strReturn == "")
    		        $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]."&action=newsFeed");
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
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newNews", "", $this->getText("modul_anlegen"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newCat", "", $this->getText("modul_kat_anlegen"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
	    $arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "newsFeed", "", $this->getText("modul_list_feed"), "", "", true, "adminnavi"));
		$arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "newNewsFeed", "", $this->getText("modul_new_feed"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


	protected function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "saveCat") {
            $arrReturn["news_cat_title"] = "string";
        }
        if($strAction == "saveNews") {
            $arrReturn["news_title"] = "string";
        }
        if($strAction == "newNewsFeed" || $strAction == "editNewsFeed") {
            $arrReturn["feed_title"] = "string";
            $arrReturn["feed_urltitle"] = "string";
            $arrReturn["feed_page"] = "string";
        }

        return $arrReturn;
    }

// --- ListenFunktionen ---------------------------------------------------------------------------------


	/**
	 * Returns a list of all categories and all news
	 * The list could be filtered by categories
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

    		//Load Categories
    		$arrCategories = class_modul_news_category::getCategories();
    		$intI = 0;
            //Print all Categories, encapsulated by the known layoutFolder
            $strCat = "";

    		if(count($arrCategories) > 0) {
    			foreach($arrCategories as $objOneCategory) {
    			    $strAction = "";
    			    if($this->objRights->rightView($objOneCategory->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "list", "&filterId=".$objOneCategory->getSystemid(), "", $this->getText("kat_anzeigen"), "icon_lens.gif"));
    		   		if($this->objRights->rightEdit($objOneCategory->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editCat", "&systemid=".$objOneCategory->getSystemid(), "", $this->getText("kat_bearbeiten"), "icon_pencil.gif"));
    		   		if($this->objRights->rightDelete($objOneCategory->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listDeleteButton($objOneCategory->getStrTitle().$this->getText("kat_loeschen_frage").getLinkAdmin($this->arrModule["modul"], "deleteCat", "&systemid=".$objOneCategory->getSystemid(), $this->getText("kat_loeschen_link")));
    		   		if($this->objRights->rightRight($objOneCategory->getSystemid()))
    				    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneCategory->getSystemid(), "", $this->getText("kat_rechte"), getRightsImageAdminName($objOneCategory->getSystemid())));
    		   		$strCat .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), $objOneCategory->getStrTitle(), $strAction, $intI++);

    			}
    		}
    		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
    		    $strCat .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newCat", "", $this->getText("modul_kat_anlegen"), $this->getText("modul_kat_anlegen"), "icon_blank.gif"), $intI++);

    		if(uniStrlen($strCat) != 0)
    		     $strCat = $this->objToolkit->listHeader().$strCat.$this->objToolkit->listFooter();

    		$strReturn .= $this->objToolkit->getLayoutFolderPic($strCat, $this->getText("kat_ausblenden"));
    		$strReturn .= $this->objToolkit->divider();


    		//Load all news, maybe using a filterid
            include_once(_systempath_."/class_array_section_iterator.php");
		    $objNews = new class_modul_news_news();
    		if($this->getParam("filterId") != "" && $this->validateSystemid($this->getParam("filterId"))) {
    			$objArraySectionIterator = new class_array_section_iterator($objNews->getNewsCount($this->getParam("filterId")));
    			$objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
    			$objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
    			$objArraySectionIterator->setArraySection(class_modul_news_news::getNewsList($this->getParam("filterId"), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
    		}
    		else {
    		    $objArraySectionIterator = new class_array_section_iterator($objNews->getNewsCount());
    		    $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
    		    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
    		    $objArraySectionIterator->setArraySection(class_modul_news_news::getNewsList("", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
    		}

    		$arrNews = $objArraySectionIterator->getArrayExtended();
    		$arrPageViews = $this->objToolkit->getPageview($arrNews, (int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1), "news", "list", "&filterId=".$this->getParam("filterId"), _admin_nr_of_rows_);
            $arrNews = $arrPageViews["elements"];


			$strNews = "";
			foreach($arrNews as $objOneNews) {
			    if($this->objRights->rightView($objOneNews->getSystemid())) {
                    $strAction = "";
                    $strCenter = "S: ".timeToString($objOneNews->getIntDateStart(), false)
                               .($objOneNews->getIntDateEnd() != 0 ?" E: ".timeToString($objOneNews->getIntDateEnd(), false) : "" )
                               .($objOneNews->getIntDateSpecial() != 0 ? " A: ".timeToString($objOneNews->getIntDateSpecial(), false) : "" );
                    if($this->objRights->rightEdit($objOneNews->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editNews", "&systemid=".$objOneNews->getSystemid(), "", $this->getText("news_grunddaten"), "icon_page.gif"));
    		   		if($this->objRights->rightRight1($objOneNews->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editNewscontent", "&systemid=".$objOneNews->getSystemid(), "", $this->getText("news_inhalt"), "icon_pencil.gif"));
    		   		if($this->objRights->rightDelete($objOneNews->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listDeleteButton($objOneNews->getStrTitle().$this->getText("news_loeschen_frage").getLinkAdmin($this->arrModule["modul"], "deleteNews", "&systemid=".$objOneNews->getSystemid()."&news_loeschen_final=1", $this->getText("news_loeschen_link")));
    		   		if($this->objRights->rightEdit($objOneNews->getSystemid()))
    				    $strAction .= $this->objToolkit->listStatusButton($objOneNews->getSystemid());
    				if($this->objRights->rightRight($objOneNews->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneNews->getSystemid(), "", $this->getText("news_rechte"), getRightsImageAdminName($objOneNews->getSystemid())));
    		   		$strNews .= $this->objToolkit->listRow3($objOneNews->getStrTitle()." (".$objOneNews->getIntHits()." Hits)", $strCenter, $strAction,getImageAdmin("icon_news.gif"), $intI++);
			    }

			}
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
			    $strNews .= $this->objToolkit->listRow3("", "", getLinkAdmin($this->arrModule["modul"], "newNews", "", $this->getText("modul_anlegen"), $this->getText("modul_anlegen"), "icon_blank.gif"), "", $intI++);

			if(uniStrlen($strNews) != 0) {
			    $strNews = $this->objToolkit->listHeader().$strNews.$this->objToolkit->listFooter();
			    if(count($arrNews) > 0)
			       $strNews .= $arrPageViews["pageview"];
			}

            if(count($arrNews) == 0)
    			$strNews.= $this->getText("liste_leer");

    		$strReturn .= $strNews;
        }
        else
            $strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

// -- News-Kategorien -----------------------------------------------------------------------------------

	/**
	 * Show the form to create or edit a news cat
	 *
	 * @param string $strMode
	 * @return string
	 */
	private function actionNewCat($strMode = "new") {
		$strReturn = "";
		//Mode?
		if($strMode == "new") {
			//New Category
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=news&amp;action=saveCat");
			    $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
				$strReturn .= $this->objToolkit->formInputText("news_cat_title", $this->getText("news_cat_title"), $this->getParam("news_cat_title"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn.= $this->getText("fehler_recht");
		}
		elseif ($strMode == "edit") {
			//Edit
			if($this->objRights->rightEdit($this->getSystemid())) {
				//Load cat data
				$objCat = new class_modul_news_category($this->getSystemid());
				$strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=news&amp;action=saveCat");
			    $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
			    $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
				$strReturn .= $this->objToolkit->formInputText("news_cat_title", $this->getText("news_cat_title"), $objCat->getStrTitle());
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Saves the passed values as a new category to the db
	 *
	 * @return string "" in case of success
	 */
	private function actionSaveCat() {
		$strReturn = "";
		if($this->getParam("mode") == "new") {
			//Check rights
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $objNews = new class_modul_news_category("");
			    $objNews->setStrTitle($this->getParam("news_cat_title"));
			    if(!$objNews->saveObjectToDb())
			        throw new class_exception("Error saving object to db", class_exception::$level_ERROR);
			}
		}
		elseif($this->getParam("mode") == "edit") {
		    //"just" update
			if($this->objRights->rightEdit($this->getSystemid())) {
				$objNews = new class_modul_news_category($this->getSystemid());
				$objNews->setStrTitle($this->getParam("news_cat_title"));
				if(!$objNews->updateObjectToDb())
				    throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Shows the warning or deletes a cat from the system
	 *
	 * @return string "" in case of success
	 */
	private function actionDeleteCategory() {
		$strReturn = "";
		//Check rights
		if($this->objRights->rightDelete($this->getSystemid())) {
           if(!class_modul_news_category::deleteCategory($this->getSystemid()))
               throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);
		}
		else
			$strReturn .= $this->getText("fehler_recht");


		return $strReturn;
	}

// --- News-Funktionen ----------------------------------------------------------------------------------

	/**
	 * Shows the form to edit oder create news
	 *
	 * @param string $strMode new || edit
	 * @return string
	 */
	private function actionNewNews($strMode = "new") {
		$strReturn = "";
		if($strMode == "new") {
			//Form to create new news
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=news&amp;action=saveNews");
				$strReturn .= $this->objToolkit->formHeadline($this->getText("news_basicdata"));
                $strReturn .= $this->objToolkit->formInputText("news_title", $this->getText("news_title"), $this->getParam("news_title"));
                //The date selector
                $strReturn .= $this->objToolkit->formDate(0, 0, 0, $this->getText("start"), $this->getText("end"), $this->getText("archive"));
                //and the cats
                $strReturn .= $this->objToolkit->formHeadline($this->getText("news_categories"));
                $arrCats = class_modul_news_category::getCategories();
                foreach ($arrCats as $objOneCat) {
            	   $strReturn .= $this->objToolkit->formInputCheckbox("cat[".$objOneCat->getSystemid()."]", $objOneCat->getStrTitle());
                }

                $strReturn .= $this->objToolkit->formInputHidden("systemid", "");
                $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
                $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		elseif ($strMode == "edit") {
			//Rights
			if($this->objRights->rightEdit($this->getSystemid())) {
			    $objNews = new class_modul_news_news($this->getSystemid());
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
			    $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=news&amp;action=saveNews");
			    $strReturn .= $this->objToolkit->formHeadline($this->getText("news_basicdata"));
                $strReturn .= $this->objToolkit->formInputText("news_title", $this->getText("news_title"), $objNews->getStrTitle());
                //The date selector
                $strReturn .= $this->objToolkit->formDate($objNews->getIntDateStart(), $objNews->getIntDateEnd(), $objNews->getIntDateSpecial(), $this->getText("start"), $this->getText("end"), $this->getText("archive"));
                //and the cats
                $strReturn .= $this->objToolkit->formHeadline($this->getText("news_categories"));
                $arrCats = class_modul_news_category::getCategories();
                $arrNewsMember = class_modul_news_category::getNewsMember($this->getSystemid());

                foreach ($arrCats as $objOneCat) {
                    $bitChecked = false;
                    foreach ($arrNewsMember as $objOneMember)
                        if($objOneMember->getSystemid() == $objOneCat->getSystemid())
                            $bitChecked = true;

            	   $strReturn .= $this->objToolkit->formInputCheckbox("cat[".$objOneCat->getSystemid()."]", $objOneCat->getStrTitle(), $bitChecked);
                }

                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		return $strReturn;
	}


	/**
	 * Saves or updates news
	 *
	 * @return string "" in case of success
	 */
	private function actionSaveNews() {
		$strReturn = "";
		if($this->getParam("mode") == "new") {
			//Check rights
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
				$arrDates = $this->objToolkit->generateDateTimestamps($this->getAllParams());

				$objNews = new class_modul_news_news("");
				$objNews->setStrTitle($this->getParam("news_title"));
				$objNews->setIntDateEnd($arrDates["end"]);
				$objNews->setIntDateStart($arrDates["start"]);
				$objNews->setIntDateSpecial($arrDates["archive"]);
                $arrParams = $this->getAllParams();
                $arrCats = array();
                if(count($arrParams["cat"]) > 0) {
                    foreach($arrParams["cat"] as $strCatID => $strValue) {
                        $arrCats[$strCatID] = $strValue;
                    }
                }
                $objNews->setArrCats($arrCats);

                if(!$objNews->saveObjectToDb())
                    throw new class_exception("Error saving object to db", class_exception::$level_ERROR);

			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		elseif($this->getParam("mode") == "edit") {
			if($this->objRights->rightEdit($this->getSystemid())) {
			    $arrDates = $this->objToolkit->generateDateTimestamps($this->getAllParams());

				$objNews = new class_modul_news_news($this->getSystemid());
				$objNews->setStrTitle($this->getParam("news_title"));
				$objNews->setIntDateEnd($arrDates["end"]);
				$objNews->setIntDateStart($arrDates["start"]);
				$objNews->setIntDateSpecial($arrDates["archive"]);
                $arrParams = $this->getAllParams();
                $arrCats = array();
                if(count($arrParams["cat"]) > 0) {
                    foreach($arrParams["cat"] as $strCatID => $strValue) {
                        $arrCats[$strCatID] = $strValue;
                    }
                }
                $objNews->setArrCats($arrCats);
                if(!$objNews->updateObjectToDb())
                    throw new class_exception("Error updating object to db", class_exception::$level_ERROR);

			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Deletes news or shows the form to warn
	 *
	 * @return string "" in case of success
	 */
	private function actionDeleteNews() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {

			if($this->getParam("news_loeschen_final") == "") {
			    $objNews = new class_modul_news_news($this->getSystemid());
				$strReturn .= $this->objToolkit->warningBox($objNews->getStrTitle().$this->getText("news_loeschen_frage")
				               ."<br /><a href=\""._indexpath_."?admin=1&amp;module=".$this->arrModule["modul"]."&amp;action=deleteNews&amp;systemid="
				               .$this->getSystemid().($this->getParam("pe") == "" ? "" : "&amp;peClose=".$this->getParam("pe"))."&amp;news_loeschen_final=1\">"
				               .$this->getText("news_loeschen_link"));
			}
			elseif($this->getParam("news_loeschen_final") == "1") {
			    if(!class_modul_news_news::deleteNews($this->getSystemid()))
			        throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);
			}
		}
		else
			$strReturn .= $this->getText("fehler_recht");


		return $strReturn;
	}



// --- Newsinhalte --------------------------------------------------------------------------------------

	/**
	 * Returns the form to edit news CONTENT
	 *
	 * @return string
	 */
	public function actionEditNewscontent() {
		$strReturn = "";

		//check rights
		// !!! NOTE !!! right1 used here!
		if($this->objRights->rightRight1($this->getSystemid())) {
			//Load content
			$objNews = new class_modul_news_news($this->getSystemid());
            //Build the form
            $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=news&amp;action=saveNewscontent");
            $strReturn .= $this->objToolkit->formInputTextArea("news_intro", $this->getText("news_intro"), $objNews->getStrIntro());
		    $strReturn .= $this->objToolkit->formWysiwygEditor("news_text", $this->getText("news_text"), $objNews->getStrNewstext());
		    $strReturn .= $this->objToolkit->formInputText("news_image", $this->getText("news_image"), $objNews->getStrImage(), "inputText", getLinkAdminPopup("folderview", "list", "&folder=/portal/pics&suffix=.jpg|.gif|.png&form_element=news_image", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
            $strReturn .= $this->objToolkit->formClose();
		}
		else
			$strReturn .= $this->getText("fehler_recht");


		return $strReturn;
	}

	/**
	 * Saves the news content
	 *
	 * @return string "" in case of success
	 */
	public function actionSaveNewscontent() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightRight1($this->getSystemid())) {
			$objNews = new class_modul_news_news($this->getSystemid());
			$objNews->setStrImage(uniStrReplace(_webpath_, "", $this->getParam("news_image")));
			$objNews->setStrIntro($this->getParam("news_intro"));
			$objNews->setStrNewstext($this->getParam("news_text"));
			if(!$objNews->updateObjectToDb(false))
				throw new class_exception("Error saving object to db", class_exception::$level_ERROR);
		}
		else
			$strReturn = $this->getText("fehler_recht");
		return $strReturn;
	}

// --- News Feeds ---------------------------------------------------------------------------------------

    /**
     * Shows a list of all views currently available
     *
     * @return string
     */
    private function actionListNewsFeed() {
        $strReturn = "";
        if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {
            $arrFeeds = class_modul_news_feed::getAllFeeds();
            if(count($arrFeeds) > 0) {
                $intI = 0;
                $strReturn .= $this->objToolkit->listHeader();
                foreach ($arrFeeds as $objOneFeed) {
                    $strAction = "";
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editNewsFeed", "&systemid=".$objOneFeed->getSystemid(), "", $this->getText("editNewsFeed"), "icon_pencil.gif"));
                    $strAction .= $this->objToolkit->listDeleteButton($objOneFeed->getStrTitle().$this->getText("feed_loeschen_frage").getLinkAdmin($this->arrModule["modul"], "deleteNewsFeed", "&systemid=".$objOneFeed->getSystemid(), $this->getText("news_loeschen_link")));

                    //mod-rewrite enabled?
                    if(_system_mod_rewrite_ == "true")
                        $strCenter = _webpath_."/".$objOneFeed->getStrUrlTitle().".rss";
                    else
                        $strCenter = _webpath_."/xml.php?module=news&action=newsFeed&feedTitle=".$objOneFeed->getStrUrlTitle();
                    $strReturn .= $this->objToolkit->listRow3($objOneFeed->getStrTitle() ." (".$objOneFeed->getIntHits()." Hits)", $strCenter, $strAction, getImageAdmin("icon_news.gif"), $intI++);
                }
                if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"])))
                    $strReturn .= $this->objToolkit->listRow3("", "", getLinkAdmin($this->arrModule["modul"], "newNewsFeed", "", $this->getText("modul_new_feed"), $this->getText("modul_new_feed"), "icon_blank.gif"), "", $intI++);
                $strReturn .= $this->objToolkit->listFooter();
            }
            else
                $strReturn .= $this->getText("feed_liste_leer");
        }
		else
			$strReturn .= $this->getText("fehler_recht");
		return $strReturn;
    }

    /**
     * Creates a form to create a news-feed
     *
     * @return string "" in case of success
     */
    private function actionCreateNewsFeed() {
        $strReturn = "";
        if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {
            //Form validation
            $bitValidate = true;
            if($this->getParam("save") == "1" && !$this->validateForm()) {
                $bitValidate = false;
                $this->setParam("save", "");
            }
            //Save or edit?
            if($this->getParam("save") != "1") {
                //Form
                if(!$bitValidate)
                    $strReturn .= $this->objToolkit->getValidationErrors($this);
                $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=news&amp;action=newNewsFeed");
                $strReturn .= $this->objToolkit->formInputText("feed_title", $this->getText("feed_title"), $this->getParam("feed_title"));
                $strReturn .= $this->objToolkit->formInputText("feed_urltitle", $this->getText("feed_urltitle"), $this->getParam("feed_urltitle"));
                $strReturn .= $this->objToolkit->formInputText("feed_link", $this->getText("feed_link"), $this->getParam("feed_link"));
                $strReturn .= $this->objToolkit->formInputText("feed_desc", $this->getText("feed_desc"), $this->getParam("feed_desc"));
                $strReturn .= $this->objToolkit->formInputPageSelector("feed_page", $this->getText("feed_page"), $this->getParam("feed_page"));
                //Dropdown with all cats
                $arrNewsCats = class_modul_news_category::getCategories();
                $arrCatsDD = array();
                foreach ($arrNewsCats as $objOneCat)
                    $arrCatsDD[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();
                $arrCatsDD["0"] = $this->getText("feed_cat_all");
                $strReturn .= $this->objToolkit->formInputDropdown("feed_cat", $arrCatsDD, $this->getText("feed_cat"), $this->getParam("feed_cat"));
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
                $strReturn .= $this->objToolkit->formInputHidden("save", "1");
                $strReturn .= $this->objToolkit->formClose();
            }
            else {
                //Save
                $objFeed = new class_modul_news_feed("");
                $objFeed->setStrTitle($this->getParam("feed_title"));
                $objFeed->setStrUrlTitle($this->getParam("feed_urltitle"));
                $objFeed->setStrLink($this->getParam("feed_link"));
                $objFeed->setStrDesc($this->getParam("feed_desc"));
                $objFeed->setStrPage($this->getParam("feed_page"));
                $objFeed->setStrCat($this->getParam("feed_cat"));

                if(!$objFeed->saveObjectToDb())
                    throw new class_exception("Error saving object to db", class_exception::$level_ERROR);

            }
        }
		else
			$strReturn .= $this->getText("fehler_recht");
		return $strReturn;
    }

    /**
     * Creates a form to edit news feeds
     *
     * @return string, "" in case of success
     */
    private function actionEditNewsFeed() {
        $strReturn = "";
        if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {
            $bitValidate = true;
            if($this->getParam("save") == "1" && !$this->validateForm()) {
                $bitValidate = false;
                $this->setParam("save", "");
            }
            //Save or edit?
            if($this->getParam("save") != "1") {
                $objFeed = new class_modul_news_feed($this->getSystemid());
                //Form
                if(!$bitValidate)
                    $strReturn .= $this->objToolkit->getValidationErrors($this);
                $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=news&amp;action=editNewsFeed");

                $strReturn .= $this->objToolkit->formInputText("feed_title", $this->getText("feed_title"), $objFeed->getStrTitle());
                $strReturn .= $this->objToolkit->formInputText("feed_urltitle", $this->getText("feed_urltitle"), $objFeed->getStrUrlTitle());
                $strReturn .= $this->objToolkit->formInputText("feed_link", $this->getText("feed_link"), $objFeed->getStrLink());
                $strReturn .= $this->objToolkit->formInputText("feed_desc", $this->getText("feed_desc"), $objFeed->getStrDesc());
                $strReturn .= $this->objToolkit->formInputPageSelector("feed_page", $this->getText("feed_page"), $objFeed->getStrPage());
                //Dropdown with all cats
                $arrNewsCats = class_modul_news_category::getCategories();
                $arrCatsDD = array();
                foreach ($arrNewsCats as $objOneCat)
                    $arrCatsDD[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();
                $arrCatsDD["0"] = $this->getText("feed_cat_all");
                $strReturn .= $this->objToolkit->formInputDropdown("feed_cat", $arrCatsDD, $this->getText("feed_cat"), $objFeed->getStrCat());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
                $strReturn .= $this->objToolkit->formInputHidden("save", "1");
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formClose();
            }
            else {
                //Save
                $objFeed = new class_modul_news_feed($this->getSystemid());
                $objFeed->setStrTitle($this->getParam("feed_title"));
                $objFeed->setStrUrlTitle($this->getParam("feed_urltitle"));
                $objFeed->setStrLink($this->getParam("feed_link"));
                $objFeed->setStrDesc($this->getParam("feed_desc"));
                $objFeed->setStrPage($this->getParam("feed_page"));
                $objFeed->setStrCat($this->getParam("feed_cat"));

                if(!$objFeed->updateObjectToDb())
                    throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
            }
        }
		else
			$strReturn .= $this->getText("fehler_recht");
		return $strReturn;
    }

    /**
     * Shows the warning or deletes a feed
     *
     */
    private function actionDeleteNewsFeed() {
        $strReturn = "";
        if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {
            if(!class_modul_news_feed::deleteNewsFeed($this->getSystemid()))
                throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);

        }
		else
			$strReturn .= $this->getText("fehler_recht");
		return $strReturn;
    }


}

?>