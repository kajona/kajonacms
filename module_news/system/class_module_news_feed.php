<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model for a newsfeed itself
 *
 * @package module_news
 * @author sidler@mulchprod.de
 * @targetTable news_feed.news_feed_id
 */
class class_module_news_feed extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn news_feed.news_feed_title
     * @listOrder
     *
     * @fieldType text
     * @fieldMandatory
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn news_feed.news_feed_urltitle
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     */
    private $strUrlTitle = "";

    /**
     * @var string
     * @tableColumn news_feed.news_feed_link
     *
     * @fieldType text
     */
    private $strLink = "";

    /**
     * @var string
     * @tableColumn news_feed.news_feed_desc
     *
     * @fieldType textarea
     */
    private $strDesc = "";

    /**
     * @var string
     * @tableColumn news_feed.news_feed_page
     * @fieldType page
     * @fieldMandatory
     */
    private $strPage = "";

    /**
     * @var string
     * @tableColumn news_feed.news_feed_cat
     *
     * @fieldType dropdown
     */
    private $strCat = "";

    /**
     * @var int
     * @tableColumn news_feed.news_feed_hits
     */
    private $intHits = 0;

    /**
     * @var int
     * @tableColumn news_feed.news_feed_amount
     */
    private $intAmount = 0;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("moduleId", _news_module_id_);
        $this->setArrModuleEntry("modul", "news");

        //base class
        parent::__construct($strSystemid);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_rss.png";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return $this->getIntHits() . " " . $this->getLang("commons_hits_header", "news");
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        if(_system_mod_rewrite_ == "true") {
            return _webpath_ . "/" . $this->getStrUrlTitle() . ".rss";
        }
        else {
            return _webpath_ . "/xml.php?module=news&action=newsFeed&feedTitle=" . $this->getStrUrlTitle();
        }
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrTitle();
    }

    /**
     * Load a newsfeed using a urltitle
     *
     * @param string $strFeedTitle
     *
     * @return class_module_news_feed
     * @static
     */
    public static function getFeedByUrlName($strFeedTitle) {
        $strQuery = "SELECT system_id
	                   FROM " . _dbprefix_ . "news_feed,
	                        " . _dbprefix_ . "system
	                   WHERE news_feed_id = system_id
	                     AND news_feed_urltitle = ? ";
        $arrOneId = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strFeedTitle));
        if(isset($arrOneId["system_id"])) {
            return new class_module_news_feed($arrOneId["system_id"]);
        }
        else {
            return null;
        }
    }


    /**
     * Increments the hits-counter by one
     *
     * @return bool
     */
    public function incrementNewsCounter() {
        $strQuery = "UPDATE " . _dbprefix_ . "news_feed SET news_feed_hits = news_feed_hits+1 WHERE news_feed_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
    }

    /**
     * Loads all news from the database
     * if passed, the filter is used to load the news of the given category
     *
     * @param string $strFilter
     * @param int $intAmount
     *
     * @return mixed
     * @static
     */
    public static function getNewsList($strFilter = "", $intAmount = 0) {
        $intNow = class_date::getCurrentTimestamp();
        $arrParams = array($intNow, $intNow, $intNow);
        if($strFilter != "") {
            $strQuery = "SELECT *
							FROM  " . _dbprefix_ . "news,
							      " . _dbprefix_ . "news_member,
							      " . _dbprefix_ . "system
					    LEFT JOIN " . _dbprefix_ . "system_date
					           ON system_id = system_date_id
							WHERE system_id = news_id
							  AND news_id = newsmem_news
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
							FROM " . _dbprefix_ . "news,
							      " . _dbprefix_ . "system
						LEFT JOIN " . _dbprefix_ . "system_date
					           ON system_id = system_date_id
							WHERE system_id = news_id
							  AND system_status = 1
							  AND (system_date_special IS NULL OR (system_date_special > ? OR system_date_special = 0))
							  AND (system_date_start IS NULL or(system_date_start < ? OR system_date_start = 0))
							  AND (system_date_end IS NULL or (system_date_end > ? OR system_date_end = 0))
							ORDER BY system_date_start DESC";
        }

        $intStart = null;
        $intEnd = null;
        if($intAmount > 0) {
            $intStart = 0;
            $intEnd = $intAmount - 1;
        }

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_news_news($arrOneId["system_id"]);
        }

        return $arrReturn;
    }

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

    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

    public function getIntAmount() {
        return $this->intAmount;
    }

    public function setIntAmount($intAmount) {
        $this->intAmount = $intAmount;
    }

}
