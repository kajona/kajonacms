<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_downloads_logbook.php                                                                   *
* 	Class representing the downloads logbook                                                            *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Model for the downloads-logbook
 *
 * @package modul_downloads
 */
class class_modul_downloads_logbook extends class_model implements interface_model  {

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_downloads";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _downloads_modul_id_;
		$arrModul["table"]       		= _dbprefix_."downloads_log";
		$arrModul["modul"]				= "downloads";
		//base class
		parent::__construct($arrModul, $strSystemid);

    }

    /**
     * not implemented
     *
     */
    public function initObject() {
    }
    /**
     * not implemented
     *
     */
    public function saveObjectToDb($strPrevId) {
    }
    /**
     * not implemented
     *
     */
    public function updateObjectToDB() {
    }

/**
	 * Generates an entry in the logbook an increases the hits-counter
	 *
	 * @param mixed $arrFile
	 * @static
	 */
	public static function generateDlLog($objFile) {
	    $objRoot = new class_modul_system_common();
	    $objDB = class_carrier::getInstance()->getObjDB();
	    $strQuery = "INSERT INTO "._dbprefix_."downloads_log
	                   (downloads_log_id, downloads_log_date, downloads_log_file, downloads_log_user, downloads_log_ip) VALUES
	                   ('".dbsafeString($objRoot->generateSystemid())."','".(int)time()."','".dbsafeString(basename($objFile->getFilename()))."',
	                    '".dbsafeString(class_carrier::getInstance()->getObjSession()->getUsername())."','".dbsafeString(getServer("REMOTE_ADDR"))."')";

		$objDB->_query($strQuery);

		$strQuery = "UPDATE "._dbprefix_."downloads_file SET downloads_hits = downloads_hits+1 WHERE downloads_id='".dbsafeString($objFile->getSystemid())."'";
		$objDB->_query($strQuery);
	}

	/**
	 * Loads the records of the dl-logbook
	 *
	 * @static
	 * @return mixed AS ARRAY
	 */
	public static function getLogbookData() {
		$strQuery = "SELECT *
					  FROM "._dbprefix_."downloads_log
					  ORDER BY downloads_log_date DESC";
        return class_carrier::getInstance()->getObjDB()->getArray($strQuery);

	}
	
	/**
	 * Counts the number of logs available
	 *
	 * @return int
	 */
	public function getLogbookDataCount() {
		$strQuery = "SELECT COUNT(*)
					  FROM "._dbprefix_."downloads_log";
        $arrTemp = $this->objDB->getRow($strQuery);
        return $arrTemp["COUNT(*)"];

	}
	
	/**
	 * Returns a section from the downloads-logbook
	 *
	 * @return int
	 */
	public function getLogbookSection($intStart, $intEnd) {
		$strQuery = "SELECT *
					   FROM "._dbprefix_."downloads_log
			       ORDER BY downloads_log_date DESC";
		
        return $this->objDB->getArraySection($strQuery, $intStart, $intEnd);
	}
	

	/**
	 * Deletes logrecords from db older than the passed timestamp
	 *
	 * @static
	 * @return
	 */
	public static function deleteFromLogs($intOlderDate) {
        $strSql = "DELETE FROM "._dbprefix_."downloads_log
			           WHERE downloads_log_date < '".(int)$intOlderDate."'";

		return class_carrier::getInstance()->getObjDB()->_query($strSql);
	}
}

?>