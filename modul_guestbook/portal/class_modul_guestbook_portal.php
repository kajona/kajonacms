<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * Portal-class of the guestbook. Handles postings
 *
 * @package modul_guestbook
 */
class class_modul_guestbook_portal extends class_portal implements interface_portal {

    private $arrErrors = array();

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($arrElementData) {
        $arrModule = array();
		$arrModule["name"] 				= "modul_guestbook";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["table"] 	    	= _dbprefix_."guestbook_book";
		$arrModule["table2"] 			= _dbprefix_."guestbook_post";
		$arrModule["moduleId"] 			= _guestbook_modul_id_;
		$arrModule["modul"] 			= "guestbook";

        parent::__construct($arrModule, $arrElementData);
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
				        $this->portalReload(getLinkPortalHref($this->getPagename()));
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
	    $objArraySectionIterator = new class_array_section_iterator(class_modul_guestbook_post::getPostsCount($this->arrElementData["guestbook_id"], true));
	    $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["guestbook_amount"]);
	    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
	    $objArraySectionIterator->setArraySection(class_modul_guestbook_post::getPostsSection($this->arrElementData["guestbook_id"], true, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

	    $arrObjPosts = $objArraySectionIterator->getArrayExtended();

		$arrObjPosts = $this->objToolkit->pager($this->arrElementData["guestbook_amount"], ($this->getParam("pv") != "" ? $this->getParam("pv") : 1), $this->getText("weiter"), $this->getText("zurueck"), "", ($this->getParam("page") != "" ? $this->getParam("page") : ""), $arrObjPosts);

		//and put posts into a template
		foreach($arrObjPosts["arrData"] as $objOnePost) {
			if($this->objRights->rightView($objOnePost->getSystemid())){
				$strTemplatePostID = $this->objTemplate->readTemplate("/modul_guestbook/".$this->arrElementData["guestbook_template"], "post");
				$arrTemplatePost = array();
				$arrTemplatePost["post_name"] = "<a href=\"mailto:".$objOnePost->getGuestbookPostEmail()."\">".$objOnePost->getGuestbookPostName()."</a>";
				$arrTemplatePost["post_name_plain"] = $objOnePost->getGuestbookPostName();
				$arrTemplatePost["post_email"] = $objOnePost->getGuestbookPostEmail();
				$arrTemplatePost["post_page"] = "<a href=\"http://".$objOnePost->getGuestbookPostPage()."\">".$objOnePost->getGuestbookPostPage()."</a>";
				//replace encoded newlines
				$arrTemplatePost["post_text"] = uniStrReplace("&lt;br /&gt;", "<br />" , $objOnePost->getGuestbookPostText());
				$arrTemplatePost["post_date"] = timeToString($objOnePost->getGuestbookPostDate());
				$arrTemplate["liste_posts"] .= $this->objTemplate->fillTemplate($arrTemplatePost, $strTemplatePostID, false);
			}
		}

        //link to the post-form & pageview links
		$arrTemplate["link_newentry"] = getLinkPortal(($this->getParam("page") ? $this->getParam("page") : ""), "", "", $this->getText("eintragen"), "insertGuestbook");
		$arrTemplate["link_forward"] = $arrObjPosts["strForward"];
		$arrTemplate["link_pages"] = $arrObjPosts["strPages"];
		$arrTemplate["link_back"] = $arrObjPosts["strBack"];

		$strTemplateID = $this->objTemplate->readTemplate("/modul_guestbook/".$this->arrElementData["guestbook_template"], "list");
		$strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);
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

        $strErrors = "";
        if(count($this->arrErrors) > 0) {
            $strErrorTemplateID = $this->objTemplate->readTemplate("/modul_guestbook/".$this->arrElementData["guestbook_template"], "error_row");
            foreach ($this->arrErrors as $strOneError)
            $strErrors .= $this->fillTemplate(array("error" => $strOneError), $strErrorTemplateID);
        }

		//update elements
		$arrTemplate = array();
		$arrTemplate["eintragen_fehler"] = $this->getParam("eintragen_fehler").$strErrors;
        $arrTemplate["gb_post_name"]  = htmlToString($this->getParam("gb_post_name"), true);
        $arrTemplate["gb_post_email"] = htmlToString($this->getParam("gb_post_email"), true);
        $arrTemplate["gb_post_text"] = htmlToString($this->getParam("gb_post_text"), true);
        $arrTemplate["gb_post_page"] = htmlToString($this->getParam("gb_post_page"), true);

		$arrTemplate["action"] = getLinkPortalHref($this->getPagename(), "", "saveGuestbook");
        $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);
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
            $objPost->setGuestbookPostDate(time());

			//Load guestbook data
			$objGB = $this->getGuestbook($this->arrElementData["guestbook_id"]);
			if($objGB->getGuestbookModerated() == 1)
			    $objPost->setGuestbookPostStatus(1);
			else
			    $objPost->setGuestbookPostStatus(0);

            //save obj to db
            if(!$objPost->updateObjectToDb($this->arrElementData["guestbook_id"]))
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
		if(!checkEmailaddress($this->getParam("gb_post_email"))) {
            $this->arrErrors[] = $this->getText("insert_error_email");
			$bitReturn = false;
        }

		if(uniStrlen($this->getParam("gb_post_name")) == 0) {
            $this->arrErrors[] = $this->getText("insert_error_name");
			$bitReturn = false;
        }

		if(uniStrlen($this->getParam("gb_post_text")) == 0) {
            $this->arrErrors[] = $this->getText("insert_error_post");
			$bitReturn = false;
        }

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