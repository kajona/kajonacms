<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model for a newsfeed itself
 *
 * @package modul_news
 * @author sidler@mulchprod.de
 */
class class_modul_news_feed extends class_model implements interface_model  {

    private $strTitle = "";
    private $strUrlTitle = "";
    private $strLink = "";
    private $strDesc = "";
    private $strPage = "";
    private $strCat = "";
    private $intHits = 0;
    private $intAmount = 0;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_news";
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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."news_feed" => "news_feed_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "news category ".$this->getStrTitle();
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
	                     AND system_id = ? ";
	    $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
	    $this->setStrTitle($arrRow["news_feed_title"]);
	    $this->setStrUrlTitle($arrRow["news_feed_urltitle"]);
	    $this->setStrLink($arrRow["news_feed_link"]);
	    $this->setStrDesc($arrRow["news_feed_desc"]);
	    $this->setStrPage($arrRow["news_feed_page"]);
	    $this->setStrCat($arrRow["news_feed_cat"]);
	    $this->setIntHits($arrRow["news_feed_hits"]);
	    $this->setIntAmount($arrRow["news_feed_amount"]);

    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                   SET news_feed_title = ?,
                       news_feed_urltitle = ?,
                       news_feed_link = ?,
                       news_feed_desc = ?,
                       news_feed_page = ?,
                       news_feed_cat = ?,
                       news_feed_hits = ?,
                       news_feed_amount = ?
                 WHERE news_feed_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getStrTitle(), $this->getStrUrlTitle(), $this->getStrLink(), $this->getStrDesc(),
                                                    $this->getStrPage(), $this->getStrCat(), $this->getIntHits(), $this->getIntAmount(), $this->getSystemid()));
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
	    $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
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
	                     AND news_feed_urltitle = ? ";
	    $arrOneId = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strFeedTitle));
	    if(isset($arrOneId["system_id"]))
		    return new class_modul_news_feed($arrOneId["system_id"]);
		else
		    return null;
	}

	/**
	 * Deletes the given news-feed
	 *
	 * @return bool
	 * @static
	 */
	public function deleteNewsFeed() {
	    class_logger::getInstance()->addLogRow("deleted newsfeed ".$this->getSystemid(), class_logger::$levelInfo);
	    $strQuery = "DELETE FROM "._dbprefix_."news_feed
                             WHERE news_feed_id = ? ";
        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
            if($this->deleteSystemRecord($this->getSystemid()))
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
	    $strQuery = "UPDATE "._dbprefix_."news_feed SET news_feed_hits = news_feed_hits+1 WHERE news_feed_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
	}

	/**
	 * Loads all news from the database
	 * if passed, the filter is used to load the news of the given category
	 *
	 * @param string $strFilter
     * @param int $intAmount
	 * @return mixed
	 * @static
	 */
	public static function getNewsList($strFilter = "", $intAmount = 0) {
        $strQuery = "";
	    $intNow = class_date::getCurrentTimestamp();
        $arrParams = array($intNow, $intNow, $intNow);
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
							  AND (system_date_special IS NULL OR (system_date_special > ? OR system_date_special = 0))
							  AND (system_date_start IS NULL or(system_date_start < ? OR system_date_start = 0))
							  AND (system_date_end IS NULL or (system_date_end > ? OR system_date_end = 0))
							  AND newsmem_category = ?
							ORDER BY system_date_start DESC";
            $arrParams[] = $strFilter;
		}
		else {
			$strQuery = "SELECT *
							FROM "._dbprefix_."news,
							      "._dbprefix_."system,
							      "._dbprefix_."system_date
							WHERE system_id = news_id
							  AND system_id = system_date_id
							  AND system_status = 1
							  AND (system_date_special IS NULL OR (system_date_special > ? OR system_date_special = 0))
							  AND (system_date_start IS NULL or(system_date_start < ? OR system_date_start = 0))
							  AND (system_date_end IS NULL or (system_date_end > ? OR system_date_end = 0))
							ORDER BY system_date_start DESC";
		}

        if($intAmount > 0)
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, $arrParams, 0, $intAmount-1);
        else
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

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

    public function getIntAmount() {
        return $this->intAmount;
    }

    public function setIntAmount($intAmount) {
        $this->intAmount = $intAmount;
    }

    
}
?>