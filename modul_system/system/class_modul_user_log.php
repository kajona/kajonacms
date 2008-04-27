<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_user_log.php                                                                            *
* 	Model for the user-login-log                                                                        *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");

/**
 * Model for a user-login-log
 *
 * @package modul_system
 */
class class_modul_user_log extends class_model implements interface_model  {

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_user";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _user_modul_id_;
		$arrModul["table"]       		= _dbprefix_."user_log";
		$arrModul["modul"]				= "user";

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

    }

    /**
     * Updates the current object to the database
     *
     */
    public function updateObjectToDb() {

    }

    /**
     * Generates a login-log-entry
     *
     * @param int $intStatus
     * @param string $strOtherUsername
     * @return bool
     * @static
     */
    public static function generateLog($intStatus = 1, $strOtherUsername = "") {
		$strQuery = "INSERT INTO "._dbprefix_."user_log
						(user_log_id, user_log_userid, user_log_date, user_log_status, user_log_ip) VALUES
						('".dbsafeString(generateSystemid())."',";

        if($strOtherUsername == "") {
			$strQuery .= "'".(class_carrier::getInstance()->getObjSession()->getSession("userid") == "" ? "0" : dbsafeString(class_carrier::getInstance()->getObjSession()->getSession("userid")))."'";
        }
		else {
		    $strQuery .= "'".dbsafeString($strOtherUsername)."'";
		}

		$strQuery .= 	",".(int)time().",
						".(int)$intStatus.",
						'".dbsafeString(getServer("REMOTE_ADDR"))."' ) ";
		return class_carrier::getInstance()->getObjDB()->_query($strQuery);
    }

    /**
     * Returns all login-logs as an array
     *
     * @return mixed
     * @static
     */
    public static function getLoginLogs() {
        $strQuery = "SELECT *
						FROM "._dbprefix_."user_log as log
							LEFT JOIN "._dbprefix_."user as user
								ON log.user_log_userid = user.user_id
						ORDER BY log.user_log_date DESC";
		return class_carrier::getInstance()->getObjDB()->getArray($strQuery);
    }
    
    /**
     * Returns the number of logins written to the log
     *
     * @return int
     */
    public function getLoginLogsCount() {
        $strQuery = "SELECT COUNT(*)
						FROM "._dbprefix_."user_log as log";
		$arrRow = $this->objDB->getRow($strQuery);
		
		return $arrRow["COUNT(*)"];
    }
    
    /**
     * Returns a section of the login-logs as an array
     *
     * @param int $intStart
     * @param int $intEnd
     * @return mixed
     * @static
     */
    public function getLoginLogsSection($intStart, $intEnd) {
        $strQuery = "SELECT *
						FROM "._dbprefix_."user_log as log
							LEFT JOIN "._dbprefix_."user as user
								ON log.user_log_userid = user.user_id
						ORDER BY log.user_log_date DESC";
        
		return $this->objDB->getArraySection($strQuery, $intStart, $intEnd);
    }
}
?>