<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Class to represent a guestbook post
 *
 * @package modul_guestbook
 */
class class_modul_guestbook_post extends class_model implements interface_model  {

    private $strGuestbookPostName = "";
    private $strGuestbookPostEmail = "";
    private $strGuestbookPostPage = "";
    private $strGuestbookPostText = "";
    private $intGuestbookPostDate = "";
    private $strGuestbookID = "";
    private $intGuestbookPostStatus = 1;


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_guestbook";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _gaestebuch_modul_id_;
		$arrModul["table"]       		= _dbprefix_."guestbook_post";
		$arrModul["modul"]				= "guestbook";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }


    /**
     * initialises the current object if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT *
						FROM ".$this->arrModule["table"].", "._dbprefix_."system
						WHERE system_id = guestbook_post_id
						  AND system_id='".$this->objDB->dbsafeString($this->getSystemid())."'
						ORDER BY guestbook_post_date DESC";
        $arrData = $this->objDB->getRow($strQuery);

        $this->strGuestbookPostName = $arrData["guestbook_post_name"];
        $this->strGuestbookPostEmail = $arrData["guestbook_post_email"];
        $this->strGuestbookPostPage = $arrData["guestbook_post_page"];
        $this->strGuestbookPostText = $arrData["guestbook_post_text"];
        $this->intGuestbookPostDate = $arrData["guestbook_post_date"];
    }

    /**
     * saves to current object as a new record to the database.
     * Set all vars before using the setters
     *
     * @return bool
     */
    public function saveObjectToDb() {

        //start tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
        //create the systemrecord
        $strPostSystemid = $this->createSystemRecord($this->strGuestbookID, "GBPost", true, "", "", $this->intGuestbookPostStatus);
        $this->setSystemid($strPostSystemid);
        class_logger::getInstance()->addLogRow("new gb-post ".$this->getSystemid(), class_logger::$levelInfo);
        //and the post itself
        $strQuery = "INSERT INTO ".$this->arrModule["table"]. "
                        (	guestbook_post_id, 	guestbook_post_name	, 	guestbook_post_email , 	guestbook_post_page , 	guestbook_post_text	,  guestbook_post_date ) VALUES
                        ('".$this->objDB->dbsafeString($strPostSystemid)."', '".$this->objDB->dbsafeString($this->strGuestbookPostName)."',
                         '".$this->objDB->dbsafeString($this->strGuestbookPostEmail)."', '".$this->objDB->dbsafeString($this->strGuestbookPostPage)."',
                         '".$this->objDB->dbsafeString($this->strGuestbookPostText)."', '".(int)time()."' )";
        if(!$this->objDB->_query($strQuery))
            $bitCommit = false;

		//Transaktion beenden
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
     * Update the current object in the db
     * NOT IMPLEMENTED YET
     *
     * @return bool
     *
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("update gb-post ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        //Update all needed tables
        //dates
        $this->objDB->transactionBegin();
        $bitCommit = true;
        
        //post
        $strQuery = "UPDATE ".$this->objDB->encloseTableName($this->arrModule["table"])." 
                        SET guestbook_post_text = '".dbsafeString($this->getGuestbookPostText(), false)."' 
                      WHERE guestbook_post_id = '".dbsafeString($this->getSystemid())."'"; 
        
         if(!$this->objDB->_query($strQuery))
            $bitCommit = false;

        //Transaktion beenden
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
     * Deltes a post from the database
     *
     * @param string $strSystemid The post set to be deleted
     * @return bool
     * @static
     */
    public static function deletePost($strSystemid) {
        class_logger::getInstance()->addLogRow("deleted dbpost ".$strSystemid, class_logger::$levelInfo);
        $objDB = class_carrier::getInstance()->getObjDB();
        //start a tx
		$objDB->transactionBegin();
		$bitCommit = false;

        $strQuery = "DELETE FROM "._dbprefix_."guestbook_post WHERE guestbook_post_id='".dbsafeString($strSystemid)."'";
        $objRoot = new class_modul_system_common($strSystemid);
	    if($objDB->_query($strQuery))    {
	        if($objRoot->deleteSystemRecord($strSystemid)) {
	            $bitCommit = true;
	        }
	    }

	    //End tx
		if($bitCommit) {
			$objDB->transactionCommit();
			return true;
		}
		else {
			$objDB->transactionRollback();
			return false;
		}
    }

    /**
	 * Loads all posts belonging to the given systemid (in most cases a guestbook)
	 *
	 * @param string $strSystemid
	 * @return string
	 * @static
	 */
	public static function getPosts($strSystemid = "", $bitJustActive = false) {
	    $strQuery = "SELECT system_id
						FROM "._dbprefix_."guestbook_post, "._dbprefix_."system
						WHERE system_id = guestbook_post_id
						  AND system_prev_id='".dbsafeString($strSystemid)."'
						  ".($bitJustActive ? " AND system_status = 1" : "" )."
						ORDER BY guestbook_post_date DESC";

	    $objDB = class_carrier::getInstance()->getObjDB();
	    $arrPosts = $objDB->getArray($strQuery);

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
	 * @return string
	 * @static
	 */
	public static function getPostsCount($strSystemid = "", $bitJustActive = false) {
	    $strQuery = "SELECT COUNT(*)
						FROM "._dbprefix_."guestbook_post, "._dbprefix_."system
						WHERE system_id = guestbook_post_id
						  AND system_prev_id='".dbsafeString($strSystemid)."'
						  ".($bitJustActive ? " AND system_status = 1" : "" )."
						ORDER BY guestbook_post_date DESC";

	    $objDB = class_carrier::getInstance()->getObjDB();
	    $arrRow = $objDB->getRow($strQuery);
	    return $arrRow["COUNT(*)"];

	}

	/**
	 * Loads a section of posts belonging to the given systemid (in most cases a guestbook)
	 *
	 * @param string $strSystemid
	 * @return string
	 * @static
	 */
	public static function getPostsSection($strSystemid = "", $bitJustActive = false, $intStart, $intEnd) {
	    $strQuery = "SELECT system_id
						FROM "._dbprefix_."guestbook_post, "._dbprefix_."system
						WHERE system_id = guestbook_post_id
						  AND system_prev_id='".dbsafeString($strSystemid)."'
						  ".($bitJustActive ? " AND system_status = 1" : "" )."
						ORDER BY guestbook_post_date DESC";

	    $objDB = class_carrier::getInstance()->getObjDB();
	    $arrPosts = $objDB->getArraySection($strQuery, $intStart, $intEnd);

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
    public function getGuestbookPostStatus() {
        return $this->intGuestbookPostStatus;
    }
    public function getGuestbookID() {
        return $this->strGuestbookID;
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
    public function setGuestbookID($strGuestbookID) {
        $this->strGuestbookID = $strGuestbookID;
    }
    public function setGuestbookPostStatus($intGuestbookPostStatus) {
        $this->intGuestbookPostStatus = $intGuestbookPostStatus;
    }

}
?>