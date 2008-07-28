<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_guestbook_portal.php																	*
* 	Portal-class of the guestbook       																*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

//Include base classes
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_portal.php");

//include needed classes
include_once(_systempath_."/class_modul_guestbook_post.php");
include_once(_systempath_."/class_modul_guestbook_guestbook.php");

/**
 * Portal-class of the guestbook. Handles postings
 *
 * @package modul_guestbook
 */
class class_modul_guestbook_portal extends class_portal implements interface_portal {
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
		$arrModul["name"] 				= "modul_guestbook";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["table"] 	    		= _dbprefix_."guestbook_book";
		$arrModul["table2"] 			= _dbprefix_."guestbook_post";
		$arrModul["moduleId"] 			= _gaestebuch_modul_id_;
		$arrModul["modul"] 			    = "guestbook";

        parent::__construct($arrModul, $arrElementData);
	}

	/**
	 * Action-block, controling the behaviour of the class
	 *
	 * @return string
	 */
	public function action() {
		$strReturn = "";
		$strAction = "";

		if($this->getParam("action") != "")
		    $strAction = $this->getParam("action");

		if($strAction == "insertGuestbook")
			$strReturn = $this->actionInsert();
		elseif($strAction == "saveGuestbook") {
			if($this->validateData()) {
			    try {
				    $strReturn = $this->actionSave();
				    if($strReturn == "")
				        header("Location: "._indexpath_."?page=".$this->getPagename());
			    }
			    catch (class_exception $objException) {
			        $objException->processException();
			        $strReturn = "An internal error occured: ".$objException->getMessage();
			    }
			}
			else {
				$this->setParam("eintragen_fehler", $this->getText("eintragen_fehler"));
				$strReturn = $this->actionInsert($this->getAllParams());
			}
		}
		else
		    $strReturn = $this->actionList();

		return $strReturn;

	}

//---Aktionsfunktionen-----------------------------------------------------------------------------------

	/**
	 * Returns a list of all posts in the current gb
	 *
	 * @return string
	 */
	public function actionList() {
		$strReturn = "";
		$arrTemplate = array();
		$arrTemplate["liste_posts"] ="";
        //Load all posts
        include_once(_systempath_."/class_array_section_iterator.php");
	    $objArraySectionIterator = new class_array_section_iterator(class_modul_guestbook_post::getPostsCount($this->arrElementData["guestbook_id"], true));
	    $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["guestbook_amount"]);
	    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
	    $objArraySectionIterator->setArraySection(class_modul_guestbook_post::getPostsSection($this->arrElementData["guestbook_id"], true, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

	    $arrObjPosts = $objArraySectionIterator->getArrayExtended();

		$arrObjPosts = $this->objToolkit->pager($this->arrElementData["guestbook_amount"], ($this->getParam("pv") != "" ? $this->getParam("pv") : 1), $this->getText("weiter"), $this->getText("zurueck"), "", ($this->getParam("page") != "" ? $this->getParam("page") : ""), $arrObjPosts);

		//and posts in the template!
		foreach($arrObjPosts["arrData"] as $objOnePost) {
			if($this->objRights->rightView($objOnePost->getSystemid())){
				$strTemplatePostID = $this->objTemplate->readTemplate("/modul_guestbook/".$this->arrElementData["guestbook_template"], "post");
				$arrtemplatePost = array();
				$arrtemplatePost["post_name_from"] = $this->getText("post_name_from");
				$arrtemplatePost["post_name"] = "<a href=\"mailto:".$objOnePost->getGuestbookPostEmail()."\">".$objOnePost->getGuestbookPostName()."</a>";
				$arrtemplatePost["post_name_plain"] = $objOnePost->getGuestbookPostName();
				$arrtemplatePost["post_mail_text"] = $this->getText("post_mail_text");
				$arrtemplatePost["post_email"] = $objOnePost->getGuestbookPostEmail();
				$arrtemplatePost["post_page_text"] = $this->getText("post_page_text");
				$arrtemplatePost["post_page"] = "<a href=\"http://".$objOnePost->getGuestbookPostPage()."\">".$objOnePost->getGuestbookPostPage()."</a>";
				$arrtemplatePost["post_message_text"] = $this->getText("post_message_text");
				$arrtemplatePost["post_text"] = $objOnePost->getGuestbookPostText();
				//replace encoded newlines
				$arrtemplatePost["post_text"] = uniStrReplace("&lt;br /&gt;", "<br />" , $arrtemplatePost["post_text"]);
				$arrtemplatePost["post_date"] = timeToString($objOnePost->getGuestbookPostDate());
				$arrTemplate["liste_posts"] .= $this->objTemplate->fillTemplate($arrtemplatePost, $strTemplatePostID);
			}
		}
        // A link to the post-form
		$arrTemplate["link_newentry"] = getLinkPortal(($this->getParam("page") ? $this->getParam("page") : ""), "", "", $this->getText("eintragen"), "insertGuestbook");
		$arrTemplate["link_forward"] = $arrObjPosts["strForward"];
		$arrTemplate["link_pages"] = $arrObjPosts["strPages"];
		$arrTemplate["link_back"] = $arrObjPosts["strBack"];

		$strTemplateID = $this->objTemplate->readTemplate("/modul_guestbook/".$this->arrElementData["guestbook_template"], "list");
		$strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
		return $strReturn . "";
	}


	/**
	 * Creates a form to handle a new post
	 *
	 * @param mixed $arrTemplate values to fill in
	 * @return string
	 */
	public function actionInsert($arrTemplateOld = array()) {
		$strReturn = "";
		$strTemplateID = $this->objTemplate->readTemplate("/modul_guestbook/".$this->arrElementData["guestbook_template"], "entry_form");

		//update elements
		$arrTemplate = array();
		$arrTemplate["eintragen_fehler"] = $this->getParam("eintragen_fehler");
		$arrTemplate["post_name_text"] = $this->getText("post_name_text");
        $arrTemplate["gb_post_name"]  = htmlToString($this->getParam("gb_post_name"), true);
		$arrTemplate["post_mail_text"] = $this->getText("post_mail_text");
        $arrTemplate["gb_post_email"] = htmlToString($this->getParam("gb_post_email"), true);
		$arrTemplate["post_message_text"] = $this->getText("post_message_text");
        $arrTemplate["gb_post_text"] = htmlToString($this->getParam("gb_post_text"), true);
		$arrTemplate["post_page_text"] = $this->getText("post_page_text");
        $arrTemplate["gb_post_page"] = htmlToString($this->getParam("gb_post_page"), true);
        $arrTemplate["post_submit_text"] = $this->getText("post_submit_text");
		$arrTemplate["post_code_text"] = $this->getText("post_code_text");

		$arrTemplate["action"] = _indexpath_."?page=".$this->getPagename()."&amp;action=saveGuestbook";
        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
		return $strReturn;
	}


	/**
	 * Saves the passed values to db
	 *
	 * @return string "" in case of success
	 */
	public function actionSave() {
		$strReturn = "";

		//check rights
		if($this->objRights->rightRight1($this->arrElementData["guestbook_id"])) {
		    //create a post-object
            $objPost = new class_modul_guestbook_post("");
            $objPost->setGuestbookID($this->arrElementData["guestbook_id"]);
            $objPost->setGuestbookPostName($this->getParam("gb_post_name"));
            $objPost->setGuestbookPostEmail($this->getParam("gb_post_email"));
            $objPost->setGuestbookPostPage($this->getParam("gb_post_page"));
            $objPost->setGuestbookPostText($this->getParam("gb_post_text"));


			//Load guestbook data
			$objGB = $this->getGuestbook($this->arrElementData["guestbook_id"]);
			if($objGB->getGuestbookModerated() == 1)
			    $objPost->setGuestbookPostStatus(1);
			else
			    $objPost->setGuestbookPostStatus(0);

            //save obj to db
            if(!$objPost->saveObjectToDb())
                throw new class_exception("Error saving entry", class_exception::$level_ERROR);

			//Flush the page from cache
			$this->flushPageFromPagesCache($this->getPagename());

		}
		else
			$strReturn = $this->getText("fehler_recht");
		return $strReturn;
	}



//---Helferfunktionen------------------------------------------------------------------------------------

	/**
	 * Validates the submitted data
	 *
	 * @return bool
	 */
	public function validateData() {
		$bitReturn = true;

		//Check captachcode
		if($this->getParam("gb_post_captcha") != $this->objSession->getCaptchaCode() || $this->getParam("gb_post_captcha") == "")
			$bitReturn = false;

		//Check mailaddress
		if(!checkEmailaddress($this->getParam("gb_post_email")))
			$bitReturn = false;

		if(uniStrlen($this->getParam("gb_post_name")) == 0)
			$bitReturn = false;

		if(uniStrlen($this->getParam("gb_post_text")) == 0)
			$bitReturn = false;

		//if there aint any errors, update texts
		if($bitReturn) {
			$this->setParam("gb_post_name", htmlToString($this->getParam("gb_post_name")));
			$this->setParam("gb_post_email", htmlToString($this->getParam("gb_post_email")));
			$this->setParam("gb_post_text", htmlToString($this->getParam("gb_post_text")));
		}

		return $bitReturn;
	}


   /**
	 * Loads one guestbook
	 *
	 * @param string $strSystemid
	 * @return mixed
	 */
	public function getGuestbook($strSystemid) {

	    $objGuestbook = new class_modul_guestbook_guestbook($strSystemid);
	    return $objGuestbook;
	}

}
?>