<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/


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
     * @param string $strSystemid (use "" on new objects)
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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."guestbook_book" => "guestbook_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "guestbook ".$this->getGuestbookTitle();
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
     * Adds the right of the guest to sign the book
     *
     * @return bool
     */
    protected function onInsertToDb() {
        return $this->objRights->addGroupToRight(_guests_group_id_, $this->getSystemid(), "right1");
    }

    /**
     * Updates the corresponding record in the database with the values of the current object
     *
     * @return bool
     */
    protected function updateStateToDb() {
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
	 * @return bool
	 * @static
	 */
    public function deleteGuestbook() {
        class_logger::getInstance()->addLogRow("deleted gb ".$this->getSystemid(), class_logger::$levelInfo);

		//start by deleting the posts
		$objPosts = class_modul_guestbook_post::getPosts($this->getSystemid());
		//Loop over them an delete
        $bitPosts = true;
		foreach($objPosts as $objOnePost) {
            $bitPosts &= $objOnePost->deletePost();
		}
		//and the book itself
		$this->objDB->transactionBegin();
		$bitCommit = false;

		//and delete the book
		$strQuery = "DELETE FROM "._dbprefix_."guestbook_book WHERE guestbook_id = '".dbsafeString($this->getSystemid())."'";

	    if($bitPosts && $this->objDB->_query($strQuery)) {
	        if($this->deleteSystemRecord($strSystemid))
	            $bitCommit = true;
	    }
	    else
	        $bitCommit = false;

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