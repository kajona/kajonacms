<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Admin-Class to handle all guestbook-stuff like creating guestbook, deleting posts, ...
 *
 * @package modul_guestbook
 */
class class_modul_guestbook_admin extends class_admin implements interface_admin  {
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 				= "modul_guestbook";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _guestbook_modul_id_;
		$arrModul["table"] 			    = _dbprefix_."guestbook_book";
		$arrModul["table2"]       		= _dbprefix_."guestbook_post";
		$arrModul["modul"]				= "guestbook";

		//base class
		parent::__construct($arrModul);
	}

	/**
	 * Action-block to decide what actions to load
	 *
	 * @param unknown_type $strAction
	 */
	public function action($strAction = "") {
	    $strReturn = "";
        if($strAction == "")
            $strAction = "list";

        try {

    		if($strAction == "list")
    			$strReturn = $this->actionList();

    		if($strAction == "newGuestbook")
    			$strReturn = $this->actionNewGuestbook("new");
    		if($strAction == "editGuestbook")
    			$strReturn = $this->actionNewGuestbook("edit");
    		if($strAction == "saveGuestbook") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSaveGuestbook();
    			    if($strReturn == "")
                        $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
    		    }
    		    else {
    		        if($this->getParam("mode") == "new")
    		            $strReturn = $this->actionNewGuestbook("new");
    		        else
    		            $strReturn = $this->actionNewGuestbook("edit");
    		    }
    		}
    		if($strAction == "viewGuestbook")
    			$strReturn = $this->actionViewGuestbook();
    		if($strAction == "deleteGuestbook") {
    			$strReturn = $this->actionDeleteGuestbook();
    			if($strReturn == "")
    			   $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
    		}
    		if($strAction == "deletePost") {
    			$strReturn = $this->actionDeletePost();
    			if($strReturn == "")
    			   $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "viewGuestbook", "systemid=".$this->getSystemid()));
    		}
            if($strAction == "editPost") {
                $strReturn = $this->actionEditPost();
                if($strReturn == "")
                   $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "viewGuestbook", "systemid=".$this->getSystemid()));
            }
            if($strAction == "updatePostcontent") {
                $strReturn = $this->updatePostcontent();
                if($strReturn == "")
                   $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "viewGuestbook", "systemid=".$this->getPrevId()));
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
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newGuestbook", "", $this->getText("modul_anlegen"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


	protected function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "saveGuestbook") {
            $arrReturn["guestbook_title"] = "string";
        }

        return $arrReturn;
    }


// --- ListenFunktionen ---------------------------------------------------------------------------------


	/**
	 * Returns a list off al installed guestbooks
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";
		//Check the rights
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
			//fetch all guestbooks
			$arrGbs = class_modul_guestbook_guestbook::getGuestbooks();
			//Any gbs found?
			$intI = 0;
			//iterate over all gbs
			foreach($arrGbs as $objOneGb) {
				//Check rights
				if($this->objRights->rightView($objOneGb->getSystemid())) {
                    $strAction = "";
                    if($this->objRights->rightView($objOneGb->getSystemid()))
           		        $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "viewGuestbook", "&systemid=".$objOneGb->getSystemid(), "", $this->getText("gaestebuch_anzeigen"), "icon_bookLens.gif"));
           		    if($this->objRights->rightEdit($objOneGb->getSystemid()))
			   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editGuestbook", "&systemid=".$objOneGb->getSystemid(), "", $this->getText("gaestebuch_bearbeiten"), "icon_pencil.gif"));
			   		if($this->objRights->rightDelete($objOneGb->getSystemid()))
			   		    $strAction .= $this->objToolkit->listDeleteButton($objOneGb->getGuestbookTitle(), $this->getText("loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deleteGuestbook", "&systemid=".$objOneGb->getSystemid()));
			   		if($this->objRights->rightRight($objOneGb->getSystemid()))
		   			    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneGb->getSystemid(), "", $this->getText("gaestebuch_rechte"), getRightsImageAdminName($objOneGb->getSystemid())));
			   		$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_book.gif"), $objOneGb->getGuestbookTitle(), $strAction, $intI++);
				}
			}
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
			    $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newGuestbook", "", $this->getText("modul_anlegen"), $this->getText("modul_anlegen"), "icon_blank.gif"), $intI++);

			if(uniStrlen($strReturn) != 0)
			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

		    if(count($arrGbs) == 0)
				$strReturn .= $this->getText("gaestebuch_listeleer");
		}
		else
		    $strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


// --- Gaestebuchverwaltung -----------------------------------------------------------------------------

	/**
	 * Returns the form to edit or create a guestbook
	 *
	 * @param string $strMode new || edit
	 * @return string
	 */
	public function actionNewGuestbook($strMode = "new") {
		$strReturn = "";
		//Needed anytime
		$arrModes = array( 0 => $this->getText("gaestebuch_modus_0"),
						   1 => $this->getText("gaestebuch_modus_1"));

		//Which mode?
		if($strMode == "new") {
			//Chek rights
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
				//Create form
				$strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveGuestbook"));
                $strReturn .= $this->objToolkit->formInputText("guestbook_title", $this->getText("guestbook_title"), $this->getParam("guestbook_title"));
                $strReturn .= $this->objToolkit->formInputDropdown("guestbook_moderated", $arrModes, $this->getText("guestbook_moderated"), $this->getParam("guestbook_moderated"));
				$strReturn .= $this->objToolkit->formInputHidden("mode", "new");
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();

				$strReturn .= $this->objToolkit->setBrowserFocus("guestbook_title");
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		elseif($strMode == "edit") {
			//rights
			if($this->objRights->rightEdit($this->getSystemid())) {
				//Load Guestbook
				$objGuestbook = new class_modul_guestbook_guestbook($this->getSystemid());
				//Create form
				$strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveGuestbook"));
                $strReturn .= $this->objToolkit->formInputText("guestbook_title", $this->getText("guestbook_title"), $objGuestbook->getGuestbookTitle());
                $strReturn .= $this->objToolkit->formInputDropdown("guestbook_moderated", $arrModes, $this->getText("guestbook_moderated"), $objGuestbook->getGuestbookModerated());
				$strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
				$strReturn .= $this->objToolkit->formInputHidden("systemid", $objGuestbook->getSystemid());
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();

				$strReturn .= $this->objToolkit->setBrowserFocus("guestbook_title");
			}
			else
				$strReturn = $this->objTemplate("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Saves or updates the passed values to db
	 *
	 * @return string "" in case of success
	 */
	public function actionSaveGuestbook() {
		$strReturn = "";
		//Create or edit?
		if($this->getParam("mode") == "new") {
			//Check rights
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $objGuestbook = new class_modul_guestbook_guestbook();
			    $objGuestbook->setGuestbookTitle($this->getParam("guestbook_title"));
			    $objGuestbook->setGuestbookModerated($this->getParam("guestbook_moderated"));
			    if(!$objGuestbook->updateObjectToDb())
			        throw new class_exception("Error saving object to db", class_exception::$level_ERROR);
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		elseif ($this->getParam("mode") == "edit") {
			if($this->objRights->rightEdit($this->getSystemid())) {
			    $objGB = new class_modul_guestbook_guestbook($this->getSystemid());
			    $objGB->setGuestbookModerated($this->getParam("guestbook_moderated"));
			    $objGB->setGuestbookTitle($this->getParam("guestbook_title"));
				if(!$objGB->updateObjectToDb())
					throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
			}
			else
				$strReturn = $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Deletes a guestbook and all posts oder shows a warning box
	 *
	 * @return string "" in case of success
	 */
	public function actionDeleteGuestbook() {
		$strReturn = "";
		if($this->objRights->rightDelete($this->getSystemid())) {
            $objGB = new class_modul_guestbook_guestbook($this->getSystemid());
            if(!$objGB->deleteGuestbook())
                throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);

		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}
// --- Posts-Verwaltung ---------------------------------------------------------------------------------

	/**
	 * Returns a list of all posts belonging to the selected guestbook
	 *
	 * @return string
	 */
	public function actionViewGuestbook() {
		$strReturn = "";
		if($this->objRights->rightView($this->getSystemid())) {


            $objArraySectionIterator = new class_array_section_iterator(class_modul_guestbook_post::getPostsCount($this->getSystemid()));
            $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
            $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
            $objArraySectionIterator->setArraySection(class_modul_guestbook_post::getPostsSection($this->getSystemid(), false, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

            $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "guestbook", "viewGuestbook", "&systemid=".$this->getSystemid());
            $arrPosts = $arrPageViews["elements"];

            $intI = 0;
			//Print all posts using a modified 2 row list
			if(count($arrPosts) > 0) {
			    $strReturn .= $this->objToolkit->listHeader();
				foreach($arrPosts as $objPost) {
                    $strActions = "";
				 	if($this->objRights->rightEdit($this->getSystemid()))
				 	    $strActions .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editPost", "&systemid=".$objPost->getSystemid(), "", $this->getText("edit_post"), "icon_pencil.gif"));
				 	if($this->objRights->rightDelete($this->getSystemid()))
				 	    $strActions .= $this->objToolkit->listDeleteButton($objPost->getGuestbookPostName() . " - ".timeToString($objPost->getGuestbookPostDate()), $this->getText("post_loeschen_frage"), getLinkAdminHref($this->arrModule["modul"], "deletePost", "&systemid=".$objPost->getSystemid()));
					if($this->objRights->rightEdit($this->getSystemid()))
					    $strActions .= $this->objToolkit->listStatusButton($objPost->getSystemid());
					$strReturn .= $this->objToolkit->listRow3(timeToString($objPost->getGuestbookPostDate()), $objPost->getGuestbookPostName()." - ".$objPost->getGuestbookPostEmail()." - ".$objPost->getGuestbookPostPage(), $strActions, " ", $intI);
					$strReturn .= $this->objToolkit->listRow3("", uniStrReplace("&lt;br /&gt;", "<br />" , $objPost->getGuestbookPostText()), "", "", $intI);
					$intI++;
				}
				$strReturn .= $this->objToolkit->listFooter().$arrPageViews["pageview"];
			}
			else
				$strReturn = $this->getText("post_liste_leer");
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Shows a form to edit the content of a post
	 *
	 * @return string
	 */
    private function actionEditPost(){
        $strReturn = "";

        //check rights
        if($this->objRights->rightEdit($this->getSystemid())) {
            //Load content
            $objPost = new class_modul_guestbook_post($this->getSystemid());
            //Build the form
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "updatePostcontent"));
            $strReturn .= $this->objToolkit->formWysiwygEditor("post_text", $this->getText("post_text"), $objPost->getGuestbookPostText());
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("post_text");
        }
        else
            $strReturn .= $this->getText("fehler_recht");

        return $strReturn;
    }

    /**
     * Saves the passed post to the db
     *
     * @return string "" in case of success
     */
    private function updatePostcontent(){
        $strReturn = "";
        if($this->objRights->rightEdit($this->getSystemid())) {
            $objPost = new class_modul_guestbook_post($this->getSystemid());
            $objPost->setGuestbookPostText(processWysiwygHtmlContent($this->getParam("post_text")));
            if(!$objPost->updateObjectToDb())
                throw new class_exception("Error saving object to db", class_exception::$level_ERROR);
        }
        else
            $strReturn = $this->getText("fehler_recht");
        return $strReturn;
    }

	/**
	 * Deletes a post or shows a warning box
	 *
	 * @return string "" in case of success
	 */
	public function actionDeletePost() {
		$strReturn = "";
		if($this->objRights->rightDelete($this->getSystemid())) {
            //Delete from module-table
            $strPrevID = $this->getPrevId();
            $objPost = new class_modul_guestbook_post($this->getSystemid());
            $bitDelete = $objPost->deletePost();
            $this->setSystemid($strPrevID);
            if(!$bitDelete)
                throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);

		}
		else
			$strReturn = $this->getText("fehler_recht");


		return $strReturn;
	}


// --- Helferfunktionen ---------------------------------------------------------------------------------

	/**
	 * Loads one guestbook
	 *
	 * @param string $strSystemid
	 * @return mixed
	 */
	public function getGuestbook($strSystemid) {
	     return new class_modul_guestbook_guestbook($strSystemid);
	}


}

?>