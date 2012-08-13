<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/

/**
 * Model for rating itself
 *
 * @package module_rating
 * @author sidler@mulchprod.de
 *
 * @targetTable rating.rating_id
 */
class class_module_rating_rate extends class_model implements interface_model, interface_recorddeleted_listener  {

    const RATING_COOKIE = "kj_ratingHistory";

    /**
     * @var string
     * @tableColumn rating_systemid
     */
    private $strRatingSystemid = "";

    /**
     * @var string
     * @tableColumn rating_checksum
     */
    private $strRatingChecksum = "";

    /**
     * @var float
     * @tableColumn rating_rate
     */
    private $floatRating = 0.0;

    /**
     * @var int
     * @tableColumn rating_hits
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
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "rating");
        $this->setArrModuleEntry("moduleId", _rating_modul_id_);

		parent::__construct($strSystemid);
    }


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return"rating for ".$this->getStrRatingSystemid();
    }


    /**
     * Adds a rating-value to the record saved in the db
     *
     * @param float $floatRating
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
        $this->setIntHits($this->getIntHits()+1);

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

	    	$arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid(), $this->objSession->getUserID() ));
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
     * @static
     * @return class_module_rating_rate
     */
    public static function getRating($strSystemid, $strChecksum = "") {
        $arrParams = array($strSystemid);
        if($strChecksum != "")
            $arrParams[] = $strChecksum;

    	$strQuery = "SELECT rating_id
                     FROM "._dbprefix_."rating
                     WHERE rating_systemid = ?
                     ".($strChecksum != "" ? " AND rating_checksum = ? " : "" )."";
    	$arrMatches = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);

        if(isset($arrMatches["rating_id"]))
            return new class_module_rating_rate($arrMatches["rating_id"]);
        else
            return null;

    }


    /**
     * Searches for ratings belonging to the systemid
     * to be deleted.
     * Overwrites class_model::doAdditionalCleanupsOnDeletion($strSystemid)
     *
     * @param string $strSystemid
     * @return bool
     * @overwrites
     *
     */
    public function handleRecordDeletedEvent($strSystemid) {
        $bitReturn = true;

        //ratings installed as a module?
        if(class_module_system_module::getModuleByName("rating") == null)
            return true;

        //check that systemid isn't the id of a rating to avoid recursions
        $objRecord = class_objectfactory::getInstance()->getObject($strSystemid);
        if($objRecord != null && $objRecord->getIntModuleNr() == _rating_modul_id_)
            return true;

        //ok, so delete matching records
        //fetch the matching ids..
        //TODO: could be changed to a direct deletion instead of a select & delete combination. a single delete could be faster.
        $strQuery = "SELECT rating_id
                     FROM "._dbprefix_."rating"."
                     WHERE rating_systemid = ? ";
        $arrRows = $this->objDB->getPArray($strQuery, array($strSystemid));

        if(count($arrRows) > 0) {
        	foreach ($arrRows as $arrOneRow) {
        		$strQuery = "DELETE FROM "._dbprefix_."rating"." WHERE rating_id= ?";
        		$bitReturn = $bitReturn && $this->objDB->_pQuery($strQuery, array($arrOneRow["rating_id"]));
        		$bitReturn = $bitReturn && $this->deleteSystemRecord($arrOneRow["rating_id"]);

        		//delete the entries from the history-table
        		$strQuery = "DELETE FROM "._dbprefix_."rating_history"." WHERE rating_history_rating=? ";
        		$bitReturn = $bitReturn && $this->objDB->_pQuery($strQuery, array($arrOneRow["rating_id"]));
        	}
        }

        return $bitReturn;
    }


    /**
     * Fetches the rating-history of the current rating from the database.
     * This is an array containing the fields:
     *    rating_history_id --> used internally
     *    rating_history_rating --> the current rating-systemid
     *    rating_history_user --> the systemid if the user who rated or '' in case of a guest
     *    rating_history_timestamp --> timestamp of the rating
     *    rating_history_value --> the value the user rated the record
     * @return array
     */
    public function getRatingHistoryAsArray() {
    	$strQuery = "SELECT * FROM ".$this->objDB->encloseTableName(_dbprefix_."rating_history")."
    	             WHERE ".$this->objDB->encloseColumnName("rating_history_rating")." = ?
    	             ORDER BY ".$this->objDB->encloseColumnName("rating_history_timestamp")." ASC";

    	return $this->objDB->getPArray($strQuery, array($this->getSystemid()));
    }



    public function getStrRatingSystemid() {
    	return $this->strRatingSystemid;
    }

    public function getStrRatingChecksum() {
    	return $this->strRatingChecksum;
    }

    public function getFloatRating($bitRound = true) {
        if($this->floatRating == "")
            return 0.0;

    	return $this->floatRating;
    }

    public function getIntHits() {
        if($this->intHits == "")
            return 0;

    	return $this->intHits;
    }


    public function setStrRatingSystemid($strRatingSystemid) {
        $this->strRatingSystemid = $strRatingSystemid;
    }

    public function setStrRatingChecksum($strRatingChecksum) {
        $this->strRatingChecksum = $strRatingChecksum;
    }

    public function setFloatRating($floatRating) {
        if($floatRating > class_module_rating_rate::$intMaxRatingValue) {
            $floatRating = class_module_rating_rate::$intMaxRatingValue;
        }
        if($floatRating < 0)
            $floatRating = 0;

        $this->floatRating = $floatRating;
    }

    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

}
