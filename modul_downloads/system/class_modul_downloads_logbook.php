<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Model for the downloads-logbook
 *
 * @package modul_downloads
 * @author sidler@mulchprod.de
 */
class class_modul_downloads_logbook extends class_model implements interface_model  {

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_downloads";
		$arrModul["moduleId"] 			= _downloads_modul_id_;
		$arrModul["table"]       		= _dbprefix_."downloads_log";
		$arrModul["modul"]				= "downloads";
		//base class
		parent::__construct($arrModul, $strSystemid);

    }


    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array();
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "";
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
    protected function updateStateToDb() {
    }

    /**
	 * Generates an entry in the logbook an increases the hits-counter
	 *
	 * @param class_modul_downloads_file $arrFile
	 * @static
	 */
	public static function generateDlLog($objFile) {
	    $objRoot = new class_modul_system_common();
	    $objDB = class_carrier::getInstance()->getObjDB();
	    $strQuery = "INSERT INTO "._dbprefix_."downloads_log
	                   (downloads_log_id, downloads_log_date, downloads_log_file, downloads_log_user, downloads_log_ip) VALUES
	                   (?, ?, ?, ?, ?)";

		$objDB->_pQuery($strQuery, array($objRoot->generateSystemid(), (int)time(), basename($objFile->getFilename()),
                      class_carrier::getInstance()->getObjSession()->getUsername(), getServer("REMOTE_ADDR")) );

		$strQuery = "UPDATE "._dbprefix_."downloads_file SET downloads_hits = downloads_hits+1 WHERE downloads_id= ?";
		$objDB->_pQuery($strQuery, array($objFile->getSystemid() ));
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
        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

	}
	
	/**
	 * Counts the number of logs available
	 *
	 * @return int
	 */
	public function getLogbookDataCount() {
		$strQuery = "SELECT COUNT(*)
					  FROM "._dbprefix_."downloads_log";
        $arrTemp = $this->objDB->getPRow($strQuery, array());
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
		
        return $this->objDB->getPArraySection($strQuery, array(), $intStart, $intEnd);
	}
	

	/**
	 * Deletes logrecords from db older than the passed timestamp
	 *
	 * @static
	 * @return
	 */
	public static function deleteFromLogs($intOlderDate) {
        $strSql = "DELETE FROM "._dbprefix_."downloads_log
			           WHERE downloads_log_date < ?";

		return class_carrier::getInstance()->getObjDB()->_pQuery($strSql,  array((int)$intOlderDate) );
	}
}

?>