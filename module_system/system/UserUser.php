<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\System\Usersources\UsersourcesUserInterface;


/**
 * Model for a user
 * Note: Users do not use the classical system-id relation, so no entry in the system-table
 *
 * @package module_user
 * @author sidler@mulchprod.de
 *
 * @module user
 * @moduleId _user_modul_id_
 *
 * @targetTable user_user.user_id
 */
class UserUser extends Model implements ModelInterface, AdminListableInterface
{

    /**
     * @var string
     * @tableColumn user_user.user_subsystem
     * @tableColumnDatatype char254
     */
    private $strSubsystem = "kajona";

    /**
     *
     * @var UsersourcesUserInterface
     */
    private $objSourceUser;

    /**
     * @var string
     * @tableColumn user_user.user_username
     * @tableColumnDatatype char254
     */
    private $strUsername = "";

    /**
     * @var int
     * @tableColumn user_user.user_logins
     * @tableColumnDatatype int
     */
    private $intLogins = 0;

    /**
     * @var int
     * @tableColumn user_user.user_lastlogin
     * @tableColumnDatatype int
     */
    private $intLastlogin = 0;

    /**
     * @var int
     * @tableColumn user_user.user_active
     * @tableColumnDatatype int
     */
    private $intActive = 0;

    /**
     * @var int
     * @tableColumn user_user.user_admin
     * @tableColumnDatatype int
     */
    private $intAdmin = 0;

    /**
     * @var int
     * @tableColumn user_user.user_portal
     * @tableColumnDatatype int
     */
    private $intPortal = 0;

    /**
     * @var string
     * @tableColumn user_user.user_admin_skin
     * @tableColumnDatatype char254
     */
    private $strAdminskin = "";

    /**
     * @var string
     * @tableColumn user_user.user_admin_language
     * @tableColumnDatatype char254
     */
    private $strAdminlanguage = "";

    /**
     * @var string
     * @tableColumn user_user.user_admin_module
     * @tableColumnDatatype char254
     */
    private $strAdminModule = "";

    /**
     * @var string
     * @tableColumn user_user.user_authcode
     * @tableColumnDatatype string20
     */
    private $strAuthcode = "";

    /**
     * @var int
     * @tableColumn user_user.user_deleted
     * @tableColumnDatatype int
     */
    private $intDeleted = 0;

    /**
     * @var int
     * @tableColumn user_user.user_items_per_page
     * @tableColumnDatatype int
     */
    private $intItemsPerPage = 0;


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        $strReturn = $this->getStrUsername();
        if ($this->getStrName() != "") {
            $strReturn .= " (".$this->getStrName().", ".$this->getStrForename().")";
        }

        if ($this->intDeleted == 1) {
            $strReturn = $this->getStrUsername()." (".$this->getLang("user_deleted").")";
        }

        return $strReturn;
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_user";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        if ($this->rightRight1()) {
            return $this->getLang("user_logins", "user")." ".$this->getIntLogins()." ".$this->getLang("user_lastlogin", "user")." ".timeToString($this->getIntLastLogin(), false);
        }
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        if ($this->objSession->isSuperAdmin()) {
            $objUsersources = new UserSourcefactory();
            if (count($objUsersources->getArrUsersources()) > 1) {
                $objSubsystem = new UserSourcefactory();
                return $this->getLang("user_list_source", "user")." ".$objSubsystem->getUsersource($this->getStrSubsystem())->getStrReadableName();
            }
        }
        return "";
    }

    /**
     * @inheritDoc
     */
    protected function onInsertToDb()
    {
        Logger::getInstance(Logger::USERSOURCES)->addLogRow("new user for subsystem ".$this->getStrSubsystem()." / ".$this->getStrUsername(), Logger::$levelInfo);
        $objSources = new UserSourcefactory();
        $objProvider = $objSources->getUsersource($this->getStrSubsystem());
        $objTargetUser = $objProvider->getNewUser();
        $objTargetUser->updateObjectToDb();
        $objTargetUser->setNewRecordId($this->getSystemid());
        $this->objDB->flushQueryCache();
        return true;
    }


    /**
     * @param FilterBase|null $objFilter
     * @param string $strUsernameFilter
     * @param null $intStart
     * @param null $intEnd
     *
     * @return UserUser[]
     */
    public static function getObjectListFiltered(FilterBase $objFilter = null, $strUsernameFilter = "", $intStart = null, $intEnd = null)
    {
        $strDbPrefix = _dbprefix_;

        $strQuery = "SELECT user_tbl.user_id
                      FROM {$strDbPrefix}system, {$strDbPrefix}user AS user_tbl
                      LEFT JOIN {$strDbPrefix}user_kajona AS user_kajona ON user_tbl.user_id = user_kajona.user_id
                      WHERE
                          (user_tbl.user_username LIKE ? OR user_kajona.user_forename LIKE ? OR user_kajona.user_name LIKE ?)
                          AND user_id = system_id
                          AND (system_deleted = 0 OR system_deleted IS NULL)\";
                      ORDER BY user_tbl.user_username, user_tbl.user_subsystem ASC";

        $arrParams = array("%".$strUsernameFilter."%", "%".$strUsernameFilter."%", "%".$strUsernameFilter."%");

        $arrIds = Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach ($arrIds as $arrOneId) {
            $arrReturn[] = new UserUser($arrOneId["user_id"]);
        }

        return $arrReturn;
    }

    /**
     * @param FilterBase|null $objFilter
     * @param string $strUsernameFilter
     *
     * @return int
     */
    public static function getObjectCountFiltered(FilterBase $objFilter = null, $strUsernameFilter = "")
    {
        $strDbPrefix = _dbprefix_;

        $strQuery = "SELECT COUNT(*)
                      FROM {$strDbPrefix}system, {$strDbPrefix}user AS user_tbl 
                      LEFT JOIN {$strDbPrefix}user_kajona AS user_kajona ON user_tbl.user_id = user_kajona.user_id
                      WHERE
                          (user_tbl.user_username LIKE ? OR user_kajona.user_forename LIKE ? OR user_kajona.user_name LIKE ?)
                          AND user_id = system_id
                          AND (system_deleted = 0 OR system_deleted IS NULL)";

        $arrParams = array("%".$strUsernameFilter."%", "%".$strUsernameFilter."%", "%".$strUsernameFilter."%");

        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }


    /**
     * Fetches all available active users with the given username an returns them in an array
     *
     * @param string $strName
     *
     * @return UserUser[]
     */
    public static function getAllUsersByName($strName)
    {
        $objSubsystem = new UserSourcefactory();
        $objUser = $objSubsystem->getUserByUsername($strName);
        if ($objUser != null) {
            return array($objUser);
        }
        else {
            return null;
        }
    }


    /**
     * Deletes a user from the systems
     *
     * @throws Exception
     * @return bool
     */
    public function deleteObject()
    {
        if ($this->objSession->getUserID() == $this->getSystemid()) {
            throw new Exception("You can't delete yourself", Exception::$level_FATALERROR);
        }

        return parent::deleteObject();
    }

    public function deleteObjectFromDatabase()
    {
        if ($this->objSession->getUserID() == $this->getSystemid()) {
            throw new Exception("You can't delete yourself", Exception::$level_FATALERROR);
        }

        Logger::getInstance(Logger::USERSOURCES)->addLogRow("deleted user with id ".$this->getSystemid()." (".$this->getStrUsername()." / ".$this->getStrName().",".$this->getStrForename().")", Logger::$levelWarning);
        $this->getObjSourceUser()->deleteUser();
        return parent::deleteObjectFromDatabase();
    }


    /**
     * Returns an array of group-ids the current user is assigned to
     *
     * @return array string
     */
    public function getArrGroupIds()
    {
        $this->loadSourceObject();
        return $this->objSourceUser->getGroupIdsForUser();
    }

    /**
     * @return string
     */
    public function getStrEmail()
    {
        $this->loadSourceObject();
        if ($this->objSourceUser != null) {
            return $this->objSourceUser->getStrEmail();
        }
        else {
            return "n.a.";
        }
    }

    /**
     * @return string
     */
    public function getStrForename()
    {
        $this->loadSourceObject();
        if ($this->objSourceUser != null) {
            return $this->objSourceUser->getStrForename();
        }
        else {
            return "n.a.";
        }
    }

    /**
     * @return string
     */
    public function getStrName()
    {
        $this->loadSourceObject();
        if ($this->objSourceUser != null) {
            return $this->objSourceUser->getStrName();
        }
        else {
            return "n.a.";
        }
    }

    /**
     * @return void
     */
    private function loadSourceObject()
    {
        if ($this->objSourceUser == null && $this->intDeleted != 1) {
            $objUsersources = new UserSourcefactory();
            $this->setObjSourceUser($objUsersources->getSourceUser($this));
        }
    }


    /**
     * @return int
     */
    public function getIntLogins()
    {
        return $this->intLogins;
    }

    /**
     * @return int
     */
    public function getIntLastLogin()
    {
        return $this->intLastlogin;
    }

    /**
     * @return int
     */
    public function getIntActive()
    {
        return $this->intActive;
    }

    /**
     * @return int
     */
    public function getIntAdmin()
    {
        return $this->intAdmin;
    }

    /**
     * @return int
     */
    public function getIntPortal()
    {
        return $this->intPortal;
    }

    /**
     * @return string
     */
    public function getStrAdminskin()
    {
        return $this->strAdminskin;
    }

    /**
     * @return string
     */
    public function getStrAdminlanguage()
    {
        return $this->strAdminlanguage;
    }

    /**
     * @return string
     */
    public function getStrUsername()
    {
        return $this->strUsername;
    }

    /**
     * @param string $strUsername
     *
     * @return void
     */
    public function setStrUsername($strUsername)
    {
        $this->strUsername = $strUsername;
    }

    /**
     * @param int $intLogins
     *
     * @return void
     */
    public function setIntLogins($intLogins)
    {
        if ($intLogins == "") {
            $intLogins = 0;
        }
        $this->intLogins = $intLogins;
    }

    /**
     * @param int $intLastLogin
     *
     * @return void
     */
    public function setIntLastLogin($intLastLogin)
    {
        if ($intLastLogin == "") {
            $intLastLogin = 0;
        }
        $this->intLastlogin = $intLastLogin;
    }

    /**
     * @param int $intActive
     *
     * @return void
     */
    public function setIntActive($intActive)
    {
        if ($intActive == "") {
            $intActive = 0;
        }
        $this->intActive = $intActive;
    }

    /**
     * @param int $intAdmin
     *
     * @return void
     */
    public function setIntAdmin($intAdmin)
    {
        if ($intAdmin == "") {
            $intAdmin = 0;
        }
        $this->intAdmin = $intAdmin;
    }

    /**
     * @param int $intPortal
     *
     * @return void
     */
    public function setIntPortal($intPortal)
    {
        if ($intPortal == "") {
            $intPortal = 0;
        }
        $this->intPortal = $intPortal;
    }

    /**
     * @param string $strAdminskin
     *
     * @return void
     */
    public function setStrAdminskin($strAdminskin)
    {
        $this->strAdminskin = $strAdminskin;
    }

    /**
     * @param string $strAdminlanguage
     *
     * @return void
     */
    public function setStrAdminlanguage($strAdminlanguage)
    {
        $this->strAdminlanguage = $strAdminlanguage;
    }

    /**
     * @return string
     */
    public function getStrAuthcode()
    {
        return $this->strAuthcode;
    }

    /**
     * @param string $strAuthcode
     *
     * @return void
     */
    public function setStrAuthcode($strAuthcode)
    {
        $this->strAuthcode = $strAuthcode;
    }

    /**
     * @return string
     */
    public function getStrSubsystem()
    {
        return $this->strSubsystem;
    }

    /**
     * @param string $strSubsystem
     *
     * @return void
     */
    public function setStrSubsystem($strSubsystem)
    {
        $this->strSubsystem = $strSubsystem;
    }

    /**
     * @return UsersourcesUserInterface
     */
    public function getObjSourceUser()
    {
        $this->loadSourceObject();
        return $this->objSourceUser;
    }

    /**
     * @param UsersourcesUserInterface $objSourceUser
     *
     * @return void
     */
    public function setObjSourceUser($objSourceUser)
    {
        $this->objSourceUser = $objSourceUser;
    }

    /**
     * @return int
     */
    public function getIntRecordStatus()
    {
        return $this->intActive;
    }

    /**
     * @param string $strAdminModule
     *
     * @return void
     */
    public function setStrAdminModule($strAdminModule)
    {
        $this->strAdminModule = $strAdminModule;
    }

    /**
     * @return string
     */
    public function getStrAdminModule()
    {
        return $this->strAdminModule;
    }

    /**
     * @return int
     */
    public function getIntDeleted()
    {
        return $this->intDeleted;
    }

    /**
     * @param integer $intItemsPerPage
     */
    public function setIntItemsPerPage($intItemsPerPage)
    {
        $this->intItemsPerPage = (int)$intItemsPerPage;
    }

    /**
     * @return int
     */
    public function getIntItemsPerPage()
    {
        if ($this->intItemsPerPage > 0) {
            return $this->intItemsPerPage;
        }
        else {
            return SystemSetting::getConfigValue("_admin_nr_of_rows_");
        }
    }

}
