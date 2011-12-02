<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * The lockmanager takes care of locking and unlocking systemrecords.
 * It provides the methods to check if a record is locked or not.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.3.0
 */
class class_lockmanager  {

    /**
     *
     * @var class_module_system_common
     */
    private $objSystemCommon;

    private $strSystemid = "";

	/**
	 * Contructor
	 */
	public function __construct($strSystemid = "") {
        $this->objSystemCommon = new class_module_system_common($strSystemid);
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
						SET system_lock_id=?,
						    system_lock_time = ?
						WHERE system_id =?";

		if(class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array(class_carrier::getInstance()->getObjSession()->getUserID(), time(), $this->strSystemid ))) {
            class_carrier::getInstance()->getObjDB()->flushQueryCache();
            $this->objSystemCommon = new class_module_system_common($this->strSystemid);
            return true;
        }

        return false;
    }

    /**
     * Checks if the current record is locked, ignoring the locking user-id.
     *
     * @return bool
     */
    public function isLocked() {
        return $this->getLockId() != "0";
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
                            WHERE system_id=? ";
            if(class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($this->strSystemid))) {
                class_carrier::getInstance()->getObjDB()->flushQueryCache();
                $this->objSystemCommon = new class_module_system_common($this->strSystemid);
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
				      WHERE system_lock_time <= ?";
	    return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($intMinTime));
	}

    /**
     * Fetches the current user-id locking the record
     *
     * @return string
     */
    private function getLockId() {
        if($this->objSystemCommon->getStrLockId() != "")
            return $this->objSystemCommon->getStrLockId();
        else
            return "0";
    }

}

