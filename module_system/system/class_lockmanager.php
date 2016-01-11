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
class class_lockmanager
{

    private $strSystemid = "";
    /**
     * @var class_root
     */
    private $objSourceObject = null;

    /**
     * Constructor
     *
     * @param string $strSystemid
     * @param \class_root|null $objSourceObject
     */
    public function __construct($strSystemid = "", class_root $objSourceObject = null)
    {
        $this->strSystemid = $strSystemid;
        $this->objSourceObject = $objSourceObject;

    }

    /**
     * Locks a systemrecord for the current user
     *
     * @return bool
     */
    public function lockRecord()
    {
        $strQuery = "UPDATE "._dbprefix_."system
						SET system_lock_id = ?,
						    system_lock_time = ?
						WHERE system_id =?";

        if (class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array(class_carrier::getInstance()->getObjSession()->getUserID(), time(), $this->strSystemid))) {
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
    public function isLocked()
    {
        return $this->getLockedUntilTimestamp(true) > time();
    }

    /**
     * Unlocks a dataRecord as long as the record is locked by the current one
     *
     * @param bool $bitForceUnlock unlocks the record, even if the user is not the owner of the lock.
     *
     * @return bool
     */
    public function unlockRecord($bitForceUnlock = false)
    {
        if ($bitForceUnlock || $this->isLockedByCurrentUser()) {

            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_lock_time = '0'
                            WHERE system_id=? ";
            if (class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($this->strSystemid))) {
                if ($this->objSourceObject !== null) {
                    $this->objSourceObject->setStrLockId("");
                }

                class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE);
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the record is locked for the current user and so accessible.
     * If the record is locked by someone else, false will be returned, true otherwise.
     *
     * @return bool
     */
    public function isAccessibleForCurrentUser()
    {
        $intLockedUntil = $this->getLockedUntilTimestamp();
        //lock is already outdated
        if($intLockedUntil < time()) {
            return true;
        }

        //lock not outdated, so validate the owner-id
        $strLockId = $this->getLockId();
        if (validateSystemid($strLockId)) {
            if ($strLockId != class_carrier::getInstance()->getObjSession()->getUserID()) {
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
    public function isLockedByCurrentUser()
    {
        $intLockedUntil = $this->getLockedUntilTimestamp();
        //lock is already outdated
        if($intLockedUntil < time()) {
            return false;
        }

        $strLockId = $this->getLockId();
        if (validateSystemid($strLockId)) {
            if ($strLockId == class_carrier::getInstance()->getObjSession()->getUserID()) {
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
    public function isUnlockableForCurrentUser()
    {

        if (class_carrier::getInstance()->getObjSession()->isSuperAdmin()) {
            return true;
        }

        return false;
    }


    /**
     * Unlocks records locked passed the defined max-locktime
     *
     * @return bool
     * @deprecated this method is no longer used
     */
    public function unlockOldRecords()
    {
        return true;

        /*

         $intMinTime = time() - class_module_system_setting::getConfigValue("_system_lock_maxtime_");
        $strQuery = "UPDATE "._dbprefix_."system
						SET system_lock_id='0'
				      WHERE system_lock_time <= ?";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($intMinTime));

        */
    }

    /**
     * Fetches the current user-id locking the record
     *
     * @return string
     */
    public function getLockId()
    {
        $objObject = class_objectfactory::getInstance()->getObject($this->strSystemid);
        if (validateSystemid($this->strSystemid) && $objObject != null && $objObject->getStrLockId() != "") {
            return $objObject->getStrLockId();
        }
        else {
            return "0";
        }
    }


    /**
     * Fetches the current user-id locking the record
     *
     * @return string
     */
    private function getLockedUntilTimestamp($bitIgnoreLockId = false)
    {
        $objObject = class_objectfactory::getInstance()->getObject($this->strSystemid);
        if (validateSystemid($this->strSystemid) && ($bitIgnoreLockId || $objObject != null && $objObject->getStrLockId() != "")) {
            return $objObject->getIntLockTime() + (int)class_module_system_setting::getConfigValue("_system_lock_maxtime_");
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
    public static function getLockedRecords($intStart = null, $intEnd = null)
    {
        $strQuery = "SELECT system_id FROM "._dbprefix_."system WHERE system_lock_time > ? ORDER BY system_id DESC";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(time() - (int)class_module_system_setting::getConfigValue("_system_lock_maxtime_")), $intStart, $intEnd);

        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
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
    public static function getLockedRecordsForUser($strUserId)
    {
        $strQuery = "SELECT system_id FROM "._dbprefix_."system WHERE system_lock_id = ? AND system_lock_time > ? ORDER BY system_id DESC";
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strUserId, time() - (int)class_module_system_setting::getConfigValue("_system_lock_maxtime_")));

        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Counts the number of records currently locked in the database
     *
     * @return int
     */
    public static function getLockedRecordsCount()
    {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system WHERE system_lock_time > ?";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array(time() - (int)class_module_system_setting::getConfigValue("_system_lock_maxtime_")));
        return $arrRow["COUNT(*)"];
    }

}

