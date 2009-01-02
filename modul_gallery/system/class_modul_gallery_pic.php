<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/interface_sortable_rating.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Model for pic & folders of the gallery
 *
 * @package modul_gallery
 */
class class_modul_gallery_pic extends class_model implements interface_model, interface_sortable_rating {
    private $strName = "";
    private $strFilename = "";
    private $strDescription = "";
    private $strSubtitle = "";
    private $intSize = 0;
    private $intHits = 0;
    private $intType = 0;


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_gallery";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _bildergalerie_modul_id_;
		$arrModul["table"]       		= _dbprefix_."gallery_pic";
		$arrModul["modul"]				= "gallery";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    public function initObject() {
        $strQuery = "SELECT *
						FROM ".$this->arrModule["table"].",
						     "._dbprefix_."system
						WHERE pic_id = system_id
						AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
		$arrRow = $this->objDB->getRow($strQuery);
		if(count($arrRow) > 0) {
    		$this->setStrName($arrRow["pic_name"]);
    		$this->setStrFilename($arrRow["pic_filename"]);
    		$this->setStrDescription($arrRow["pic_description"]);
    		$this->setIntSize($arrRow["pic_size"]);
    		$this->setIntHits($arrRow["pic_hits"]);
    		$this->setIntType($arrRow["pic_type"]);
    		$this->setStrSubtitle($arrRow["pic_subtitle"]);
		}
    }

    public function updateObjectToDb($bitHtmlEntities = true) {
        class_logger::getInstance()->addLogRow("updated pic ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE ".$this->arrModule["table"]." SET
                        pic_name = '".$this->objDB->dbsafeString($this->getStrName(), $bitHtmlEntities)."',
                        pic_filename = '".$this->objDB->dbsafeString($this->getStrFilename(), $bitHtmlEntities)."',
                        pic_description = '".$this->objDB->dbsafeString($this->getStrDescription(), false)."',
                        pic_subtitle = '".$this->objDB->dbsafeString($this->getStrSubtitle(), $bitHtmlEntities)."',
                        pic_size = '".(int)$this->getIntSize()."',
                        pic_hits = '".(int)$this->getIntHits()."',
                        pic_type = '".(int)$this->getIntType()."'
                     WHERE pic_id = '".$this->objDB->dbsafeString($this->getSystemid(), $bitHtmlEntities)."' ";
        return $this->objDB->_query($strQuery);
    }

    /**
     * Saves the current object as a new object to db
     *
     * @return bool
     */
    public function saveObjectToDb($strPrevID) {
		//start tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
        //system records
        $strPicId = $this->createSystemRecord($strPrevID, $this->getStrFilename());
        $this->setSystemid($strPicId);
        class_logger::getInstance()->addLogRow("new pic ".$this->getSystemid(), class_logger::$levelInfo);
		//start with the module
		$strQuery = "INSERT INTO ".$this->arrModule["table"]."
		          (pic_id, pic_name, pic_filename, pic_description, pic_subtitle, pic_size, pic_hits, pic_type) VALUES
		          ('".$this->objDB->dbsafeString($strPicId)."', '".$this->objDB->dbsafeString($this->getStrName())."',
		           '".$this->objDB->dbsafeString($this->getStrFilename())."', '', '".$this->objDB->dbsafeString($this->getStrSubtitle())."', '".(int)$this->getIntSize()."', '0', '".(int)$this->getIntType()."' )";
		if(!$this->objDB->_query($strQuery)) {
		    $bitCommit = false;
		}
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
	 * Loads all files ( & folders) under the given systemid available in the db
	 *
	 * @param string $strPrevID
	 * @param bool $bitFilesOnly
	 * @param bool $bitActiveOnly
	 * @return mixed
	 * @static
	 */
	public static  function loadFilesDB($strPrevID, $bitFilesOnly = false, $bitActiveOnly = false) {
		$strQuery = "SELECT system_id FROM "._dbprefix_."system,
		                      "._dbprefix_."gallery_pic
					WHERE system_id = pic_id
					  AND system_prev_id = '".dbsafeString($strPrevID)."'
						".(!$bitFilesOnly ? "" : "AND pic_type = 0 ")."
						".(!$bitActiveOnly ? "" : "AND system_status = 1 ")."
						ORDER BY system_sort ASC,
							pic_type DESC,
							pic_name ASC";
        $arrIds  = class_carrier::getInstance()->getObjDB()->getArray($strQuery);

        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_modul_gallery_pic($arrOneId["system_id"]);
        }
        return $arrReturn;
	}
	
    /**
     * Loads all files ( & folders) under the given systemid available in the db but using section limitations
     *
     * @param string $strPrevID
     * @param bool $bitFilesOnly
     * @param bool $bitActiveOnly
     * @param int $intStart
     * @param int $intEnd
     * @return mixed
     * @static
     */
    public static  function loadFilesDBSection($strPrevID, $bitFilesOnly, $bitActiveOnly, $intStart, $intEnd) {
        $strQuery = "SELECT system_id FROM "._dbprefix_."system,
                              "._dbprefix_."gallery_pic
                    WHERE system_id = pic_id
                      AND system_prev_id = '".dbsafeString($strPrevID)."'
                        ".(!$bitFilesOnly ? "" : "AND pic_type = 0 ")."
                        ".(!$bitActiveOnly ? "" : "AND system_status = 1 ")."
                        ORDER BY system_sort ASC,
                            pic_type DESC,
                            pic_name ASC";
        $arrIds  = class_carrier::getInstance()->getObjDB()->getArraySection($strQuery, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_modul_gallery_pic($arrOneId["system_id"]);
        }
        return $arrReturn;
    }
    
    /**
     * Counts the number of files returnd by the corresponding query
     *
     * @param string $strPrevID
     * @param bool $bitFilesOnly
     * @param bool $bitActiveOnly
     * @return int
     * @static
     */
    public static function getFileCount($strPrevID, $bitFilesOnly = false, $bitActiveOnly = false) {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system,
                              "._dbprefix_."gallery_pic
                    WHERE system_id = pic_id
                      AND system_prev_id = '".dbsafeString($strPrevID)."'
                        ".(!$bitFilesOnly ? "" : "AND pic_type = 0 ")."
                        ".(!$bitActiveOnly ? "" : "AND system_status = 1 ")."
                        ORDER BY system_sort ASC,
                            pic_type DESC,
                            pic_name ASC";
        $arrIds  = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
        return $arrIds["COUNT(*)"];
    }

	/**
	 * Loads the folders under the given systemid
	 *
	 * @param string $strPrevid
     * @param bool $bitActiveOnly
	 * @return mixed
	 * @static
	 */
	public static function loadFoldersDB($strPrevid, $bitActiveOnly = false) {
		$strQuery= "SELECT system_id FROM "._dbprefix_."system,
		                           "._dbprefix_."gallery_pic
		              WHERE system_id = pic_id
		                AND system_prev_id = '".dbsafeString($strPrevid)."'
                        ".(!$bitActiveOnly ? "" : "AND system_status = 1 ")."
		                AND pic_type = 1
		                ORDER BY system_sort ASC,
                            pic_name ASC";
		$arrIds  = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_modul_gallery_pic($arrOneId["system_id"]);
        }
        return $arrReturn;
	}

	/**
	 * Deltes one record from the db
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public static function deletePictureRecord($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted pic ".$strSystemid, class_logger::$levelInfo);
        $objRoot = new class_modul_system_common();
		$bitReturn = false;
		//Delete from module-table
		$strQuery = "DELETE FROM "._dbprefix_."gallery_pic
						WHERE pic_id='".dbsafeString($strSystemid)."'";
		if(class_carrier::getInstance()->getObjDB()->_query($strQuery)) {
		    if($objRoot->deleteSystemRecord($strSystemid)) {
			    $bitReturn = true;
		    }
		}

		return $bitReturn;
	}

	/**
	 * Syncs the files in the db with the files in the filesystem
	 *
	 * @param string $strPrevID
	 * @param stirng $strPath
	 * @return mixed
	 */
	public static function syncRecursive($strPrevID, $strPath) {
	    $arrReturn["insert"] = 0;
	    $arrReturn["delete"] = 0;
	    $arrReturn["update"] = 0;

	    $objRoot = new class_modul_system_common();
	    $objDB = class_carrier::getInstance()->getObjDB();

	    //Load the files in the DB
		$arrObjDB = class_modul_gallery_pic::loadFilesDB($strPrevID);
		//Load files and folder from filesystem
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrFilesystem = $objFilesystem->getCompleteList($strPath, explode(",", _bildergalerie_bildtypen_), array(), array(".", "..", ".svn"));
		//So, lets sync those two arrays
		//At first the files
		foreach($arrFilesystem["files"] as $intKeyFS => $arrOneFileFilesystem) {
			//search the db-array for this file
			foreach($arrObjDB as $intKeyDB => $objOneFileDB ) {
				//File or folder
				if($objOneFileDB->getintType() == 0) {
					//compare
					if($objOneFileDB->getStrFilename() == str_replace(_realpath_, "", $arrOneFileFilesystem["filepath"])) {
						//if the size changes, update it!
						if($objOneFileDB->getIntSize() != $arrOneFileFilesystem["filesize"]) {
							//Update
							$objOneFileDB->setIntSize($arrOneFileFilesystem["filesize"]);
							$objOneFileDB->updateObjectToDb();
							$arrReturn["update"]++;
						}
						//And unset from both arrays
						unset($arrFilesystem["files"][$intKeyFS]);
						unset($arrObjDB[$intKeyDB]);
					}
				}
			}
		}
		//And loop the folders
		foreach($arrFilesystem["folders"] as $intKeyFolder => $strFolder) {
			//search the array for folders
			foreach($arrObjDB as $intKeyDB => $objOneFolderDB) {
				//file or folder?
				if($objOneFolderDB->getIntType() == 1) {
					//compare
					if($objOneFolderDB->getStrFilename() == $strPath."/".$strFolder) {
						//Unset from both
						unset($arrFilesystem["folders"][$intKeyFolder]);
						unset($arrObjDB[$intKeyDB]);
					}
				}
			}
		}

		//the remaining records from the database have to be deleted!
		if(count($arrObjDB) > 0) {
			//start tx
			$objDB->transactionBegin();
			$bitCommit = true;
			foreach($arrObjDB as $objOneFileDB) {
				//Folders: recursive!
				if($objOneFileDB->getIntType() == 1) {
					//if record has childs, recursive, else delete direct
					if(count(class_modul_gallery_pic::loadFilesDB($objOneFileDB->getSystemid())) > 0)
                        class_modul_gallery_gallery::deleteGalleryRecursive($objOneFileDB->getSystemid(), true);

                    //and delete the folder
					if(!class_modul_gallery_pic::deletePictureRecord($objOneFileDB->getSystemid()))
						$bitCommit = false;

					$arrReturn["delete"]++;

				}
				elseif ($objOneFileDB->getIntType() == 0)
					if(!class_modul_gallery_pic::deletePictureRecord($objOneFileDB->getSystemid()))
						$bitCommit = false;

					$arrReturn["delete"]++;
			}

			//end tx
			if($bitCommit)
				$objDB->transactionCommit();
			else {
				$objDB->transactionRollback();
			}
		}

		//the remaining records from the filesystem have to be added
		foreach($arrFilesystem["files"] as $arrOneFileFilesystem) {
			$strPicName = $arrOneFileFilesystem["filename"];
			$strPicFilename = str_replace(_realpath_, "", $arrOneFileFilesystem["filepath"]);
			$intSize = $arrOneFileFilesystem["filesize"];
			$strComment = $strPicName;
			$objPic = new class_modul_gallery_pic("");
			$objPic->setStrDescription($strComment);
			$objPic->setStrFilename($strPicFilename);
			$objPic->setStrName($strPicName);
			$objPic->setIntSize($intSize);
			$objPic->setIntType(0);
            $objPic->saveObjectToDb($strPrevID);
			$arrReturn["insert"]++;
		}

		foreach($arrFilesystem["folders"] as $strFolder) {
			$strPicName = $strFolder;
			$strPicFilename = $strPath."/".$strFolder;
			$intSize = 0;
			$strComment= $strPicName;
			$objPic = new class_modul_gallery_pic("");
			$objPic->setStrDescription($strComment);
			$objPic->setStrFilename($strPicFilename);
			$objPic->setStrName($strPicName);
			$objPic->setIntSize($intSize);
			$objPic->setIntType(1);
            $objPic->saveObjectToDb($strPrevID);
			$arrReturn["insert"]++;
		}

		//Load subfolders
		$objFolders = class_modul_gallery_pic::loadFoldersDB($strPrevID);
		foreach($objFolders as $objOneFolderDB) {

		    $arrTemp = class_modul_gallery_pic::syncRecursive($objOneFolderDB->getSystemid(), $objOneFolderDB->getStrFilename());
			$arrReturn["insert"] += $arrTemp["insert"];
			$arrReturn["update"] += $arrTemp["update"];
			$arrReturn["delete"] += $arrTemp["delete"];
		}

		return $arrReturn;
	}
	

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function setStrName($strName) {
        $this->strName = $strName;
    }
    public function setStrFilename($strFilename) {
        $this->strFilename = $strFilename;
    }
    public function setStrDescription($strDesc) {
        $this->strDescription = $strDesc;
    }
    public function setStrSubtitle($strSubtitle) {
        $this->strSubtitle = $strSubtitle;
    }
    public function setIntSize($intSize) {
        $this->intSize = $intSize;
    }
    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }
    public function setIntType($intType) {
        $this->intType = $intType;
    }
    public function getStrName() {
        return $this->strName;
    }
    public function getStrFilename() {
        return $this->strFilename;
    }
    public function getStrDescription() {
        return $this->strDescription;
    }
    public function getStrSubtitle() {
        return $this->strSubtitle;
    }
    public function getIntSize() {
        return (int)$this->intSize;
    }
    public function getIntHits() {
        return (int)$this->intHits;
    }
    public function getIntType() {
        return (int)$this->intType;
    }

}

?>