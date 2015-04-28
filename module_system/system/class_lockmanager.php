<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
class class_lockmanager {

    private $strSystemid = "";
    /**
     * @var class_root
     */
    private $objSourceObject = null;

    private static $bitUnlockTriggered = false;

    /**
     * Constructor
     *
     * @param string $strSystemid
     * @param \class_root|null $objSourceObject
     */
    public function __construct($strSystemid = "", class_root $objSourceObject = null) {
        $this->strSystemid = $strSystemid;
        $this->objSourceObject = $objSourceObject;

        $this->unlockOldRecords();
    }

    /**
     * Locks a systemrecord for the current user
     *
     * @return bool
     */
    public function lockRecord() {
        $strQuery = "UPDATE " . _dbprefix_ . "system
						SET system_lock_id=?,
						    system_lock_time = ?
						WHERE system_id =?";

        if(class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array(class_carrier::getInstance()->getObjSession()->getUserID(), time(), $this->strSystemid))) {
            class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE);
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
     * @param bool $bitForceUnlock unlocks the record, even if the user is not the owner of the lock.
     *
     * @return bool
     */
    public function unlockRecord($bitForceUnlock = false) {
        if($this->isLockedByCurrentUser() || $bitForceUnlock ) {

            $strQuery = "UPDATE " . _dbprefix_ . "system
                            SET system_lock_id='0'
                            WHERE system_id=? ";
            if(class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($this->strSystemid))) {
                if($this->objSourceObject !== null)
                    $this->objSourceObject->setStrLockId("");

                class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE);
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
            if($strLockId != class_carrier::getInstance()->getObjSession()->getUserID()) {
                return false;
            }
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
            if($strLockId == class_carrier::getInstance()->getObjSession()->getUserID()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines whether the current user is allowed to unlock a record or not.
     * This is only the case, if the user is member of the admin-group.
     *
     * @return bool
     */
    public function isUnlockableForCurrentUser() {

        if(class_carrier::getInstance()->getObjSession()->isSuperAdmin()) {
            return true;
        }

        return false;
    }


    /**
     * Unlocks records locked passed the defined max-locktime
     *
     * @return bool
     */
    private function unlockOldRecords() {

        if(self::$bitUnlockTriggered)
            return true;

        self::$bitUnlockTriggered = true;

        if(!defined("_system_lock_maxtime_"))
            define("_system_lock_maxtime_", 0);


        $intMinTime = time() - _system_lock_maxtime_;
        $strQuery = "UPDATE " . _dbprefix_ . "system
						SET system_lock_id='0'
				      WHERE system_lock_time <= ?";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($intMinTime));
    }

    /**
     * Fetches the current user-id locking the record
     *
     * @return string
     */
    public function getLockId() {
        $objObject = class_objectfactory::getInstance()->getObject($this->strSystemid);
        if(validateSystemid($this->strSystemid) && $objObject != null && $objObject->getStrLockId() != "") {
            return $objObject->getStrLockId();
        }
        else {
            return "0";
        }
    }


    /**
     * Fetches a list of records currently locked in the database
     *
     * @param null|int $intStart
     * @param null|int $intEnd
     *
     * @return class_model[]
     */
    public static function getLockedRecords($intStart = null, $intEnd = null) {
        $strQuery = "SELECT system_id FROM "._dbprefix_."system WHERE system_lock_id != '0' AND system_lock_id IS NOT NULL ORDER BY system_id DESC";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Fetches a list of records currently locked in the database
     *
     * @param string $strUserId
     *
     * @return class_model[]
     */
    public static function getLockedRecordsForUser($strUserId) {
        $strQuery = "SELECT system_id FROM "._dbprefix_."system WHERE system_lock_id = ? ORDER BY system_id DESC";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strUserId));

        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Counts the number of records currently locked in the database
     *
     * @return int
     */
    public static function getLockedRecordsCount() {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_lock_id != '0' AND system_lock_id IS NOT NULL";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        return $arrRow["COUNT(*)"];
    }

}

