<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\Ldap\System\Usersources;

use Kajona\Ldap\System\Ldap;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\Exception;
use Kajona\System\System\Logger;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\Usersources\UsersourcesGroupInterface;
use Kajona\System\System\Usersources\UsersourcesUserInterface;
use Kajona\System\System\UserUser;


/**
 * Represents a single group inside the directory.
 * Main functionality is to map to a DN inside the ldap.
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_ldap
 *
 * @module ldap
 * @moduleId _ldap_module_id_
 */
class UsersourcesGroupLdap extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, UsersourcesGroupInterface
{

    /**
     * @var string
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     */
    private $strDn = "";

    /**
     * @var int
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     */
    private $intCfg = 0;

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrDn();
    }

    /**
     * Initalises the current object, if a systemid was given
     */
    protected function initObjectInternal()
    {
        $strQuery = "SELECT * FROM " . _dbprefix_ . "user_group_ldap WHERE group_ldap_id=?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if (count($arrRow) > 0) {
            $this->setStrDn($arrRow["group_ldap_dn"]);
            $this->setIntCfg($arrRow["group_ldap_cfg"]);
        }
    }

    /**
     * Updates the current object to the database.
     * Overwrites Roots' logic since a ldap group is not reflected in the system-table
     *
     * @param bool $strPrevId
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false)
    {
        //mode-splitting
        if ($this->getSystemid() == "") {
            Logger::getInstance(Logger::USERSOURCES)->addLogRow("saved new ldap group " . $this->getStrSystemid(), Logger::$levelInfo);
            $strGrId = generateSystemid();
            $this->setSystemid($strGrId);
            $strQuery = "INSERT INTO " . _dbprefix_ . "user_group_ldap
                          (group_ldap_id, group_ldap_dn, group_ldap_cfg) VALUES
                          (?, ?, ?)";
            return $this->objDB->_pQuery($strQuery, array($strGrId, $this->getStrDn(), $this->getIntCfg()), array(true, false));
        } else {
            Logger::getInstance(Logger::USERSOURCES)->addLogRow("updated ldap group " . $this->getSystemid(), Logger::$levelInfo);
            $strQuery = "UPDATE " . _dbprefix_ . "user_group_ldap
                            SET group_ldap_dn=?, group_ldap_cfg=?
                          WHERE group_ldap_id=?";
            return $this->objDB->_pQuery($strQuery, array($this->getStrDn(), $this->getIntCfg(), $this->getSystemid()), array(false, false));
        }
    }

    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb()
    {
        return true;
    }

    /**
     * Passes a new system-id to the object.
     * This id has to be used for newly created objects,
     * otherwise the mapping of kajona-users to users in the
     * subsystem may fail.
     *
     * @param string $strId
     *
     * @return void
     */
    public function setNewRecordId($strId)
    {
        $strQuery = "UPDATE " . _dbprefix_ . "user_group_ldap SET group_ldap_id = ? WHERE group_ldap_id = ?";
        $this->objDB->_pQuery($strQuery, array($strId, $this->getSystemid()));
        $this->setSystemid($strId);
    }

    /**
     * Returns an array of user-ids associated with the current group.
     * If possible, pageing should be supported
     *
     * @param int $intStart
     * @param int $intEnd
     *
     * @return array
     */
    public function getUserIdsForGroup($intStart = null, $intEnd = null)
    {

        $arrReturn = array();
        //load all members from ldap
        $objLdap = Ldap::getInstance($this->intCfg);
        $arrMembers = $objLdap->getMembersOfGroup($this->getStrDn());
        sort($arrMembers);
        $objSource = new UsersourcesSourceLdap();

        $intI = 0;
        foreach ($arrMembers as $strOneMemberDn) {

            if ($intStart !== null && $intEnd !== null && ($intI < $intStart || $intI > $intEnd)) {
                $intI++;
                continue;
            }
            $intI++;

            //fetch the user by DN and see of a matching user with the relevant user-id is given
            $arrUserDetails = $objLdap->getUserDetailsByDN($strOneMemberDn);

            $objUser = null;
            $strUsername = null;
            if ($arrUserDetails !== false) {
                $strUsername = $arrUserDetails["username"];
                $objUser = $objSource->getUserByUsername($strUsername);
            }


            //check if the user exists in the kajona-database
            if ($objUser != null) {
                $arrReturn[] = $objUser->getSystemid();
            } else {
                //import the user into the system transparently
                $objUser = new UserUser();
                $objUser->setStrUsername($arrUserDetails["username"]);
                $objUser->setStrSubsystem("ldap");
                $objUser->setIntActive(1);
                $objUser->setIntAdmin(1);
                $objUser->updateObjectToDb();

                $objSourceUser = $objUser->getObjSourceUser();
                if ($objSourceUser instanceof UsersourcesUserLdap) {
                    $objSourceUser->setStrDN($arrUserDetails["identifier"]);
                    $objSourceUser->setStrFamilyname($arrUserDetails["familyname"]);
                    $objSourceUser->setStrGivenname($arrUserDetails["givenname"]);
                    $objSourceUser->setStrEmail($arrUserDetails["mail"]);
                    $objSourceUser->updateObjectToDb();

                    $this->objDB->flushQueryCache();
                }

                $arrReturn[] = $objUser->getSystemid();
            }
        }

        return $arrReturn;
    }

    /**
     * Returns the number of members of the current group.
     *
     * @return int
     */
    public function getNumberOfMembers()
    {
        $objLdap = Ldap::getInstance($this->intCfg);
        try {
            return $objLdap->getNumberOfGroupMembers($this->getStrDn());
        } catch (Exception $objEx) {
            return "n.a.";
        }
    }

    /**
     * Deletes the given group
     *
     * @return bool
     */
    public function deleteGroup()
    {
        Logger::getInstance()->addLogRow("deleted ldap group with id " . $this->getSystemid(), Logger::$levelInfo);
        $strQuery = "DELETE FROM " . _dbprefix_ . "user_group_ldap WHERE group_ldap_id=?";
        CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, array($this->getSystemid(), get_class($this)));
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
    }

    /**
     * Deletes the current object from the system
     *
     * @return bool
     */
    public function deleteObjectFromDatabase()
    {
        return $this->deleteObject();
    }

    /**
     * Adds a new member to the group - if possible
     *
     * @param UsersourcesUserInterface $objUser
     *
     * @return bool
     */
    public function addMember(UsersourcesUserInterface $objUser)
    {
        return true;
    }


    /**
     * Defines whether the current group-properties (e.g. the name) may be edited or is read-only
     *
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }


    /**
     * Hook to update the admin-form when editing / creating a single group
     * @param AdminFormgenerator $objForm
     *
     * @return mixed
     */
    public function updateAdminForm(AdminFormgenerator $objForm)
    {
        $arrDD = array();
        foreach (Ldap::getAllInstances() as $objOneInstance)
            $arrDD[$objOneInstance->getIntCfgNr()] = $objOneInstance->getStrCfgName();

        $objForm->getField("cfg")->setArrKeyValues($arrDD);
    }

    /**
     * Removes a member from the current group - if possible.
     *
     * @param UsersourcesUserInterface $objUser
     *
     * @return bool
     */
    public function removeMember(UsersourcesUserInterface $objUser)
    {
        return false;
    }

    public function getStrDn()
    {
        return $this->strDn;
    }

    public function setStrDn($strDn)
    {
        $this->strDn = $strDn;
    }

    /**
     * @return int
     */
    public function getIntCfg()
    {
        return $this->intCfg;
    }

    /**
     * @param int $intCfg
     */
    public function setIntCfg($intCfg)
    {
        $this->intCfg = $intCfg;
    }


}
