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
 *
 * @module eventmanager
 * @moduleId _eventmanager_module_id_
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
     * @tableColumn em_participant.em_pt_userid
     * @versionable
     *
     * @fieldType user
     * @fieldLabel participant_userid
     */
    private $strUserId = "";

    /**
     * @var int
     *
     * @tableColumn em_pt_status
     * @versionable
     *
     * @fieldType dropdown
     * @fieldLabel participant_status
     * @fieldDDValues [1 => participant_status_1],[2 => participant_status_2],[3 => participant_status_3]
     */
    private $intParticipationStatus = 1;


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
        if(validateSystemid($this->getStrUserId())) {
            $objUser = new class_module_user_user($this->getStrUserId());
            $strName = $objUser->getStrDisplayName();
        }
        else
            $strName = $this->getStrEmail() .( $this->getStrLastname() != "" || $this->getStrForename() != "" ? $this->getStrLastname().", ".$this->getStrForename() : "");

        if($this->getIntParticipationStatus() == 2)
            $strName = "<span style='text-decoration: line-through'>{$strName}</span>";
        if($this->getIntParticipationStatus() == 3)
            $strName = "<span style='font-style: italic'>{$strName}</span>";

        return $strName;
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

    /**
     * @param $strUserid
     * @param $strEventId
     *
     * @return class_module_eventmanager_participant
     */
    public static function getParticipantByUserid($strUserid, $strEventId) {
        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."system,
                            "._dbprefix_."em_participant
                      WHERE system_id = em_pt_id
                        AND system_prev_id = ?
                        AND em_pt_userid = ?";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strEventId, $strUserid));
        if(isset($arrRow["system_id"]))
            return new class_module_eventmanager_participant($arrRow["system_id"]);
        else
            return null;
    }


    /**
     * @param $strEventId
     *
     * @return int
     */
    public static function getActiveParticipantsCount($strEventId) {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."system,
                            "._dbprefix_."em_participant
                      WHERE system_id = em_pt_id
                        AND system_prev_id = ?
                        AND em_pt_status != 2";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strEventId));
        return $arrRow["COUNT(*)"];
    }


    protected function onInsertToDb() {

        //send a message to all registered editors
        $objEvent = new class_module_eventmanager_event($this->getStrPrevId());

        $strMailtext = $this->getLang("new_participant_mail")."\n\n";
        $strMailtext .= $this->getLang("new_participant_participant")." ".$this->getStrDisplayName()."\n";
        $strMailtext .= $this->getLang("new_participant_event")." ".$objEvent->getStrDisplayName()."\n";
        $strMailtext .= $this->getLang("new_participant_details")." ".getLinkAdminHref("eventmanager", "listParticipant", "&systemid=".$this->getStrPrevId(), false);
        $objMessageHandler = new class_module_messaging_messagehandler();

        $arrGroups = array();
        $allGroups = class_module_user_group::getObjectList();
        foreach($allGroups as $objOneGroup) {
            if(class_rights::getInstance()->checkPermissionForGroup($objOneGroup->getSystemid(), class_rights::$STR_RIGHT_EDIT, $this->getSystemid()))
                $arrGroups[] = $objOneGroup;
        }

        $objMessageHandler->sendMessage($strMailtext, $arrGroups, new class_messageprovider_eventmanager());

        return true;
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
        if(validateSystemid($this->getStrUserId())) {
            $objUser = new class_module_user_user($this->getStrUserId());
            return $objUser->getStrEmail();
        }
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

    public function setStrUserId($strUserId) {
        $this->strUserId = $strUserId;
    }

    public function getStrUserId() {
        return $this->strUserId;
    }

    public function setIntParticipationStatus($intParticipationStatus) {
        $this->intParticipationStatus = $intParticipationStatus;
    }

    public function getIntParticipationStatus() {
        return $this->intParticipationStatus;
    }




}
