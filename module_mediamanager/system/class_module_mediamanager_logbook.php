<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_downloads_logbook.php 4026 2011-07-23 18:45:25Z sidler $                              *
********************************************************************************************************/

/**
 * Model for the downloads-logbook
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_module_mediamanager_logbook extends class_model implements interface_model  {

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
		$this->setArrModuleEntry("moduleId", _mediamanager_module_id_);
		$this->setArrModuleEntry("modul", "mediamanager");
		//base class
		parent::__construct($strSystemid);

    }


    /**
     * Generates an entry in the logbook an increases the hits-counter
     *
     * @param \class_module_mediamanager_file $objFile
     */
	public static function generateDlLog(class_module_mediamanager_file $objFile) {
	    $objDB = class_carrier::getInstance()->getObjDB();
	    $strQuery = "INSERT INTO "._dbprefix_."mediamanager_dllog
	                   (downloads_log_id, downloads_log_date, downloads_log_file, downloads_log_user, downloads_log_ip) VALUES
	                   (?, ?, ?, ?, ?)";

		$objDB->_pQuery($strQuery, array(generateSystemid(), (int)time(), basename($objFile->getStrFilename()),
                      class_carrier::getInstance()->getObjSession()->getUsername(), getServer("REMOTE_ADDR")) );

        $objFile->setIntHits($objFile->getIntHits()+1);
        $objFile->updateObjectToDb();
	}

    /**
     * Loads the records of the dl-logbook
     *
     * @static
     *
     * @param null $intStart
     * @param null $intEnd
     *
     * @return mixed AS ARRAY
     */
	public static function getLogbookData($intStart = null, $intEnd = null) {
		$strQuery = "SELECT *
					  FROM "._dbprefix_."mediamanager_dllog
					  ORDER BY downloads_log_date DESC";
        return class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);

	}

	/**
	 * Counts the number of logs available
	 *
	 * @return int
	 */
	public function getLogbookDataCount() {
		$strQuery = "SELECT COUNT(*)
					  FROM "._dbprefix_."mediamanager_dllog";
        $arrTemp = $this->objDB->getPRow($strQuery, array());
        return $arrTemp["COUNT(*)"];

	}


    /**
     * Deletes logrecords from db older than the passed timestamp
     *
     * @static
     *
     * @param $intOlderDate
     *
     * @return bool
     */
	public static function deleteFromLogs($intOlderDate) {
        $strSql = "DELETE FROM "._dbprefix_."mediamanager_dllog
			           WHERE downloads_log_date < ?";

		return class_carrier::getInstance()->getObjDB()->_pQuery($strSql,  array((int)$intOlderDate) );
	}

    /**
     * Returns a list of tables the current object is persisted to.
     * A new record is created in each table, as soon as a save-/update-request was triggered by the framework.
     * The array should contain the name of the table as the key and the name
     * of the primary-key (so the column name) as the matching value.
     * E.g.: array(_dbprefix_."pages" => "page_id)
     *
     * @return array [table => primary row name]
     */
    protected function getObjectTables() {
        return array();
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }
}

