<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Eventmanager\System;
use Kajona\Eventmanager\System\Messageproviders\MessageproviderEventmanager;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Link;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\MessagingMessagehandler;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistRestriction;
use Kajona\System\System\Rights;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\UserGroup;
use Kajona\System\System\UserUser;
use Kajona\System\System\VersionableInterface;


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
 *
 * @formGenerator Kajona\Eventmanager\Admin\EventmanagerParticipantFormgenerator
 */
class EventmanagerParticipant extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, VersionableInterface, AdminListableInterface  {

    /**
     * @var string
     * @tableColumn em_participant.em_pt_forename
     * @tableColumnDatatype char254
     * @versionable
     * @addSearchIndex
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     * @fieldLabel participant_forename
     *
     * @addSearchIndex
     */
    private $strForename = "";

    /**
     * @var string
     * @tableColumn em_participant.em_pt_lastname
     * @tableColumnDatatype char254
     * @versionable
     * @addSearchIndex
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     * @fieldLabel participant_lastname
     *
     * @addSearchIndex
     */
    private $strLastname = "";

    /**
     * @var string
     * @tableColumn em_participant.em_pt_email
     * @tableColumnDatatype char254
     * @versionable
     * @listOrder
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldValidator Kajona\System\System\Validators\EmailValidator
     * @fieldMandatory
     * @fieldLabel participant_email
     *
     * @addSearchIndex
     */
    private $strEmail = "";

    /**
     * @var string
     * @tableColumn em_participant.em_pt_phone
     * @tableColumnDatatype char254
     * @versionable
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel participant_phone
     *
     * @addSearchIndex
     */
    private $strPhone = "";

    /**
     * @var string
     * @tableColumn em_participant.em_pt_userid
     * @tableColumnDatatype char20
     * @versionable
     *
     * @fieldType user
     * @fieldLabel participant_userid
     */
    private $strUserId = "";

    /**
     * @var int
     *
     * @tableColumn em_participant.em_pt_status
     * @tableColumnDatatype int
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
     * @tableColumnDatatype text
     * @versionable
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryTextarea
     * @fieldLabel participant_comment
     *
     * @addSearchIndex
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
            $objUser = new UserUser($this->getStrUserId());
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
        if($strAction == SystemChangelog::$STR_ACTION_EDIT)
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
     * @param string $strUserid
     * @param string $strEventId
     *
     * @return EventmanagerParticipant
     */
    public static function getParticipantByUserid($strUserid, $strEventId) {

        $objOrm = new OrmObjectlist();
        $objOrm->addWhereRestriction(new OrmObjectlistRestriction("AND em_pt_userid = ?", array($strUserid)));
        return $objOrm->getSingleObject(get_called_class(), $strEventId);
    }


    /**
     * @param string $strEventId
     *
     * @return int
     */
    public static function getActiveParticipantsCount($strEventId) {
        $objOrm = new OrmObjectlist();
        $objOrm->addWhereRestriction(new OrmObjectlistRestriction("AND em_pt_status != 2", array()));
        return $objOrm->getObjectCount(get_called_class(), $strEventId);
    }


    /**
     * @return bool
     */
    protected function onInsertToDb() {

        //send a message to all registered editors
        $objEvent = new EventmanagerEvent($this->getStrPrevId());

        $strMailtext = $this->getLang("new_participant_mail")."\n\n";
        $strMailtext .= $this->getLang("new_participant_participant")." ".$this->getStrDisplayName()."\n";
        $strMailtext .= $this->getLang("new_participant_event")." ".$objEvent->getStrDisplayName()."\n";
        $strMailtext .= $this->getLang("new_participant_details")." ".Link::getLinkAdminHref("eventmanager", "listParticipant", "&systemid=".$this->getStrPrevId(), false);
        $objMessageHandler = new MessagingMessagehandler();

        $arrGroups = array();
        $allGroups = UserGroup::getObjectList();
        foreach($allGroups as $objOneGroup) {
            if(Rights::getInstance()->checkPermissionForGroup($objOneGroup->getSystemid(), Rights::$STR_RIGHT_EDIT, $this->getSystemid()))
                $arrGroups[] = $objOneGroup;
        }

        $objMessage = new MessagingMessage();
        $objMessage->setStrBody(strip_tags($strMailtext));
        $objMessage->setObjMessageProvider(new MessageproviderEventmanager());
        $objMessageHandler->sendMessageObject($objMessage, $arrGroups);

        return true;
    }


    /**
     * @return string
     */
    public function getStrForename() {
        return $this->strForename;
    }

    /**
     * @param string $strForename
     * @return void
     */
    public function setStrForename($strForename) {
        $this->strForename = $strForename;
    }

    /**
     * @return string
     */
    public function getStrLastname() {
        return $this->strLastname;
    }

    /**
     * @param string $strLastname
     * @return void
     */
    public function setStrLastname($strLastname) {
        $this->strLastname = $strLastname;
    }

    /**
     * @return string
     */
    public function getStrEmail() {
        if(validateSystemid($this->getStrUserId())) {
            $objUser = new UserUser($this->getStrUserId());
            return $objUser->getStrEmail();
        }
        return $this->strEmail;
    }

    /**
     * @param string $strEmail
     * @return void
     */
    public function setStrEmail($strEmail) {
        $this->strEmail = $strEmail;
    }

    /**
     * @return string
     */
    public function getStrPhone() {
        return $this->strPhone;
    }

    /**
     * @param string $strPhone
     * @return void
     */
    public function setStrPhone($strPhone) {
        $this->strPhone = $strPhone;
    }

    /**
     * @return string
     */
    public function getStrComment() {
        return $this->strComment;
    }

    /**
     * @param string $strComment
     * @return void
     */
    public function setStrComment($strComment) {
        $this->strComment = $strComment;
    }

    /**
     * @param string $strUserId
     * @return void
     */
    public function setStrUserId($strUserId) {
        $this->strUserId = $strUserId;
    }

    /**
     * @return string
     */
    public function getStrUserId() {
        return $this->strUserId;
    }

    /**
     * @param int $intParticipationStatus
     * @return void
     */
    public function setIntParticipationStatus($intParticipationStatus) {
        $this->intParticipationStatus = $intParticipationStatus;
    }

    /**
     * @return int
     */
    public function getIntParticipationStatus() {
        return $this->intParticipationStatus;
    }




}
