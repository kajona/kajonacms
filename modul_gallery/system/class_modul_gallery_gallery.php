<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

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
     * @param string $strSystemid (use "" on new objects)
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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."gallery_gallery" => "gallery_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "gallery ".$this->getStrName();
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

    /**
     * Transfers the current state back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
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
     * @see class_model::onInsertToDb()
     *
     * @return bool
     */
    public function onInsertToDb() {
        //gallery was created, create an internal filemanager repo
        $objRepo = new class_modul_filemanager_repo();
        $objRepo->setStrPath($this->getStrPath());
        $objRepo->setStrForeignId($this->getSystemid());
        $objRepo->setStrName("Internal Repo for Gallery ".$this->getSystemid());
        $objRepo->setStrViewFilter(class_modul_gallery_gallery::$strFilemanagerViewFilter);
        $objRepo->setStrUploadFilter(class_modul_gallery_gallery::$strFilemanagerUploadFilter);
        return $objRepo->updateObjectToDb();
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
	public function deleteGallery() {
	    class_logger::getInstance()->addLogRow("deleted gallery ".$this->getSystemid(), class_logger::$levelInfo);

	    $strQuery = "DELETE FROM "._dbprefix_."gallery_gallery
					WHERE gallery_id='".dbsafeString($this->getSystemid())."'";
	    if($this->objDB->_query($strQuery)) {
	        if($this->deleteSystemRecord($this->getSystemid())) {
                //and delete the filemanager repo
                $objRepo = class_modul_filemanager_repo::getRepoForForeignId($this->getSystemid());
                if($objRepo->deleteRepo())
                    return true;
	        }
	    }

	    return false;
	}

	/**
	 * Deletes a folder and all subfolders / files from the db
	 *
	 * @param bool $bitIgnoreRights
	 * @return bool
	 */
	public function deleteGalleryRecursive($bitIgnoreRights = false) {
		$bitReturn = true;
		//Load all child-records
		$objFolders = class_modul_gallery_pic::loadFoldersDB($this->getSystemid());
		//And call us foreach folder
		if(count($objFolders) > 0) {
			foreach($objFolders as $objOneFolder) {
                $objGallery = new class_modul_gallery_gallery($objOneFolder->getSystemid());
				if(!$objGallery->deleteGalleryRecursive()) {
					$bitReturn = false;
					break;
				}
			}
		}

		//delete folders and files
		if(count($objFolders) > 0 && $bitReturn) {
			foreach ($objFolders as $objOneFolder)
				if($objOneFolder->rightDelete() || $bitIgnoreRights)
				    $objOneFolder->deletePictureRecord();
		}

		$objFiles = class_modul_gallery_pic::loadFilesDB($this->getSystemid(), true);
		if(count($objFiles) > 0 && $bitReturn) {
			foreach($objFiles as $objOneFile)
				if($objOneFile->rightDelete() || $bitIgnoreRights)
				    $objOneFile->deletePictureRecord();
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