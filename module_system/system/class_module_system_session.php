<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Model for a single session. Session are managed by class_session, so there should be no need
 * to create instances directly.
 * Session-Entries have are not reflected by a systemrecord
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_module_system_session extends class_model implements interface_model {

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

        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);

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

     */
    protected function initObjectInternal() {

        class_logger::getInstance()->addLogRow("init session ".$this->getSystemid(), class_logger::$levelInfo);
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

        if($this->getSystemid() == "") {
            $strInternalSessionId = generateSystemid();
            $this->setSystemid($strInternalSessionId);

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
                    $strInternalSessionId,
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
     * @return array
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

    public function isSessionValid() {
        return $this->bitValid && $this->getIntReleasetime() > time();
    }


    public function setStrPHPSessionId($strPHPSessId) {
        $this->strPHPSessionId = $strPHPSessId;
    }

    public function setStrUserid($strUserid) {
        $this->strUserid = $strUserid;
    }

    public function setStrGroupids($strGroupids) {
        $this->strGroupids = $strGroupids;
    }

    public function setIntReleasetime($intReleasetime) {
        $this->intReleasetime = $intReleasetime;
    }

    public function setStrLoginprovider($strLoginprovider) {
        $this->strLoginprovider = $strLoginprovider;
    }

    public function setStrLasturl($strLasturl) {
        //limit to 255 chars
        $this->strLasturl = uniStrTrim($strLasturl, 450, "");
    }

    public function setStrLoginstatus($strLoginstatus) {
        $this->strLoginstatus = $strLoginstatus;
    }

    public function getStrPHPSessionId() {
        return $this->strPHPSessionId;
    }

    public function getStrUserid() {
        return $this->strUserid;
    }

    public function getStrGroupids() {
        return $this->strGroupids;
    }

    public function getIntReleasetime() {
        return $this->intReleasetime;
    }

    public function getStrLoginprovider() {
        return $this->strLoginprovider;
    }

    public function getStrLasturl() {
        return $this->strLasturl;
    }

    public function getStrLoginstatus() {
        return $this->strLoginstatus;
    }

}
