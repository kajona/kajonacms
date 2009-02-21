<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                               *
********************************************************************************************************/

include_once(_systempath_."/class_root.php");

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

// --- RATING -------------------------------------------------------------------------------------------

    /**
     * Rating of the current file, if module rating is installed.
     *
     * @see interface_sortable_rating
     * @return float
     */
    public function getFloatRating() {
        $floatRating = null;
        $objModule = class_modul_system_module::getModuleByName("rating");
        if($objModule != null) {
            include_once(_systempath_."/class_modul_rating_rate.php");
            $objRating = class_modul_rating_rate::getRating($this->getSystemid());
            if($objRating != null)
               $floatRating = $objRating->getFloatRating();
            else
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
            include_once(_systempath_."/class_modul_rating_rate.php");
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
            include_once(_systempath_."/class_modul_rating_rate.php");
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