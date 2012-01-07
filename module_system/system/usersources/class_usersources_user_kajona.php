<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * Model representing an user within the classical kajona subsystem.
 * Since kajona-users are NOT reflected in the system-table, the classical systemid is not available.
 * Relevant methods have to be reimplemented to reflect this change.
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_usersource
 */
class class_usersources_user_kajona extends class_model implements interface_model, interface_usersources_user {

    private $strPass = "";
    private $strEmail = "";
    private $strForename = "";
    private $strName = "";
    private $strStreet = "";
    private $strPostal = "";
    private $strCity = "";
    private $strTel = "";
    private $strMobile = "";
    private $longDate = 0;

    /**
     * The immutable password from the database.
     * $strPass is not published with the information from the database, otherwise it would be
     * overwritten.
     * @var string
     */
    private $strFinalPass = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "user");
        $this->setArrModuleEntry("moduleId", _user_modul_id_);

		parent::__construct($strSystemid);

    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName();
    }


    /**
     * Initialises the current object, if a systemid was given
     */
    protected function initObjectInternal() {
        $strQuery = "SELECT * FROM ".$this->objDB->dbsafeString(_dbprefix_."user_kajona")." WHERE user_id=?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {

            $this->setStrEmail($arrRow["user_email"]);
            $this->setStrForename($arrRow["user_forename"]);
            $this->setStrName($arrRow["user_name"]);
            $this->setStrStreet($arrRow["user_street"]);
            $this->setStrPostal($arrRow["user_postal"]);
            $this->setStrCity($arrRow["user_city"]);
            $this->setStrTel($arrRow["user_tel"]);
            $this->setStrMobile($arrRow["user_mobile"]);
            $this->setLongDate($arrRow["user_date"]);
            $this->setSystemid($arrRow["user_id"]);

            $this->strFinalPass = $arrRow["user_pass"];
        }
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
        $strQuery = "UPDATE "._dbprefix_."user_kajona SET user_id = ? WHERE user_id = ?";
        $this->objDB->_pQuery($strQuery, array($strId, $this->getSystemid()));
        $this->setSystemid($strId);
    }

    /**
     * Updates the current object to the database
     * <b>ATTENTION</b> If you don't want to update the password, set it to "" before!
     *
     * @param bool $strPrevId
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {

        if($this->getSystemid() == "") {
            $strUserid = generateSystemid();
            $this->setSystemid($strUserid);
            $strQuery = "INSERT INTO "._dbprefix_."user_kajona (
                        user_id,
                        user_pass, user_email, user_forename,
                        user_name, 	user_street,
                        user_postal, user_city,
                        user_tel, user_mobile,
                        user_date

                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?)";

            class_logger::getInstance(class_logger::$USERSOURCES)->addLogRow("new kajona user: ".$this->getStrEmail(), class_logger::$levelInfo);

            return $this->objDB->_pQuery($strQuery, array(
                $strUserid,
                $this->getStrPass(),
                $this->getStrEmail(),
                $this->getStrForename(),
                $this->getStrName(),
                $this->getStrStreet(),
                $this->getStrPostal(),
                $this->getStrCity(),
                $this->getStrTel(),
                $this->getStrMobile(),
                $this->getLongDate()
            ));
        }
        else {

            $strQuery = ""; $arrParams = array();

            if($this->getStrPass() != "") {
                $strQuery = "UPDATE "._dbprefix_."user_kajona SET
                        user_pass=?, user_email=?, user_forename=?, user_name=?, user_street=?, user_postal=?, user_city=?, user_tel=?, user_mobile=?,
                        user_date=? WHERE user_id = ?";
                   $arrParams = array(
                        $this->getStrPass(),
                        $this->getStrEmail(), $this->getStrForename(), $this->getStrName(), $this->getStrStreet(), $this->getStrPostal(),
                        $this->getStrCity(), $this->getStrTel(), $this->getStrMobile(), $this->getLongDate(), $this->getSystemid()
                   );

            }
            else {
                $strQuery = "UPDATE "._dbprefix_."user_kajona SET
                        user_email=?, user_forename=?, user_name=?, user_street=?, user_postal=?, user_city=?, user_tel=?, user_mobile=?,
                        user_date=? WHERE user_id = ?";

                $arrParams = array(
                        $this->getStrEmail(), $this->getStrForename(), $this->getStrName(), $this->getStrStreet(), $this->getStrPostal(),
                        $this->getStrCity(), $this->getStrTel(), $this->getStrMobile(), $this->getLongDate(), $this->getSystemid()
                   );

            }

            class_logger::getInstance(class_logger::$USERSOURCES)->addLogRow("updated user ".$this->getStrEmail(), class_logger::$levelInfo);

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
        class_logger::getInstance(class_logger::$USERSOURCES)->addLogRow("deleted user with id ".$this->getSystemid(), class_logger::$levelInfo);
        $this->deleteAllUserMemberships();
        $strQuery = "DELETE FROM "._dbprefix_."user_kajona WHERE user_id=?";
        //call other models that may be interested
        $bitDelete = $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
        class_core_eventdispatcher::notifyRecordDeletedListeners($this->getSystemid());
        return $bitDelete;
    }

    /**
     * Deletes the current object from the system
     * @return bool
     */
    public function deleteObject() {
        return $this->deleteUser();
    }

    /**
     * Deletes the current object from the system.
     * Overwrite this method in order to remove the current object from the system.
     * The system-record itself is being delete automatically.
     *
     * @return bool
     */
    protected function deleteObjectInternal() {
        return false;
    }


    /**
     * Returns a list of tables the current object is persisted to.
     * A new record is created in each table, as soon as a save-/update-request was triggered by the framework.
     * The array should contain the name of the table as the key and the name
     * of the primary-key (so the column name) as the matching value.
     * E.g.: array(_dbprefix_."pages" => "page_id)
     *
     * @return array [table => primary row name]
     */
    protected function getObjectTables() {
        return array();
    }


    /**
	 * Deletes all memberships of the given USER from ALL groups
	 *
	 * @return bool
	 * @static
	 */
	private function deleteAllUserMemberships() {
        $strQuery = "DELETE FROM "._dbprefix_."user_kajona_members WHERE group_member_user_kajona_id=?";
		return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
	}

    /**
     * Indicates if the current users' password may be reset, e.g. via a password-forgotten mail
     * @return bool
     */
    public function isPasswortResetable() {
        return true;
    }

    /**
	 * Returns the list of editable fields
	 * @return class_usersources_form_entry $arrParams
	 */
	public function getEditFormEntries() {
        $arrTemp = array();
        if($this->getSystemid() == "")
            $arrTemp[] = new class_usersources_form_entry("password", class_usersources_form_entry::$INT_TYPE_PASSWORD, "", true );
        else
            $arrTemp[] = new class_usersources_form_entry("password", class_usersources_form_entry::$INT_TYPE_PASSWORD, "", false );
        $arrTemp[] = new class_usersources_form_entry("email", class_usersources_form_entry::$INT_TYPE_EMAIL, $this->getStrEmail(), true );
        $arrTemp[] = new class_usersources_form_entry("forename", class_usersources_form_entry::$INT_TYPE_TEXT, $this->getStrForename(), false );
        $arrTemp[] = new class_usersources_form_entry("name", class_usersources_form_entry::$INT_TYPE_TEXT, $this->getStrName(), false );
        $arrTemp[] = new class_usersources_form_entry("street", class_usersources_form_entry::$INT_TYPE_TEXT, $this->getStrStreet(), false );
        $arrTemp[] = new class_usersources_form_entry("postal", class_usersources_form_entry::$INT_TYPE_TEXT, $this->getStrPostal(), false );
        $arrTemp[] = new class_usersources_form_entry("city", class_usersources_form_entry::$INT_TYPE_TEXT, $this->getStrCity(), false );
        $arrTemp[] = new class_usersources_form_entry("tel", class_usersources_form_entry::$INT_TYPE_TEXT, $this->getStrTel(), false );
        $arrTemp[] = new class_usersources_form_entry("mobile", class_usersources_form_entry::$INT_TYPE_TEXT, $this->getStrMobile(), false );
        $arrTemp[] = new class_usersources_form_entry("date", class_usersources_form_entry::$INT_TYPE_DATE, $this->getLongDate(), false );

        return $arrTemp;
    }

    /**
     * Returns the list of group-ids the current user is assigned to
     * @return array
     */
	public function getGroupIdsForUser() {
        $strQuery = "SELECT group_id
                       FROM "._dbprefix_."user_group,
                            "._dbprefix_."user_kajona_members
                      WHERE group_member_user_kajona_id= ?
                        AND group_id = group_member_group_kajona_id
                   ORDER BY group_name ASC  ";

        $arrIds = $this->objDB->getPArray($strQuery, array($this->getSystemid()));

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = $arrOneId["group_id"];

        return $arrReturn;
    }

    /**
     * Indicates if the current user is editable or read-only
     * @return bool
     */
	public function isEditable() {
        return true;
    }

    /**
     * Sets the list of fields in order to be written to the database
     * @param class_admin_form_entry $arrParams
     */
	public function setEditFormEntries($arrParams) {
        foreach($arrParams as $objOneParam) {
            if($objOneParam->getStrName() == "password")
                $this->setStrPass($objOneParam->getStrValue());
            if($objOneParam->getStrName() == "email")
                $this->setStrEmail($objOneParam->getStrValue());
            if($objOneParam->getStrName() == "forename")
                $this->setStrForename($objOneParam->getStrValue());
            if($objOneParam->getStrName() == "name")
                $this->setStrName($objOneParam->getStrValue());
            if($objOneParam->getStrName() == "street")
                $this->setStrStreet($objOneParam->getStrValue());
            if($objOneParam->getStrName() == "postal")
                $this->setStrPostal($objOneParam->getStrValue());
            if($objOneParam->getStrName() == "city")
                $this->setStrCity($objOneParam->getStrValue());
            if($objOneParam->getStrName() == "tel")
                $this->setStrTel($objOneParam->getStrValue());
            if($objOneParam->getStrName() == "mobile")
                $this->setStrMobile($objOneParam->getStrValue());
            if($objOneParam->getStrName() == "date")
                $this->setLongDate($objOneParam->getStrValue());
        }
    }

    /**
     * Writes a single field to the user
     * @param string $strName
     * @param string $strValue
     */
    public function setField($strName, $strValue) {
        if($strName == "password")
            $this->setStrPass($strValue);
        if($strName == "email")
            $this->setStrEmail($strValue);
        if($strName == "forename")
            $this->setStrForename($strValue);
        if($strName == "name")
            $this->setStrName($strValue);
        if($strName == "street")
            $this->setStrStreet($strValue);
        if($strName == "postal")
            $this->setStrPostal($strValue);
        if($strName == "city")
            $this->setStrCity($strValue);
        if($strName == "tel")
            $this->setStrTel($strValue);
        if($strName == "mobile")
            $this->setStrMobile($strValue);
        if($strName == "date")
            $this->setLongDate($strValue);
    }



    // --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrPass() {
        return $this->strPass;
    }
    public function getStrEmail() {
        return $this->strEmail;
    }
    public function getStrForename() {
        return $this->strForename;
    }
    public function getStrName() {
        return $this->strName;
    }

    /**
     * The immutable password from the database.
     * @return type
     */
    public function getStrFinalPass() {
        return $this->strFinalPass;
    }



    public function setStrPass($strPass) {
        if(trim($strPass) != "")
            $this->strPass = class_usersources_source_kajona::encryptPassword($strPass);
    }

    public function setStrEmail($strEmail) {
        $this->strEmail = $strEmail;
    }

    public function setStrForename($strForename) {
        $this->strForename = $strForename;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function getStrStreet() {
        return $this->strStreet;
    }

    public function setStrStreet($strStreet) {
        $this->strStreet = $strStreet;
    }

    public function getStrPostal() {
        return $this->strPostal;
    }

    public function setStrPostal($strPostal) {
        $this->strPostal = $strPostal;
    }

    public function getStrCity() {
        return $this->strCity;
    }

    public function setStrCity($strCity) {
        $this->strCity = $strCity;
    }

    public function getStrTel() {
        return $this->strTel;
    }

    public function setStrTel($strTel) {
        $this->strTel = $strTel;
    }

    public function getStrMobile() {
        return $this->strMobile;
    }

    public function setStrMobile($strMobile) {
        $this->strMobile = $strMobile;
    }

    public function getLongDate() {
        return $this->longDate;
    }

    public function setLongDate($longDate) {
        $this->longDate = $longDate;
    }



}
