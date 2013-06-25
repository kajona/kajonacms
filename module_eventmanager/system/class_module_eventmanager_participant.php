<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * Business Object for a single participant, participating at an event.
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 * @targetTable em_participant.em_pt_id
 */
class class_module_eventmanager_participant extends class_model implements interface_model, interface_versionable, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn em_participant.em_pt_forename
     * @versionable
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel participant_forename
     */
    private $strForename = "";

    /**
     * @var string
     * @tableColumn em_participant.em_pt_lastname
     * @versionable
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel participant_lastname
     */
    private $strLastname = "";

    /**
     * @var string
     * @tableColumn em_participant.em_pt_email
     * @versionable
     * @listOrder
     *
     * @fieldType text
     * @fieldValidator email
     * @fieldMandatory
     * @fieldLabel participant_email
     */
    private $strEmail = "";

    /**
     * @var string
     * @tableColumn em_participant.em_pt_phone
     * @versionable
     *
     * @fieldType text
     * @fieldLabel participant_phone
     */
    private $strPhone = "";

    /**
     * @var string
     * @tableColumn em_participant.em_pt_comment
     * @versionable
     *
     * @fieldType textarea
     * @fieldLabel participant_comment
     */
    private $strComment = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "eventmanager");
        $this->setArrModuleEntry("moduleId", _eventmanager_module_id_);
        parent::__construct($strSystemid);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_user";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrEmail() .( $this->getStrLastname() != "" || $this->getStrForename() != "" ? $this->getStrLastname().", ".$this->getStrForename() : "");
    }

    /**
     * Returns a human readable name of the action stored with the changeset.
     *
     * @param string $strAction the technical actionname
     *
     * @return string the human readable name
     */
    public function getVersionActionName($strAction) {
        if($strAction == class_module_system_changelog::$STR_ACTION_EDIT)
            return $this->getLang("participant_edit");

        return $strAction;
    }

    /**
     * Returns a human readable name of the record / object stored with the changeset.
     *
     * @return string the human readable name
     */
    public function getVersionRecordName() {
        return $this->getLang("change_object_participant");
    }

    /**
     * Returns a human readable name of the property-name stored with the changeset.
     *
     * @param string $strProperty the technical property-name
     *
     * @return string the human readable name
     */
    public function getVersionPropertyName($strProperty) {
        return $strProperty;
    }

    /**
     * Renders a stored value. Allows the class to modify the value to display, e.g. to
     * replace a timestamp by a readable string.
     *
     * @param string $strProperty
     * @param string $strValue
     *
     * @return string
     */
    public function renderVersionValue($strProperty, $strValue) {
        return $strValue;
    }


    public function getStrForename() {
        return $this->strForename;
    }

    public function setStrForename($strForename) {
        $this->strForename = $strForename;
    }

    public function getStrLastname() {
        return $this->strLastname;
    }

    public function setStrLastname($strLastname) {
        $this->strLastname = $strLastname;
    }

    public function getStrEmail() {
        return $this->strEmail;
    }

    public function setStrEmail($strEmail) {
        $this->strEmail = $strEmail;
    }

    public function getStrPhone() {
        return $this->strPhone;
    }

    public function setStrPhone($strPhone) {
        $this->strPhone = $strPhone;
    }

    public function getStrComment() {
        return $this->strComment;
    }

    public function setStrComment($strComment) {
        $this->strComment = $strComment;
    }



}
