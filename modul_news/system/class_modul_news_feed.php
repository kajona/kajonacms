<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_news_feed.php                                                                           *
* 	Model for the news feed                                                                             *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Model for a newsfeed itself
 *
 * @package modul_news
 */
class class_modul_news_feed extends class_model implements interface_model  {

    private $strTitle = "";
    private $strUrlTitle = "";
    private $strLink = "";
    private $strDesc = "";
    private $strPage = "";
    private $strCat = "";
    private $intHits = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_news";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _news_modul_id_;
		$arrModul["table"]       		= _dbprefix_."news_feed";
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
        $strQuery = "SELECT *
	                   FROM ".$this->arrModule["table"].",
	                        "._dbprefix_."system
	                   WHERE news_feed_id = system_id
	                     AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
	    $arrRow = $this->objDB->getRow($strQuery);
	    $this->setStrTitle($arrRow["news_feed_title"]);
	    $this->setStrUrlTitle($arrRow["news_feed_urltitle"]);
	    $this->setStrLink($arrRow["news_feed_link"]);
	    $this->setStrDesc($arrRow["news_feed_desc"]);
	    $this->setStrPage($arrRow["news_feed_page"]);
	    $this->setStrCat($arrRow["news_feed_cat"]);
	    $this->setIntHits($arrRow["news_feed_hits"]);

    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated newsfeed ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                   SET news_feed_title = '".$this->objDB->dbsafeString($this->getStrTitle())."',
                       news_feed_urltitle = '".$this->objDB->dbsafeString($this->getStrUrlTitle())."',
                       news_feed_link = '".$this->objDB->dbsafeString($this->getStrLink())."',
                       news_feed_desc = '".$this->objDB->dbsafeString($this->getStrDesc())."',
                       news_feed_page = '".$this->objDB->dbsafeString($this->getStrPage())."',
                       news_feed_cat = '".$this->objDB->dbsafeString($this->getStrCat())."'
                 WHERE news_feed_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    public function saveObjectToDb() {
        $this->objDB->transactionBegin();

        $strNewsID = $this->createSystemRecord($this->getModuleSystemid($this->arrModule["modul"]), "Feed: ".$this->strTitle);
        $this->setSystemid($strNewsID);
        class_logger::getInstance()->addLogRow("new newsfeed ".$this->getSystemid(), class_logger::$levelInfo);
        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                    (news_feed_id, news_feed_title, news_feed_urltitle, news_feed_link, news_feed_desc, news_feed_page, news_feed_cat, news_feed_hits) VALUES
                    ('".$this->objDB->dbsafeString($strNewsID)."', '".$this->objDB->dbsafeString($this->getStrTitle())."', '".$this->objDB->dbsafeString($this->getStrUrlTitle())."', '".$this->objDB->dbsafeString($this->getStrLink())."',
                     '".$this->objDB->dbsafeString($this->getStrDesc())."', '".$this->objDB->dbsafeString($this->getStrPage())."',
                     '".$this->objDB->dbsafeString($this->getStrCat())."', 0 )";

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
	 * Loads all newsfeeds
	 *
	 * @return mixed
	 * @static
	 */
	public static function getAllFeeds() {
	    $strQuery = "SELECT system_id
	                   FROM "._dbprefix_."news_feed,
	                        "._dbprefix_."system
	                   WHERE news_feed_id = system_id";
	    $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_news_feed($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Load a newsfeed using a urltitle
	 *
	 * @param string $strFeedTitle
	 * @return class_modul_news_feed
	 * @static
	 */
	public static function getFeedByUrlName($strFeedTitle) {
	    $strQuery = "SELECT system_id
	                   FROM "._dbprefix_."news_feed,
	                        "._dbprefix_."system
	                   WHERE news_feed_id = system_id
	                     AND news_feed_urltitle = '".dbsafeString($strFeedTitle)."'";
	    $arrOneId = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
	    if(isset($arrOneId["system_id"]))
		    return new class_modul_news_feed($arrOneId["system_id"]);
		else
		    return null;
	}

	/**
	 * Deletes the given news-feed
	 *
	 * @param string $strSystemid
	 * @return bool
	 * @static
	 */
	public static function deleteNewsFeed($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted newsfeed ".$strSystemid, class_logger::$levelInfo);
	    $objRoot = new class_modul_system_common();
	    $strQuery = "DELETE FROM "._dbprefix_."news_feed
                             WHERE news_feed_id = '".dbsafeString($strSystemid)."'";
        if(class_carrier::getInstance()->getObjDB()->_query($strQuery)) {
            if($objRoot->deleteSystemRecord($strSystemid))
                return true;
        }
        return false;
	}

	/**
	 * Increments the hits-counter by one
	 *
	 * @return bool
	 */
	public function incrementNewsCounter() {
	    $strQuery = "UPDATE "._dbprefix_."news_feed SET news_feed_hits = news_feed_hits+1 WHERE news_feed_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
	}

	/**
	 * Loads all news from the database
	 * if passed, the filter is used to load the news of the given category
	 *
	 * @param string $strFilter
	 * @return mixed
	 * @static
	 */
	public static function getNewsList($strFilter = "") {
	    $intNow = time();
		if($strFilter != "") {
			$strQuery = "SELECT *
							FROM  "._dbprefix_."news,
							      "._dbprefix_."system,
							      "._dbprefix_."system_date,
							      "._dbprefix_."news_member
							WHERE system_id = news_id
							  AND news_id = newsmem_news
							  AND news_id = system_date_id
							  AND system_status = 1
							  AND (system_date_special IS NULL OR (system_date_special > ".(int)$intNow." OR system_date_special = 0))
							  AND (system_date_start IS NULL or(system_date_start < ".(int)$intNow." OR system_date_start = 0))
							  AND (system_date_end IS NULL or (system_date_end > ".(int)$intNow." OR system_date_end = 0))
							  AND newsmem_category = '".dbsafeString($strFilter)."'
							ORDER BY system_date_start DESC";
		}
		else {
			$strQuery = "SELECT *
							FROM "._dbprefix_."news,
							      "._dbprefix_."system,
							      "._dbprefix_."system_date
							WHERE system_id = news_id
							  AND system_id = system_date_id
							  AND system_status = 1
							  AND (system_date_special IS NULL OR (system_date_special > ".(int)$intNow." OR system_date_special = 0))
							  AND (system_date_start IS NULL or(system_date_start < ".(int)$intNow." OR system_date_start = 0))
							  AND (system_date_end IS NULL or (system_date_end > ".(int)$intNow." OR system_date_end = 0))
							ORDER BY system_date_start DESC";
		}

		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_news_news($arrOneId["system_id"]);

		return $arrReturn;
	}

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrTitle() {
        return $this->strTitle;
    }
    public function getStrUrlTitle() {
        return $this->strUrlTitle;
    }
    public function getStrLink() {
        return $this->strLink;
    }
    public function getStrDesc() {
        return $this->strDesc;
    }
    public function getStrPage() {
        return $this->strPage;
    }
    public function getStrCat() {
        return $this->strCat;
    }
    public function getIntHits() {
        return $this->intHits;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }
    public function setStrUrlTitle($strUrlTitle) {
        $this->strUrlTitle = $strUrlTitle;
    }
    public function setStrLink($strLink) {
        $this->strLink = $strLink;
    }
    public function setStrDesc($strDesc) {
        $this->strDesc = $strDesc;
    }
    public function setStrPage($strPage) {
        $this->strPage = $strPage;
    }
    public function setStrCat($strCat) {
        $this->strCat = $strCat;
    }
    private function setIntHits($intHits) {
        $this->intHits = $intHits;
    }
}
?>