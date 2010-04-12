<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/

/**
 * Portal-class of the postacomment. Handles the printing of postacomment lists / detail
 *
 * @package modul_postacomment
 */
class class_modul_postacomment_portal extends class_portal implements interface_portal {

    private $strErrors = "";

    private $strPagefilter = null;

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $arrModule = array();
		$arrModule["name"] 				= "modul_postacomment";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["table"] 			= _dbprefix_."postacomment";
		$arrModule["moduleId"] 			= _postacomment_modul_id_;
		$arrModule["modul"]				= "postacomment";

		parent::__construct($arrModule, $arrElementData);
	}

	/**
	 * Action-Block, decides what to do
	 *
	 * @return string
	 */
	public function action() {
		$strReturn = "";
		$strAction = "";

		if($this->getParam("action") != "postComment")
		    $strAction = "list";
		else
		    $strAction = "postComment";

		if ($strAction == "postComment") {
		    if($this->validateForm()) {
			    $strReturn .= $this->actionPostComment();
			    //load the page before
			    $this->portalReload(_indexpath_."?".$this->getHistory(1));
		    }
		}

		//the list should be loaded in every case
	    $strReturn = $this->actionList();

		return $strReturn;

	}

	/**
	 * Returns a list of comments.
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";
		$strPosts = "";
		$strForm = "";
		$strNewButton = "";

		//pageid or systemid to filter?
		$strSystemidfilter = "";
		$strPagefilter = $this->strPagefilter;

		if($this->getSystemid() != "")
		    $strSystemidfilter = $this->getSystemid();

        if($strPagefilter === null)
            $strPagefilter = class_modul_pages_page::getPageByName($this->getPagename())->getSystemid();

        $intNrOfPosts = isset($this->arrElementData["int1"]) ? $this->arrElementData["int1"] : 0;

        //Load all posts
	    $objArraySectionIterator = new class_array_section_iterator(class_modul_postacomment_post::getNumberOfPostsAvailable(true, $strPagefilter, $strSystemidfilter, $this->getPortalLanguage()));
	    $objArraySectionIterator->setIntElementsPerPage($intNrOfPosts);
	    $objArraySectionIterator->setPageNumber((int)($this->getParam("pvPAC") != "" ? $this->getParam("pvPAC") : 1));
	    $objArraySectionIterator->setArraySection(class_modul_postacomment_post::loadPostList(true, $strPagefilter, $strSystemidfilter, $this->getPortalLanguage(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));



        //params to add?
        $strAdd = "";
        if($this->getParam("action") != "")
            $strAdd .= "&action=".$this->getParam("action");
        if($this->getParam("systemid") != "")
            $strAdd .= "&systemid=".$this->getParam("systemid");
        if($this->getParam("pv") != "")
            $strAdd .= "&pv=".$this->getParam("pv");

		$arrComments = $this->objToolkit->simplePager($objArraySectionIterator, $this->getText("postacomment_next"), $this->getText("postacomment_prev"), "", $this->getPagename(), $strAdd, "pvPAC");


		$strTemplateID = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "postacomment_post");
		//Check rights
		if(count($arrComments) > 0) {
    		foreach($arrComments["arrData"] as $objOnePost) {
    			if($this->objRights->rightView($objOnePost->getSystemid())) {
    			    $strOnePost = "";
    				$arrOnePost = array();
    				$arrOnePost["postacomment_post_name"] = $objOnePost->getStrUsername();
    				$arrOnePost["postacomment_post_subject"] = $objOnePost->getStrTitle();
    				$arrOnePost["postacomment_post_message"] = $objOnePost->getStrComment();
    				$arrOnePost["postacomment_post_systemid"] = $objOnePost->getSystemid();
    				$arrOnePost["postacomment_post_date"] = timeToString($objOnePost->getIntDate(), true);
    			    //ratings available?
                    if($objOnePost->getFloatRating() !== null) {
                        $arrOnePost["postacomment_post_rating"] = $this->buildRatingBar($objOnePost->getFloatRating(), $objOnePost->getIntRatingHits(), $objOnePost->getSystemid(), $objOnePost->isRateableByUser(), $objOnePost->rightRight2());
                    }


    				$strOnePost .= $this->objTemplate->fillTemplate($arrOnePost, $strTemplateID, false);

    				//Add pe code
    			    $arrPeConfig = array(
    			                              "pe_module" => "postacomment",
    			                              "pe_action_edit" => "editPost",
    			                              "pe_action_edit_params" => "&systemid=".$objOnePost->getSystemid(),
    			                              "pe_action_new" => "",
    			                              "pe_action_new_params" => "",
    			                              "pe_action_delete" => "deletePost",
    			                              "pe_action_delete_params" => "&systemid=".$objOnePost->getSystemid()
    			                        );
    			    $strPosts .= class_element_portal::addPortalEditorCode($strOnePost, $objOnePost->getSystemid(), $arrPeConfig);
    			}
    		}

		}
		else
		    $strPosts .= $this->getText("postacomment_empty");

		//Create form
		if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
	        $strTemplateID = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "postacomment_form");
	        $arrForm = array();
	        $arrForm["formaction"] = getLinkPortalHref($this->getPagename(), "", "postComment", "", $this->getSystemid());
			$arrForm["comment_name"] = $this->getParam("comment_name");
			$arrForm["comment_subject"] = $this->getParam("comment_subject");
			$arrForm["comment_message"] = $this->getParam("comment_message");
			$arrForm["comment_template"] = $this->arrElementData["char1"];
            $arrForm["comment_systemid"] = $this->getSystemid();
			$arrForm["comment_page"] = $this->getPagename();
			$arrForm["validation_errors"] = $this->strErrors;

			$strForm .= $this->objTemplate->fillTemplate($arrForm, $strTemplateID, false);

			//button to show the form
			$strTemplateNewButtonID = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "postacomment_new_button");
            $strNewButton = $this->objTemplate->fillTemplate(array("comment_systemid" => $this->getSystemid()), $strTemplateNewButtonID, false);
		}
		//add sourrounding list template
		$strTemplateID = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "postacomment_list");


        //link to the post-form & pageview links
        $arrTemplate = array();
        $arrTemplate["postacomment_forward"] = $arrComments["strForward"];
        $arrTemplate["postacomment_pages"] = $arrComments["strPages"];
        $arrTemplate["postacomment_back"] = $arrComments["strBack"];
        $arrTemplate["postacomment_form"] = $strForm;
        $arrTemplate["postacomment_new_button"] = $strNewButton;
        $arrTemplate["postacomment_systemid"] = $this->getSystemid();
        $arrTemplate["postacomment_list"] = $strPosts;


        $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

		return $strReturn;
	}


	/**
	 * Saves a post to the databases
	 *
	 */
	private function actionPostComment() {

	    //pageid or systemid to filter?
	    if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
			$strSystemidfilter = "";
			$strPagefilter = "";
			if($this->getSystemid() != "")
			    $strSystemidfilter = $this->getSystemid();

			$strPagefilter = class_modul_pages_page::getPageByName($this->getPagename())->getSystemid();

		    $objPost = new class_modul_postacomment_post();
		    $objPost->setStrUsername($this->getParam("comment_name"));
		    $objPost->setStrTitle($this->getParam("comment_subject"));
		    $objPost->setStrComment($this->getParam("comment_message"));

		    $objPost->setStrAssignedPage($strPagefilter);
		    $objPost->setStrAssignedSystemid($strSystemidfilter);
		    $objPost->setStrAssignedLanguage($this->getPortalLanguage());

		    $objPost->updateObjectToDb();
	    }
	}

	/**
	 * Validates the form data provided by the user
	 *
	 * @return bool
	 */
	public function validateForm() {
	    $bitReturn = true;

	    $strTemplateId = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "validation_error_row");
	    if(uniStrlen($this->getParam("comment_name")) < 2) {
	        $bitReturn = false;
	        $this->strErrors .= $this->fillTemplate(array("error" => $this->getText("validation_name")), $strTemplateId);
	    }
	    if(uniStrlen($this->getParam("comment_message")) < 2) {
	        $bitReturn = false;
	        $this->strErrors .= $this->fillTemplate(array("error" => $this->getText("validation_message")), $strTemplateId);
	    }
	    if($this->objSession->getCaptchaCode() != $this->getParam("form_captcha") || $this->getParam("form_captcha") == "") {
	        $bitReturn = false;
	        $this->strErrors .= $this->fillTemplate(array("error" => $this->getText("validation_code")), $strTemplateId);
	    }
	    return $bitReturn;
	}

    /**
     * Builds the rating bar available for every comment.
     * Creates the needed js-links and image-tags as defined by the template.
     *
     * @param float $floatRating
     * @param int $intRatings
     * @param string $strSystemid
     * @param bool $bitRatingAllowed
     * @return string
     */
    private function buildRatingBar($floatRating, $intRatings, $strSystemid, $bitRatingAllowed = true, $bitPermissions = true) {
        $strIcons = "";
        $strRatingBarTitle = "";

        $intNumberOfIcons = class_modul_rating_rate::$intMaxRatingValue;

        //read the templates
        $strTemplateBarId = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "rating_bar");

        if($bitRatingAllowed && $bitPermissions) {
            $strTemplateIconId = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "rating_icon");

            for($intI = 1; $intI <= $intNumberOfIcons; $intI++) {
                $arrTemplate = array();
                $arrTemplate["rating_icon_number"] = $intI;

                $arrTemplate["rating_icon_onclick"] = "KAJONA.portal.rating.rate('".$strSystemid."', '".$intI.".0', ".$intNumberOfIcons."); return false;";

                $strIcons .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateIconId, false);
            }
        } else {
            //disable caching
            class_modul_pages_portal::disablePageCacheForGeneration();
            if(!$bitRatingAllowed)
                $strRatingBarTitle = $this->getText("postacomment_rating_voted");
            else
                $strRatingBarTitle = $this->getText("postacomment_rating_permissions");
        }

        return $this->fillTemplate(array("rating_icons" => $strIcons, "rating_bar_title" => $strRatingBarTitle,
                                         "rating_rating" => $floatRating, "rating_hits" => $intRatings,
                                         "rating_ratingPercent" => ($floatRating/$intNumberOfIcons*100),
                                         "system_id" => $strSystemid, 2), $strTemplateBarId);
    }

    /**
     * If you want to set a sepcial page to be used for loading and rendering the portal list, use this
     * setter. Pass the systemid (!) of the page to load.
     *
     * @param string $strPagefilter
     */
    public function setStrPagefilter($strPagefilter) {
        $this->strPagefilter = $strPagefilter;
    }



}
?>