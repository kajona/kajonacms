<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\System\System\Usersources;

use Kajona\System\System\Carrier;
use Kajona\System\System\Logger;
use Kajona\System\System\StringUtil;


/**
 * The kajona usersource is the global entry and factory / facade for the classical kajona usersystem
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_usersource
 */
class UsersourcesSourceKajona implements UsersourcesUsersourceInterface
{


    private static $arrUserCache = array();

    private $objDB;

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->objDB = Carrier::getInstance()->getObjDB();
    }

    /**
     * Returns a readable name of the source, e.g. "Kajona" or "LDAP Company 1"
     *
     * @return mixed
     */
    public function getStrReadableName()
    {
        return Carrier::getInstance()->getObjLang()->getLang("usersource_kajona_name", "user");
    }


    /**
     * Tries to authenticate a user with the given credentials.
     * The password is unencrypted, each source should take care of its own encryption.
     *
     * @param UsersourcesUserInterface|UsersourcesUserKajona $objUser
     * @param string $strPassword
     *
     * @return bool
     */
    public function authenticateUser(UsersourcesUserInterface $objUser, $strPassword)
    {
        if ($objUser instanceof UsersourcesUserKajona) {
            $bitMD5Encryption = false;
            if (StringUtil::length($objUser->getStrFinalPass()) == 32) {
                $bitMD5Encryption = true;
            }
            if ($objUser->getStrFinalPass() == self::encryptPassword($strPassword, $objUser->getStrSalt(), $bitMD5Encryption)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getCreationOfGroupsAllowed()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function getCreationOfUsersAllowed()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function getMembersEditable()
    {
        return true;
    }

    /**
     * Loads the group identified by the passed id
     *
     * @param string $strId
     *
     * @return UsersourcesGroupInterface or null
     */
    public function getGroupById($strId)
    {
        $strQuery = "SELECT group_id FROM "._dbprefix_."user_group_kajona WHERE group_id = ?";

        $arrIds = $this->objDB->getPRow($strQuery, array($strId));
        if (isset($arrIds["group_id"]) && validateSystemid($arrIds["group_id"])) {
            return new UsersourcesGroupKajona($arrIds["group_id"]);
        }

        return null;
    }

    /**
     * Returns an empty group, e.g. to fetch the fields available and
     * in order to fill a new one.
     *
     * @return UsersourcesGroupInterface
     */
    public function getNewGroup()
    {
        return new UsersourcesGroupKajona();
    }

    /**
     * Returns an empty user, e.g. to fetch the fields available and
     * in order to fill a new one.
     *
     * @return UsersourcesUserInterface
     */
    public function getNewUser()
    {
        return new UsersourcesUserKajona();
    }

    /**
     * Loads the user identified by the passed id
     *
     * @param string $strId
     *
     * @param bool $bitIgnoreDeletedFlag
     * @return UsersourcesUserInterface or null
     */
    public function getUserById($strId, $bitIgnoreDeletedFlag = false)
    {

        if (isset(self::$arrUserCache[$strId])) {
            return self::$arrUserCache[$strId];
        }

        $strQuery = "SELECT user_id FROM "._dbprefix_."user_kajona  WHERE user_id = ? ";

        $arrIds = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strId));
        if (isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"])) {
            self::$arrUserCache[$strId] = new UsersourcesUserKajona($arrIds["user_id"]);
            return self::$arrUserCache[$strId];
        }

        return null;
    }

    /**
     * Loads the user identified by the passed name.
     * This method may be called during the authentication of users and may be used as a hook
     * in order to create new users in the central database not yet existing.
     *
     * @param string $strUsername
     *
     * @return UsersourcesUserInterface or null
     */
    public function getUserByUsername($strUsername)
    {
        $strQuery = "SELECT user_id FROM "._dbprefix_."user, "._dbprefix_."system WHERE user_id = system_id AND user_username = ? AND user_subsystem = 'kajona' AND (system_deleted = 0 OR system_deleted IS NULL)";

        $arrIds = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUsername));
        if (isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"])) {
            if (!isset(self::$arrUserCache[$arrIds["user_id"]])) {
                self::$arrUserCache[$arrIds["user_id"]] = new UsersourcesUserKajona($arrIds["user_id"]);
            }

            return self::$arrUserCache[$arrIds["user_id"]];
        }

        return null;
    }

    /**
     * Fetches a user by mail. This way of fetching users is not officially supported since not covered by all login-providers.
     *
     * @param string $strEmail
     *
     * @return UsersourcesUserInterface or null
     */
    public function getUserByEmail($strEmail)
    {
        $strQuery = "SELECT sysuser.user_id 
                       FROM "._dbprefix_."user as sysuser, 
                            "._dbprefix_."user_kajona as kjuser, 
                            "._dbprefix_."system 
                      WHERE sysuser.user_id = system_id 
                        AND sysuser.user_id = kjuser.user_id 
                        AND user_email = ? 
                        AND user_subsystem = 'kajona' 
                        AND (system_deleted = 0 OR system_deleted IS NULL)";

        $arrIds = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strEmail));
        if (isset($arrIds["user_id"]) && validateSystemid($arrIds["user_id"])) {
            if (!isset(self::$arrUserCache[$arrIds["user_id"]])) {
                self::$arrUserCache[$arrIds["user_id"]] = new UsersourcesUserKajona($arrIds["user_id"]);
            }

            return self::$arrUserCache[$arrIds["user_id"]];
        }

        return null;
    }


    /**
     * Encrypts the password, e.g. in order to be validated during logins
     *
     * @param string $strPassword
     * @param string $strSalt
     * @param bool $bitMD5Encryption
     *
     * @return string
     */
    public static function encryptPassword($strPassword, $strSalt = "", $bitMD5Encryption = false)
    {
        if ($bitMD5Encryption) {
            Logger::getInstance(Logger::USERSOURCES)->warning("usage of old md5-encrypted password!");
            return md5($strPassword);
        }

        if ($strSalt == "") {
            return sha1($strPassword);
        } else {
            return sha1(md5($strSalt).$strPassword);
        }
    }


    /**
     * Returns an array of group-ids provided by the current source.
     *
     * @return string
     */
    public function getAllGroupIds()
    {
        $strQuery = "SELECT gk.group_id as group_id
                       FROM "._dbprefix_."user_group_kajona AS gk,
                            "._dbprefix_."user_group AS g
                      WHERE g.group_id = gk.group_id
                      ORDER BY g.group_name";
        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
            $arrReturn[] = $arrOneRow["group_id"];
        }

        return $arrReturn;
    }

}
