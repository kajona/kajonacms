<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Model for a single session. Session are managed by class_session, so there should be no need
 * to create instances directly.
 * Session-Entries are not reflected by a systemrecord
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @module system
 * @moduleId _system_modul_id_
 */
class class_module_system_session extends class_model implements interface_model {

    /**
     * Internal session id. used to validate if the current session was already persisted to the database.
     * @var string
     */
    private $strDbSystemid = "";

    public static $LOGINSTATUS_LOGGEDIN = "loggedin";
    public static $LOGINSTATUS_LOGGEDOUT = "loggedout";

    private $strPHPSessionId = "";
    private $strUserid = "";
    private $strGroupids = "";
    private $intReleasetime = 0;
    private $strLoginprovider = "";
    private $strLasturl = "";
    private $strLoginstatus = "";

    private $bitValid = false;


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->strLoginstatus = self::$LOGINSTATUS_LOGGEDOUT;

        //base class
        parent::__construct($strSystemid);

    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getSystemid();
    }


    /**
     * Initalises the current object, if a systemid was given
     * @return void
     */
    protected function initObjectInternal() {

        $strQuery = "SELECT * FROM "._dbprefix_."session WHERE session_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        //avoid useless query, set internal row
        $this->setArrInitRow(array("system_id" => ""));

        if(count($arrRow) > 1) {
            $this->setStrPHPSessionId($arrRow["session_phpid"]);
            $this->setStrUserid($arrRow["session_userid"]);
            $this->setStrGroupids($arrRow["session_groupids"]);
            $this->setIntReleasetime($arrRow["session_releasetime"]);
            $this->setStrLoginstatus($arrRow["session_loginstatus"]);
            $this->setStrLoginprovider($arrRow["session_loginprovider"]);
            $this->setStrLasturl($arrRow["session_lasturl"]);

            $this->strDbSystemid = $this->getSystemid();

            $this->bitValid = true;
        }
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @param bool $strPrevId
     *
     * @return bool
     * @overwrite class_model::updateObjectToDb() due to performance issues
     */
    public function updateObjectToDb($strPrevId = false) {

        $this->bitValid = true;

        if($this->strDbSystemid == "") {
            $this->strDbSystemid = $this->getSystemid();

            //only relevant for special conditions, no usage in real world scenarios since handled by class_session
            if(!validateSystemid($this->strDbSystemid)) {
                $this->strDbSystemid = generateSystemid();
                $this->setSystemid($this->strDbSystemid);
            }

            class_logger::getInstance()->addLogRow("new session ".$this->getSystemid(), class_logger::$levelInfo);

            //insert in session table
            $strQuery = "INSERT INTO "._dbprefix_."session
                         (session_id,
                          session_phpid,
                          session_userid,
                          session_groupids,
                          session_releasetime,
                          session_loginstatus,
                          session_loginprovider,
                          session_lasturl
                          ) VALUES ( ?,?,?,?,?,?,?,? )";

            return $this->objDB->_pQuery(
                $strQuery,
                array(
                    $this->strDbSystemid,
                    $this->getStrPHPSessionId(),
                    $this->getStrUserid(),
                    $this->getStrGroupids(),
                    (int)$this->getIntReleasetime(),
                    $this->getStrLoginstatus(),
                    $this->getStrLoginprovider(),
                    $this->getStrLasturl()
                )
            );

        }
        else {

            class_logger::getInstance()->addLogRow("updated session ".$this->getSystemid(), class_logger::$levelInfo);
            $strQuery = "UPDATE "._dbprefix_."session SET
                          session_phpid = ?,
                          session_userid = ?,
                          session_groupids = ?,
                          session_releasetime = ?,
                          session_loginstatus = ?,
                          session_loginprovider = ?,
                          session_lasturl = ?
                        WHERE session_id = ? ";

            return $this->objDB->_pQuery(
                $strQuery,
                array(
                    $this->getStrPHPSessionId(),
                    $this->getStrUserid(),
                    $this->getStrGroupids(),
                    (int)$this->getIntReleasetime(),
                    $this->getStrLoginstatus(),
                    $this->getStrLoginprovider(),
                    $this->getStrLasturl(),
                    $this->getSystemid()
                )
            );
        }
    }

    /**
     * Called whenever an update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb() {
        return true;
    }


    /**
     * Deletes the current object from the database
     *
     * @return bool
     */
    public function deleteObject() {
        class_logger::getInstance()->addLogRow("deleted session ".$this->getSystemid(), class_logger::$levelInfo);
        //start with the modul-table
        $strQuery = "DELETE FROM "._dbprefix_."session WHERE session_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
    }


    /**
     * Returns, if available, the internal session-object for the passed internal session-id
     *
     * @param string $strSessionid
     *
     * @return class_module_system_session
     */
    public static function getSessionById($strSessionid) {
        $objSession = new class_module_system_session($strSessionid);
        if($objSession->isSessionValid())
            return $objSession;
        else
            return null;
    }


    /**
     * Returns, if available, the internal session-object for the passed internal session-id
     *
     * @param int $intStart
     * @param int $intEnd
     *
     * @return class_module_system_session[]
     */
    public static function getAllActiveSessions($intStart = null, $intEnd = null) {

        $strQuery = "SELECT session_id FROM "._dbprefix_."session WHERE session_releasetime > ? ORDER BY session_releasetime DESC, session_id ASC";
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(time()), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_system_session($arrOneId["session_id"]);

        return $arrReturn;
    }

    /**
     * Returns the number of session currently being active
     *
     * @return int
     */
    public static function getNumberOfActiveSessions() {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."session WHERE session_releasetime > ?";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array(time()));
        return $arrRow["COUNT(*)"];
    }


    /**
     * Returns if the current user has logged in or not
     *
     * @return bool
     */
    public function isLoggedIn() {
        if($this->isSessionValid() && $this->getStrLoginstatus() == self::$LOGINSTATUS_LOGGEDIN)
            return true;
        else
            return false;
    }

    /**
     * Deletes all invalid session-entries from the database
     *
     * @return bool
     */
    public static function deleteInvalidSessions() {
        $strSql = "DELETE FROM "._dbprefix_."session WHERE session_releasetime < ?";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strSql, array(time()));
    }

    /**
     * @return bool
     */
    public function isSessionValid() {
        return $this->bitValid && $this->getIntReleasetime() > time();
    }


    /**
     * @param string $strPHPSessId
     * @return void
     */
    public function setStrPHPSessionId($strPHPSessId) {
        $this->strPHPSessionId = $strPHPSessId;
    }

    /**
     * @param string $strUserid
     * @return void
     */
    public function setStrUserid($strUserid) {
        $this->strUserid = $strUserid;
    }

    /**
     * @param string $strGroupids
     * @return void
     */
    public function setStrGroupids($strGroupids) {
        $this->strGroupids = $strGroupids;
    }

    /**
     * @param int $intReleasetime
     * @return void
     */
    public function setIntReleasetime($intReleasetime) {
        $this->intReleasetime = $intReleasetime;
    }

    /**
     * @param string $strLoginprovider
     * @return void
     */
    public function setStrLoginprovider($strLoginprovider) {
        $this->strLoginprovider = $strLoginprovider;
    }

    /**
     * @param string $strLasturl
     * @return void
     */
    public function setStrLasturl($strLasturl) {
        //limit to 255 chars
        $this->strLasturl = uniStrTrim($strLasturl, 450, "");
    }

    /**
     * @param string $strLoginstatus
     * @return void
     */
    public function setStrLoginstatus($strLoginstatus) {
        $this->strLoginstatus = $strLoginstatus;
    }

    /**
     * @return string
     */
    public function getStrPHPSessionId() {
        return $this->strPHPSessionId;
    }

    /**
     * @return string
     */
    public function getStrUserid() {
        return $this->strUserid;
    }

    /**
     * @return string
     */
    public function getStrGroupids() {
        return $this->strGroupids;
    }

    /**
     * @return int
     */
    public function getIntReleasetime() {
        return $this->intReleasetime;
    }

    /**
     * @return string
     */
    public function getStrLoginprovider() {
        return $this->strLoginprovider;
    }

    /**
     * @return string
     */
    public function getStrLasturl() {
        return $this->strLasturl;
    }

    /**
     * @return string
     */
    public function getStrLoginstatus() {
        return $this->strLoginstatus;
    }

}
