<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/

/**
 * Model for rating itself
 *
 * @package modul_rating
 */
class class_modul_rating_rate extends class_model implements interface_model  {

    private $strRatingSystemid;
    private $strRatingChecksum;
    private $floatRating = 0.0;
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
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_rating";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _rating_modul_id_;
		$arrModul["table"]       		= _dbprefix_."rating";
		$arrModul["table2"]             = _dbprefix_."rating_history";
		$arrModul["modul"]				= "rating";

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
        return array(_dbprefix_."rating" => "rating_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "rating for ".$this->getStrRatingSystemid();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT *
		   			 FROM ".$this->arrModule["table"]."
					 WHERE rating_id = '".$this->getSystemid()."'";

        $arrRow = $this->objDB->getRow($strQuery);

        $this->setStrRatingSystemid($arrRow["rating_systemid"]);
        $this->setStrRatingChecksum($arrRow["rating_checksum"]);
        $this->setFloatRating($arrRow["rating_rate"]);
        $this->setIntHits($arrRow["rating_hits"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {

        $strQuery = "UPDATE ".$this->arrModule["table"]." SET
                    	rating_systemid		= '".dbsafeString($this->getStrRatingSystemid())."',
                    	rating_checksum		= '".dbsafeString($this->getStrRatingChecksum())."',
						rating_rate	        = '".dbsafeString($this->getFloatRating())."',
                    	rating_hits         = ".dbsafeString($this->getIntHits())."
					WHERE rating_id         = '".dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }


    /**
     * Adds a rating-value to the record saved in the db
     *
     * @param float $floatRating
     * @return bool
     */
    public function saveRating($floatRating) {
    	if($floatRating < 0 || !$this->isRateableByCurrentUser() || $floatRating > class_modul_rating_rate::$intMaxRatingValue)
    	   return false;

    	$floatRatingOriginal = $floatRating;

    	$objRatingAlgo = new class_modul_rating_algo_gaussian();
    	$floatRating = $objRatingAlgo->doRating($this, $floatRating);

        class_logger::getInstance()->addLogRow("updated rating of record ".$this->getSystemid().", added ".$floatRating, class_logger::$levelInfo);

        //update the values to remain consistent
        $this->setFloatRating($floatRating);
        $this->setIntHits($this->getIntHits()+1);

        //save a hint in the history table
        //if($this->objSession->getUserID() != "") {
        	$strInsert = "INSERT INTO ".$this->objDB->encloseTableName($this->arrModule["table2"])."
        	              (rating_history_id, rating_history_rating, rating_history_user, rating_history_timestamp, rating_history_value) VALUES
        	              ('".dbsafeString(generateSystemid())."', '".dbsafeString($this->getSystemid())."', '".dbsafeString($this->objSession->getUserID())."',
        	               '".(int)time()."', '".dbsafeString($floatRatingOriginal)."')";
        	$this->objDB->_query($strInsert);
        //}

        //and save it in a cookie
        $objCookie = new class_cookie();
        $objCookie->setCookie("kj_ratingHistory", getCookie("kj_ratingHistory").$this->getSystemid().",");

        //flush the page-cache to have all pages rerendered using the correct values
        $this->flushCompletePagesCache();
        
        return $this->updateStateToDb();

    }

    /**
     * Checks, if the record is already rated by the current user to avoid double-ratings
     *
     * @param $intReason
     * @return boolean
     */
    public function isRateableByCurrentUser() {
    	$bitReturn = true;

    	//sql-check - only if user is not a guest
    	$arrRow = array();
    	$arrRow["COUNT(*)"] = 0;

    	if($this->objSession->getUserID() != "") {
	    	$strQuery = "SELECT COUNT(*) FROM ".$this->objDB->encloseTableName($this->arrModule["table2"])."
	    	               WHERE rating_history_rating = '".dbsafeString($this->getSystemid())."'
	    	                 AND rating_history_user = '".dbsafeString($this->objSession->getUserID())."'";

	    	$arrRow = $this->objDB->getRow($strQuery);
    	}

    	if($arrRow["COUNT(*)"] == 0) {
    		//cookie available?
    		if(getCookie("kj_ratingHistory") != "") {
    			$strRatingCookie = getCookie("kj_ratingHistory");
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
     * Loads a single rating for a given sysid, if needed concreted by a checksum.
     * If no rating is found, null is being returned.
     *
     * @param string $strSystemid
     * @param string $strChecksum
     * @static
     * @return class_modul_rating_rate
     */
    public static function getRating($strSystemid, $strChecksum = "") {
    	$strQuery = "SELECT rating_id
                     FROM "._dbprefix_."rating
                     WHERE rating_systemid = '".dbsafeString($strSystemid)."'
                     ".($strChecksum != "" ? " AND rating_checksum = '".dbsafeString($strChecksum)."'" : "" )."";
    	$arrMatches = class_carrier::getInstance()->getObjDB()->getRow($strQuery);

    	if(isset($arrMatches["rating_id"]))
    		return new class_modul_rating_rate($arrMatches["rating_id"]);
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
    public function doAdditionalCleanupsOnDeletion($strSystemid) {
        $bitReturn = true;

        //ratings installed as a module?
        if(class_modul_system_module::getModuleByName("rating") == null)
            return true;

        //check that systemid isn't the id of a rating to avoid recursions
        $arrRecordModulId = $this->getSystemRecord($strSystemid);
        if(isset($arrRecordModulId["system_modul_nr"]) && $arrRecordModulId["system_module_nr"] == _rating_modul_id_)
            return true;

        //ok, so delete matching records
        //fetch the matching ids..
        $strQuery = "SELECT rating_id
                     FROM ".$this->arrModule["table"]."
                     WHERE rating_systemid = '".dbsafeString($strSystemid)."'";
        $arrRows = $this->objDB->getArray($strQuery);

        if(count($arrRows) > 0) {
        	foreach ($arrRows as $arrOneRow) {
        		$strQuery = "DELETE FROM ".$this->arrModule["table"]." WHERE rating_id='".dbsafeString($arrOneRow["rating_id"])."'";
        		$bitReturn &= $this->objDB->_query($strQuery);
        		$bitReturn &= $this->deleteSystemRecord($arrOneRow["rating_id"]);

        		//delete the entries from the history-table
        		$strQuery = "DELETE FROM ".$this->arrModule["table2"]." WHERE rating_history_rating='".dbsafeString($arrOneRow["rating_id"])."'";
        		$bitReturn &= $this->objDB->_query($strQuery);
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
    	$strQuery = "SELECT * FROM ".$this->objDB->encloseTableName($this->arrModule["table2"])."
    	             WHERE ".$this->objDB->encloseColumnName("rating_history_rating")." = '".dbsafeString($this->getSystemid())."'
    	             ORDER BY ".$this->objDB->encloseColumnName("rating_history_timestamp")." ASC";

    	return $this->objDB->getArray($strQuery);
    }


// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrRatingSystemid() {
    	return $this->strRatingSystemid;
    }

    public function getStrRatingChecksum() {
    	return $this->strRatingChecksum;
    }

    public function getFloatRating() {
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
        if($floatRating > class_modul_rating_rate::$intMaxRatingValue) {
            $floatRating = class_modul_rating_rate::$intMaxRatingValue;
        }
        if($floatRating < 0)
            $floatRating = 0;
        $this->floatRating = $floatRating;
    }

    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

}
?>