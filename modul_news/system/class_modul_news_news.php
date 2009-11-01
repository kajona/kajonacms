<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model for a news itself
 *
 * @package modul_news
 */
class class_modul_news_news extends class_model implements interface_model  {

    private $strTitle = "";
    private $strImage = "";
    private $intHits = 0;
    private $strIntro = "";
    private $strText = "";

    private $intDateStart = 0;
    private $intDateEnd = 0;
    private $intDateSpecial = 0;

    private $arrCats = array();

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_news";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _news_modul_id_;
		$arrModul["table"]       		= _dbprefix_."news";
		$arrModul["table2"]       		= _dbprefix_."news_member";
		$arrModul["modul"]				= "news";

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
         $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
	                "._dbprefix_."system, "._dbprefix_."system_date
	                WHERE system_id = news_id
	                  AND system_id = system_date_id
	                  AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
         $arrRow = $this->objDB->getRow($strQuery);
         $this->setStrImage($arrRow["news_image"]);
         $this->setStrIntro($arrRow["news_intro"]);
         $this->setStrNewstext($arrRow["news_text"]);
         $this->setStrTitle($arrRow["news_title"]);
         $this->setIntHits($arrRow["news_hits"]);
         $this->setIntDateEnd($arrRow["system_date_end"]);
         $this->setIntDateStart($arrRow["system_date_start"]);
         $this->setIntDateSpecial($arrRow["system_date_special"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    public function updateObjectToDb($bitMemberships = true, $bitEncodeNewsTitle = false) {
        class_logger::getInstance()->addLogRow("updated news ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        //Update all needed tables
	    //dates
        $this->updateDateRecord($this->getSystemid(), $this->getIntDateStart(), $this->getIntDateEnd(), $this->getIntDateSpecial());
        //news
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET news_title = '".$this->objDB->dbsafeString($this->getStrTitle(), $bitEncodeNewsTitle)."',
                            news_hits = '".$this->objDB->dbsafeString($this->getIntHits())."',
                            news_intro = '".$this->objDB->dbsafeString($this->getStrIntro(), false)."',
                            news_text = '".$this->objDB->dbsafeString($this->getStrNewstext(), false)."',
                            news_image = '".$this->objDB->dbsafeString($this->getStrImage())."'
                       WHERE news_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
        $this->objDB->_query($strQuery);

        //delete all relations
        if($bitMemberships) {
            class_modul_news_category::deleteNewsMemberships($this->getSystemid());
            $arrParams = $this->getAllParams();
            //insert all memberships
            foreach($this->arrCats as $strCatID => $strValue) {
                $strQuery = "INSERT INTO ".$this->arrModule["table2"]."
                            (newsmem_id, newsmem_news, newsmem_category) VALUES
                            ('".$this->objDB->dbsafeString($this->generateSystemid())."', '".$this->objDB->dbsafeString($this->getSystemid())."',
                             '".$this->objDB->dbsafeString($strCatID)."')";
                if(!$this->objDB->_query($strQuery))
                    return false;
            }
        }

        return true;
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    public function saveObjectToDb() {
        //Start wit the system-recods and a tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
        $strNewsId = $this->createSystemRecord($this->getModuleSystemid($this->arrModule["modul"]), "news:".$this->getStrTitle());
        $this->setSystemid($strNewsId);
        class_logger::getInstance()->addLogRow("new news ".$this->getSystemid(), class_logger::$levelInfo);
        //The news-Table
        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                    (news_id, news_title, news_hits) VALUES
                    ('".$this->objDB->dbsafeString($strNewsId)."', '".$this->objDB->dbsafeString($this->getStrTitle())."', 0)";

        //The dates
        $this->createDateRecord($strNewsId, $this->getIntDateStart(), $this->getIntDateEnd(), $this->getIntDateSpecial());

        if($this->objDB->_query($strQuery)) {
            //and all memberships
            foreach($this->arrCats as $strCatID => $strValue) {
                $strQuery = "INSERT INTO ".$this->arrModule["table2"]."
                            (newsmem_id, newsmem_news, newsmem_category) VALUES
                            ('".$this->objDB->dbsafeString($this->generateSystemid())."', '".$this->objDB->dbsafeString($strNewsId)."', '".$this->objDB->dbsafeString($strCatID)."')";
                if(!$this->objDB->_query($strQuery))
                    $bitCommit = false;
            }
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

    /**
	 * Loads all news from the database
	 * if passed, the filter is used to load the news of the given category
	 * If a start and end value is given, just a section of the list is being loaded
	 *
	 * @param string $strFilter
	 * @param int $intStart
	 * @param int $intEnd
	 * @return mixed
	 * @static
	 */
	public static function getNewsList($strFilter = "", $intStart = false, $intEnd = false) {
        $strQuery = "";
		if($strFilter != "") {
			$strQuery = "SELECT system_id
							FROM "._dbprefix_."news,
							      "._dbprefix_."system,
							      "._dbprefix_."system_date,
							      "._dbprefix_."news_member
							WHERE system_id = news_id
							  AND news_id = newsmem_news
							  AND news_id = system_date_id
							  AND newsmem_category = '".dbsafeString($strFilter)."'
							ORDER BY system_date_start DESC";
		}
		else {
			$strQuery = "SELECT system_id
							FROM "._dbprefix_."news,
							      "._dbprefix_."system,
							      "._dbprefix_."system_date
							WHERE system_id = news_id
							  AND system_id = system_date_id
							ORDER BY system_date_start DESC";
		}

		if($intEnd === false && $intStart === false)
		    $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		else
		    $arrIds = class_carrier::getInstance()->getObjDB()->getArraySection($strQuery, $intStart, $intEnd);

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_news_news($arrOneId["system_id"]);

		return $arrReturn;
	}


	/**
	 * Calculates the number of news available for the
	 * given cat or in total
	 *
	 * @param string $strFilter
	 * @return int
	 */
	public function getNewsCount($strFilter = "") {
        $strQuery = "";
        if($strFilter != "") {
			$strQuery = "SELECT COUNT(*)
							FROM "._dbprefix_."news_member
							WHERE newsmem_category = '".dbsafeString($strFilter)."'";
		}
		else {
			$strQuery = "SELECT COUNT(*)
							FROM "._dbprefix_."news";
		}

		$arrRow = $this->objDB->getRow($strQuery);
		return $arrRow["COUNT(*)"];
	}

	/**
	 * Deletes the given news and all relating memberships
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public static function deleteNews($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted news ".$strSystemid, class_logger::$levelInfo);
	    $objRoot = new class_modul_system_common();
	    //Delete memberships
	    if(class_modul_news_category::deleteNewsMemberships($strSystemid)) {
			$strQuery = "DELETE FROM "._dbprefix_."news WHERE news_id = '".dbsafeString($strSystemid)."'";
			if(class_carrier::getInstance()->getObjDB()->_query($strQuery)) {
			    if($objRoot->deleteSystemRecord($strSystemid))
			        return true;
			}
	    }
	    return false;
	}


    /**
     * Counts the number of news displayed for the passed portal-setup
     *
     * @param int $intMode 0 = regular, 1 = archive
	 * @param string $strCat
	 * @return int
	 * @static
     */
    public static function getNewsCountPortal($intMode, $strCat = 0) {
        return count(self::loadListNewsPortal($intMode, $strCat));
    }

	/**
	 * Loads all news from the db assigned to the passed cat
	 *
	 * @param int $intMode 0 = regular, 1 = archive
	 * @param string $strCat
	 * @param int $intOrder 0 = descending, 1 = ascending
     * @param int $intStart
     * @param int $intEnd
	 * @return mixed
	 * @static
	 */
	public static function loadListNewsPortal($intMode, $strCat = 0, $intOrder = 0, $intStart = false, $intEnd = false) {
		$arrReturn = array();
        $strOrder = "";
        $strOneCat = "";
		$intNow = time();
		//Get Timeintervall
		if($intMode == "0") {
			//Regular news
			$strTime  = "AND (system_date_special IS NULL OR (system_date_special > ".(int)$intNow." OR system_date_special = 0))";
		}
		elseif($intMode == "1") {
			//Archivnews
			$strTime = "AND (system_date_special < ".(int)$intNow." AND system_date_special IS NOT NULL AND system_date_special != 0)";
		}
		else
			$strTime = "";
            
		
		
		//check if news should be ordered de- or ascending
		if ($intOrder == 0) {
			$strOrder  = "DESC";
		} else {
			$strOrder  = "ASC";
		}

        if($strCat != "0") {
            $strQuery = "SELECT system_id
                            FROM "._dbprefix_."news,
                                 "._dbprefix_."news_member,
                                 "._dbprefix_."system,
                                 "._dbprefix_."system_date
                            WHERE system_id = news_id
                              AND system_id = system_date_id
                              AND news_id = newsmem_news
                              AND newsmem_category = '".dbsafeString($strCat)."'
                              AND system_status = 1
                              AND (system_date_start IS NULL or(system_date_start < ".(int)$intNow." OR system_date_start = 0))
                                ".$strTime."
                              AND (system_date_end IS NULL or (system_date_end > ".(int)$intNow." OR system_date_end = 0))
                            ORDER BY system_date_start ".$strOrder;
        }
        else {
             $strQuery = "SELECT system_id
                            FROM "._dbprefix_."news,
                                 "._dbprefix_."system,
                                 "._dbprefix_."system_date
                            WHERE system_id = news_id
                              AND system_id = system_date_id
                              AND system_status = 1
                              AND (system_date_start IS NULL or(system_date_start < ".(int)$intNow." OR system_date_start = 0))
                                ".$strTime."
                              AND (system_date_end IS NULL or (system_date_end > ".(int)$intNow." OR system_date_end = 0))
                            ORDER BY system_date_start ".$strOrder;
        }

        if($intStart !== false && $intEnd !== false)
            $arrIds = class_carrier::getInstance()->getObjDB()->getArraySection($strQuery, $intStart, $intEnd);
        else
            $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
            
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_news_news($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Increments the hits counter of the current object
	 *
	 * @return unknown
	 */
	public function increaseHits() {
	    $strQuery = "UPDATE ".$this->arrModule["table"]." SET news_hits = ".($this->getIntHits()+1)." WHERE news_id='".$this->objDB->dbsafeString($this->getSystemid())."'";
		return $this->objDB->_query($strQuery);
	}

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrTitle() {
        return $this->strTitle;
    }
    public function getStrIntro() {
        return $this->strIntro;
    }
    public function getStrNewstext() {
        return $this->strText;
    }
    public function getStrImage() {
        return $this->strImage;
    }
    public function getIntHits() {
        return $this->intHits;
    }
    public function getIntDateStart() {
        return $this->intDateStart;
    }
    public function getIntDateEnd() {
        return $this->intDateEnd;
    }
    public function getIntDateSpecial() {
        return $this->intDateSpecial;
    }
    public function getArrCats() {
        return $this->arrCats;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }
    public function setStrIntro($strIntro) {
        $this->strIntro = $strIntro;
    }
    public function setStrNewstext($strText) {
        $this->strText = $strText;
    }
    public function setStrImage($strImage) {
        $this->strImage = $strImage;
    }
    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }
    public function setIntDateStart($intDateStart) {
        $this->intDateStart = $intDateStart;
    }
    public function setIntDateEnd($intDateEnd) {
        $this->intDateEnd = $intDateEnd;
    }
    public function setIntDateSpecial($intDateSpecial) {
        $this->intDateSpecial = $intDateSpecial;
    }
    public function setArrCats($arrCats) {
        $this->arrCats = $arrCats;
    }
}
?>