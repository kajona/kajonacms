<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
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
 */
class class_module_messaging_message extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn message_body
     */
    private $strBody = "";

    /**
     * @var string
     * @tableColumn message_title
     */
    private $strTitle = "";

    /**
     * @var bool
     * @tableColumn message_read
     */
    private $bitRead = 0;

    /**
     * @var string
     * @tableColumn message_user
     */
    private $strUser = "";

    /**
     * @var string
     * @tableColumn message_internalidentifier
     */
    private $strInternalIdentifier = "";

    /**
     * @var string
     * @tableColumn message_provider
     */
    private $strMessageProvider = "";


    private $bitOnReadTrigger = false;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "messaging");
        $this->setArrModuleEntry("moduleId", _messaging_module_id_);

        parent::__construct($strSystemid);

    }

    public function rightView() {
        return parent::rightView() && $this->getStrUser() == $this->objSession->getUserID();
    }


    /**
     * Updates the record
     *
     * @return bool
     */
    protected function updateStateToDb() {
        $bitReturn = parent::updateStateToDb();

        if($this->bitOnReadTrigger && $this->getStrMessageProvider() != "") {
            $this->bitOnReadTrigger = false;
            $strHandler = $this->getStrMessageProvider();
            /** @var $objHandler interface_messageprovider */
            $objHandler = new $strHandler();
            $objHandler->onSetRead($this);
        }

        return $bitReturn;
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

        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."messages, "._dbprefix_."system
		            WHERE system_id = message_id
		              AND message_user = ?
		         ORDER BY message_read ASC, system_create_date DESC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strUserid), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_messaging_message($arrOneId["system_id"]);

        return $arrReturn;
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
        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."messages, "._dbprefix_."system
		            WHERE system_id = message_id
		              AND message_internalidentifier = ?
		         ORDER BY message_read ASC, system_create_date DESC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strIdentifier), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_messaging_message($arrOneId["system_id"]);

        return $arrReturn;
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

        $arrParams = array($strUserid);

        $strQuery = "SELECT COUNT(*)
                     FROM "._dbprefix_."messages, "._dbprefix_."system
		            WHERE system_id = message_id
		              AND message_user = ?
		              ".($bitOnlyUnread ? " AND (message_read IS NULL OR message_read = 0 )" : "")."";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }


    /**
     * @param boolean $bitRead
     */
    public function setBitRead($bitRead) {
        if($bitRead === true && $bitRead != $this->bitRead)
            $this->bitOnReadTrigger = true;

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
     */
    public function setStrMessageProvider($strMessageProvider) {
        $this->strMessageProvider = $strMessageProvider;
    }

    /**
     * @return string
     */
    public function getStrMessageProvider() {
        return $this->strMessageProvider;
    }

    /**
     * @param string $strTitle
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


}
