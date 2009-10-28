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
include_once(_systempath_."/class_modul_filemanager_repo.php");

/**
 * Model for glleries itself
 *
 * @package modul_gallery
 */
class class_modul_gallery_gallery extends class_model implements interface_model  {

    private $strPath = "";
    private $strTitle = "";

    public static $strFilemanagerViewFilter = ".jpg,.png,.gif,.jpeg";
    public static $strFilemanagerUploadFilter = ".jpg,.png,.gif,.jpeg";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_gallery";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _gallery_modul_id_;
		$arrModul["table"]       		= _dbprefix_."gallery_gallery";
		$arrModul["modul"]				= "gallery";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initialises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM
						".$this->arrModule["table"].",
						"._dbprefix_."system
						WHERE gallery_id = system_id
						AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'
						ORDER BY gallery_title ASC";
        $arrRow =  $this->objDB->getRow($strQuery);
        if(count($arrRow) > 1) {
            $this->setStrPath($arrRow["gallery_path"]);
            $this->setStrTitle($arrRow["gallery_title"]);
        }
    }

    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated gallery ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE ".$this->arrModule["table"]."
					SET gallery_path='".$this->objDB->dbsafeString($this->getStrPath())."',
					    gallery_title='".$this->objDB->dbsafeString($this->getStrTitle())."'
					WHERE gallery_id='".$this->objDB->dbsafeString($this->getSystemid())."'";

		if($this->objDB->_query($strQuery)) {
            //update the filemanager-repo
            $objRepo = class_modul_filemanager_repo::getRepoForForeignId($this->getSystemid());
            $objRepo->setStrPath($this->getStrPath());
            if($objRepo->updateObjectToDb())
                return true;
        }
        else
            return false;
    }

    /**
     * Saves the current object as a new object to the db
     *
     * @return unknown
     */
    public function saveObjectToDb() {
        //start tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
		//system-tables
		$strGalID = $this->createSystemRecord($this->getModuleSystemid($this->arrModule["modul"]), "Gallery: ".$this->getStrTitle());
		$this->setSystemid($strGalID);
		class_logger::getInstance()->addLogRow("new gallery ".$this->getSystemid(), class_logger::$levelInfo);
		//and the gall itself
		$strQuery = "INSERT INTO ".$this->arrModule["table"]."
		            (gallery_id, gallery_title, gallery_path) VALUES
		            ('".$this->objDB->dbsafeString($strGalID)."', '".$this->objDB->dbsafeString($this->getStrTitle())."', '".$this->objDB->dbsafeString($this->getStrPath())."')";
		if(!$this->objDB->_query($strQuery))
		    $bitCommit = false;

		if($bitCommit) {
		    $this->objDB->transactionCommit();

            //gallery was created, create an internal filemanager repo
            $objRepo = new class_modul_filemanager_repo();
            $objRepo->setStrPath($this->getStrPath());
            $objRepo->setStrForeignId($this->getSystemid());
            $objRepo->setStrName("Internal Repo for Gallery ".$this->getSystemid());
            $objRepo->setStrViewFilter(class_modul_gallery_gallery::$strFilemanagerViewFilter);
            $objRepo->setStrUploadFilter(class_modul_gallery_gallery::$strFilemanagerUploadFilter);
            $objRepo->saveObjectToDb();

		    return true;
		}
		else {
		    $strReturn = "Rollback!";
		    $this->objDB->transactionRollback();
		    return false;
		}
    }

    /**
	 * loads all available galleries
	 *
	 * @return mixed array oj objects
	 * @static
	 */
	public static function getGalleries() {
		$strQuery = "SELECT system_id FROM
						"._dbprefix_."gallery_gallery,
						"._dbprefix_."system
						WHERE gallery_id = system_id
						ORDER BY gallery_title ASC";
		$objDB = class_carrier::getInstance()->getObjDB();
        $arrIds = $objDB->getArray($strQuery);
        $arrReturn = array();
        foreach ($arrIds as $arrOneRecord) {
        	$arrReturn[] = new class_modul_gallery_gallery($arrOneRecord["system_id"]);
        }
        return $arrReturn;
	}

	/**
	 * Deletes a gallery. CAUTION: NOT the contents, invoke deleteGalleryRecursive()
	 * before!
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public static function deleteGallery($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted gallery ".$strSystemid, class_logger::$levelInfo);
	    $objDB = class_carrier::getInstance()->getObjDB();
        $objRoot = new class_modul_system_common();

	    $strQuery = "DELETE FROM "._dbprefix_."gallery_gallery
					WHERE gallery_id='".dbsafeString($strSystemid)."'";
	    if($objDB->_query($strQuery)) {
	        if($objRoot->deleteSystemRecord($strSystemid)) {
                //and delete the filemanager repo
                $objRepo = class_modul_filemanager_repo::getRepoForForeignId($strSystemid);
                if($objRepo->deleteRepo())
                    return true;
	        }
	    }

	    return false;
	}

	/**
	 * Deletes a folder and all subfolders / files from the db
	 *
	 * @param string $strPrevID
	 * @param bool $bitIgnoreRights
	 * @return bool
	 */
	public static function deleteGalleryRecursive($strPrevID, $bitIgnoreRights = false) {
		$bitReturn = true;
		$objRoot = new class_modul_system_common();
	    $objDB = class_carrier::getInstance()->getObjDB();
		//Load all child-records
		$objFolders = class_modul_gallery_pic::loadFoldersDB($strPrevID);
		//And call us foreach folder
		if(count($objFolders) > 0) {
			foreach($objFolders as $objOneFolder) {
				if(!class_modul_gallery_gallery::deleteGalleryRecursive($objOneFolder->getSystemid())) {
					$bitReturn = false;
					break;
				}
			}
		}

		//delete folders and files
		if(count($objFolders) > 0 && $bitReturn) {
			foreach ($objFolders as $objOneFolder)
				if($objOneFolder->rightDelete() || $bitIgnoreRights)
				    class_modul_gallery_pic::deletePictureRecord($objOneFolder->getSystemid());
		}

		$objFiles = class_modul_gallery_pic::loadFilesDB($strPrevID, true);
		if(count($objFiles) > 0 && $bitReturn) {
			foreach($objFiles as $objOneFile)
				if($objOneFile->rightDelete() || $bitIgnoreRights)
				    class_modul_gallery_pic::deletePictureRecord($objOneFile->getSystemid());
		}

		return $bitReturn;
	}

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrPath() {
        return $this->strPath;
    }
    public function getStrTitle() {
        return $this->strTitle;
    }
    public function getStrName() {
        return $this->strTitle;
    }

    public function setStrPath($strPath) {
        $this->strPath = $strPath;
    }
    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

}

?>