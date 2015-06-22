<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/


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
class class_usersources_group_ldap extends class_model implements interface_model, interface_usersources_group {

    /**
     * @var string
     * @fieldType text
     * @fieldMandatory
     */
    private $strDn = "";

    /**
     * @var int
     */
    private $intCfg = 0;

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrDn();
    }

    /**
     * Initalises the current object, if a systemid was given

     */
    protected function initObjectInternal() {
        $strQuery = "SELECT * FROM " . _dbprefix_ . "user_group_ldap WHERE group_ldap_id=?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
            $this->setStrDn($arrRow["group_ldap_dn"]);
            $this->setIntCfg($arrRow["group_ldap_cfg"]);
        }
    }

    /**
     * Updates the current object to the database.
     * Overwrites class_roots' logic since a ldap group is not reflected in the system-table
     *
     * @param bool $strPrevId
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {
        //mode-splitting
        if($this->getSystemid() == "") {
            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("saved new ldap group " . $this->getStrSystemid(), class_logger::$levelInfo);
            $strGrId = generateSystemid();
            $this->setSystemid($strGrId);
            $strQuery = "INSERT INTO " . _dbprefix_ . "user_group_ldap
                          (group_ldap_id, group_ldap_dn, group_ldap_cfg) VALUES
                          (?, ?, ?)";
            return $this->objDB->_pQuery($strQuery, array($strGrId, $this->getStrDn(), $this->getIntCfg()));
        }
        else {
            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("updated ldap group " . $this->getSystemid(), class_logger::$levelInfo);
            $strQuery = "UPDATE " . _dbprefix_ . "user_group_ldap
                            SET group_ldap_dn=?, group_ldap_cfg=?
                          WHERE group_ldap_id=?";
            return $this->objDB->_pQuery($strQuery, array($this->getStrDn(), $this->getIntCfg(), $this->getSystemid()));
        }
    }

    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb() {
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
    public function setNewRecordId($strId) {
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
    public function getUserIdsForGroup($intStart = null, $intEnd = null) {

        $arrReturn = array();
        //load all members from ldap
        $objLdap = class_ldap::getInstance();
        $arrMembers = $objLdap->getMembersOfGroup($this->getStrDn());
        $objSource = new class_usersources_source_ldap();

        foreach($arrMembers as $strOneMemberDn) {
            //check if the user exists in the kajona-database
            $objUser = $objSource->getUserByDn($strOneMemberDn);
            if($objUser != null) {
                $arrReturn[] = $objUser->getSystemid();
            }
            else {
                //import the user into the system transparently
                $arrSingleUser = $objLdap->getUserDetailsByDN($strOneMemberDn);
                $objUser = new class_module_user_user();
                $objUser->setStrUsername($arrSingleUser["username"]);
                $objUser->setStrSubsystem("ldap");
                $objUser->setIntActive(1);
                $objUser->setIntAdmin(1);
                $objUser->updateObjectToDb();

                $objSourceUser = $objUser->getObjSourceUser();
                if($objSourceUser instanceof class_usersources_user_ldap) {
                    $objSourceUser->setStrDN($arrSingleUser["identifier"]);
                    $objSourceUser->setStrFamilyname($arrSingleUser["familyname"]);
                    $objSourceUser->setStrGivenname($arrSingleUser["givenname"]);
                    $objSourceUser->setStrEmail($arrSingleUser["mail"]);
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
    public function getNumberOfMembers() {
        $objLdap = class_ldap::getInstance();
        try {
            return $objLdap->getNumberOfGroupMembers($this->getStrDn());
        }
        catch(class_exception $objEx) {
            return "n.a.";
        }
    }

    /**
     * Deletes the given group
     *
     * @return bool
     */
    public function deleteGroup() {
        class_logger::getInstance()->addLogRow("deleted ldap group with id " . $this->getSystemid(), class_logger::$levelInfo);
        $strQuery = "DELETE FROM " . _dbprefix_ . "user_group_ldap WHERE group_ldap_id=?";
        class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, array($this->getSystemid(), get_class($this)));
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
    }

    /**
     * Deletes the current object from the system
     *
     * @return bool
     */
    public function deleteObjectFromDatabase() {
        return $this->deleteObject();
    }

    /**
     * Adds a new member to the group - if possible
     *
     * @param interface_usersources_user $objUser
     *
     * @return bool
     */
    public function addMember(interface_usersources_user $objUser) {
        return true;
    }


    /**
     * Defines whether the current group-properties (e.g. the name) may be edited or is read-only
     *
     * @return bool
     */
    public function isEditable() {
        return true;
    }


    /**
     * Removes a member from the current group - if possible.
     *
     * @param interface_usersources_user $objUser
     *
     * @return bool
     */
    public function removeMember(interface_usersources_user $objUser) {
        return false;
    }

    public function getStrDn() {
        return $this->strDn;
    }

    public function setStrDn($strDn) {
        $this->strDn = $strDn;
    }

    /**
     * @return int
     */
    public function getIntCfg() {
        return $this->intCfg;
    }

    /**
     * @param int $intCfg
     */
    public function setIntCfg($intCfg) {
        $this->intCfg = $intCfg;
    }


}
