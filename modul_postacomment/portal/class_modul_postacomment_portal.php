<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_postacomment_portal.php																	*
* 	portalclass of the postacomment																		*
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/

//Include der Mutter-Klasse
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_portal.php");
include_once(_portalpath_."/class_elemente_portal.php");

//model
include_once(_systempath_."/class_modul_postacomment_post.php");

/**
 * Portal-class of the postacomment. Handles thd printing of postacomment lists / detail
 *
 * @package modul_postacomment
 */
class class_modul_postacomment_portal extends class_portal implements interface_portal {
    
    private $strErrors = "";
    
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
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
			    header("Location: "._indexpath_."?".$this->getHistory(1));
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
	public function actionList() {
		$strReturn = "";
		$strPosts = "";
		$strForm = "";
		
		//pageid or systemid to filter?
		$strSystemidfilter = "";
		$strPagefilter = "";
		if($this->getSystemid() != "")
		    $strSystemidfilter = $this->getSystemid();
		    
		$strPagefilter = class_modul_pages_page::getPageByName($this->getPagename())->getSystemid();
		    
		
		//Load postacomment
		$arrComments = class_modul_postacomment_post::loadPostList(true, $strPagefilter, $strSystemidfilter, $this->getPortalLanguage());
      
		$strTemplateID = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "postacomment_post");
		//Check rights
		if(count($arrComments) > 0) {
    		foreach($arrComments as $objOnePost) {
    			if($this->objRights->rightView($objOnePost->getSystemid())) {
    			    $strOnePost = "";
    				$arrOnePost = array();
    				$arrOnePost["postacomment_post_name"] = $objOnePost->getStrUsername();
    				$arrOnePost["postacomment_post_subject"] = $objOnePost->getStrTitle();
    				$arrOnePost["postacomment_post_message"] = $objOnePost->getStrComment();
    				$arrOnePost["postacomment_post_systemid"] = $objOnePost->getSystemid();
    				$arrOnePost["postacomment_post_date"] = timeToString($objOnePost->getIntDate(), true);

    				
    				$strOnePost .= $this->objTemplate->fillTemplate($arrOnePost, $strTemplateID);
    
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
    			    $strPosts .= class_element_portal::addPortalEditorCode($strOnePost, $objOnePost->getSystemid(), $arrPeConfig, true);
    			}
    		}
    		
		}
		else
		    $strPosts .= $this->getText("postacomment_empty");
		    
		//Create form
        $strTemplateID = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "postacomment_form");
        $arrForm = array();
        $arrForm["formaction"] = getLinkPortalRaw($this->getPagename(), "", "postComment", "", $this->getSystemid());
		$arrForm["comment_name"] = $this->getParam("comment_name");
		$arrForm["comment_subject"] = $this->getParam("comment_subject");
		$arrForm["comment_message"] = $this->getParam("comment_message");
		$arrForm["comment_template"] = $this->arrElementData["char1"];
		$arrForm["comment_systemid"] = $this->getParam("systemid");
		$arrForm["comment_page"] = $this->getPagename();
		$arrForm["validation_errors"] = $this->strErrors;
		$strForm .= $this->objTemplate->fillTemplate($arrForm, $strTemplateID);
		
		//add sourrounding list template
		$strTemplateID = $this->objTemplate->readTemplate("/modul_postacomment/".$this->arrElementData["char1"], "postacomment_list");
		$strReturn .= $this->objTemplate->fillTemplate(array("postacomment_form" => $strForm, "postacomment_list" => $strPosts), $strTemplateID); 
		return $strReturn;
	}
	
	
	/**
	 * Saves a post to the databases
	 *
	 */
	public function actionPostComment() {
	    
	    //pageid or systemid to filter?
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
	    
	    $objPost->saveObjectToDb();
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
	        $this->strErrors .= $this->objTemplate->fillTemplate(array("error" => $this->getText("validation_name")), $strTemplateId);
	    }
	    if(uniStrlen($this->getParam("comment_message")) < 2) {
	        $bitReturn = false;
	        $this->strErrors .= $this->objTemplate->fillTemplate(array("error" => $this->getText("validation_message")), $strTemplateId);
	    }
	    if($this->objSession->getCaptchaCode() != $this->getParam("form_captcha")) {
	        $bitReturn = false;
	        $this->strErrors .= $this->objTemplate->fillTemplate(array("error" => $this->getText("validation_code")), $strTemplateId);
	    }
	    return $bitReturn;
	}

	

}
?>