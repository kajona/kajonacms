<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
 *
 * @targetTable messages.message_id
 */
class class_module_messaging_message extends class_model implements interface_model, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn message_body
     */
    private $strBody = "";

    /**
     * @var bool
     * @tableColumn message_read
     */
    private $bitRead = false;

    /**
     * @var string
     * @tableColumn message_user
     */
    private $strUser = "";

    /**
     * @var class_date
     */
    private $objDate = null;

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

    protected function initObjectInternal() {
        parent::initObjectInternal();
        $arrInitRow = $this->getArrInitRow();
        $this->setObjDate(new class_date($arrInitRow["system_date_start"]));
    }


    /**
     * When creating a new record, the current date is set as relevant.
     * @return bool
     */
    protected function onInsertToDb() {
        $this->setObjDate(new class_date());
        return $this->createDateRecord($this->getSystemid(), $this->getObjDate());
    }

    /**
     * Updates the record
     * @return bool
     */
    protected function updateStateToDb() {
        $this->updateDateRecord($this->getSystemid(), $this->getObjDate());
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
     * @return string
     */
    public function getStrDisplayName() {
        return uniStrTrim($this->getStrBody(), 50);
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
            return "icon_mailDisabled.gif";
        else
            return "icon_mail.gif";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        return dateToString($this->getObjDate());
    }

    /**
     * If not empty, the returned string is rendered below the common title.
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
     * @return class_module_system_aspect[]
     * @static
     */
    public static function getMessagesForUser($strUserid, $intStart = null, $intEnd = null) {
        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."messages, "._dbprefix_."system, "._dbprefix_."system_date
		            WHERE system_id = message_id
		              AND message_user = ?
		              AND system_date_id = system_id
		         ORDER BY message_read ASC, system_date_start DESC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strUserid), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_messaging_message($arrOneId["system_id"]);

        return $arrReturn;
    }


    /**
     * Returns the number of mesages for a single user - ignoring the messages states.
     *
     * @param string $strUserid
     *
     * @return int
     */
    public static function getNumberOfMessagesForUser($strUserid) {
        $strQuery = "SELECT COUNT(*)
                     FROM "._dbprefix_."messages, "._dbprefix_."system, "._dbprefix_."system_date
		            WHERE system_id = message_id
		              AND message_user = ?
		              AND system_date_id = system_id";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUserid));
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
     * @param \class_date $objDate
     */
    public function setObjDate($objDate) {
        $this->objDate = $objDate;
    }

    /**
     * @return \class_date
     */
    public function getObjDate() {
        return $this->objDate;
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


}
