<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_postacomment_admin.php 3962 2011-07-03 12:10:54Z sidler $                          *
********************************************************************************************************/


/**
 * Admin class of the postacomment-module. Responsible for listing posts and organizing them
 *
 * @package modul_postacomment
 * @author sidler@mulchprod.de
 */
class class_module_postacomment_admin extends class_admin_simple implements interface_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $this->setArrModuleEntry("modul", "postacomment");
        $this->setArrModuleEntry("moduleId", _postacomment_modul_id_);
        parent::__construct();
	}

    public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
    	$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
		return $arrReturn;
	}





	/**
	 * Returns a list of all categories and all postacomment
	 * The list could be filtered by categories
	 *
	 * @return string
     * @permissions view
     * @autoTestable
	 */
	protected function actionList() {

        //a small filter would be nice...
        $strReturn = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "list"));

        $arrPages = array();
        $arrPages[""] = "---";
        foreach(class_module_pages_page::getAllPages() as $objOnePage)
            $arrPages[$objOnePage->getSystemid()] = $objOnePage->getStrName();

        $strReturn .= $this->objToolkit->formInputDropdown("filterId", $arrPages, $this->getLang("postacomment_filter"), $this->getParam("filterId"));
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("postacomment_dofilter"));
        $strReturn .= $this->objToolkit->formClose();

        $strReturn .= $this->objToolkit->divider();

        $objArraySectionIterator = new class_array_section_iterator(class_module_postacomment_post::getNumberOfPostsAvailable(false, $this->getParam("filterId")));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_postacomment_post::loadPostList(false, $this->getParam("filterId"), false, "", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $strReturn .= $this->renderList($objArraySectionIterator);
        return $strReturn;


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
            $objArraySectionIterator = null;
    		if($this->getParam("filterId") != "" && validateSystemid($this->getParam("filterId"))) {
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

    		$arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "postacomment", "list", "&filterId=".$this->getParam("filterId"));

    		$arrPosts = $arrPageViews["elements"];


			$strPostRows = "";

			if(is_array($arrPosts) && count($arrPosts) > 0) {

				foreach($arrPosts as $objOnePost) {
				    if($this->objRights->rightView($objOnePost->getSystemid())) {

	    			 	$objPage = new class_modul_pages_page($objOnePost->getStrAssignedPage());
	    			 	$strCenter = ($objOnePost->getStrAssignedLanguage() != "" ? " (". $objOnePost->getStrAssignedLanguage() .")" : ""). " | ". timeToString($objOnePost->getIntDate());
	                    $strAction = "";

				        //ratings available?
                        $floatRating = $objOnePost->getFloatRating();
                        if ($floatRating !== null) {
                            $strCenter .= " - ".$floatRating;
                        }

	                    if($this->objRights->rightEdit($objOnePost->getSystemid()))
	    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editPost", "&systemid=".$objOnePost->getSystemid(), "", $this->getText("postacomment_edit"), "icon_pencil.gif"));
	    		   		if($this->objRights->rightDelete($objOnePost->getSystemid()))
	    		   		    $strAction .= $this->objToolkit->listDeleteButton($objOnePost->getStrTitle(), $this->getText("postacomment_delete_question"),
	    		   		                  getLinkAdminHref($this->arrModule["modul"], "deletePost", "&systemid=".$objOnePost->getSystemid()."&postacommentDeleteFinal=1".($this->getParam("pe") == "" ? "" : "&amp;peClose=".$this->getParam("pe"))));
	    		   		if($this->objRights->rightEdit($objOnePost->getSystemid()))
	    		   		    $strAction .= $this->objToolkit->listStatusButton($objOnePost->getSystemid());
	    		   		if($this->objRights->rightRight($objOnePost->getSystemid()))
	    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOnePost->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOnePost->getSystemid())));

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
            $strReturn = $this->getText("commons_error_permissions");

		return $strReturn;
	}

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return "";
    }


    /**
     * Renders the form to create a new entry
     *
     * @throws class_exception
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        throw new class_exception("actioNew not supported by module postacomment", class_exception::$level_ERROR);
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @param class_admin_formgenerator|null $objForm
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit(class_admin_formgenerator $objForm = null) {
        $objComment = new class_module_postacomment_post($this->getSystemid());
        if($objComment->rightEdit()) {

            if($objForm == null)
                $objForm = $this->getAdminForm($objComment);

            return $objForm->renderForm(getLinkAdminHref($this->arrModule["modul"], "saveComment"));
        }
        else
            return $this->getLang("commons_error_permissions");
    }

    private function getAdminForm(class_module_postacomment_post $objComment) {
        $objForm = new class_admin_formgenerator("comment", $objComment);
        $objForm->generateFieldsFromObject();
        return $objForm;
    }

    /**
     * Saves the passed comment-data back to the database.
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveComment() {

        $objComment = new class_module_postacomment_post($this->getSystemid());
        $objForm = $this->getAdminForm($objComment);

        if(!$objForm->validateForm())
            return $this->actionEdit($objForm);

        if($objComment->rightEdit()) {
            $objForm->updateSourceObject();
            $objComment->updateObjectToDb();
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
            return "";
        }
        else
            return $this->getLang("commons_error_permissions");
    }

    /**
	 * Shows the warning before deleting a post or deletes a post
	 *
	 * @return string, "" in case of success
	 */
	protected function actionDeletePost() {
	    $strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {
			if($this->getParam("postacommentDeleteFinal") == "") {
			    $objPost = new class_modul_postacomment_post($this->getSystemid());
				$strReturn .= $this->objToolkit->warningBox($objPost->getStrTitle().$this->getText("postacomment_delete_question")
				               ."<br /><a href=\""._indexpath_."?admin=1&amp;module=".$this->arrModule["modul"]."&amp;action=deletePost&amp;systemid="
				               .$this->getSystemid().($this->getParam("pe") == "" ? "" : "&amp;peClose=".$this->getParam("pe"))."&amp;postacommentDeleteFinal=1\">"
				               .$this->getText("commons_delete"));
			}
			elseif($this->getParam("postacommentDeleteFinal") == "1") {
                $objPost = new class_modul_postacomment_post($this->getSystemid());
			    if(!$objPost->deletePost())
			        throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);
                
                $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
			}
		}
		else
			$strReturn .= $this->getText("commons_error_permissions");


		return $strReturn;
	}

	/**
	 * Shows a form to edit the current post
	 *
	 * @return string
	 */
	protected function actionEditPost() {
	    $strReturn = "";
	    //Rights
		if($this->objRights->rightEdit($this->getSystemid())) {
            $objPost = new class_modul_postacomment_post($this->getSystemid());

            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "savePost", ($this->getParam("pe") == "1" ? "pe=".$this->getParam("pe") : "")));
            $strReturn .= $this->objToolkit->getValidationErrors($this, "savePost");
            $strReturn .= $this->objToolkit->formInputText("postacomment_username", $this->getText("commons_name"), $this->getParam("postacomment_username") != "" ?  $this->getParam("postacomment_username") : $objPost->getStrUsername());
            $strReturn .= $this->objToolkit->formInputText("postacomment_title", $this->getText("postacomment_title"), $this->getParam("postacomment_title") != "" ? $this->getParam("postacomment_title") : $objPost->getStrTitle() );
            $strReturn .= $this->objToolkit->formInputTextArea("postacomment_comment", $this->getText("postacomment_comment"), $this->getParam("postacomment_comment") != "" ? $this->getParam("postacomment_comment") : $objPost->getStrComment());
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("postacomment_username");
		}
		else
			$strReturn .= $this->getText("commons_error_permissions");


		return $strReturn;
	}

	/**
	 * Saves a modified post to the db
	 *
	 * @return string, "" in case of success
	 */
	protected function actionSavePost() {

        if(!$this->validateForm()) {
            return $this->actionEditPost();
        }
        
	    $strReturn = "";
	    if($this->objRights->rightEdit($this->getSystemid())) {
        	$objPost = new class_modul_postacomment_post($this->getSystemid());
        	$objPost->setStrUsername($this->getParam("postacomment_username"));
        	$objPost->setStrComment($this->getParam("postacomment_comment"));
        	$objPost->setStrTitle($this->getParam("postacomment_title"));
        	if(!$objPost->updateObjectToDb())
        	    throw new class_exception("Error saving post to db", class_exception::$level_ERROR);
            
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "list", ($this->getParam("pe") == "1" ? "peClose=".$this->getParam("pe") : "")));
	    }
		else
			$strReturn .= $this->getText("commons_error_permissions");

		return $strReturn;
	}


}

