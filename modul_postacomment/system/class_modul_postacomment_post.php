<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Model for comment itself
 *
 * @package modul_postacomment
 */
class class_modul_postacomment_post extends class_model implements interface_model  {

    private $strTitle;
    private $strComment;
    private $strUsername;
    private $intDate;
    private $strAssigendPage;
    private $strAssignedSystemid;
    private $strAssignedLanguage;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_postacomment";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _postacomment_modul_id_;
		$arrModul["table"]       		= _dbprefix_."postacomment";
		$arrModul["modul"]				= "postacomment";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * 
		   			 FROM ".$this->arrModule["table"]."
					 WHERE postacomment_id = '".$this->getSystemid()."'";
        $arrRow = $this->objDB->getRow($strQuery);
        $this->setStrTitle($arrRow["postacomment_title"]);
        $this->setStrComment($arrRow["postacomment_comment"]);
        $this->setStrUsername($arrRow["postacomment_username"]);
        $this->setIntDate($arrRow["postacomment_date"]);
        $this->setStrAssignedPage($arrRow["postacomment_page"]);
        $this->setStrAssignedLanguage($arrRow["postacomment_language"]);
        $this->setStrAssignedSystemid($arrRow["postacomment_systemid"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated postacomment ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        
        $strQuery = "UPDATE ".$this->arrModule["table"]." SET 
                    	postacomment_date		= '".(int)$this->getIntDate()."',
                    	postacomment_page		= '".dbsafeString($this->getStrAssignedPage())."',
						postacomment_language	= '".dbsafeString($this->getStrAssignedLanguage())."',
                    	postacomment_systemid	= '".dbsafeString($this->getStrAssignedSystemid())."',
                    	postacomment_username	= '".dbsafeString($this->getStrUsername())."',
                    	postacomment_title		= '".dbsafeString($this->getStrTitle())."',
	                	postacomment_comment	= '".dbsafeString($this->getStrComment())."'
					WHERE postacomment_id = '".dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    public function saveObjectToDb() {
        //Start wit the system-recods and a tx
		$this->objDB->transactionBegin();
		
        $strPostId = $this->createSystemRecord(0, "postacomment:".$this->getStrTitle());
        $this->setSystemid($strPostId);
        class_logger::getInstance()->addLogRow("new postacomment ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setIntDate(time());
        //The news-Table
        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                    (postacomment_id, postacomment_date, postacomment_language, postacomment_page, postacomment_systemid, postacomment_username, postacomment_title, postacomment_comment) VALUES
                    (
					 '".dbsafeString($this->getSystemid())."', 
					 ".(int)dbsafeString($this->getIntDate()).",
					 '".dbsafeString($this->getStrAssignedLanguage())."',
                     '".dbsafeString($this->getStrAssignedPage())."',
					 '".dbsafeString($this->getStrAssignedSystemid())."',
					 '".dbsafeString($this->getStrUsername())."',
					 '".dbsafeString($this->getStrTitle())."',
					 '".dbsafeString($this->getStrComment())."' 
					)";


        if($this->objDB->_query($strQuery)) {
            $this->objDB->transactionCommit();
			return true;
        }
        else {
            $this->objDB->transactionRollback();
			return false;
        }

    }
    
    
    /**
     * Returns a list of posts belongig
     *
     * @param bool $bitJustActive
     * @param string $strPagefilter
     * @param string $strSystemidfilter false to ignore the filter
     * @param bool $intStart
     * @param bool $intEnd
     * 
     * @return array
     */
    public static function loadPostList($bitJustActive = true, $strPagefilter = "", $strSystemidfilter = "", $strLanguagefilter = "", $intStart = false, $intEnd = false) {
        $arrReturn = array();
        
        $strFilter = "";
        if($strPagefilter != "")
            $strFilter .= " AND postacomment_page = '".dbsafeString($strPagefilter)."' ";

        if($strSystemidfilter !== false)
            $strFilter .= " AND postacomment_systemid = '".dbsafeString($strSystemidfilter)."' ";
            
        if($strLanguagefilter != "")
            $strFilter .= " AND postacomment_language = '".dbsafeString($strLanguagefilter)."' ";    
        if($bitJustActive)
            $strFilter .= " AND system_status = 1 ";        
        
        $strQuery = "SELECT system_id 
					 FROM "._dbprefix_."postacomment, 
						  "._dbprefix_."system
					 WHERE system_id = postacomment_id "
					 . $strFilter ."
					 ORDER BY postacomment_page ASC,
						      postacomment_language ASC,
							  postacomment_date DESC";

        if($intStart !== false && $intEnd !== false)
            $arrComments = class_carrier::getInstance()->getObjDB()->getArraySection($strQuery, $intStart, $intEnd);
        else    
            $arrComments = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        if(count($arrComments) > 0) {
            foreach($arrComments as $arrOneComment)
                $arrReturn[] = new class_modul_postacomment_post($arrOneComment["system_id"]);
        }
        
        return $arrReturn;
    }
    
    /**
     * Counts the number of posts currently in the database
     *
     * @params string $strPageid
     * @return int
     */
    public function getNumberOfPostsAvailable($strPageid = "") {
        $strQuery = "SELECT COUNT(*) FROM ".$this->arrModule["table"]."";
        if($strPageid != "")
            $strQuery .= " WHERE postacomment_page='".dbsafeString($strPageid)."'";
        $arrRow = $this->objDB->getRow($strQuery);
        return $arrRow["COUNT(*)"];
    }
    
    
    /**
     * Deletes the post with the given systemid from the system
     *
     * @param string $strSystemid
     * @return bool
     */
    public static function deletePost($strSystemid) {
        class_logger::getInstance()->addLogRow("deleted postacomment post ".$strSystemid, class_logger::$levelInfo);
        $objDB = class_carrier::getInstance()->getObjDB();
        //start a tx
		$objDB->transactionBegin();
		$bitCommit = false;

        $strQuery = "DELETE FROM "._dbprefix_."postacomment WHERE postacomment_id='".dbsafeString($strSystemid)."'";
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
     * Searches for comments belonging to the systemid
     * to be deleted.
     * Overwrites class_model::doAdditionalCleanupsOnDeletion($strSystemid) 
     *
     * @param string $strSystemid
     * @return bool
     * 
     */
    public function doAdditionalCleanupsOnDeletion($strSystemid) {
        $bitReturn = true;
        //module installed?
        if(class_modul_system_module::getModuleByName("postacomment") == null)
            return true;
        //check that systemid isn't the id of a comment to avoid recursions
        $arrRecordModulId = $this->getSystemRecord($strSystemid);
        if(isset($arrRecordModulId["system_modul_nr"]) && $arrRecordModulId["system_module_nr"] == _postacomment_modul_id_)
            return true;
            
        //ok, so search for a records matching
        $arrPosts1 = class_modul_postacomment_post::loadPostList(false, $strSystemid);
        $arrPosts2 = class_modul_postacomment_post::loadPostList(false, "", $strSystemid);
        
        //and delete
        foreach($arrPosts1 as $objOnePost) {
            $bitReturn &= class_modul_postacomment_post::deletePost($objOnePost->getSystemid());
        }
        
        foreach($arrPosts2 as $objOnePost) {
            $bitReturn &= class_modul_postacomment_post::deletePost($objOnePost->getSystemid());
        }
            
        return $bitReturn;
    }
	

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrTitle() {
        return $this->strTitle;
    }
    public function getStrComment() {
        return $this->strComment;
    }
    public function getStrUsername() {
        return $this->strUsername;
    }
    public function getIntDate() {
        return $this->intDate;
    }
    public function getStrAssignedPage() {
        return $this->strAssigendPage;
    }
    public function getStrAssignedSystemid() {
        return $this->strAssignedSystemid;
    }
    public function getStrAssignedLanguage() {
        return $this->strAssignedLanguage;
    }
    
    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }
    public function setStrComment($strComment) {
        $this->strComment = $strComment;
    }
    public function setStrUsername($strUsername) {
        $this->strUsername = $strUsername;
    }
    public function setIntDate($intDate) {
        $this->intDate = $intDate;
    }
    public function setStrAssignedPage($strAssignedPage) {
        $this->strAssigendPage = $strAssignedPage;
    }
    public function setStrAssignedSystemid($strAssignedSystemid) {
        $this->strAssignedSystemid = $strAssignedSystemid;
    }    
    public function setStrAssignedLanguage($strAssignedLanguage) {
        $this->strAssignedLanguage = $strAssignedLanguage;
    }


}
?>