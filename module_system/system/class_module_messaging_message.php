<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Model for a single message, emitted by the messaging subsytem.
 * Each message is directed to a single user.
 * On message creation, the current date is set as the sent-date.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_messaging
 * @targetTable messages.message_id
 *
 * @module messaging
 * @moduleId _messaging_module_id_
 *
 * @formGenerator class_module_messaging_message_formgenerator
 */
class class_module_messaging_message extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn messages.message_user
     * @tableColumnDatatype char20
     * @tableColumnIndex
     * @fieldType user
     * @fieldLabel message_to
     * @fieldMandatory
     */
    private $strUser = "";

    /**
     * @var string
     * @tableColumn messages.message_title
     * @tableColumnDatatype char254
     * @fieldType text
     * @fieldLabel message_subject
     * @fieldMandatory
     *
     * @addSearchIndex
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn messages.message_body
     * @tableColumnDatatype longtext
     * @fieldType textarea
     * @fieldLabel message_body
     * @fieldMandatory
     *
     * @addSearchIndex
     */
    private $strBody = "";


    /**
     * @var bool
     * @tableColumn messages.message_read
     * @tableColumnDatatype int
     * @tableColumnIndex
     */
    private $bitRead = 0;



    /**
     * @var string
     * @tableColumn messages.message_internalidentifier
     * @tableColumnDatatype char254
     */
    private $strInternalIdentifier = "";

    /**
     * @var string
     * @tableColumn messages.message_provider
     * @tableColumnDatatype char254
     */
    private $strMessageProvider = "";

    /**
     * @var string
     * @tableColumn messages.message_sender
     * @tableColumnDatatype char20
     */
    private $strSenderId = "";

    /**
     * @var string
     * @tableColumn messages.message_messageref
     * @tableColumnDatatype char20
     * @fieldType hidden
     */
    private $strMessageRefId = "";


    /**
     * @return bool
     */
    public function rightView() {
        return parent::rightView() && $this->getStrUser() == $this->objSession->getUserID();
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        if($this->getStrTitle() != "")
            return uniStrTrim($this->getStrTitle(), 70);

        return uniStrTrim($this->getStrBody(), 70);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        if($this->getBitRead())
            return "icon_mail";
        else
            return "icon_mailNew";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return dateToString($this->getObjDate());
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        $strHandlerName = $this->getStrMessageProvider();
        if($strHandlerName == "")
            return "";

        /** @var $objHandler interface_messageprovider */
        $objHandler = new $strHandlerName();
        return $objHandler->getStrName();
    }


    /**
     * Returns an array of all messages available for a single user
     *
     * @param string $strUserid
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return class_module_messaging_message[]
     * @static
     */
    public static function getObjectList($strUserid = "", $intStart = null, $intEnd = null) {
        if($strUserid == "")
            $strUserid = class_carrier::getInstance()->getObjSession()->getUserID();

        $objOrm = new class_orm_objectlist();
        $objOrm->addWhereRestriction(new class_orm_objectlist_property_restriction("strUser", class_orm_comparator_enum::Equal(), $strUserid));
        $objOrm->addOrderBy(new class_orm_objectlist_orderby("message_read ASC"));
        $objOrm->addOrderBy(new class_orm_objectlist_orderby("system_create_date DESC"));
        return $objOrm->getObjectList(__CLASS__, "", $intStart, $intEnd);
    }


    /**
     * Returns an array of all messages matching the passed identifier
     *
     * @param string $strIdentifier
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return class_module_messaging_message[]
     * @static
     */
    public static function getMessagesByIdentifier($strIdentifier, $intStart = null, $intEnd = null) {
        $objOrm = new class_orm_objectlist();
        $objOrm->addWhereRestriction(new class_orm_objectlist_property_restriction("strInternalIdentifier", class_orm_comparator_enum::Equal(), $strIdentifier));
        $objOrm->addOrderBy(new class_orm_objectlist_orderby("message_read ASC"));
        $objOrm->addOrderBy(new class_orm_objectlist_orderby("system_create_date DESC"));
        return $objOrm->getObjectList(__CLASS__, $intStart, $intEnd);
    }



    /**
     * Returns the number of messages for a single user - ignoring the messages states.
     *
     * @param string $strUserid
     * @param bool $bitOnlyUnread
     *
     * @return int
     */
    public static function getNumberOfMessagesForUser($strUserid, $bitOnlyUnread = false) {
        $objOrm = new class_orm_objectlist();
        $objOrm->addWhereRestriction(new class_orm_objectlist_property_restriction("strUser", class_orm_comparator_enum::Equal(), $strUserid));
        if($bitOnlyUnread)
            $objOrm->addWhereRestriction(new class_orm_objectlist_restriction("AND (message_read IS NULL OR message_read = 0 )"));

        return $objOrm->getObjectCount(__CLASS__);
    }


    /**
     * @param boolean $bitRead
     * @return void
     */
    public function setBitRead($bitRead) {
        $this->bitRead = $bitRead;

    }

    /**
     * @return boolean
     */
    public function getBitRead() {
        return $this->bitRead;
    }

    /**
     * @param string $strBody
     * @return void
     */
    public function setStrBody($strBody) {
        $this->strBody = $strBody;
    }

    /**
     * @return string
     */
    public function getStrBody() {
        return $this->strBody;
    }

    /**
     * @param string $strInternalIdentifier
     * @return void
     */
    public function setStrInternalIdentifier($strInternalIdentifier) {
        $this->strInternalIdentifier = $strInternalIdentifier;
    }

    /**
     * @return string
     */
    public function getStrInternalIdentifier() {
        return $this->strInternalIdentifier;
    }

    /**
     * @param string $strUser
     * @return void
     */
    public function setStrUser($strUser) {
        $this->strUser = $strUser;
    }

    /**
     * @return string
     */
    public function getStrUser() {
        return $this->strUser;
    }

    /**
     * @return \class_date
     */
    public function getObjDate() {
        return $this->getObjCreateDate();
    }

    /**
     * @param string $strMessageProvider
     * @return void
     */
    public function setStrMessageProvider($strMessageProvider) {
        $this->strMessageProvider = $strMessageProvider;
    }

    /**
     * @param interface_messageprovider $objMessageProvider
     * @return void
     */
    public function setObjMessageProvider(interface_messageprovider $objMessageProvider) {
        $this->strMessageProvider = get_class($objMessageProvider);
    }

    /**
     * @return string
     */
    public function getStrMessageProvider() {
        return $this->strMessageProvider;
    }

    /**
     * @return interface_messageprovider
     */
    public function getObjMessageProvider() {
        if($this->strMessageProvider != "")
            return new $this->strMessageProvider();
        else
            return null;
    }

    /**
     * @param string $strTitle
     * @return void
     */
    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    /**
     * @return string
     */
    public function getStrTitle() {
        return $this->strTitle;
    }

    /**
     * @param string $strSenderId
     * @return void
     */
    public function setStrSenderId($strSenderId) {
        $this->strSenderId = $strSenderId;
    }

    /**
     * @return string
     */
    public function getStrSenderId() {
        return $this->strSenderId;
    }

    /**
     * @param string $strMessageRefId
     * @return void
     */
    public function setStrMessageRefId($strMessageRefId) {
        $this->strMessageRefId = $strMessageRefId;
    }

    /**
     * @return string
     */
    public function getStrMessageRefId() {
        return $this->strMessageRefId;
    }


}
