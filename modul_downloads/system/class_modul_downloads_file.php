<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/interface_sortable_rating.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Model for files & folders of the downloads
 *
 * @package modul_downloads
 */
class class_modul_downloads_file extends class_model implements interface_model, interface_sortable_rating {

    private $strName = "";
    private $strFilename = "";
    private $strDescription = "";
    private $strChecksum = "";
    private $intSize = 0;
    private $intHits = 0;
    private $intType = 0;
    private $intMaxKb = 0;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModule = array();
        $arrModule["name"] 				= "modul_downloads";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _downloads_modul_id_;
		$arrModule["table"]       		= _dbprefix_."downloads_file";
		$arrModule["modul"]				= "downloads";

		//base class
		parent::__construct($arrModule, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * inits this object with the values from the db. needs a given systemid
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM "._dbprefix_."system,
		            ".$this->arrModule["table"]."
					WHERE system_id = downloads_id
						AND system_id='".$this->objDB->dbsafeString($this->getSystemid())."'";
        $arrRow = $this->objDB->getRow($strQuery);
        if(count($arrRow) > 0) {
            $this->setDescription($arrRow["downloads_description"]);
            $this->setFilename($arrRow["downloads_filename"]);
            $this->setHits($arrRow["downloads_hits"]);
            $this->setMaxKb($arrRow["downloads_max_kb"]);
            $this->setName($arrRow["downloads_name"]);
            $this->setSize($arrRow["downloads_size"]);
            $this->setType($arrRow["downloads_type"]);
            $this->setChecksum($arrRow["downloads_checksum"]);
        }
    }



    /**
     * Saves the current object as a new object to the database
     *
     */
    public function saveObjectToDb($strPrevId) {
        $bitReturn = false;
		//start tx
		$this->objDB->transactionBegin();
		$bitCommit = false;
		//start with the system-records
		$strDlID = $this->createSystemRecord($strPrevId, "DL: ".$this->getFilename());
		$this->setSystemid($strDlID);
		class_logger::getInstance()->addLogRow("new dl-file ".$this->getSystemid(), class_logger::$levelInfo);
		//Modul-Table
		$strQuery = "INSERT INTO ".$this->arrModule["table"]."
		              (downloads_id, downloads_name, downloads_filename, downloads_description, downloads_size, downloads_hits, downloads_type, downloads_max_kb, downloads_checksum) VALUES
		              ('".$this->objDB->dbsafeString($strDlID)."', '".$this->objDB->dbsafeString($this->getName())."', '".$this->objDB->dbsafeString($this->getFilename())."',
                       '', '".$this->objDB->dbsafeString($this->getSize())."', '0', '".$this->objDB->dbsafeString($this->getType())."', '0', '".dbsafeString(@md5_file(_realpath_.$this->getFilename()))."')";

		if($this->objDB->_query($strQuery))
			$bitCommit = true;

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
     * Updates the object to the database
     *
     * @return bool
     */
    public function updateObjectToDB() {
        class_logger::getInstance()->addLogRow("updated dl-file ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE ".$this->arrModule["table"]."
					SET downloads_name='".$this->objDB->dbsafeString($this->getName())."',
					    downloads_description='".$this->objDB->dbsafeString($this->getDescription(), false)."',
					    downloads_size=".(int)$this->objDB->dbsafeString($this->getSize()).",
					    downloads_max_kb=".(int)$this->objDB->dbsafeString($this->getMaxKb()).",
                        downloads_checksum='".$this->objDB->dbsafeString($this->getChecksum())."'
				  WHERE downloads_id='".$this->objDB->dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }
    

    /**
     * Deletes the given dl-record from the database
     *
     * @param string $strSystemid
     * @return bool
     * @static
     */
    public static function deleteRecord($strSystemid) {
        class_logger::getInstance()->addLogRow("deleted dl-file ".$strSystemid, class_logger::$levelInfo);
        $bitReturn = false;
        $objDB = class_carrier::getInstance()->getObjDB();
        $objRoot = new class_modul_system_common();
		//Modul-Table
		$strQuery = "DELETE FROM "._dbprefix_."downloads_file
						WHERE downloads_id='".dbsafeString($strSystemid)."'";
		if($objDB->_query($strQuery)) {
    	    if($objRoot->deleteSystemRecord($strSystemid))
			    $bitReturn = true;
		}

		return $bitReturn;
    }


  /**
    * Loads all FOLDERS (!) under the given systemid
    *
    * @param string $strPrevId
    * @return mixed
    * @static
    */
	public static function getFolderLevel($strPrevId) {
		$strQuery = "SELECT system_id FROM "._dbprefix_."system,
		                           "._dbprefix_."downloads_file
						WHERE system_id = downloads_id
						AND system_prev_id='".dbsafeString($strPrevId)."'
						AND downloads_type = 1
						ORDER BY system_sort";

		$objDB = class_carrier::getInstance()->getObjDB();

		$arrIds =  $objDB->getArray($strQuery);
		$arrReturn = array();
		foreach ($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_downloads_file($arrOneId["system_id"]);

		return $arrReturn;
	}
	
	/**
	 * Loads all files, and only file under a given folderlevel recusively.
	 *
	 * @param string $strFolderlevel
	 * @return array
	 */
    public static function getAllFilesUnderFolderLevelRecursive($strFolderlevel) {
        $arrFiles = class_modul_downloads_file::getFilesDB($strFolderlevel);
        
        $arrChilds = array();
        $arrReturn = array();
        foreach ($arrFiles as $objOneFile) {
           if($objOneFile->getType() == 1) {
              $arrChilds = class_modul_downloads_file::getAllFilesUnderFolderLevelRecursive($objOneFile->getSystemid());
           }
           else if($objOneFile->getType() == 0) {
           	  $arrReturn[$objOneFile->getSystemid()] = $objOneFile;
           }
        }
        $arrReturn = array_merge($arrReturn, $arrChilds);
        
        return $arrReturn;               
    }

   /**
	 * Loads all files AND folders from db
	 *
	 * @param string $strPrevId
	 * @param bool $bitFilesOnly
	 * @return mixed
	 * @static
	 */
	public static function getFilesDB($strPrevId, $bitFilesOnly = false, $bitJustActive = false) {
		$strQuery = "SELECT * FROM "._dbprefix_."system,
		                           "._dbprefix_."downloads_file
						WHERE system_id = downloads_id
						  AND system_prev_id='".dbsafeString($strPrevId)."'
							".(!$bitFilesOnly ? "" : " AND downloads_type = 0 ")."
							".(!$bitJustActive ? "" : " AND system_status = 1 ")."
						ORDER BY system_sort ASC,
							downloads_type DESC,
							downloads_name ASC";

		$objDB = class_carrier::getInstance()->getObjDB();

		$arrIds =  $objDB->getArray($strQuery);
		$arrReturn = array();
		foreach ($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_downloads_file($arrOneId["system_id"]);

		return $arrReturn;
	}

   /**
	 * Synchronises the filesystem with the database
	 *
	 * @param string $strPrevId, id to append new records to
	 * @param string $strPath, path to scan for folders and files
	 * @static
	 * @return mixed
	 */
	public static function syncRecursive($strPrevId, $strPath) {
	    $objDB = class_carrier::getInstance()->getObjDB();
        $arrReturn = array();
        $arrReturn["insert"] = 0;
	    $arrReturn["delete"] = 0;
	    $arrReturn["update"] = 0;
		//Load Files from DB
		$arrDB = class_modul_downloads_file::getFilesDB($strPrevId);

		//Files from filesystem
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrFilesystem = $objFilesystem->getCompleteList($strPath, array(), array(".htaccess"), array(".", "..", ".svn"));
		//Sync both arrays
		//start witht the files
		foreach($arrFilesystem["files"] as $intKeyFs => $arrOneFileFilesystem) {
			//Lopp over db-array
			foreach($arrDB as $intKeyDb => $objOneFileDatabase) {
				//File or folder?
				if($objOneFileDatabase->getType() == 0) {
					//Compare
					if($objOneFileDatabase->getFilename() == str_replace(_realpath_, "", $arrOneFileFilesystem["filepath"])) {
						//if checksum differs, update record
                        if(@md5_file($arrOneFileFilesystem["filepath"]) != $objOneFileDatabase->getChecksum()) {
							$objOneFileDatabase->setSize($arrOneFileFilesystem["filesize"]);
                            $objOneFileDatabase->setChecksum(@md5_file($arrOneFileFilesystem["filepath"]));
							$objOneFileDatabase->updateObjectToDB();
							$arrReturn["update"]++;
						}
						//Remove from both arrays
						unset($arrFilesystem["files"][$intKeyFs]);
						unset($arrDB[$intKeyDb]);
					}
				}
			}
		}
		//ok, loop the folders
		foreach($arrFilesystem["folders"] as $intKeyFs => $arrOneFolderFilesystem) {
			//Iterate over the db-array
			foreach($arrDB as $intKeyDb => $objOneFolderDatabase) {
				//folder?
				if($objOneFolderDatabase->getType() == 1) {
					//compare
					if($objOneFolderDatabase->getFilename() == $strPath."/".$arrOneFolderFilesystem) {
						//remove from arrays
						unset($arrFilesystem["folders"][$intKeyFs]);
						unset($arrDB[$intKeyDb]);
					}
				}
			}
		}

		//The remaining records from the db have to be deleted
		if(count($arrDB) > 0) {
			//start tx
			$objDB->transactionBegin();
			$bitCommit = true;
			foreach($arrDB as $objOneRecordDb) {
				//special: folder, then recursive!
				if($objOneRecordDb->getType() == 1) {
					//if childs, recursive
					if(count(class_modul_downloads_file::getFilesDB($objOneRecordDb->getSystemid())) > 0)
					    class_modul_downloads_archive::deleteArchiveRecursive($objOneRecordDb->getSystemid(), true);

					if(!class_modul_downloads_file::deleteRecord($objOneRecordDb->getSystemid()))
						$bitCommit = false;

                    $arrReturn["delete"]++;
				}
				elseif ($objOneRecordDb->getType() == 0)
					if(!class_modul_downloads_file::deleteRecord($objOneRecordDb->getSystemid()))
						$bitCommit = false;
					$arrReturn["delete"]++;
			}

			//End tx
			if($bitCommit)
				$objDB->transactionCommit();
			else {
				$objDB->transactionRollback();
				echo "Rollback!";
			}
		}

		//The remaining records have to be put into the database
		foreach($arrFilesystem["files"] as $arrOneFileFilesystem) {
			$strDlName = $arrOneFileFilesystem["filename"];
			$strDlNameIntern = str_replace(_realpath_, "", $arrOneFileFilesystem["filepath"]);
			$intSize = $arrOneFileFilesystem["filesize"];
			$objDlFile = new class_modul_downloads_file("");
			$objDlFile->setName($strDlName);
			$objDlFile->setFilename($strDlNameIntern);
			$objDlFile->setSize($intSize);
			$objDlFile->setType(0);
			$objDlFile->saveObjectToDb($strPrevId);
            $arrReturn["insert"]++;
		}

		foreach($arrFilesystem["folders"] as $arrOneFolderFilesystem) {
			$strDlName = $arrOneFolderFilesystem;
			$strDlFilename = $strPath."/".$arrOneFolderFilesystem;
			$intSize = 0;
			$objDlFile = new class_modul_downloads_file("");
			$objDlFile->setName($strDlName);
			$objDlFile->setFilename($strDlFilename);
			$objDlFile->setSize($intSize);
			$objDlFile->setType(1);
			$objDlFile->saveObjectToDb($strPrevId);
            $arrReturn["insert"]++;
		}
		//And call all subfolders
		$arrFolders = class_modul_downloads_file::getFolderLevel($strPrevId);
		foreach($arrFolders as $objOneFolderDatabase) {
			$arrTemp = class_modul_downloads_file::syncRecursive($objOneFolderDatabase->getSystemid(), $objOneFolderDatabase->getFilename());
			$arrReturn["insert"] += $arrTemp["insert"];
			$arrReturn["update"] += $arrTemp["update"];
			$arrReturn["delete"] += $arrTemp["delete"];
		}

		return $arrReturn;
	}
	

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getName() {
        return $this->strName;
    }
    /* Just for the portal path navi */
    public function getTitle() {
        return $this->strName;
    }
    public function getHits() {
        return $this->intHits;
    }
    public function getMaxKb() {
        return $this->intMaxKb;
    }
    public function getSize() {
        return $this->intSize;
    }
    public function getType() {
        return $this->intType;
    }
    public function getDescription() {
        return $this->strDescription;
    }
    public function getFilename() {
        return $this->strFilename;
    }
    /**
     * @deprecated Use getStrChecksum instead!
     * @return string
     */
    public function getMd5Sum() {
        return @md5_file(_realpath_.$this->getFilename());
    }
    public function getChecksum() {
        return $this->strChecksum;
    }

    public function setName($strName) {
        $this->strName = $strName;
    }
    public function setHits($intHits) {
        $this->intHits = $intHits;
    }
    public function setMaxKb($intMaxKB) {
        $this->intMaxKb = $intMaxKB;
    }
    public function setSize($intSize) {
        $this->intSize = $intSize;
    }
    public function setType($intType) {
        $this->intType = $intType;
    }
    public function setDescription($strDescription) {
        $this->strDescription = $strDescription;
    }
    public function setFilename($strFilename) {
        $this->strFilename = $strFilename;
    }
    public function setChecksum($strChecksum) {
        $this->strChecksum = $strChecksum;
    }

}

?>