<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Class to represent an archive, used to store download-files and folders
 *
 * @package modul_downloads
 * @author sidler@mulchprod.de
 */
class class_modul_downloads_archive extends class_model implements interface_model  {

    private $strPath = "";
    private $strTitle = "";


   /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_downloads";
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
						WHERE system_id = ?
						  AND system_id = archive_id";
        $arrResult = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
        if(count($arrResult) > 0) {
            $this->setPath($arrResult["archive_path"]);
            $this->setTitle($arrResult["archive_title"]);
        }
    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."downloads_archive" => "archive_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "downloads archive ".$this->getTitle();
    }

    /**
     * @see class_model::onInsertToDb()
     *
     * @return bool
     */
    public function onInsertToDb() {
        //archive was created, create an internal filemanager repo
        $objRepo = new class_modul_filemanager_repo();
        $objRepo->setStrPath($this->getPath());
        $objRepo->setStrForeignId($this->getSystemid());
        $objRepo->setStrName("Internal Repo for DL-Archive ".$this->getSystemid());
        $objRepo->setStrViewFilter("");
        $objRepo->setStrUploadFilter("");
        return $objRepo->updateObjectToDb();
    }

    protected function updateStateToDb() {
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET archive_title = ?,
                            archive_path = ?
                      WHERE archive_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getTitle(), $this->getPath(), $this->getSystemid()));
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

		$arrIds =  $objDB->getPArray($strQuery, array());
		$arrReturn = array();
		foreach ($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_downloads_archive($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Deletes an archive and all childs recursive
	 *
	 * @param bool $bitIgnoreRights
	 * @return bool
	 */
	public function deleteArchiveRecursive($bitIgnoreRights = false) {
		$bitReturn = true;

		//Load the current level
		$arrFolder = class_modul_downloads_file::getFolderLevel($this->getSystemid());

		//Call us foreach folder
		if(count($arrFolder) > 0) {
			foreach($arrFolder as $objOneFolder) {
                $objArchive = new class_modul_downloads_archive($objOneFolder->getSystemid());
				if(!$objArchive->deleteArchiveRecursive() ) {
					$bitReturn = false;
					break;
				}
			}
		}

		//Delete folders & files
		if(count($arrFolder) > 0 && $bitReturn) {
			foreach ($arrFolder as $objOneFolder)
				if($this->objRights->rightDelete($objOneFolder->getSystemid()) || $bitIgnoreRights)
				    $objOneFolder->deleteRecord();
		}

		$arrFiles = class_modul_downloads_file::getFilesDB($this->getPrevId(), true);
		if(count($arrFiles) > 0 && $bitReturn) {
			foreach($arrFiles as $objOneFile)
				if($this->objRights->rightDelete($objOneFile->getSystemid()) || $bitIgnoreRights) {
				    $objOneFile->deleteRecord();
                }
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
	public function deleteArchive() {
	    class_logger::getInstance()->addLogRow("deleted dl-archive ".$this->getSystemid(), class_logger::$levelInfo);
	    $strQuery = "DELETE FROM "._dbprefix_."downloads_archive
					WHERE archive_id= ?";
	    $objDB = $this->objDB;
	    if($objDB->_pQuery($strQuery, array($this->getSystemid()))) {
	       if($this->deleteSystemRecord($this->getSystemid())) {
               //and delete the filemanager repo
                $objRepo = class_modul_filemanager_repo::getRepoForForeignId($this->getSystemid());
                if($objRepo->deleteRepo())
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