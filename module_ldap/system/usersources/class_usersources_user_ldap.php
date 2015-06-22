<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * User-part of the ldap-connector. Tries to load the user-details provided by the ldap-directory and 
 * takes care of authentication.
 * 
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_ldap
 *
 * @module ldap
 * @moduleId _ldap_module_id_
 */
class class_usersources_user_ldap extends class_model implements interface_model, interface_usersources_user {
    
    private $strEmail = "";
    private $strFamilyname = "";
    private $strGivenname = "";
    private $strDN = "";
    private $intCfg = 0;

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrEmail();
    }

    /**
     * Initialises the current object, if a systemid was given
     */
    public function initObjectInternal() {
        $strQuery = "SELECT * FROM ".$this->objDB->dbsafeString(_dbprefix_."user_ldap")." WHERE user_ldap_id=?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
            $this->setStrEmail($arrRow["user_ldap_email"]);
            $this->setStrFamilyname($arrRow["user_ldap_familyname"]);
            $this->setStrGivenname($arrRow["user_ldap_givenname"]);
            $this->setStrDN($arrRow["user_ldap_dn"]);
            $this->setIntCfg($arrRow["user_ldap_cfg"]);
        }
    }
    
    /**
     * Indicates if the current users' password may be reset, e.g. via a password-forgotten mail
     */
    public function isPasswordResettable() {
        return false;
    }
    
    /**
     * Passes a new system-id to the object.
     * This id has to be used for newly created objects,
     * otherwise the mapping of kajona-users to users in the
     * subsystem may fail.
     * 
     * @param string $strId
     * @return void
     */
    public function setNewRecordId($strId) {
        $strQuery = "UPDATE "._dbprefix_."user_ldap SET user_ldap_id = ? WHERE user_ldap_id = ?";
        $this->objDB->_pQuery($strQuery, array($strId, $this->getSystemid()));
        $this->setSystemid($strId);
    }

    /**
     * Updates the current object to the database
     *
     * @param bool $strPrevId
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {
        if($this->getSystemid() == "") {
            $strUserid = generateSystemid();
            $this->setSystemid($strUserid);
            $strQuery = "INSERT INTO "._dbprefix_."user_ldap (
                        user_ldap_id, 
                        user_ldap_email, user_ldap_familyname,
                        user_ldap_givenname, user_ldap_dn, user_ldap_cfg

                        ) VALUES (?,?,?,?,?,?)";

            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("new ldap user: ".$this->getStrDN(), class_logger::$levelInfo);

            return $this->objDB->_pQuery($strQuery, array(
                $strUserid,
                $this->getStrEmail(),
                $this->getStrName(),
                $this->getStrForename(),
                $this->getStrDN(),
                $this->getIntCfg()
            ));
        }
        else {
                $strQuery = "UPDATE "._dbprefix_."user_ldap SET
                        user_ldap_email=?, user_ldap_familyname=?, user_ldap_givenname=?, user_ldap_dn=?, user_ldap_cfg=? WHERE user_ldap_id = ?";

                $arrParams = array(
                        $this->getStrEmail(), $this->getStrFamilyname(), $this->getStrGivenname(), $this->getStrDN(), $this->getIntCfg(), $this->getSystemid()
                   );
                   

            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("updated user ".$this->getStrDN(), class_logger::$levelInfo);

            return $this->objDB->_pQuery($strQuery, $arrParams);
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
     * Deletes a user from the systems
     *
     * @return bool
     */
    public function deleteUser() {
        class_logger::getInstance()->addLogRow("deleted ldap user with dn ".$this->getStrDN(), class_logger::$levelInfo);
        $strQuery = "DELETE FROM "._dbprefix_."user_ldap WHERE user_ldap_id=?";
        //call other models that may be interested
        $bitDelete = $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
        class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, array($this->getSystemid(), get_class($this)));
        return $bitDelete;
    }

    /**
     * Deletes the current object from the system
     * @return bool
     */
    public function deleteObjectFromDatabase() {
        return $this->deleteUser();
    }

    /**
     * Returns the list of group-ids the current user is assigned to
     * @return array
     */
	public function getGroupIdsForUser() {

		$arrReturn = array();
        
        $objLdap = class_ldap::getInstance();
        $objLdapSource = new class_usersources_source_ldap();
        $arrLdapGroups = $objLdapSource->getAllGroupIds();
        
        foreach($arrLdapGroups as $strOneGroupId) {
            $objGroup = new class_usersources_group_ldap($strOneGroupId);
            
            if($objLdap->isUserMemberOfGroup($this->getStrDN(), $objGroup->getStrDn()))
                $arrReturn[] = $strOneGroupId;
        }
        
        
        return $arrReturn;
    }
    
    /**
     * Indicates if the current user is editable or read-only
     * @return bool
     */
	public function isEditable() {
        return false;
    }

    public function getStrForename() {
        return $this->strGivenname;
    }
    
    public function getStrName() {
        return $this->strFamilyname;
    }
    
    public function getStrEmail() {
        return $this->strEmail;
    }

    public function setStrEmail($strEmail) {
        $this->strEmail = $strEmail;
    }

    public function getStrFamilyname() {
        return $this->strFamilyname;
    }

    public function setStrFamilyname($strFamilyname) {
        $this->strFamilyname = $strFamilyname;
    }

    public function getStrGivenname() {
        return $this->strGivenname;
    }

    public function setStrGivenname($strGivenname) {
        $this->strGivenname = $strGivenname;
    }

    public function getStrDN() {
        return $this->strDN;
    }

    public function setStrDN($strDN) {
        $this->strDN = $strDN;
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
