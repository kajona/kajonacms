<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Mediamanager\System;

use Kajona\Packagemanager\System\PackagemanagerMetadata;
use Kajona\Pages\System\PagesPage;
use Kajona\Search\System\SearchResult;
use Kajona\System\System\AdminGridableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Filesystem;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmComparatorEnum;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistOrderby;
use Kajona\System\System\OrmObjectlistPropertyRestriction;
use Kajona\System\System\OrmObjectlistRestriction;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\SearchPortalobjectInterface;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemModule;
use Kajona\System\System\Zip;


/**
 * Model for a single file inside a mediamanagers' repo
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @targetTable mediamanager_file.file_id
 * @sortManager Kajona\System\System\CommonSortmanager
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 *
 * @formGenerator Kajona\Mediamanager\Admin\MediamanagerFileFormgenerator
 */
class MediamanagerFile extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, AdminGridableInterface, SearchPortalobjectInterface
{


    public static $INT_TYPE_FILE = 0;
    public static $INT_TYPE_FOLDER = 1;

    /**
     * @var string
     * @tableColumn mediamanager_file.file_name
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     *
     * @addSearchIndex
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn mediamanager_file.file_filename
     * @tableColumnDatatype char254
     *
     * @addSearchIndex
     */
    private $strFilename = "";

    /**
     * @var string
     * @tableColumn mediamanager_file.file_description
     * @tableColumnDatatype text
     * @blockEscaping
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryWysiwygsmall
     *
     * @addSearchIndex
     */
    private $strDescription = "";

    /**
     * @var string
     * @tableColumn mediamanager_file.file_subtitle
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     *
     * @addSearchIndex
     */
    private $strSubtitle = "";

    /**
     * @var int
     * @tableColumn mediamanager_file.file_hits
     * @tableColumnDatatype int
     */
    private $intHits = 0;

    /**
     * 0 = file, 1 = folder
     *
     * @var int
     * @tableColumn mediamanager_file.file_type
     * @tableColumnDatatype int
     */
    private $intType = 0;

    /**
     * @var int
     * @tableColumn mediamanager_file.file_ispackage
     * @tableColumnDatatype int
     */
    private $bitIspackage = 0;

    /**
     * @var int
     * @tableColumn mediamanager_file.file_cat
     * @tableColumnDatatype char254
     */
    private $strCat = "";

    /**
     * @var string
     * @tableColumn mediamanager_file.file_screen1
     * @tableColumnDatatype char254
     */
    private $strScreen1 = "";

    /**
     * @var string
     * @tableColumn mediamanager_file.file_screen2
     * @tableColumnDatatype char254
     */
    private $strScreen2 = "";

    /**
     * @var string
     * @tableColumn mediamanager_file.file_screen3
     * @tableColumnDatatype char254
     */
    private $strScreen3 = "";

    /**
     * Return an on-lick link for the passed object.
     * This link is used by the backend-search for the autocomplete-field
     *
     * @see getLinkAdminHref()
     * @return mixed
     */
    public function getSearchAdminLinkForObject()
    {
        return Link::getLinkAdminHref("mediamanager", "edit", "&systemid=".$this->getSystemid()."&source=search");
    }

    /**
     * Return an on-lick link for the passed object.
     * This link is rendered by the portal search result generator, so
     * make sure the link is a valid portal page.
     * If you want to suppress the entry from the result, return an empty string instead.
     * If you want to add additional entries to the result set, clone the result and modify
     * the new instance to your needs. Pack them in an array and they'll be merged
     * into the result set afterwards.
     * Make sure to return the passed result-object in this array, too.
     *
     * @param SearchResult $objResult
     *
     * @see getLinkPortalHref()
     * @return mixed
     */
    public function updateSearchResult(SearchResult $objResult)
    {
        $objLanguages = new LanguagesLanguage();
        $objORM = new OrmObjectlist();

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."element_downloads,
                            "._dbprefix_."page_element,
                            "._dbprefix_."system
                      WHERE download_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND system_status = 1
                        ".$objORM->getDeletedWhereRestriction()."
                        AND page_element_ph_language = ? ";

        $arrRows = $this->objDB->getPArray($strQuery, array($this->getRepositoryId(), $objResult->getObjSearch()->getStrPortalLangFilter()));

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."element_gallery,
                            "._dbprefix_."page_element,
                            "._dbprefix_."system
                      WHERE gallery_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND system_status = 1
                        ".$objORM->getDeletedWhereRestriction()."
                        AND page_element_ph_language = ? ";

        $arrRows = array_merge($arrRows, $this->objDB->getPArray($strQuery, array($this->getRepositoryId(), $objResult->getObjSearch()->getStrPortalLangFilter())));
        $arrReturn = array();

        foreach ($arrRows as $arrOneElement) {


            $objCur = Objectfactory::getInstance()->getObject($arrOneElement["system_id"]);
            while($objCur != null && !$objCur instanceof PagesPage && !$objCur instanceof SystemModule) {
                $objCur = Objectfactory::getInstance()->getObject($objCur->getStrPrevId());
            }

            if ($objCur instanceof PagesPage && $objCur->getStrName() != 'master') {
                $objCurResult = clone($objResult);
                $objCurResult->setStrPagelink(Link::getLinkPortal($objCur->getStrName(), "", "_self", $this->getStrDisplayName(), "mediaFolder", "&highlight=".urlencode(html_entity_decode($objResult->getObjSearch()->getStrQuery(), ENT_QUOTES, "UTF-8")), $this->getPrevId(), "", "", $this->getStrDisplayName()));
                $objCurResult->setStrPagename($objCur->getStrName());
                $objCurResult->setStrDescription($this->getStrDescription());
                $arrReturn[] = $objCurResult;

            }


        }

        return $arrReturn;
    }

    /**
     * Since the portal may be split in different languages,
     * return the content lang of the current record using the common
     * abbreviation such as "de" or "en".
     * If the content is not assigned to any language, return "" instead (e.g. a single image).
     *
     * @return mixed
     */
    public function getContentLang()
    {
        return "";
    }


    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {

        if ($this->getIntType() == self::$INT_TYPE_FOLDER) {
            return "icon_folderClosed";
        }

        //get the filetype
        $arrMime = Carrier::getInstance()->getObjToolkit("admin")->mimeType($this->getStrFilename());
        $strImage = $arrMime[2];
        $strAlt = $arrMime[0];

        if ($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif") {
            $strAlt = "<img src='"._webpath_."/image.php?image=".urlencode($this->getStrFilename())."&amp;maxWidth=100&amp;maxHeight=100' />";
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
    public function getStrGridIcon()
    {
        if ($this->getIntType() == self::$INT_TYPE_FOLDER) {
            return _webpath_.Resourceloader::getInstance()->getWebPathForModule("module_mediamanager")."/admin/pics/folder_grey.png";
        }

        //get the filetype
        $arrMime = Carrier::getInstance()->getObjToolkit("admin")->mimeType($this->getStrFilename());

        if ($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif") {
            return _webpath_."/image.php?image=".urlencode($this->getStrFilename())."&amp;fixedWidth=260&amp;fixedHeight=180";
        }

        return _webpath_.Resourceloader::getInstance()->getWebPathForModule("module_mediamanager")."/admin/pics/file.png";
    }


    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        $strReturn = basename($this->getStrFilename());
        if ($this->getIntType() == self::$INT_TYPE_FILE) {
            $strReturn .= ",  ".bytesToString(@filesize(_realpath_.$this->getStrFilename()));
            $strReturn .= ", ".$this->getIntHits()." ".$this->getLang("file_hits", "mediamanager");
        }

        return $strReturn;
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return $this->getStrSubtitle();
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrName();
    }

    /**
     * @inheritDoc
     */
    public function deleteObject()
    {
        return $this->deleteObjectFromDatabase();
    }


    public function deleteObjectFromDatabase()
    {

        //delete the current file
        //only delete of not used by another repo directly
        if ($this->getParam("deleteMediamanagerRepo") != true && ($this->getParam("mediamanagerDeleteFileFromFilesystem") == true || $this->countOtherFilesWithSamePath() == 0)) {
            $objFilesystem = new Filesystem();
            if ($this->getIntType() == self::$INT_TYPE_FILE) {
                $objFilesystem->fileDelete($this->getStrFilename());
            }
            else {
                $objFilesystem->folderDelete($this->getStrFilename());
            }
        }

        return parent::deleteObjectFromDatabase();
    }

    /**
     * Internal helper to count the number of files with the same path
     *
     * @return mixed
     */
    private function countOtherFilesWithSamePath()
    {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."system,
                            "._dbprefix_."mediamanager_file
                    WHERE system_id = file_id
                      AND file_filename = ?
                      AND file_id != ?";


        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($this->getStrFilename(), $this->getSystemid()));
        return $arrRow["COUNT(*)"];
    }

    protected function updateStateToDb()
    {

        //check if its a valid package
        if (StringUtil::endsWith($this->getStrFilename(), ".phar") || StringUtil::endsWith($this->getStrFilename(), ".zip")) {
            $this->updatePackageInformation();
        }

        return parent::updateStateToDb();
    }

    /**
     * Updates the internal information of the file based on the metadata.xml
     */
    private function updatePackageInformation()
    {
        //if it's either a phar or a zip based package, do some extracts
        $objZip = new Zip();
        if ((StringUtil::endsWith($this->getStrFilename(), ".phar") && is_file("phar://"._realpath_."/".$this->getStrFilename()."/metadata.xml")) ||
            (StringUtil::endsWith($this->getStrFilename(), ".zip") && $objZip->getFileFromArchive($this->getStrFilename(), "/metadata.xml") !== false)
        ) {
            $objMetadata = new PackagemanagerMetadata();
            $objMetadata->autoInit($this->getStrFilename());
            $this->setBitIspackage(1);
            $this->setStrName($objMetadata->getStrTitle());
            $this->setStrDescription($objMetadata->getStrDescription());
            $this->setStrCat($objMetadata->getStrType());
        }
        else {
            $this->setBitIspackage(0);
        }
    }

    /**
     * Increases a files' hits without touching its last-modified date
     *
     * @return bool
     */
    public function increaseHits()
    {
        $this->setIntHits($this->getIntHits() + 1);
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
     * @return MediamanagerFile[]
     * @static
     */
    public static function loadFilesDB($strPrevID, $intTypeFilter = false, $bitActiveOnly = false, $intStart = null, $intEnd = null, $bitOnlyPackages = false)
    {

        $objORM = new OrmObjectlist();
        if ($intTypeFilter !== false) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND file_type = ? ", array($intTypeFilter)));
        }

        if ($bitActiveOnly) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND system_status = 1 ", array()));
        }

        if ($bitOnlyPackages) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND (file_ispackage = 1 OR file_type= ? ) ", array(self::$INT_TYPE_FOLDER)));
        }

        return $objORM->getObjectList(get_called_class(), $strPrevID, $intStart, $intEnd);
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
    public static function getFileCount($strPrevID, $intTypeFilter = false, $bitActiveOnly = false, $bitOnlyPackages = false)
    {

        $objORM = new OrmObjectlist();
        if ($intTypeFilter !== false) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND file_type = ? ", array($intTypeFilter)));
        }

        if ($bitActiveOnly) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND system_status = 1 ", array()));
        }

        if ($bitOnlyPackages) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND (file_ispackage = 1 OR file_type= ? ) ", array(self::$INT_TYPE_FOLDER)));
        }

        return $objORM->getObjectCount(get_called_class(), $strPrevID);
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
     * @return MediamanagerFile[]
     */
    public static function getFlatPackageList($strCategoryFilter = false, $bitActiveOnly = false, $intStart = null, $intEnd = null, $strNameFilter = false)
    {

        $objORM = new OrmObjectlist();
        if ($bitActiveOnly) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND system_status = 1 ", array()));
        }
        if ($strCategoryFilter !== false) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND file_cat = ? ", array($strCategoryFilter)));
        }

        if ($strNameFilter !== false) {
            $arrParams = array();
            if (uniStrpos($strNameFilter, ",") !== false) {
                $arrWhere = array();
                foreach (explode(",", $strNameFilter) as $strOneLike) {
                    $arrWhere[] = " file_name = ?";
                    $arrParams[] = trim($strOneLike);
                }

                $strWhere = "AND ( ".implode(" OR ", $arrWhere)." )";
            }
            else {
                $arrParams[] = $strNameFilter."%";
                $strWhere = "AND file_name LIKE ?";
            }

            $objORM->addWhereRestriction(new OrmObjectlistRestriction($strWhere, $arrParams));
        }
        $objORM->addWhereRestriction(new OrmObjectlistPropertyRestriction("bitIspackage", OrmComparatorEnum::Equal(), 1));
        $objORM->addOrderBy(new OrmObjectlistOrderby("file_name ASC"));
        return $objORM->getObjectList(get_called_class(), "", $intStart, $intEnd);

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
    public static function getFlatPackageListCount($strCategoryFilter = false, $bitActiveOnly = false, $strNameFilter = false)
    {

        $objORM = new OrmObjectlist();

        if ($bitActiveOnly) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND system_status = 1 ", array()));
        }
        if ($strCategoryFilter !== false) {
            $objORM->addWhereRestriction(new OrmObjectlistRestriction(" AND file_cat = ? ", array($strCategoryFilter)));
        }

        if ($strNameFilter !== false) {
            $arrParams = array();
            if (uniStrpos($strNameFilter, ",") !== false) {
                $arrWhere = array();
                foreach (explode(",", $strNameFilter) as $strOneLike) {
                    $arrWhere[] = " file_name = ?";
                    $arrParams[] = trim($strOneLike);
                }

                $strWhere = "AND ( ".implode(" OR ", $arrWhere)." )";
            }
            else {
                $arrParams[] = $strNameFilter."%";
                $strWhere = "AND file_name LIKE ?";
            }

            $objORM->addWhereRestriction(new OrmObjectlistRestriction($strWhere, $arrParams));
        }
        $objORM->addWhereRestriction(new OrmObjectlistPropertyRestriction("bitIspackage", OrmComparatorEnum::Equal(), 1));
        return $objORM->getObjectCount(get_called_class());
    }


    /**
     * Loads a single folder for a given path. Please be aware that you have to pass the previd, too
     *
     * @param string $strPrevId
     * @param string $strPath
     *
     * @return MediamanagerFile
     * @deprecated use self::getFileForPath() instead
     */
    public static function getFolderForPath($strPrevId, $strPath)
    {
        return self::getFileForPath($strPrevId, $strPath);
    }

    /**
     * Tries to find a single mediamanager file identified by its path.
     * Pass the systemid of the matching repo/parent-id in order to find the correct file (e.g. if the file
     * is saved in multiple repos).
     *
     * @param string $strRepoId
     * @param string $strPath
     *
     * @return MediamanagerFile|null
     */
    public static function getFileForPath($strRepoId, $strPath)
    {

        $objORM = new OrmObjectlist();
        $objORM->addWhereRestriction(new OrmObjectlistRestriction("AND file_filename = ?", array($strPath)));
        $arrFiles = $objORM->getObjectList(get_called_class());

        foreach ($arrFiles as $objFile) {

            $objTemp = Objectfactory::getInstance()->getObject($objFile->getStrPrevId());
            while (validateSystemid($objTemp->getSystemid())) {
                if ($objTemp->getSystemid() == $strRepoId) {
                    return $objFile;
                }

                $objTemp = Objectfactory::getInstance()->getObject($objTemp->getStrPrevId());
            }
        }

        return null;
    }


    /**
     * Searches the repository-id for the current file.
     *
     * @return string
     */
    public function getRepositoryId()
    {

        $strPrevid = $this->getPrevId();
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."mediamanager_repo WHERE repo_id = ?", array($strPrevid));
        while ($arrRow["COUNT(*)"] == 0 && $strPrevid != "" && $strPrevid != "0") {
            /** @var MediamanagerFile $objFile */
            $objFile = Objectfactory::getInstance()->getObject($strPrevid);
            $strPrevid = $objFile->getPrevId();
            $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."mediamanager_repo WHERE repo_id = ?", array($strPrevid));
        }

        return $strPrevid;
    }


    /**
     * Syncs the files in the db with the files in the filesystem
     *
     * @param string $strPrevID
     * @param string $strPath
     * @param bool $bitRecursive
     * @param MediamanagerRepo|null $objRepo
     *
     * @return array [insert, delete]
     */
    public static function syncRecursive($strPrevID, $strPath, $bitRecursive = true, MediamanagerRepo $objRepo = null)
    {
        $arrReturn = array();
        $arrReturn["insert"] = 0;
        $arrReturn["delete"] = 0;

        if ($objRepo == null) {
            $objRepo = Objectfactory::getInstance()->getObject($strPrevID);
            while ($objRepo != null && !$objRepo instanceof MediamanagerRepo) {
                $objRepo = Objectfactory::getInstance()->getObject($objRepo->getPrevId());
            }
        }

        //Load the files in the DB
        $arrObjDB = MediamanagerFile::loadFilesDB($strPrevID);
        //Load files and folder from filesystem
        $objFilesystem = new Filesystem();

        //if the repo defines a view-filter, take that one into account
        $arrViewFilter = array();
        if ($objRepo->getStrViewFilter() != "") {
            $arrViewFilter = explode(",", $objRepo->getStrViewFilter());
        }

        $arrFilesystem = $objFilesystem->getCompleteList($strPath, $arrViewFilter, array(), array(".", "..", ".svn"));

        //So, lets sync those two arrays
        //At first the files
        foreach ($arrFilesystem["files"] as $intKeyFS => $arrOneFileFilesystem) {
            //search the db-array for this file
            foreach ($arrObjDB as $intKeyDB => $objOneFileDB) {
                //File or folder
                if ($objOneFileDB->getintType() == self::$INT_TYPE_FILE) {
                    //compare
                    if ($objOneFileDB->getStrFilename() == str_replace(_realpath_, "", $arrOneFileFilesystem["filepath"])) {
                        //And unset from both arrays
                        unset($arrFilesystem["files"][$intKeyFS]);
                        unset($arrObjDB[$intKeyDB]);
                    }
                }
            }
        }

        //And loop the folders
        foreach ($arrFilesystem["folders"] as $intKeyFolder => $strFolder) {
            //search the array for folders
            foreach ($arrObjDB as $intKeyDB => $objOneFolderDB) {
                //file or folder?
                if ($objOneFolderDB->getIntType() == self::$INT_TYPE_FOLDER) {
                    //compare
                    if ($objOneFolderDB->getStrFilename() == $strPath."/".$strFolder) {
                        //Unset from both
                        unset($arrFilesystem["folders"][$intKeyFolder]);
                        unset($arrObjDB[$intKeyDB]);
                    }
                }
            }
        }

        //the remaining records from the database have to be deleted!
        if (count($arrObjDB) > 0) {

            foreach ($arrObjDB as $objOneFileDB) {
                $objOneFileDB->deleteObjectFromDatabase();
                $arrReturn["delete"]++;
            }
        }

        //the remaining records from the filesystem have to be added
        foreach ($arrFilesystem["files"] as $arrOneFileFilesystem) {
            $strFileName = $arrOneFileFilesystem["filename"];
            $strFileFilename = str_replace(_realpath_, "", $arrOneFileFilesystem["filepath"]);
            $objFile = new MediamanagerFile();
            $objFile->setStrFilename($strFileFilename);
            $objFile->setStrName($strFileName);
            $objFile->setIntType(self::$INT_TYPE_FILE);

            $objFile->updateObjectToDb($strPrevID);
            $arrReturn["insert"]++;
        }

        foreach ($arrFilesystem["folders"] as $strFolder) {
            $strFileName = $strFolder;
            $strFileFilename = $strPath."/".$strFolder;
            $objFile = new MediamanagerFile();
            $objFile->setStrFilename($strFileFilename);
            $objFile->setStrName($strFileName);
            $objFile->setIntType(self::$INT_TYPE_FOLDER);
            $objFile->updateObjectToDb($strPrevID);
            $arrReturn["insert"]++;
        }

        //Load subfolders
        Carrier::getInstance()->getObjDB()->flushQueryCache();
        if ($bitRecursive) {
            $objFolders = MediamanagerFile::loadFilesDB($strPrevID, self::$INT_TYPE_FOLDER);
            foreach ($objFolders as $objOneFolderDB) {
                $arrTemp = MediamanagerFile::syncRecursive($objOneFolderDB->getSystemid(), $objOneFolderDB->getStrFilename(), $bitRecursive, $objRepo);
                $arrReturn["insert"] += $arrTemp["insert"];
                $arrReturn["delete"] += $arrTemp["delete"];
            }
        }

        return $arrReturn;
    }


    public function getIntFileSize()
    {
        return @filesize(_realpath_.$this->getStrFilename());
    }

    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    public function setStrFilename($strFilename)
    {
        $this->strFilename = $strFilename;
    }

    public function setStrDescription($strDesc)
    {
        $this->strDescription = $strDesc;
    }

    public function setStrSubtitle($strSubtitle)
    {
        $this->strSubtitle = $strSubtitle;
    }

    public function setIntHits($intHits)
    {
        $this->intHits = $intHits;
    }

    public function setIntType($intType)
    {
        $this->intType = $intType;
    }

    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    public function getStrFilename()
    {
        return $this->strFilename;
    }

    public function getStrDescription()
    {
        return $this->strDescription;
    }

    public function getStrSubtitle()
    {
        return $this->strSubtitle;
    }

    public function getIntHits()
    {
        return (int)$this->intHits;
    }

    public function getIntType()
    {
        return (int)$this->intType;
    }

    /**
     * @param int $bitIspackage
     */
    public function setBitIspackage($bitIspackage)
    {
        $this->bitIspackage = $bitIspackage;
    }

    /**
     * @return int
     */
    public function getBitIspackage()
    {
        return $this->bitIspackage;
    }

    /**
     * @param int $intCat
     */
    public function setStrCat($intCat)
    {
        $this->strCat = $intCat;
    }

    /**
     * @return int
     */
    public function getStrCat()
    {
        return $this->strCat;
    }

    /**
     * @param string $strScreen1
     */
    public function setStrScreen1($strScreen1)
    {
        $this->strScreen1 = $strScreen1;
    }

    /**
     * @return string
     */
    public function getStrScreen1()
    {
        return $this->strScreen1;
    }

    /**
     * @param string $strScreen2
     */
    public function setStrScreen2($strScreen2)
    {
        $this->strScreen2 = $strScreen2;
    }

    /**
     * @return string
     */
    public function getStrScreen2()
    {
        return $this->strScreen2;
    }

    /**
     * @param string $strScreen3
     */
    public function setStrScreen3($strScreen3)
    {
        $this->strScreen3 = $strScreen3;
    }

    /**
     * @return string
     */
    public function getStrScreen3()
    {
        return $this->strScreen3;
    }


}

