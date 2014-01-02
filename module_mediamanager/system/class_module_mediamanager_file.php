<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/

/**
 * Model for a single file inside a mediamanagers' repo
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @targetTable mediamanager_file.file_id
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 */
class class_module_mediamanager_file extends class_model implements interface_model, interface_admin_gridable, interface_search_resultobject {


    public static $INT_TYPE_FILE = 0;
    public static $INT_TYPE_FOLDER = 1;

    /**
     * @var string
     * @tableColumn file_name
     *
     * @fieldType text
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
     *
     * @fieldType wysiwygsmall
     */
    private $strDescription = "";

    /**
     * @var string
     * @tableColumn file_subtitle
     *
     * @fieldType text
     */
    private $strSubtitle = "";

    /**
     * @var int
     * @tableColumn file_hits
     */
    private $intHits = 0;

    /**
     * 0 = file, 1 = folder
     *
     * @var int
     * @tableColumn file_type
     */
    private $intType = 0;

    /**
     * @var int
     * @tableColumn file_ispackage
     */
    private $bitIspackage = 0;

    /**
     * @var int
     * @tableColumn file_cat
     */
    private $strCat = "";

    /**
     * @var string
     * @tableColumn file_screen1
     */
    private $strScreen1 = "";

    /**
     * @var string
     * @tableColumn file_screen2
     */
    private $strScreen2 = "";

    /**
     * @var string
     * @tableColumn file_screen3
     */
    private $strScreen3 = "";

    /**
     * Return an on-lick link for the passed object.
     * This link is used by the backend-search for the autocomplete-field
     *
     * @see getLinkAdminHref()
     * @return mixed
     */
    public function getSearchAdminLinkForObject() {
        return getLinkAdminHref("mediamanager", "edit", "&systemid=".$this->getSystemid()."&source=search");
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
            return "icon_folderClosed";

        //get the filetype
        $arrMime = class_carrier::getInstance()->getObjToolkit("admin")->mimeType($this->getStrFilename());
        $strImage = $arrMime[2];
        $strAlt = $arrMime[0];

        if($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif") {
            $strAlt = "<img src='"._webpath_."/image.php?image=" . urlencode($this->getStrFilename()) . "&amp;maxWidth=100&amp;maxHeight=100' />";
        }
        return array($strImage, $strAlt);
    }

    /**
     * Returns the image the be used in a grid-view.
     * Make sure to return the full url, otherwise the
     * img-tag may be broken
     *
     * @return string the full url to the image that should be embedded into the grid
     */
    public function getStrGridIcon() {
        if($this->getIntType() == self::$INT_TYPE_FOLDER) {
            return _webpath_."/core/module_mediamanager/admin/pics/folder_grey.png";
        }

        //get the filetype
        $arrMime = class_carrier::getInstance()->getObjToolkit("admin")->mimeType($this->getStrFilename());

        if($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif") {
            return _webpath_ . "/image.php?image=" . urlencode($this->getStrFilename()) . "&amp;fixedWidth=260&amp;fixedHeight=180";
        }

        return _webpath_."/core/module_mediamanager/admin/pics/file.png";
    }


    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        $strReturn = basename($this->getStrFilename());
        if($this->getIntType() == self::$INT_TYPE_FILE) {
            $strReturn .= ",  " . bytesToString(@filesize(_realpath_ . $this->getStrFilename()));
            $strReturn .= ", " . $this->getIntHits() . " " . $this->getLang("file_hits", "mediamanager");
        }

        return $strReturn;
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return $this->getStrSubtitle();
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName();
    }

    protected function deleteObjectInternal() {

        //delete the current file
        if($this->getParam("deleteMediamanagerRepo") != true) {
            $objFilesystem = new class_filesystem();
            if($this->getIntType() == self::$INT_TYPE_FILE) {
                $objFilesystem->fileDelete($this->getStrFilename());
            }
            else {
                $objFilesystem->folderDelete($this->getStrFilename());
            }
        }

        return parent::deleteObjectInternal();
    }

    protected function updateStateToDb() {

        //check if its a valid package
        if(uniSubstr($this->getStrFilename(), -4) == ".zip") {
            $this->updatePackageInformation();
        }

        return parent::updateStateToDb();
    }

    /**
     * Updates the internal information of the file based on the metadata.xml
     */
    private function updatePackageInformation() {
        $objZip = new class_zip();
        $strMetadata = $objZip->getFileFromArchive($this->getStrFilename(), "/metadata.xml");
        if($strMetadata !== false) {
            $objMetadata = new class_module_packagemanager_metadata();
            $objMetadata->autoInit($this->getStrFilename());
            $this->setBitIspackage(1);
            $this->setStrCat($objMetadata->getStrType());
        }
        else
            $this->setBitIspackage(0);
    }

    /**
     * Increases a files' hits without touching its last-modified date
     * @return bool
     */
    public function increaseHits() {
        $this->setIntHits($this->getIntHits()+1);
        $strQuery = "UPDATE "._dbprefix_."mediamanager_file SET file_hits = file_hits+1 WHERE file_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
    }

    /**
     * Loads all files ( & folders) under the given systemid available in the db but using section limitations
     *
     * @param string $strPrevID
     * @param int|bool $intTypeFilter
     * @param bool $bitActiveOnly
     * @param int $intStart
     * @param int $intEnd
     * @param bool $bitOnlyPackages
     *
     * @return class_module_mediamanager_file[]
     * @static
     */
    public static function loadFilesDB($strPrevID, $intTypeFilter = false, $bitActiveOnly = false, $intStart = null, $intEnd = null, $bitOnlyPackages = false) {

        $arrParams = array();
        $arrParams[] = $strPrevID;
        if($intTypeFilter !== false) {
            $arrParams[] = $intTypeFilter;
        }

        if($bitOnlyPackages)
            $arrParams[] = self::$INT_TYPE_FOLDER;

        $strQuery = "SELECT system_id
                       FROM " . _dbprefix_ . "system,
                            " . _dbprefix_ . "mediamanager_file
                    WHERE system_id = file_id
                      AND system_prev_id = ?
                        " . ($intTypeFilter !== false ? " AND file_type = ? " : "") . "
                        " . (!$bitActiveOnly ? "" : " AND system_status = 1 ") . "
                        " . (!$bitOnlyPackages ? "" : " AND (file_ispackage = 1 OR file_type= ? ) ") . "
                        ORDER BY system_sort ASC";
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_mediamanager_file($arrOneId["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Returns a list of all packages available
     *
     * @param bool $strCategoryFilter
     * @param bool $bitActiveOnly
     * @param null $intStart
     * @param null $intEnd
     * @param bool $strNameFilter
     *
     * @return class_module_mediamanager_file[]
     */
    public static function getFlatPackageList($strCategoryFilter = false, $bitActiveOnly = false, $intStart = null, $intEnd = null, $strNameFilter = false) {

        $arrParams = array();
        if($strCategoryFilter !== false)
            $arrParams[] = $strCategoryFilter;


        $strWhere = "";
        if($strNameFilter !== false) {
            if(uniStrpos($strNameFilter, ",") !== false) {
                $arrWhere = array();
                foreach(explode(",", $strNameFilter) as $strOneLike) {
                    $arrWhere[] = " file_name = ?";
                    $arrParams[] = trim($strOneLike);
                }

                $strWhere = "AND ( ".implode(" OR ", $arrWhere)." )";
            }
            else {
                $arrParams[] = $strNameFilter."%";
                $strWhere = "AND file_name LIKE ?";
            }
        }

        $strQuery = "SELECT system_id
                       FROM " . _dbprefix_ . "system,
                            " . _dbprefix_ . "mediamanager_file
                    WHERE system_id = file_id
                      AND file_ispackage = 1
                        " . (!$bitActiveOnly ? "" : " AND system_status = 1 ") . "
                        " . ($strCategoryFilter === false ? "" : " AND file_cat = ?  ") . "
                        " . $strWhere . "
                        ORDER BY file_name ASC";
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_mediamanager_file($arrOneId["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Counts the number of packages available
     *
     * @param bool $strCategoryFilter
     * @param bool $bitActiveOnly
     * @param bool $strNameFilter
     *
     * @return mixed
     */
    public static function getFlatPackageListCount($strCategoryFilter = false, $bitActiveOnly = false, $strNameFilter = false) {

        $arrParams = array();
        if($strCategoryFilter !== false)
            $arrParams[] = $strCategoryFilter;


        $strWhere = "";
        if($strNameFilter !== false) {
            if(uniStrpos($strNameFilter, ",") !== false) {
                $arrWhere = array();
                foreach(explode(",", $strNameFilter) as $strOneLike) {
                    $arrWhere[] = " file_name = ?";
                    $arrParams[] = trim($strOneLike);
                }

                $strWhere = "AND ( ".implode(" OR ", $arrWhere)." )";
            }
            else {
                $arrParams[] = $strNameFilter."%";
                $strWhere = "AND file_name LIKE ?";
            }
        }

        $strQuery = "SELECT COUNT(*)
                       FROM " . _dbprefix_ . "system,
                            " . _dbprefix_ . "mediamanager_file
                    WHERE system_id = file_id
                      AND file_ispackage = 1
                        " . (!$bitActiveOnly ? "" : " AND system_status = 1 ") . "
                        " . ($strCategoryFilter === false ? "" : " AND file_cat = ?  ") . "
                        " . $strWhere . "
                        ";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }


    /**
     * Loads a single folder for a given path. Please be aware that you have to pass the previd, too
     *
     * @param $strPrevId
     * @param $strPath
     *
     * @return class_module_mediamanager_file
     */
    public static function getFolderForPath($strPrevId, $strPath) {

        $strQuery = "SELECT system_id
                       FROM " . _dbprefix_ . "system,
                            " . _dbprefix_ . "mediamanager_file
                    WHERE system_id = file_id
                      AND file_type = ?
                      AND system_prev_id = ?
                      AND file_filename = ?";
        $arrId = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array(self::$INT_TYPE_FOLDER, $strPrevId, $strPath));
        if(isset($arrId["system_id"]) && validateSystemid($arrId["system_id"]))
            return new class_module_mediamanager_file($arrId["system_id"]);
        else
            return null;
    }

    /**
     * Counts the number of files returned by the corresponding query
     *
     * @param string $strPrevID
     * @param bool|int $intTypeFilter
     * @param bool $bitActiveOnly
     * @param bool $bitOnlyPackages
     *
     * @return int
     * @static
     */
    public static function getFileCount($strPrevID, $intTypeFilter = false, $bitActiveOnly = false, $bitOnlyPackages = false) {

        $arrParams = array();
        $arrParams[] = $strPrevID;
        if($intTypeFilter !== false) {
            $arrParams[] = $intTypeFilter;
        }

        if($bitOnlyPackages)
            $arrParams[] = self::$INT_TYPE_FOLDER;

        $strQuery = "SELECT COUNT(*)
                       FROM " . _dbprefix_ . "system,
                            " . _dbprefix_ . "mediamanager_file
                    WHERE system_id = file_id
                      AND system_prev_id = ?
                         " . ($intTypeFilter !== false ? " AND file_type = ? " : "") . "
                        " . (!$bitActiveOnly ? "" : "AND system_status = 1 ") . "
                        " . (!$bitOnlyPackages ? "" : " AND (file_ispackage = 1 OR file_type= ? ) ") . "";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }

    /**
     * Searches the repository-id for the current file.
     *
     * @return string
     */
    public function getRepositoryId() {

        $strPrevid = $this->getPrevId();
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "mediamanager_repo WHERE repo_id = ?", array($strPrevid));
        while($arrRow["COUNT(*)"] == 0 && $strPrevid != "" && $strPrevid != "0") {

            $objFile = new class_module_mediamanager_file($strPrevid);
            $strPrevid = $objFile->getPrevId();
            $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM " . _dbprefix_ . "mediamanager_repo WHERE repo_id = ?", array($strPrevid));
        }

        return $strPrevid;
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
            while($objRepo != null && !$objRepo instanceof class_module_mediamanager_repo) {
                $objRepo = class_objectfactory::getInstance()->getObject($objRepo->getPrevId());
            }
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
            foreach($arrObjDB as $intKeyDB => $objOneFileDB) {
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
                    if($objOneFolderDB->getStrFilename() == $strPath . "/" . $strFolder) {
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
            $strFileFilename = $strPath . "/" . $strFolder;
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
        return @filesize(_realpath_ . $this->getStrFilename());
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
     */
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

    public function getIntHits() {
        return (int)$this->intHits;
    }

    public function getIntType() {
        return (int)$this->intType;
    }

    /**
     * @param int $bitIspackage
     */
    public function setBitIspackage($bitIspackage) {
        $this->bitIspackage = $bitIspackage;
    }

    /**
     * @return int
     */
    public function getBitIspackage() {
        return $this->bitIspackage;
    }

    /**
     * @param int $intCat
     */
    public function setStrCat($intCat) {
        $this->strCat = $intCat;
    }

    /**
     * @return int
     */
    public function getStrCat() {
        return $this->strCat;
    }

    /**
     * @param string $strScreen1
     */
    public function setStrScreen1($strScreen1) {
        $this->strScreen1 = $strScreen1;
    }

    /**
     * @return string
     */
    public function getStrScreen1() {
        return $this->strScreen1;
    }

    /**
     * @param string $strScreen2
     */
    public function setStrScreen2($strScreen2) {
        $this->strScreen2 = $strScreen2;
    }

    /**
     * @return string
     */
    public function getStrScreen2() {
        return $this->strScreen2;
    }

    /**
     * @param string $strScreen3
     */
    public function setStrScreen3($strScreen3) {
        $this->strScreen3 = $strScreen3;
    }

    /**
     * @return string
     */
    public function getStrScreen3() {
        return $this->strScreen3;
    }



}

