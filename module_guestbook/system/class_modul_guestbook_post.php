<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Class to represent a guestbook post
 *
 * @package modul_guestbook
 * @author sidler@mulchprod.de
 */
class class_modul_guestbook_post extends class_model implements interface_model  {

    private $strGuestbookPostName = "";
    private $strGuestbookPostEmail = "";
    private $strGuestbookPostPage = "";
    private $strGuestbookPostText = "";
    private $intGuestbookPostDate = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_guestbook";
		$arrModul["moduleId"] 			= _guestbook_modul_id_;
		$arrModul["table"]       		= _dbprefix_."guestbook_post";
		$arrModul["modul"]				= "guestbook";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }


    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."guestbook_post" => "guestbook_post_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "guestbook post ".$this->getGuestbookPostDate();
    }



    /**
     * initialises the current object if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT *
						FROM ".$this->arrModule["table"].", "._dbprefix_."system
						WHERE system_id = guestbook_post_id
						  AND system_id= ?
						ORDER BY guestbook_post_date DESC";
        $arrData = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        $this->strGuestbookPostName = $arrData["guestbook_post_name"];
        $this->strGuestbookPostEmail = $arrData["guestbook_post_email"];
        $this->strGuestbookPostPage = $arrData["guestbook_post_page"];
        $this->strGuestbookPostText = $arrData["guestbook_post_text"];
        $this->intGuestbookPostDate = $arrData["guestbook_post_date"];
    }


    /**
     * Update the current object in the db
     *
     * @return bool
     *
     */
    protected function updateStateToDb() {
        $strQuery = "UPDATE ".$this->objDB->encloseTableName($this->arrModule["table"])." 
                        SET guestbook_post_text = ?,
                            guestbook_post_name = ?,
                            guestbook_post_email = ?,
                            guestbook_post_page = ?,
                            guestbook_post_date = ?
                      WHERE guestbook_post_id = ?";
        
        return $this->objDB->_pQuery($strQuery, array($this->getGuestbookPostText(), $this->getGuestbookPostName(), $this->getGuestbookPostEmail(),
                   $this->getGuestbookPostPage(), $this->getGuestbookPostDate(), $this->getSystemid() ));
    }

    /**
     * Disables new posts if the guestbook itself is moderated.
     *
     * @return bool
     */
    protected function onInsertToDb() {
        $objGuestbook = new class_modul_guestbook_guestbook($this->getPrevId());
        if($objGuestbook->getGuestbookModerated() == "0")
            $this->setStatus();

        return true;
    }


    /**
     * Deltes a post from the database
     *
     * @return bool
     * @static
     */
    public function deletePost() {
        class_logger::getInstance()->addLogRow("deleted dbpost ".$this->getSystemid(), class_logger::$levelInfo);
        //start a tx
		$this->objDB->transactionBegin();
		$bitCommit = false;

        $strQuery = "DELETE FROM "._dbprefix_."guestbook_post WHERE guestbook_post_id= ?";
	    if($this->objDB->_pQuery($strQuery, array($this->getSystemid()) ))    {
	        if($this->deleteSystemRecord($this->getSystemid())) {
	            $bitCommit = true;
	        }
	    }

	    //End tx
		if($bitCommit) {
			$this->objDB->transactionCommit();
			return true;
		}
		else {
			$this->objDB->transactionRollback();
			return false;
		}
    }

    /**
	 * Loads all posts belonging to the given systemid (in most cases a guestbook)
	 *
	 * @param string $strSystemid
	 * @return array
	 * @static
	 */
	public static function getPosts($strSystemid = "", $bitJustActive = false) {
	    $strQuery = "SELECT system_id
						FROM "._dbprefix_."guestbook_post, "._dbprefix_."system
						WHERE system_id = guestbook_post_id
						  AND system_prev_id=?
						  ".($bitJustActive ? " AND system_status = 1" : "" )."
						ORDER BY guestbook_post_date DESC";

	    $objDB = class_carrier::getInstance()->getObjDB();
	    $arrPosts = $objDB->getPArray($strQuery, array($strSystemid));

	    $arrReturn = array();
	    //load all posts as objects
	    foreach($arrPosts as $arrOnePostID) {
            $arrReturn[] = new class_modul_guestbook_post($arrOnePostID["system_id"]);
	    }
		return $arrReturn;
	}

	/**
	 * Looks up the posts available
	 *
	 * @param string $strSystemid
	 * @return int
	 * @static
	 */
	public static function getPostsCount($strSystemid = "", $bitJustActive = false) {
	    $strQuery = "SELECT COUNT(*)
						FROM "._dbprefix_."guestbook_post, "._dbprefix_."system
						WHERE system_id = guestbook_post_id
						  AND system_prev_id=?
						  ".($bitJustActive ? " AND system_status = 1" : "" )."";

	    $objDB = class_carrier::getInstance()->getObjDB();
	    $arrRow = $objDB->getPRow($strQuery, array($strSystemid));
	    return $arrRow["COUNT(*)"];

	}

	/**
	 * Loads a section of posts belonging to the given systemid (in most cases a guestbook)
	 *
	 * @param string $strSystemid
	 * @return string
	 * @static
	 */
	public static function getPostsSection($strSystemid, $bitJustActive, $intStart, $intEnd) {
	    $strQuery = "SELECT system_id
						FROM "._dbprefix_."guestbook_post, "._dbprefix_."system
						WHERE system_id = guestbook_post_id
						  AND system_prev_id=?
						  ".($bitJustActive ? " AND system_status = 1" : "" )."
						ORDER BY guestbook_post_date DESC";

	    $objDB = class_carrier::getInstance()->getObjDB();
	    $arrPosts = $objDB->getPArraySection($strQuery, array($strSystemid), $intStart, $intEnd);

	    $arrReturn = array();
	    //load all posts as objects
	    foreach($arrPosts as $arrOnePostID) {
            $arrReturn[] = new class_modul_guestbook_post($arrOnePostID["system_id"]);
	    }
		return $arrReturn;
	}

// --- SETTERS / GETTERS --------------------------------------------------------------------------------

    public function getGuestbookPostName() {
        return $this->strGuestbookPostName;
    }
    public function getGuestbookPostEmail() {
        return $this->strGuestbookPostEmail;
    }
    public function getGuestbookPostPage() {
        return $this->strGuestbookPostPage;
    }
    public function getGuestbookPostText() {
        return $this->strGuestbookPostText;
    }
    public function getGuestbookPostDate() {
        return $this->intGuestbookPostDate;
    }

    public function setGuestbookPostName($strGuestbookPostName) {
        $this->strGuestbookPostName = $strGuestbookPostName;
    }
    public function setGuestbookPostEmail($strGuestbookPostEmail) {
        $this->strGuestbookPostEmail = $strGuestbookPostEmail;
    }
    public function setGuestbookPostPage($strGuestbookPostPage) {
        //Remove protocol-prefixes
        $strGuestbookPostPage = str_replace("http://", "", $strGuestbookPostPage);
        $strGuestbookPostPage = str_replace("https://", "", $strGuestbookPostPage);
        $this->strGuestbookPostPage = $strGuestbookPostPage;
    }
    public function setGuestbookPostText($strGuestbookPostText) {
        $this->strGuestbookPostText = $strGuestbookPostText;
    }
    public function setGuestbookPostDate($strGuestbookPostDate) {
        $this->intGuestbookPostDate = $strGuestbookPostDate;
    }

}
?>