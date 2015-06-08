<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Model for rating itself
 *
 * @package module_rating
 * @author sidler@mulchprod.de
 * @targetTable rating.rating_id
 *
 * @module rating
 * @moduleId _rating_modul_id_
 */
class class_module_rating_rate extends class_model implements interface_model {

    const RATING_COOKIE = "kj_ratingHistory";

    /**
     * @var string
     * @tableColumn rating.rating_systemid
     * @tableColumnDatatype char20
     */
    private $strRatingSystemid = "";

    /**
     * @var string
     * @tableColumn rating.rating_checksum
     * @tableColumnDatatype char254
     */
    private $strRatingChecksum = "";

    /**
     * @var float
     * @tableColumn rating.rating_rate
     * @tableColumnDatatype double
     */
    private $floatRating = 0.0;

    /**
     * @var int
     * @tableColumn rating.rating_hits
     * @tableColumnDatatype int
     */
    private $intHits = 0;

    /**
     * The max value an object can be rated. Depending on the portal classes, this value
     * may also define the number of "rating-stars" which will be shown in the rating bar.
     *
     * @var int
     */
    public static $intMaxRatingValue = 5;


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return "rating for ".$this->getStrRatingSystemid();
    }


    /**
     * Adds a rating-value to the record saved in the db
     *
     * @param float $floatRating
     *
     * @return bool
     */
    public function saveRating($floatRating) {
        if($floatRating < 0 || !$this->isRateableByCurrentUser() || $floatRating > class_module_rating_rate::$intMaxRatingValue)
            return false;

        $floatRatingOriginal = $floatRating;

        $objRatingAlgo = new class_module_rating_algo_gaussian();
        $floatRating = $objRatingAlgo->doRating($this, $floatRating);

        class_logger::getInstance()->addLogRow("updated rating of record ".$this->getSystemid().", added ".$floatRating, class_logger::$levelInfo);

        //update the values to remain consistent
        $this->setFloatRating($floatRating);
        $this->setIntHits($this->getIntHits() + 1);

        //save a hint in the history table
        //if($this->objSession->getUserID() != "") {
        $strInsert = "INSERT INTO ".$this->objDB->encloseTableName(_dbprefix_."rating_history")."
        	              (rating_history_id, rating_history_rating, rating_history_user, rating_history_timestamp, rating_history_value) VALUES
        	              (?, ?, ?, ?, ?)";
        $this->objDB->_pQuery($strInsert, array(generateSystemid(), $this->getSystemid(), $this->objSession->getUserID(), (int)time(), $floatRatingOriginal));
        //}

        //and save it in a cookie
        $objCookie = new class_cookie();
        $objCookie->setCookie(class_module_rating_rate::RATING_COOKIE, getCookie(class_module_rating_rate::RATING_COOKIE).$this->getSystemid().",");

        //flush the page-cache to have all pages rendered using the correct values
        $this->flushCompletePagesCache();

        return true;
    }

    /**
     * Checks, if the record is already rated by the current user to avoid double-ratings
     *
     * @return bool
     */
    public function isRateableByCurrentUser() {
        $bitReturn = true;

        //sql-check - only if user is not a guest
        $arrRow = array();
        $arrRow["COUNT(*)"] = 0;

        if($this->objSession->getUserID() != "") {
            $strQuery = "SELECT COUNT(*) FROM ".$this->objDB->encloseTableName(_dbprefix_."rating_history")."
	    	               WHERE rating_history_rating = ?
	    	                 AND rating_history_user = ?";

            $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid(), $this->objSession->getUserID()));
        }

        if($arrRow["COUNT(*)"] == 0) {
            //cookie available?
            $objCookie = new class_cookie();
            if($objCookie->getCookie(class_module_rating_rate::RATING_COOKIE) != "") {
                $strRatingCookie = $objCookie->getCookie(class_module_rating_rate::RATING_COOKIE);
                if(uniStrpos($strRatingCookie, $this->getSystemid()) !== false) {
                    $bitReturn = false;
                }
            }
        }
        else
            $bitReturn = false;

        return $bitReturn;
    }

    /**
     * Loads a single rating for a given systemid, if needed concreted by a checksum.
     * If no rating is found, null is being returned.
     *
     * @param string $strSystemid
     * @param string $strChecksum
     *
     * @static
     * @return class_module_rating_rate
     */
    public static function getRating($strSystemid, $strChecksum = "") {
        $objORM = new class_orm_objectlist();
        $objORM->addWhereRestriction(new class_orm_objectlist_restriction("AND rating_systemid = ?"), $strSystemid);
        if($strChecksum != "")
            $objORM->addWhereRestriction(new class_orm_objectlist_restriction("AND rating_checksum = ?"), $strChecksum);

        return $objORM->getObjectCount(get_called_class());
    }

    /**
     * Fetches the rating-history of the current rating from the database.
     * This is an array containing the fields:
     *    rating_history_id --> used internally
     *    rating_history_rating --> the current rating-systemid
     *    rating_history_user --> the systemid if the user who rated or '' in case of a guest
     *    rating_history_timestamp --> timestamp of the rating
     *    rating_history_value --> the value the user rated the record
     *
     * @return array
     */
    public function getRatingHistoryAsArray() {
        $strQuery = "SELECT * FROM ".$this->objDB->encloseTableName(_dbprefix_."rating_history")."
    	             WHERE ".$this->objDB->encloseColumnName("rating_history_rating")." = ?
    	             ORDER BY ".$this->objDB->encloseColumnName("rating_history_timestamp")." ASC";

        return $this->objDB->getPArray($strQuery, array($this->getSystemid()));
    }


    /**
     * @return string
     */
    public function getStrRatingSystemid() {
        return $this->strRatingSystemid;
    }

    /**
     * @return string
     */
    public function getStrRatingChecksum() {
        return $this->strRatingChecksum;
    }

    /**
     * @param bool $bitRound
     *
     * @return float
     */
    public function getFloatRating($bitRound = true) {
        if($this->floatRating == "")
            return 0.0;

        return $this->floatRating;
    }

    /**
     * @return int
     */
    public function getIntHits() {
        if($this->intHits == "")
            return 0;

        return $this->intHits;
    }


    /**
     * @param string $strRatingSystemid
     * @return void
     */
    public function setStrRatingSystemid($strRatingSystemid) {
        $this->strRatingSystemid = $strRatingSystemid;
    }

    /**
     * @param string $strRatingChecksum
     * @return void
     */
    public function setStrRatingChecksum($strRatingChecksum) {
        $this->strRatingChecksum = $strRatingChecksum;
    }

    /**
     * @param float $floatRating
     * @return void
     */
    public function setFloatRating($floatRating) {
        if($floatRating > class_module_rating_rate::$intMaxRatingValue) {
            $floatRating = class_module_rating_rate::$intMaxRatingValue;
        }
        if($floatRating < 0)
            $floatRating = 0;

        $this->floatRating = $floatRating;
    }

    /**
     * @param int $intHits
     * @return void
     */
    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

}
