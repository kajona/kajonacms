<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");

/**
 * Model for a single session. Session are managed by class_session, so there should be no need
 * to create instances directly. 
 *
 * @package modul_system
 */
class class_modul_system_session extends class_model implements interface_model  {

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
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_system";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["table"]       		= _dbprefix_."session";
		$arrModul["modul"]				= "system";

		$this->strLoginstatus = self::$LOGINSTATUS_LOGGEDOUT;
		
		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        
        class_logger::getInstance()->addLogRow("init session ".$this->getSystemid(), class_logger::$levelInfo);
        $strQuery = "SELECT * FROM "._dbprefix_."session
                     WHERE session_id = '".dbsafeString($this->getSystemid())."'";
        $arrRow = $this->objDB->getRow($strQuery);

        if(count($arrRow) > 1) {
            $this->setStrPHPSessionId($arrRow["session_phpid"]) ;
            $this->setStrUserid($arrRow["session_userid"]) ;
            $this->setStrGroupids($arrRow["session_groupids"]) ;
            $this->setIntReleasetime($arrRow["session_releasetime"]) ;
            $this->setStrLoginstatus($arrRow["session_loginstatus"]) ;
            $this->setStrLoginprovider($arrRow["session_loginprovider"]) ;
            $this->setStrLasturl($arrRow["session_lasturl"]) ;
            
            $this->bitValid = true;
        }
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated session ".$this->getSystemid(), class_logger::$levelInfo);
        
        $strQuery = "UPDATE ".$this->arrModule["table"]." SET
                      session_phpid =  '".dbsafeString($this->getStrPHPSessionId())."',
                      session_userid = '".dbsafeString($this->getStrUserid())."',
                      session_groupids =  '".dbsafeString($this->getStrGroupids())."',
                      session_releasetime =  ".(int)dbsafeString($this->getIntReleasetime()).",
                      session_loginstatus = '".dbsafeString($this->getStrLoginstatus())."',
                      session_loginprovider = '".dbsafeString($this->getStrLoginprovider())."', 
                      session_lasturl =  '".dbsafeString($this->getStrLasturl())."'
                    WHERE session_id = '".dbsafeString($this->getSystemid())."' ";
        
		return $this->objDB->_query($strQuery);
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    public function saveObjectToDb() {
		//Start tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
		$strInternalSessionId = generateSystemid();
        //Create System-Records
        $this->setSystemid($strInternalSessionId);
        class_logger::getInstance()->addLogRow("new session ".$this->getSystemid(), class_logger::$levelInfo);
        
        //insert in session table
        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                     (session_id, 
                      session_phpid, 
                      session_userid, 
                      session_groupids, 
                      session_releasetime,
                      session_loginstatus, 
                      session_loginprovider, 
                      session_lasturl
                      ) VALUES (
                      '".dbsafeString($strInternalSessionId)."', 
                      '".dbsafeString($this->getStrPHPSessionId())."', 
                      '".dbsafeString($this->getStrUserid())."',
                      '".dbsafeString($this->getStrGroupids())."',
                      ".(int)dbsafeString($this->getIntReleasetime()).",
                      '".dbsafeString($this->getStrLoginstatus())."',
                      '".dbsafeString($this->getStrLoginprovider())."',
                      '".dbsafeString($this->getStrLasturl())."')";
        
		if(!$this->objDB->_query($strQuery))
		    $bitCommit = false;


		//End tx
		if($bitCommit) {
			$this->objDB->transactionCommit();
			$this->bitValid = true;
			return true;
		}
		else {
			$this->objDB->transactionRollback();
			return false;
		}
    }
   

    /**
     * Deletes the current object from the database
     *
     * @return bool
     */
    public function deleteObject() {
        //Start tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
        class_logger::getInstance()->addLogRow("deleted session ".$this->getSystemid(), class_logger::$levelInfo);
        //start with the modul-table
        $strQuery = "DELETE FROM ".$this->arrModule["table"]." WHERE session_id = '".dbsafeString($this->getSystemid())."'";
		if(!$this->objDB->_query($strQuery))
		    $bitCommit = false;

		//End tx
		if($bitCommit) {
			$this->objDB->transactionCommit();
			return true;
		}
		else {
			$this->objDB->transactionRollback();
			return false;
		}
    }
    
    /**
     * Returns, if available, the internal session-object for the passed internal session-id
     *
     * @param sring $strSessionid
     * @return class_modul_system_session
     */
    public static function getSessionById($strSessionid) {
        $objSession = new class_modul_system_session($strSessionid);
        if($objSession->isSessionValid())
            return $objSession;
        else 
            return null;    
    }
    
    
    /**
     * Returns, if available, the internal session-object for the passed internal session-id
     *
     * @param sring $strSessionid
     * @return array
     */
    public static function getAllActiveSessions() {
        $arrIds = class_carrier::getInstance()->getObjDB()->getArray("SELECT session_id FROM "._dbprefix_."session WHERE session_releasetime > ".(int)time());
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_modul_system_session($arrOneId["session_id"]);
            
        return $arrReturn;    
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
        $strSql = "DELETE FROM "._dbprefix_."session WHERE session_releasetime < ".(int)time()."";
        return class_carrier::getInstance()->getObjDB()->_query($strSql);
    }
    
    public function isSessionValid() {
        return $this->bitValid && $this->getIntReleasetime() > time();        
    }
    

// --- GETTERS / SETTERS --------------------------------------------------------------------------------
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
?>