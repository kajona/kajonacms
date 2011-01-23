<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                               *
********************************************************************************************************/

/**
 * Top-level class for all model-classes
 *
 * @package modul_system
 */
class class_model extends class_root {

    public function __construct($arrModule, $strSystemid)  {

        parent::__construct($arrModule, $strSystemid, "model");
    }

   /**
     * Forces to reinit the object from the database
     *
     */
    public function loadDataFromDb() {
        $this->initObject();
    }


// --- DATABASE-SYNCHRONIZATION -------------------------------------------------------------------------

    /**
     * Saves the current object to the database. Determins, whether the current object has to be inserted
     * or updated to the database.
     * In case of an update, the objects' updateStateToDb() method is being called (as required by class_model).
     * In the case of a new object, a blank record is being created. Thererfore, all tables returned by the
     * objects' getObjectTables() method will be filled with a new record (using the same new systemid as the
     * primary key). The newly created systemid is being set as the current objects' one and can be used in the afterwards
     * called updateStateToDb() method to reference the correct rows.
     *
     * @param string $strPrevId The prev-id of the records, either to be used for the insert or to be used during the update of the record
     * @param string $strComment Comment to describe the record in the systemtable
     * @return bool
     * @since 3.3.0
     * @throws class_exception
     * @todo will become final before 3.3.0, please update your implementations of interface_model
     * @see interface_model
     */
    public function updateObjectToDb($strPrevId = false) {
        $bitCommit = true;

        $this->objDB->transactionBegin();

        //current systemid given? if not, create a new record.
        if(!validateSystemid($this->getSystemid())) {

            if($strPrevId == false) {
                //try to find the current modules-one
                if(isset($this->arrModule["modul"])) {
                    $strPrevId = $this->getModuleSystemid($this->arrModule["modul"]);
                    if(!validateSystemid($strPrevId))
                        throw new class_exception("automatic determination of module-id failed ", class_exception::$level_FATALERROR);
                }
                else
                    throw new class_exception("insert with no previd ", class_exception::$level_FATALERROR);
            }

            //create the new systemrecord
            $strNewSystemid = $this->createSystemRecord($strPrevId, $this->getObjectDescription());

            if(validateSystemid($strNewSystemid)) {
                $this->setSystemid($strNewSystemid);

                //Create the foreign records
                $arrTables = $this->getObjectTables();
                if(is_array($arrTables)) {
                    foreach($arrTables as $strTable => $strColumn) {
                        $strQuery = "INSERT INTO ".$this->objDB->encloseTableName($strTable)."
                                                (".$this->objDB->encloseColumnName($strColumn).") VALUES
                                                ('".dbsafeString($strNewSystemid)."') ";

                        if(!$this->objDB->_query($strQuery))
                            $bitCommit = false;

                    }
                }

                if(!$this->onInsertToDb())
                    $bitCommit = false;

            }
            else
                throw new class_exception("creation of systemrecord failed", class_exception::$level_FATALERROR);

            //all updates are done, start the "real" update
            $this->objDB->flushQueryCache();
        }

        //update ourself to the database
        if(!$this->updateStateToDb())
            $bitCommit = false;

        //do work to be done afterwards
        $this->setLastEditUser();
        $this->setEditDate();

        //new prev-id?
        if($strPrevId !== false && $this->getPrevId() != $strPrevId && $this->getSystemid() != $strPrevId && (validateSystemid($strPrevId) || $strPrevId = "0"))
            if(!$this->setPrevId($strPrevId))
                $bitCommit = false;

        //new comment?
        if($this->getRecordComment() != $this->getObjectDescription())
            if(!$this->setRecordComment($this->getObjectDescription()))
                $bitCommit = false;

        if($bitCommit) {
            $this->objDB->transactionCommit();
            $bitReturn = true;
            class_logger::getInstance()->addLogRow("updateObjectToDb() succeeded for systemid ".$this->getSystemid()." (".$this->getRecordComment().")", class_logger::$levelInfo);
        }
        else {
            $this->objDB->transactionRollback();
            $bitReturn = false;
            class_logger::getInstance()->addLogRow("updateObjectToDb() failed for systemid ".$this->getSystemid()." (".$this->getRecordComment().")", class_logger::$levelWarning);
        }


        return $bitReturn;
    }

    /**
     * Overwrite this method if you want to trigger additional commands during the insert
     * of an object, e.g. to create additional objects / relations
     *
     * @return bool
     */
    protected function onInsertToDb() {
        return true;
    }

    /**
     * Updates the current object to the database.
     * Use this method in order to synchronize the objects' internal state
     * to the database.
     *
     * @return bool
     * @abstract
     * @todo will become abstract before 3.3.0, please update your implementations of interface_model
     */
    protected function updateStateToDb() {}

    /**
     * Returns the tables being used to store the current objects' state.
     * The array should contain the name of the table as the key and the name
     * of the primary-key (so the column name) as the matching value.
     * E.g.: array(_dbprefix_."pages" => "page_id)
     *
     * @return array tablename => table-primary-key-column
     * @abstract
     * @todo will become abstract before 3.3.0, please update your implementations of interface_model
     */
    protected function getObjectTables() {}

    /**
     * Returns a human-readable description of the current record.
     * To be used within the system-table as a comment
     *
     * @return string
     * @abstract
     * @todo will become abstract before 3.3.0, please update your implementations of interface_model
     */
    protected function getObjectDescription() {}

// --- RIGHTS-METHODS -----------------------------------------------------------------------------------

    /**
     * Returns the bool-value for the right to view this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     * @static
     * @final
     */
    public final function rightView() {
        if($this->getSystemid() != "") {
            return $this->objRights->rightView($this->getSystemid());
        }
        return false;
    }

    /**
     * Returns the bool-value for the right to edit this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     * @static
     * @final
     */
    public final function rightEdit() {
        if($this->getSystemid() != "") {
            return $this->objRights->rightEdit($this->getSystemid());
        }
        return false;
    }

    /**
     * Returns the bool-value for the right to delete this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     * @static
     * @final
     */
    public final function rightDelete() {
        if($this->getSystemid() != "") {
            return $this->objRights->rightDelete($this->getSystemid());
        }
        return false;
    }

    /**
     * Returns the bool-value for the right to change rights of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     * @static
     * @final
     */
    public final function rightRight() {
        if($this->getSystemid() != "") {
            return $this->objRights->rightRight($this->getSystemid());
        }
        return false;
    }

    /**
     * Returns the bool-value for the right1 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     * @static
     * @final
     */
    public final function rightRight1() {
        if($this->getSystemid() != "") {
            return $this->objRights->rightRight1($this->getSystemid());
        }
        return false;
    }

    /**
     * Returns the bool-value for the right2 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     * @static
     * @final
     */
    public final function rightRight2() {
        if($this->getSystemid() != "") {
            return $this->objRights->rightRight2($this->getSystemid());
        }
        return false;
    }

    /**
     * Returns the bool-value for the right3 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     * @static
     * @final
     */
    public final function rightRight3() {
        if($this->getSystemid() != "") {
            return $this->objRights->rightRight3($this->getSystemid());
        }
        return false;
    }

    /**
     * Returns the bool-value for the right4 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     * @static
     * @final
     */
    public final function rightRight4() {
        if($this->getSystemid() != "") {
            return $this->objRights->rightRight4($this->getSystemid());
        }
        return false;
    }

    /**
     * Returns the bool-value for the right5 of this record,
     * Systemid MUST be given, otherwise false
     *
     * @return bool
     * @static
     * @final
     */
    public final function rightRight5() {
        if($this->getSystemid() != "") {
            return $this->objRights->rightRight5($this->getSystemid());
        }
        return false;
    }

// --- MISC ---------------------------------------------------------------------------------------------

    /**
	 * Overwrite this method, if an object should be notified in case of deleting a systemrecord.
	 * This can be useful to delete other records being dependent on the record to be deleted
	 */
    public function doAdditionalCleanupsOnDeletion($strSystemid) {
        return true;
	}


    /**
	 * Overwrite this method, if an object should be notified in case of changing the status of a systemrecord.
	 * This can be useful to trigger workflows or other events.
	 */
    public function doAdditionalActionsOnStatusChange($strSystemid) {
        return true;
	}

// --- RATING -------------------------------------------------------------------------------------------

    /**
     * Rating of the current file, if module rating is installed.
     *
     * @param $bitRound Rounds the rating or disables rounding
     * @see interface_sortable_rating
     * @return float
     */
    public function getFloatRating($bitRound = true) {
        $floatRating = null;
        $objModule = class_modul_system_module::getModuleByName("rating");
        if($objModule != null) {
            $objRating = class_modul_rating_rate::getRating($this->getSystemid());
            if($objRating != null) {
               $floatRating = $objRating->getFloatRating();
               if($bitRound) {
                   $floatRating = round($floatRating, 2);
               }
            } else
               $floatRating = 0.0;
        }

        return $floatRating;
    }

    /**
     * Checks if the current user is allowed to rate the file
     *
     * @return bool
     */
    public function isRateableByUser() {
        $bitReturn = false;
        $objModule = class_modul_system_module::getModuleByName("rating");
        if($objModule != null) {
            $objRating = class_modul_rating_rate::getRating($this->getSystemid());
            if($objRating != null)
               $bitReturn = $objRating->isRateableByCurrentUser();
            else
               $bitReturn = true;
        }

        return $bitReturn;
    }

    /**
     * Number of rating for the current file
     *
     * @see interface_sortable_rating
     * @return int
     */
    public function getIntRatingHits() {
        $intHits = 0;
        $objModule = class_modul_system_module::getModuleByName("rating");
        if($objModule != null) {
            $objRating = class_modul_rating_rate::getRating($this->getSystemid());
            if($objRating != null)
               $intHits = $objRating->getIntHits();
            else
               return 0;
        }

        return $intHits;
    }

}
?>