<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						*
********************************************************************************************************/

/**
 * Portal-class of the postacomment-module
 * Serves xml-requests, e.g. saves a sent comment
 *
 * @package modul_postacomment
 */
class class_modul_postacomment_portal_xml extends class_portal implements interface_xml_portal {

    private $strErrors;

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_postacomment";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _postacomment_modul_id_;
		$arrModule["modul"]				= "postacomment";

		parent::__construct($arrModule, array());
	}


	/**
	 * Actionblock. Controls the further behaviour.
	 *
	 * @param string $strAction
	 * @return string
	 */
	public function action($strAction = "") {
        $strReturn = "";
        if($strAction == "savepost")
            $strReturn .= $this->actionSavePost();

        return $strReturn;
	}


	/**
	 * saves a post in the database and returns the post as html.
	 * In case of missing fields, the form is returned again
	 *
	 * @return string
	 */
	private function actionSavePost() {
	    $strReturn = "";

	    $strXMLContent = "";

		//check permissions
		if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
	        //validate needed fields
	        if(!$this->validateForm()) {
	            //Create form to reenter values
                $strTemplateID = $this->objTemplate->readTemplate("/modul_postacomment/".$this->getParam("comment_template"), "postacomment_form");
                $arrForm = array();
                $arrForm["formaction"] = getLinkPortalHref($this->getPagename(), "", "postComment", "", $this->getSystemid());
        		$arrForm["comment_name"] = $this->getParam("comment_name");
        		$arrForm["comment_subject"] = $this->getParam("comment_subject");
        		$arrForm["comment_message"] = $this->getParam("comment_message");
        		$arrForm["comment_template"] = $this->getParam("comment_template");
        		$arrForm["comment_systemid"] = $this->getParam("comment_systemid");
		        $arrForm["comment_page"] = $this->getParam("comment_page");
        		$arrForm["validation_errors"] = $this->strErrors;

        		//texts
        		$arrForm["postacomment_write_new"] = $this->getText("postacomment_write_new");
	            $arrForm["form_name_label"] = $this->getText("form_name_label");
	            $arrForm["form_subject_label"] = $this->getText("form_subject_label");
	            $arrForm["form_message_label"] = $this->getText("form_message_label");
	            $arrForm["form_captcha_label"] = $this->getText("form_captcha_label");
	            $arrForm["form_captcha_reload_label"] = $this->getText("form_captcha_reload_label");
	            $arrForm["form_submit_label"] = $this->getText("form_submit_label");

        		$strXMLContent .= $this->fillTemplate($arrForm, $strTemplateID);
	        }
	        else {
	            //save the post to the db
	            //pageid or systemid to filter?
        		$strSystemidfilter = $this->getParam("comment_systemid");
        		$strPagefilter = "";

        		$strPagefilter = class_modul_pages_page::getPageByName($this->getParam("comment_page"))->getSystemid();

        	    $objPost = new class_modul_postacomment_post();
        	    $objPost->setStrUsername($this->getParam("comment_name"));
        	    $objPost->setStrTitle($this->getParam("comment_subject"));
        	    $objPost->setStrComment($this->getParam("comment_message"));

        	    $objPost->setStrAssignedPage($strPagefilter);
        	    $objPost->setStrAssignedSystemid($strSystemidfilter);
        	    $objPost->setStrAssignedLanguage($this->getPortalLanguage());

        	    $objPost->updateObjectToDb();
                $this->flushPageFromPagesCache($this->getPagename());
        	    //reinit post -> encoded entities
        	    $objPost->loadDataFromDb();


        	    //load the post as a new post to add it at top of the list
				$arrOnePost = array();
				$arrOnePost["postacomment_post_name"] = $objPost->getStrUsername();
				$arrOnePost["postacomment_post_subject"] = $objPost->getStrTitle();
				$arrOnePost["postacomment_post_message"] = $objPost->getStrComment();
				$arrOnePost["postacomment_post_systemid"] = $objPost->getSystemid();
				$arrOnePost["postacomment_post_date"] = timeToString($objPost->getIntDate(), true);

				$strTemplateID = $this->objTemplate->readTemplate("/modul_postacomment/".$this->getParam("comment_template"), "postacomment_post");
				$strXMLContent .= $this->fillTemplate($arrOnePost, $strTemplateID);
	        }

		}
		else
		    $strXMLContent = $this->getText("fehler_recht");

	    $strReturn .= $this->createPostCommentXML($strXMLContent);

        return $strReturn;
	}


	private function createPostCommentXML($strContent) {
        $strReturn = "";

        $strReturn .= "<postacomment>\n";

        //placing a html-part, so no xmlSafeString needed
        //$strReturn .= xmlSafeString($strContent);
        $strReturn .= $strContent;

	    $strReturn .= "</postacomment>";
        return $strReturn;
	}


    /**
	 * Validates the form data provided by the user
	 *
	 * @return bool
	 */
	public function validateForm() {
	    $bitReturn = true;

	    $strTemplateId = $this->objTemplate->readTemplate("/modul_postacomment/".$this->getParam("comment_template"), "validation_error_row");
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
}
?>