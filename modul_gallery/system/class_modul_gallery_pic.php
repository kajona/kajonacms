<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Model for pic & folders of the gallery
 *
 * @package modul_gallery
 * @author sidler@mulchprod.de
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
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_gallery";
		$arrModul["moduleId"] 			= _gallery_modul_id_;
		$arrModul["table"]       		= _dbprefix_."gallery_pic";
		$arrModul["modul"]				= "gallery";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }


    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."gallery_pic" => "pic_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "gallery pic ".$this->getStrFilename();
    }

    public function initObject() {
        $strQuery = "SELECT *
						FROM ".$this->arrModule["table"].",
						     "._dbprefix_."system
						WHERE pic_id = system_id
						AND system_id = ?";
		$arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
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

    protected function updateStateToDb($bitHtmlEntities = true) {
        $strQuery = "UPDATE ".$this->arrModule["table"]." SET
                        pic_name = ?,
                        pic_filename = ?,
                        pic_description = ?,
                        pic_subtitle = ?,
                        pic_size = ?,
                        pic_hits = ?,
                        pic_type = ?
                     WHERE pic_id = ? ";
        return $this->objDB->_pQuery($strQuery, array($this->getStrName(), $this->getStrFilename(), $this->getStrDescription(), 
                            $this->getStrSubtitle(), $this->getIntSize(), $this->getIntHits(), $this->getIntType(), $this->getSystemid()), array(true, true, false));
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
					  AND system_prev_id = ?
						".(!$bitFilesOnly ? "" : "AND pic_type = 0 ")."
						".(!$bitActiveOnly ? "" : "AND system_status = 1 ")."
						ORDER BY system_sort ASC,
							pic_type DESC,
							pic_name ASC";
        $arrIds  = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strPrevID));

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
                      AND system_prev_id = ?
                        ".(!$bitFilesOnly ? "" : "AND pic_type = 0 ")."
                        ".(!$bitActiveOnly ? "" : "AND system_status = 1 ")."
                        ORDER BY system_sort ASC,
                            pic_type DESC,
                            pic_name ASC";
        $arrIds  = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array($strPrevID), $intStart, $intEnd);

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
                      AND system_prev_id = ?
                        ".(!$bitFilesOnly ? "" : "AND pic_type = 0 ")."
                        ".(!$bitActiveOnly ? "" : "AND system_status = 1 ")."";
        $arrIds  = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strPrevID));
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
		                AND system_prev_id = ?
                        ".(!$bitActiveOnly ? "" : "AND system_status = 1 ")."
		                AND pic_type = 1
		                ORDER BY system_sort ASC,
                            pic_name ASC";
		$arrIds  = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strPrevid));
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
	public function deletePictureRecord() {
	    class_logger::getInstance()->addLogRow("deleted pic ".$this->getSystemid(), class_logger::$levelInfo);
		$bitReturn = false;
		//Delete from module-table
		$strQuery = "DELETE FROM "._dbprefix_."gallery_pic
						WHERE pic_id= ?";
		if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
		    if($this->deleteSystemRecord($this->getSystemid())) {
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
     * @param bool $bitRecursive
	 * @return mixed
	 */
	public static function syncRecursive($strPrevID, $strPath, $bitRecursive = true) {
        $arrReturn = array();
	    $arrReturn["insert"] = 0;
	    $arrReturn["delete"] = 0;
	    $arrReturn["update"] = 0;

	    $objDB = class_carrier::getInstance()->getObjDB();

	    //Load the files in the DB
		$arrObjDB = class_modul_gallery_pic::loadFilesDB($strPrevID);
		//Load files and folder from filesystem
		$objFilesystem = new class_filesystem();
		$arrFilesystem = $objFilesystem->getCompleteList($strPath, explode(",", _gallery_imagetypes_), array(), array(".", "..", ".svn"));
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
					//if record has childs, recursive, else delete directly
					if(count(class_modul_gallery_pic::loadFilesDB($objOneFileDB->getSystemid())) > 0) {
                        $objGallery = new class_modul_gallery_gallery($objOneFileDB->getSystemid());
                        $objGallery->deleteGalleryRecursive(true);
                    }

                    //and delete the folder
					if(!$objOneFileDB->deletePictureRecord())
						$bitCommit = false;

					$arrReturn["delete"]++;

				}
				elseif ($objOneFileDB->getIntType() == 0)
					if(!$objOneFileDB->deletePictureRecord())
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
            $objPic->updateObjectToDb($strPrevID);
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
            $objPic->updateObjectToDb($strPrevID);
			$arrReturn["insert"]++;
		}

		//Load subfolders
        if($bitRecursive) {
            $objFolders = class_modul_gallery_pic::loadFoldersDB($strPrevID);
            foreach($objFolders as $objOneFolderDB) {

                $arrTemp = class_modul_gallery_pic::syncRecursive($objOneFolderDB->getSystemid(), $objOneFolderDB->getStrFilename());
                $arrReturn["insert"] += $arrTemp["insert"];
                $arrReturn["update"] += $arrTemp["update"];
                $arrReturn["delete"] += $arrTemp["delete"];
            }
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