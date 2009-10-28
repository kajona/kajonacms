<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/


include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Class to represent a guestbook book
 *
 * @package modul_guestbook
 */
class class_modul_guestbook_guestbook extends class_model implements interface_model  {

    private $strGuestbookTitle;
    private $intGuestbookModerated;


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_guestbook";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _guestbook_modul_id_;
		$arrModul["table"]       		= _dbprefix_."guestbook_book";
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
						WHERE system_id = guestbook_id
						  AND system_id='".$this->objDB->dbsafeString($this->getSystemid())."'
						ORDER BY guestbook_title";
        $arrData = $this->objDB->getRow($strQuery);

        $this->strGuestbookTitle = $arrData["guestbook_title"];
        $this->intGuestbookModerated = $arrData["guestbook_moderated"];

    }

    /**
     * Saves the current object as a new guestbook to the database
     *
     * @return bool
     */
    public function saveObjectToDb() {

        //start tx
		$this->objDB->transactionBegin();
		$bitCommit = true;


		//create the systemrecord
		$strGbSystemid = $this->createSystemRecord($this->getModuleSystemid($this->arrModule["modul"]), "GB: ".$this->getGuestbookTitle());
        $this->setSystemid($strGbSystemid);
        class_logger::getInstance()->addLogRow("new gb ".$this->getSystemid(), class_logger::$levelInfo);
        //and the book itself
        $strQuery = "INSERT INTO ".$this->arrModule["table"]." (guestbook_id, guestbook_title, guestbook_moderated) VALUES
				                    ('".$this->objDB->dbsafeString($strGbSystemid)."', '".$this->objDB->dbsafeString($this->getGuestbookTitle())."',
				                     '".$this->objDB->dbsafeString($this->getGuestbookModerated())."')";


        if(!$this->objDB->_query($strQuery))
            $bitCommit = false;

        //new guestbooks should be allowed to be signed by guests
        if($bitCommit) {
            $this->objRights->addGroupToRight(_guests_group_id_, $strGbSystemid, "right1");
        }

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
     * Updates the corresponding record in the database with the values of the current object
     *
     * @return bool
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated gb ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE ".$this->arrModule["table"]."
								SET guestbook_title = '".$this->objDB->dbsafeString($this->getGuestbookTitle())."',
									guestbook_moderated = ".$this->objDB->dbsafeString($this->getGuestbookModerated())."
									WHERE  guestbook_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }


    /**
	 * Loads all guestbooks
	 *
	 * @return mixed, array of all guestbook objects
	 * @static
	 */
	public static function getGuestbooks() {
		$strQuery = "SELECT system_id
						FROM "._dbprefix_."guestbook_book, "._dbprefix_."system
						WHERE system_id = guestbook_id
						ORDER BY guestbook_title";

		$objDB = class_carrier::getInstance()->getObjDB();
		$arrIds =  $objDB->getArray($strQuery);
		$arrReturn = array();
		foreach ($arrIds as $arrOneId)
		  $arrReturn[] = new class_modul_guestbook_guestbook($arrOneId["system_id"]);

		return $arrReturn;
	}


	/**
	 * Deletes a whole guestbook, including all posts in this guestbook from the db
	 *
	 * @param string $strSystemid
	 * @return bool
	 * @static
	 */
    public static function deleteGuestbook($strSystemid) {
        class_logger::getInstance()->addLogRow("deleted gb ".$strSystemid, class_logger::$levelInfo);
        $objDB = class_carrier::getInstance()->getObjDB();

		//start by deleting the posts
		$objPosts = class_modul_guestbook_post::getPosts($strSystemid);
		//Loop over them an delete
		foreach($objPosts as $objOnePost) {
            class_modul_guestbook_post::deletePost($objOnePost->getSystemid());
		}
		//and the book itself
		$objDB->transactionBegin();
		$bitCommit = false;
		$objRoot = new class_modul_system_common($strSystemid);

		//and delete the book
		$strQuery = "DELETE FROM "._dbprefix_."guestbook_book WHERE guestbook_id = '".dbsafeString($strSystemid)."'";

	    if($objDB->_query($strQuery)) {
	        if($objRoot->deleteSystemRecord($strSystemid))
	            $bitCommit = true;
	    }
	    else
	        $bitCommit = false;

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

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getGuestbookModerated() {
        return $this->intGuestbookModerated;
    }
    public function getGuestbookTitle() {
        return $this->strGuestbookTitle;
    }

    public function setGuestbookTitle($strTitle) {
        $this->strGuestbookTitle = $strTitle;
    }
    public function setGuestbookModerated($intStatus) {
        $this->intGuestbookModerated = $intStatus;
    }

}
?>