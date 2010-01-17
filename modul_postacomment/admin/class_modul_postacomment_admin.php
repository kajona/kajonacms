<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/


/**
 * Admin-Class of the postacomment-module. Responsible for listing posts and organizing them
 *
 * @package modul_postacomment
 */
class class_modul_postacomment_admin extends class_admin implements interface_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 				= "modul_postacomment";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _postacomment_modul_id_;
		$arrModul["table"] 			    = _dbprefix_."postacomment";
		$arrModul["modul"]				= "postacomment";

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

    		if($strAction == "editPost")
    			$strReturn = $this->actionEditPost();
    		if($strAction == "savePost") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSavePost();
    			    if($strReturn == "") {
                        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", ($this->getParam("pe") == "1" ? "peClose=".$this->getParam("pe") : "")));
    			    }
    		    }
    		    else {
   		            $strReturn = $this->actionEditPost();
    		    }
    		}
    		if($strAction == "deletePost") {
    			$strReturn = $this->actionDeletePost();
    			if($strReturn == "")
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
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
    	$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("module_list"), "", "", true, "adminnavi"));
		return $arrReturn;
	}

	protected function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "savePost") {

            $arrReturn["postacomment_username"] = "string";
            $arrReturn["postacomment_comment"] = "string";
        }
        return $arrReturn;
    }

// --- ListenFunktionen ---------------------------------------------------------------------------------


	/**
	 * Returns a list of all categories and all postacomment
	 * The list could be filtered by categories
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strReturn = "";
            $intI = 0;

            //a small filter would be nice...
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "list"));

            $arrPages = array();
            $arrPages[""] = "---";
            foreach(class_modul_pages_page::getAllPages() as $objOnePage)
                $arrPages[$objOnePage->getSystemid()] = $objOnePage->getStrName();

            $strReturn .= $this->objToolkit->formInputDropdown("filterId", $arrPages, $this->getText("postacomment_filter"), $this->getParam("filterId"));
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("postacomment_dofilter"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->divider();

    		//Load all posts
		    $objPost = new class_modul_postacomment_post();

    		if($this->getParam("filterId") != "" && $this->validateSystemid($this->getParam("filterId"))) {
    			$objArraySectionIterator = new class_array_section_iterator(class_modul_postacomment_post::getNumberOfPostsAvailable(false, $this->getParam("filterId")));
    			$objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
    			$objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
    			$objArraySectionIterator->setArraySection(class_modul_postacomment_post::loadPostList(false, $this->getParam("filterId"), false, "", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
    		}
    		else {
    		    $objArraySectionIterator = new class_array_section_iterator(class_modul_postacomment_post::getNumberOfPostsAvailable(false));
    		    $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
    		    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
    		    $objArraySectionIterator->setArraySection(class_modul_postacomment_post::loadPostList(false, "", false, "", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));
    		}

    		$arrPosts = $objArraySectionIterator->getArrayExtended();

    		$arrPageViews = $this->objToolkit->getPageview($arrPosts, (int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1), "postacomment", "list", "&filterId=".$this->getParam("filterId"), _admin_nr_of_rows_);

    		$arrPosts = $arrPageViews["elements"];


			$strPostRows = "";

			if(is_array($arrPosts) && count($arrPosts) > 0) {

				foreach($arrPosts as $objOnePost) {
				    if($this->objRights->rightView($objOnePost->getSystemid())) {

	    			 	$objPage = new class_modul_pages_page($objOnePost->getStrAssignedPage());
	    			 	$strCenter = ($objOnePost->getStrAssignedLanguage() != "" ? " (". $objOnePost->getStrAssignedLanguage() .")" : ""). " | ". timeToString($objOnePost->getIntDate());
	                    $strAction = "";


				        //ratings available?
                        try {
                            $objMdlRating = class_modul_system_module::getModuleByName("rating");
                            if($objMdlRating != null) {
                                $objRating = class_modul_rating_rate::getRating($objOnePost->getSystemid());
                                if($objRating != null)
                                    $strCenter .= " - ".$objOnePost->getFloatRating();
                                else
                                    $strCenter .= " - 0.0";
                            }

                        }
                        catch (class_exception $objException) { }


	                    if($this->objRights->rightEdit($objOnePost->getSystemid()))
	    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editPost", "&systemid=".$objOnePost->getSystemid(), "", $this->getText("postacomment_edit"), "icon_pencil.gif"));
	    		   		if($this->objRights->rightDelete($objOnePost->getSystemid()))
	    		   		    $strAction .= $this->objToolkit->listDeleteButton($objOnePost->getStrTitle(), $this->getText("postacomment_delete_question"),
	    		   		                  getLinkAdminHref($this->arrModule["modul"], "deletePost", "&systemid=".$objOnePost->getSystemid()."&postacommentDeleteFinal=1".($this->getParam("pe") == "" ? "" : "&amp;peClose=".$this->getParam("pe"))));
	    		   		if($this->objRights->rightEdit($objOnePost->getSystemid()))
	    		   		    $strAction .= $this->objToolkit->listStatusButton($objOnePost->getSystemid());
	    		   		if($this->objRights->rightRight($objOnePost->getSystemid()))
	    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOnePost->getSystemid(), "", $this->getText("postacomment_rights"), getRightsImageAdminName($objOnePost->getSystemid())));

	    		   		$strPostRows .= $this->objToolkit->listRow3($objPage->getStrName(), $strCenter, $strAction, getImageAdmin("icon_comment.gif"), $intI);

	    		   		//create the content of the details rows
	    		   		$strPostRows .= $this->objToolkit->listRow3("", uniStrTrim($objOnePost->getStrUsername(), 40)." | ".uniStrTrim($objOnePost->getStrTitle(), 60), "", "", $intI);
	    		   		$strPostRows .= $this->objToolkit->listRow3("", uniStrTrim($objOnePost->getStrComment(), 100), "", "", $intI++);
				    }

				}

			}

			if(uniStrlen($strPostRows) != 0) {
			    $strPostRows = $this->objToolkit->listHeader().$strPostRows.$this->objToolkit->listFooter();
			    if(count($arrPosts) > 0)
			       $strPostRows .= $arrPageViews["pageview"];
			}

            if(count($arrPosts) == 0)
    			$strPostRows.= $this->getText("liste_leer");

    		$strReturn .= $strPostRows;
        }
        else
            $strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Shows the warning before deleting a post or deletes a post
	 *
	 * @return string, "" in case of success
	 */
	private function actionDeletePost() {
	    $strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {
			if($this->getParam("postacommentDeleteFinal") == "") {
			    $objPost = new class_modul_postacomment_post($this->getSystemid());
				$strName = $objPost->getStrTitle();
				$strReturn .= $this->objToolkit->warningBox($objPost->getStrTitle().$this->getText("postacomment_delete_question")
				               ."<br /><a href=\""._indexpath_."?admin=1&amp;module=".$this->arrModule["modul"]."&amp;action=deletePost&amp;systemid="
				               .$this->getSystemid().($this->getParam("pe") == "" ? "" : "&amp;peClose=".$this->getParam("pe"))."&amp;postacommentDeleteFinal=1\">"
				               .$this->getText("postacomment_delete_link"));
			}
			elseif($this->getParam("postacommentDeleteFinal") == "1") {
                $objPost = new class_modul_postacomment_post($this->getSystemid());
			    if(!$objPost->deletePost())
			        throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);
			}
		}
		else
			$strReturn .= $this->getText("fehler_recht");


		return $strReturn;
	}

	/**
	 * Shows a form to edit the current post
	 *
	 * @return string
	 */
	private function actionEditPost() {
	    $strReturn = "";
	    //Rights
		if($this->objRights->rightEdit($this->getSystemid())) {
            $objPost = new class_modul_postacomment_post($this->getSystemid());

            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "savePost", ($this->getParam("pe") == "1" ? "pe=".$this->getParam("pe") : "")));

            if(count($this->getValidationErrors()) == 0) {
                $strReturn .= $this->objToolkit->formInputText("postacomment_username", $this->getText("postacomment_username"), $objPost->getStrUsername() );
                $strReturn .= $this->objToolkit->formInputText("postacomment_title", $this->getText("postacomment_title"), $objPost->getStrTitle() );
                $strReturn .= $this->objToolkit->formInputTextArea("postacomment_comment", $this->getText("postacomment_comment"), $objPost->getStrUsername() );
            }
            else {
                $strReturn .= $this->objToolkit->getValidationErrors($this);
                $strReturn .= $this->objToolkit->formInputText("postacomment_username", $this->getText("postacomment_username"), $this->getParam("postacomment_username") );
                $strReturn .= $this->objToolkit->formInputText("postacomment_title", $this->getText("postacomment_title"), $this->getParam("postacomment_title") );
                $strReturn .= $this->objToolkit->formInputTextArea("postacomment_comment", $this->getText("postacomment_comment"), $this->getParam("postacomment_comment"));
            }
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            if($this->getParam("pe") == "1")
                $strReturn .= $this->objToolkit->formInputHidden("pe", "1");
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("postacomment_username");
		}
		else
			$strReturn .= $this->getText("fehler_recht");


		return $strReturn;
	}

	/**
	 * Saves a modified post to the db
	 *
	 * @return string, "" in case of success
	 */
	private function actionSavePost() {

	    $strReturn = "";
	    if($this->objRights->rightEdit($this->getSystemid())) {
        	$objPost = new class_modul_postacomment_post($this->getSystemid());
        	$objPost->setStrUsername($this->getParam("postacomment_username"));
        	$objPost->setStrComment($this->getParam("postacomment_comment"));
        	$objPost->setStrTitle($this->getParam("postacomment_title"));
        	if(!$objPost->updateObjectToDb())
        	    throw new class_exception("Error saving post to db", class_exception::$level_ERROR);
        	$objPost->setEditDate();
	    }
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}


} //class_modul_postacomment_admin

?>