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
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Class to represent an archive, used to store download-files and folders
 *
 * @package modul_downloads
 */
class class_modul_downloads_archive extends class_model implements interface_model  {

    private $strPath = "";
    private $strTitle = "";


   /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_downloads";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _downloads_modul_id_;
		$arrModul["table"]       		= _dbprefix_."downloads_archive";
		$arrModul["modul"]				= "downloads";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    public function initObject() {
        $strQuery= "SELECT * FROM "._dbprefix_."system,
		                            ".$this->arrModule["table"]."
						WHERE system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'
						  AND system_id = archive_id";
        $arrResult = $this->objDB->getRow($strQuery);
        $this->setPath($arrResult["archive_path"]);
        $this->setTitle($arrResult["archive_title"]);
    }

    /**
     * Creates a new record in the database using the current object
     *
     * @return bool
     */
    public function saveObjectToDb() {
        //start tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
		//system-tables
		$strArchiveID = $this->createSystemRecord(0, "dl Archive: ".$this->getTitle());
		$this->setSystemid($strArchiveID);
		class_logger::getInstance()->addLogRow("new dl-archive ".$this->getSystemid(), class_logger::$levelInfo);
		//and the gall itself
		$strQuery = "INSERT INTO ".$this->arrModule["table"]."
		            (archive_id, archive_title, archive_path) VALUES
		            ('".$this->objDB->dbsafeString($strArchiveID)."', '".$this->objDB->dbsafeString($this->getTitle())."', '".$this->objDB->dbsafeString($this->getPath())."')";
		if(!$this->objDB->_query($strQuery))
		    $bitCommit = false;

		if($bitCommit) {
		    $this->objDB->transactionCommit();
		    return true;
		}
		else {
		    $this->objDB->transactionRollback();
		    return false;
		}
    }

    public function updateObjectToDB() {
        class_logger::getInstance()->addLogRow("updated dl-archive ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                     SET archive_title = '".$this->objDB->dbsafeString($this->getTitle())."',
                         archive_path = '".$this->objDB->dbsafeString($this->getPath())."'
                     WHERE archive_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }

    /**
	 * Loads all archives from db
	 *
	 * @return mixed
	 * @static
	 */
	public static function getAllArchives() {
		$strQuery= "SELECT system_id FROM "._dbprefix_."system,
		                                  "._dbprefix_."downloads_archive
						WHERE system_id = archive_id
						  ORDER BY archive_title ASC";
		$objDB = class_carrier::getInstance()->getObjDB();

		$arrIds =  $objDB->getArray($strQuery);
		$arrReturn = array();
		foreach ($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_downloads_archive($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Deletes an archive and all childs recursive
	 *
	 * @param string $strPrevId
	 * @param bool $bitIgnoreRights
	 * @static s
	 * @return bool
	 */
	public static function deleteArchiveRecursive($strPrevId, $bitIgnoreRights = false) {
		$bitReturn = true;
		$objRoot = new class_modul_system_common();

		//Load the current level
		$arrFolder = class_modul_downloads_file::getFolderLevel($strPrevId);

		//Call us foreach folder
		if(count($arrFolder) > 0) {
			foreach($arrFolder as $objOneFolder) {
				if(!class_modul_downloads_archive::deleteArchiveRecursive($objOneFolder->getSystemid())) {
					$bitReturn = false;
					break;
				}
			}
		}

		//Delete folders & files
		if(count($arrFolder) > 0 && $bitReturn) {
			foreach ($arrFolder as $objOneFolder)
				if(class_carrier::getInstance()->getObjRights()->rightDelete($objOneFolder->getSystemid()) || $bitIgnoreRights)
				    class_modul_downloads_file::deleteRecord($objOneFolder->getSystemid());
		}

		$arrFiles = class_modul_downloads_file::getFilesDB($strPrevId, true);
		if(count($arrFiles) > 0 && $bitReturn) {
			foreach($arrFiles as $objOneFile)
				if(class_carrier::getInstance()->getObjRights()->rightDelete($objOneFile->getSystemid()) || $bitIgnoreRights)
				    class_modul_downloads_file::deleteRecord($objOneFile->getSystemid());
		}
		return $bitReturn;
	}

	/**
	 * Deletes an archive. CAUTION: NOT the contents, invoke deleteArchiveRecursive()
	 * before!
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public static function deleteArchive($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted dl-archive ".$strSystemid, class_logger::$levelInfo);
	    $strQuery = "DELETE FROM "._dbprefix_."downloads_archive
					WHERE archive_id='".dbsafeString($strSystemid)."'";
	    $objDB = class_carrier::getInstance()->getObjDB();
	    $objRoot = new class_modul_system_common();
	    if($objDB->_query($strQuery)) {
	       if($objRoot->deleteSystemRecord($strSystemid)) {
	           return true;
	       }
	    }

	    return false;
	}


// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getPath() {
        return $this->strPath;
    }
    public function getTitle() {
        return $this->strTitle;
    }
    public function setPath($strPath) {
        $this->strPath = $strPath;
    }
    public function setTitle($strTitle) {
        $this->strTitle = $strTitle;
    }
}

?>