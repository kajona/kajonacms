<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
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
     * Returns the bool-value for the right to right1 this record,
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
     * Returns the bool-value for the right to right2 this record,
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
     * Returns the bool-value for the right to right3 this record,
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
     * Returns the bool-value for the right to right4 this record,
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
     * Returns the bool-value for the right to right5 this record,
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

}
?>