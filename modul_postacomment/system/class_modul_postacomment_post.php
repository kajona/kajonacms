<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model for comment itself
 *
 * @package modul_postacomment
 */
class class_modul_postacomment_post extends class_model implements interface_model, interface_sortable_rating  {

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
        $arrModul = array();
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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."postacomment" => "postacomment_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "postacomment for ".$this->getStrAssignedSystemid();
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
    public function updateStateToDb() {
        
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
            
        if($strLanguagefilter != "") //check against '' to remain backwards-compatible
            $strFilter .= " AND (postacomment_language = '".dbsafeString($strLanguagefilter)."' OR postacomment_language = '')";
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
    public function deletePost() {
        class_logger::getInstance()->addLogRow("deleted postacomment post ".$this->getSystemid(), class_logger::$levelInfo);
        $objDB = class_carrier::getInstance()->getObjDB();
        //start a tx
		$objDB->transactionBegin();
		$bitCommit = false;

        $strQuery = "DELETE FROM "._dbprefix_."postacomment WHERE postacomment_id='".dbsafeString($this->getSystemid())."'";
	    if($this->objDB->_query($strQuery))    {
	        if($this->deleteSystemRecord($this->getSystemid())) {
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
            $bitReturn &= $objOnePost->deletePost();
        }
        
        foreach($arrPosts2 as $objOnePost) {
            $bitReturn &= $objOnePost->deletePost();
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
        if($this->intDate == null || $this->intDate != "")
            $this->intDate = time();

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