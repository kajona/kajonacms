<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
********************************************************************************************************/

/**
 * Model for a single file inside a mediamanagers' repo
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_module_mediamanager_file extends class_model implements interface_model, interface_admin_listable {


    public static $INT_TYPE_FILE = 0;
    public static $INT_TYPE_FOLDER = 1;


    /**
     * @var string
     * @tableColumn file_name
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn file_filename
     */
    private $strFilename = "";

    /**
     * @var string
     * @tableColumn file_description
     * @blockEscaping
     */
    private $strDescription = "";

    /**
     * @var string
     * @tableColumn file_subtitle
     */
    private $strSubtitle = "";

    /**
     * @var int
     * @tableColumn file_hits
     */
    private $intHits = 0;

    /**
     *
     * 0 = file, 1 = folder
     * @var int
     * @tableColumn file_type
     */
    private $intType = 0;


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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."mediamanager_file" => "file_id");
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {

        if($this->getIntType() == self::$INT_TYPE_FOLDER)
            return "icon_folderClosed.gif";


        //get the filetype
        $arrMime  = class_carrier::getInstance()->getObjToolkit("admin")->mimeType($this->getStrFilename());
        $strImage = $arrMime[2];
        $strAlt = $arrMime[0];

        if($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif") {
            $strAlt = "<div class='loadingContainer'><img src='"._webpath_."/image.php?image=".urlencode($this->getStrFilename())."&amp;maxWidth=100&amp;maxHeight=100' /></div>";
        }
        return array($strImage, $strAlt);
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        $strReturn = basename($this->getStrFilename());
        if($this->getIntType() == self::$INT_TYPE_FILE)
            $strReturn .= ",  ".bytesToString(filesize(_realpath_.$this->getStrFilename()));

        return $strReturn;
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return $this->getStrSubtitle();
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName();
    }

    protected function deleteObjectInternal() {

        //check for subrecords
        $arrFiles = self::loadFilesDB($this->getSystemid());
        foreach($arrFiles as $objOneFile)
            $objOneFile->deleteObject();

        //delete the current file
        $objFilesystem = new class_filesystem();
        if($this->getIntType() == self::$INT_TYPE_FILE)
            $objFilesystem->fileDelete($this->getStrFilename());
        else
            $objFilesystem->folderDelete($this->getStrFilename());

        return parent::deleteObjectInternal();
    }


    /**
     * Loads all files ( & folders) under the given systemid available in the db but using section limitations
     *
     * @param string $strPrevID
     * @param int|bool $intTypeFilter
     * @param bool $bitActiveOnly
     * @param int $intStart
     * @param int $intEnd
     * @return class_module_mediamanager_file[]
     * @static
     */
    public static  function loadFilesDB($strPrevID, $intTypeFilter = false, $bitActiveOnly = false, $intStart = null, $intEnd = null) {

        $arrParams = array();
        $arrParams[] = $strPrevID;
        if($intTypeFilter !== false)
            $arrParams[] = $intTypeFilter;

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."system,
                            "._dbprefix_."mediamanager_file
                    WHERE system_id = file_id
                      AND system_prev_id = ?
                        ".($intTypeFilter !== false  ?  " AND file_type = ? " : "")."
                        ".(!$bitActiveOnly ? "" : " AND system_status = 1 ")."
                        ORDER BY system_sort ASC";
        $arrIds  = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_mediamanager_file($arrOneId["system_id"]);

        return $arrReturn;
    }

    /**
     * Counts the number of files returnd by the corresponding query
     *
     * @param string $strPrevID
     * @param bool|int $intTypeFilter
     * @param bool $bitActiveOnly
     * @return int
     * @static
     */
    public static function getFileCount($strPrevID, $intTypeFilter = false, $bitActiveOnly = false) {

        $arrParams = array();
        $arrParams[] = $strPrevID;
        if($intTypeFilter !== false)
            $arrParams[] = $intTypeFilter;

        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."system,
                            "._dbprefix_."mediamanager_file
                    WHERE system_id = file_id
                      AND system_prev_id = ?
                         ".($intTypeFilter !== false  ? " AND file_type = ? " : "" )."
                        ".(!$bitActiveOnly ? "" : "AND system_status = 1 ")."";
        $arrRow  = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }


    /**
     * Syncs the files in the db with the files in the filesystem
     *
     * @param string $strPrevID
     * @param string $strPath
     * @param bool $bitRecursive
     * @param \class_module_mediamanager_repo|null $objRepo
     *
     * @return array [insert, delete]
     */
	public static function syncRecursive($strPrevID, $strPath, $bitRecursive = true, class_module_mediamanager_repo $objRepo = null) {
        $arrReturn = array();
	    $arrReturn["insert"] = 0;
	    $arrReturn["delete"] = 0;

        if($objRepo == null) {
            $objRepo = class_objectfactory::getInstance()->getObject($strPrevID);
            while($objRepo != null && !$objRepo instanceof class_module_mediamanager_repo)
                $objRepo = class_objectfactory::getInstance()->getObject($objRepo->getPrevId());
        }

	    //Load the files in the DB
		$arrObjDB = class_module_mediamanager_file::loadFilesDB($strPrevID);
		//Load files and folder from filesystem
		$objFilesystem = new class_filesystem();

        //if the repo defines a view-filter, take that one into account
        $arrViewFilter = array();
        if($objRepo->getStrViewFilter() != "") {
            $arrViewFilter = explode(",", $objRepo->getStrViewFilter());
        }

		$arrFilesystem = $objFilesystem->getCompleteList($strPath, $arrViewFilter, array(), array(".", "..", ".svn"));

		//So, lets sync those two arrays
		//At first the files
		foreach($arrFilesystem["files"] as $intKeyFS => $arrOneFileFilesystem) {
			//search the db-array for this file
			foreach($arrObjDB as $intKeyDB => $objOneFileDB ) {
				//File or folder
				if($objOneFileDB->getintType() == self::$INT_TYPE_FILE) {
					//compare
					if($objOneFileDB->getStrFilename() == str_replace(_realpath_, "", $arrOneFileFilesystem["filepath"])) {
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
				if($objOneFolderDB->getIntType() == self::$INT_TYPE_FOLDER) {
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

			foreach($arrObjDB as $objOneFileDB) {
                $objOneFileDB->deleteObject();
                $arrReturn["delete"]++;
			}
		}

		//the remaining records from the filesystem have to be added
		foreach($arrFilesystem["files"] as $arrOneFileFilesystem) {
			$strFileName = $arrOneFileFilesystem["filename"];
			$strFileFilename = str_replace(_realpath_, "", $arrOneFileFilesystem["filepath"]);
			$objFile = new class_module_mediamanager_file();
			$objFile->setStrFilename($strFileFilename);
			$objFile->setStrName($strFileName);
			$objFile->setIntType(self::$INT_TYPE_FILE);
            $objFile->updateObjectToDb($strPrevID);
			$arrReturn["insert"]++;
		}

		foreach($arrFilesystem["folders"] as $strFolder) {
			$strFileName = $strFolder;
			$strFileFilename = $strPath."/".$strFolder;
			$objFile = new class_module_mediamanager_file();
			$objFile->setStrFilename($strFileFilename);
			$objFile->setStrName($strFileName);
			$objFile->setIntType(self::$INT_TYPE_FOLDER);
            $objFile->updateObjectToDb($strPrevID);
			$arrReturn["insert"]++;
		}

		//Load subfolders
        class_carrier::getInstance()->getObjDB()->flushQueryCache();
        if($bitRecursive) {
            $objFolders = class_module_mediamanager_file::loadFilesDB($strPrevID, self::$INT_TYPE_FOLDER);
            foreach($objFolders as $objOneFolderDB) {
                $arrTemp = class_module_mediamanager_file::syncRecursive($objOneFolderDB->getSystemid(), $objOneFolderDB->getStrFilename(), $bitRecursive, $objRepo);
                $arrReturn["insert"] += $arrTemp["insert"];
                $arrReturn["delete"] += $arrTemp["delete"];
            }
        }

		return $arrReturn;
	}


    public function getIntFileSize() {
        return filesize(_realpath_.$this->getStrFilename());
    }


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

    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

    public function setIntType($intType) {
        $this->intType = $intType;
    }

    /**
     * @return string
     * @fieldType text
     */
    public function getStrName() {
        return $this->strName;
    }

    public function getStrFilename() {
        return $this->strFilename;
    }

    /**
     * @return string
     * @fieldType wysiwygsmall
     */
    public function getStrDescription() {
        return $this->strDescription;
    }

    /**
     * @return string
     * @fieldType text
     */
    public function getStrSubtitle() {
        return $this->strSubtitle;
    }

    public function getIntHits() {
        return (int)$this->intHits;
    }

    public function getIntType() {
        return (int)$this->intType;
    }

}

