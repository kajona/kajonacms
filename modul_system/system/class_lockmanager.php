<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_cookie.php 2353 2008-12-31 15:22:01Z sidler $                                            *
********************************************************************************************************/

/**
 * The lockmanager takes care of locking and unlocking systemrecords.
 * It provides the methods to check if a record is locked or not.
 *
 * @package modul_system
 * @author sidler
 * @since 3.3.0
 */
class class_lockmanager  {

    /**
     *
     * @var class_modul_system_common
     */
    private $objSystemCommon;

    private $strSystemid = "";

	/**
	 * Contructor
	 */
	public function __construct($strSystemid = "") {
        $this->objSystemCommon = new class_modul_system_common($strSystemid);
        $this->strSystemid = $strSystemid;

        $this->unlockOldRecords();
	}

    /**
	 * Locks a systemrecord for the current user
	 *
	 * @return bool
	 */
    public function lockRecord() {
		$strQuery = "UPDATE "._dbprefix_."system
						SET system_lock_id='".dbsafeString(class_carrier::getInstance()->getObjSession()->getUserID())."',
						    system_lock_time = '".dbsafeString(time())."'
						WHERE system_id ='".dbsafeString($this->strSystemid)."'";

		if(class_carrier::getInstance()->getObjDB()->_query($strQuery)) {
            class_carrier::getInstance()->getObjDB()->flushQueryCache();
            return true;
        }

        return false;
    }

    /**
	 * Unlocks a dataRecord as long as the record is locked by the current one
	 *
     * @param bool $bitForceUnlock unlocks the record, even if the user is not the owner of the lock. must be an admin therefore!
	 * @return bool
	 */
	public function unlockRecord($bitForceUnlock = false)	{
        if($this->isLockedByCurrentUser() ||
            ($bitForceUnlock && class_carrier::getInstance()->getObjRights()->userIsAdmin(class_carrier::getInstance()->getObjSession()->getUserID()) )) {
            
            
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_lock_id='0'
                            WHERE system_id='".dbsafeString($this->strSystemid)."'";
            if(class_carrier::getInstance()->getObjDB()->_query($strQuery)) {
                class_carrier::getInstance()->getObjDB()->flushQueryCache();
                return true;
            }
        }

        return false;
	}

    /**
     * Checks if the record is locked for the current user and so not accessible.
     * If the record is locked by someone else, false will be returned, true otherwise.
     *
     * @return bool
     */
    public function isAccessibleForCurrentUser() {
        $strLockId = $this->getLockId();
        if(validateSystemid($strLockId)) {
            if($strLockId != class_carrier::getInstance()->getObjSession()->getUserID())
                return false;
        }

        return true;
    }

    /**
     * Checks of the current record is locked exclusively for the current user,
     * so the lock is being held by the current user.
     *
     * @return bool
     */
    public function isLockedByCurrentUser() {
        $strLockId = $this->getLockId();
        if(validateSystemid($strLockId)) {
            if($strLockId == class_carrier::getInstance()->getObjSession()->getUserID())
                return true;
        }

        return false;
    }

    /**
     * Determins wether the current user is allowed to unlock a record or not.
     * This is only the case, if the user is member of the admin-group.
     *
     * @return bool
     */
    public function isUnlockableForCurrentUser() {

        if(class_carrier::getInstance()->getObjRights()->userIsAdmin(class_carrier::getInstance()->getObjSession()->getUserID()))
            return true;

        return false;
    }


    /**
	 * Unlocks records locked passed the defined max-locktime
	 *
	 * @return true
	 */
	private function unlockOldRecords() {
	    $intMinTime = time() - _system_lock_maxtime_;
	    $strQuery = "UPDATE "._dbprefix_."system
						SET system_lock_id='0'
				      WHERE system_lock_time <='".dbsafeString($intMinTime)."'";
	    return class_carrier::getInstance()->getObjDB()->_query($strQuery);
	}

    /**
     * Fetches the current user-id locking the record
     *
     * @return string
     */
    private function getLockId() {
        $arrSystemrecord = $this->objSystemCommon->getSystemRecord();
        if(isset($arrSystemrecord["system_lock_id"]) && $arrSystemrecord["system_lock_id"] != "")
            return $arrSystemrecord["system_lock_id"];
        else
            return "0";
    }

} 

?>