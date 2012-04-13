<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_navigation_tree.php 4582 2012-04-11 18:27:04Z sidler $                              *
********************************************************************************************************/
/**
 * Portal-class of the guestbook. Handles postings
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 */
class class_module_guestbook_portal extends class_portal implements interface_portal {

    private $arrErrors = array();

	/**
	 * Constructor
	 *
	 * @param $arrElementData
	 */
	public function __construct($arrElementData) {
		$this->setArrModuleEntry("moduleId", _guestbook_module_id_);
		$this->setArrModuleEntry("modul", "guestbook");

        parent::__construct($arrElementData);
	}

	

	/**
	 * Returns a list of all posts in the current gb
	 *
	 * @return string
     * @permissions view
	 */
	protected function actionList() {
		$strReturn = "";
		$arrTemplate = array();
		$arrTemplate["liste_posts"] ="";
        //Load all posts
	    $objArraySectionIterator = new class_array_section_iterator(class_module_guestbook_post::getPostsCount($this->arrElementData["guestbook_id"], true));
	    $objArraySectionIterator->setIntElementsPerPage($this->arrElementData["guestbook_amount"]);
	    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
	    $objArraySectionIterator->setArraySection(
            class_module_guestbook_post::getPosts($this->arrElementData["guestbook_id"], true, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
        );

		$arrObjPosts = $this->objToolkit->simplePager($objArraySectionIterator, $this->getLang("commons_next"), $this->getLang("commons_back"), "", $this->getPagename());

		//and put posts into a template
        /** @var class_module_guestbook_post $objOnePost */
		foreach($arrObjPosts["arrData"] as $objOnePost) {
			if($objOnePost->rightView()) {
				$strTemplatePostID = $this->objTemplate->readTemplate("/module_guestbook/".$this->arrElementData["guestbook_template"], "post");
				$arrTemplatePost = array();
				$arrTemplatePost["post_name"] = "<a href=\"mailto:".$objOnePost->getStrGuestbookPostEmail()."\">".$objOnePost->getStrGuestbookPostName()."</a>";
				$arrTemplatePost["post_name_plain"] = $objOnePost->getStrGuestbookPostName();
				$arrTemplatePost["post_email"] = $objOnePost->getStrGuestbookPostEmail();
				$arrTemplatePost["post_page"] = "<a href=\"http://".$objOnePost->getStrGuestbookPostPage()."\">".$objOnePost->getStrGuestbookPostPage()."</a>";
				//replace encoded newlines
				$arrTemplatePost["post_text"] = uniStrReplace("&lt;br /&gt;", "<br />", $objOnePost->getStrGuestbookPostText());
				$arrTemplatePost["post_date"] = timeToString($objOnePost->getIntGuestbookPostDate());
				$arrTemplate["liste_posts"] .= $this->objTemplate->fillTemplate($arrTemplatePost, $strTemplatePostID, false);
			}
		}

        //link to the post-form & pageview links
		$arrTemplate["link_newentry"] = getLinkPortal(($this->getParam("page") ? $this->getParam("page") : ""), "", "", $this->getLang("eintragen"), "insertGuestbook");
		$arrTemplate["link_forward"] = $arrObjPosts["strForward"];
		$arrTemplate["link_pages"] = $arrObjPosts["strPages"];
		$arrTemplate["link_back"] = $arrObjPosts["strBack"];

		$strTemplateID = $this->objTemplate->readTemplate("/module_guestbook/".$this->arrElementData["guestbook_template"], "list");
		$strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);
		return $strReturn . "";
	}


    /**
     * Creates a form to handle a new post
     *
     * @param array $arrTemplateOld
     *
     * @internal param mixed $arrTemplate values to fill in
     * @return string
     */
	protected function actionInsertGuestbook($arrTemplateOld = array()) {
		$strReturn = "";
		$strTemplateID = $this->objTemplate->readTemplate("/module_guestbook/".$this->arrElementData["guestbook_template"], "entry_form");

        $strErrors = "";
        if(count($this->arrErrors) > 0) {
            $strErrorTemplateID = $this->objTemplate->readTemplate("/module_guestbook/".$this->arrElementData["guestbook_template"], "error_row");
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
	protected function actionSaveGuestbook() {
		$strReturn = "";
        
        if(!$this->validateData()) {
            $this->setParam("eintragen_fehler", $this->getLang("eintragen_fehler"));
            return $this->actionInsertGuestbook($this->getAllParams());
        }

        $objBook = new class_module_guestbook_guestbook($this->arrElementData["guestbook_id"]);

		//check rights
		if($objBook->rightRight1()) {
		    //create a post-object
            $objPost = new class_module_guestbook_post();
            $objPost->setStrGuestbookPostName($this->getParam("gb_post_name"));
            $objPost->setStrGuestbookPostEmail($this->getParam("gb_post_email"));
            $objPost->setStrGuestbookPostPage($this->getParam("gb_post_page"));
            $objPost->setStrGuestbookPostText($this->getParam("gb_post_text"));
            $objPost->setIntGuestbookPostDate(time());

            //save obj to db
            if(!$objPost->updateObjectToDb($objBook->getSystemid()))
                throw new class_exception("Error saving entry", class_exception::$level_ERROR);



            $strMailtext = $this->getLang("new_post_mail");
            $strMailtext .= getLinkAdminHref("guestbook", "edit", "&systemid=".$objPost->getSystemid(), false);
            $objMessageHandler = new class_module_messaging_messagehandler();

            $arrGroups = array();
            $allGroups = class_module_user_group::getAllGroups();
            foreach($allGroups as $objOneGroup) {
                if(class_rights::getInstance()->checkPermissionForGroup($objOneGroup->getSystemid(), class_rights::$STR_RIGHT_EDIT, $this->getObjModule()->getSystemid()))
                    $arrGroups[] = $objOneGroup;
            }

            $objMessageHandler->sendMessage($strMailtext, $arrGroups, new class_messageprovider_guestbook());


			//Flush the page from cache
            $this->flushPageFromPagesCache($this->getPagename());
            $this->portalReload(getLinkPortalHref($this->getPagename()));

		}
		else
			$strReturn = $this->getLang("commons_error_permissions");
		return $strReturn;
	}




	/**
	 * Validates the submitted data
	 *
	 * @return bool
	 */
	private function validateData() {
		$bitReturn = true;

		//Check captachcode
		if($this->getParam("gb_post_captcha") != $this->objSession->getCaptchaCode() || $this->getParam("gb_post_captcha") == "")
			$bitReturn = false;

		//Check mailaddress
        $objMailValidator = new class_email_validator();
		if(!$objMailValidator->validate($this->getParam("gb_post_email"))) {
            $this->arrErrors[] = $this->getLang("insert_error_email");
			$bitReturn = false;
        }

		if(uniStrlen($this->getParam("gb_post_name")) == 0) {
            $this->arrErrors[] = $this->getLang("insert_error_name");
			$bitReturn = false;
        }

		if(uniStrlen($this->getParam("gb_post_text")) == 0) {
            $this->arrErrors[] = $this->getLang("insert_error_post");
			$bitReturn = false;
        }

		//if there ain't any errors, update texts
		if($bitReturn) {
			$this->setParam("gb_post_name", htmlToString($this->getParam("gb_post_name")));
			$this->setParam("gb_post_email", htmlToString($this->getParam("gb_post_email")));
			$this->setParam("gb_post_text", htmlToString($this->getParam("gb_post_text")));
		}

		return $bitReturn;
	}


}
