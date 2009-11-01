<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/

/**
 * This class manages all stuff related with folders, used by pages. Folders just exist in the database,
 * not in the filesystem
 *
 * @package modul_pages
 */
class class_modul_pages_folder extends class_model implements interface_model  {

    private $strName = "";
    private $strPrevId = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_pages";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _pages_folder_id_;
		$arrModul["table"]       		= _dbprefix_."page";
		$arrModul["modul"]				= "pages";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM "._dbprefix_."system WHERE system_id='".$this->objDB->dbsafeString($this->getSystemid())."'";
        $arrRow = $this->objDB->getRow($strQuery);
        if(count($arrRow) > 0)
            $this->setStrName($arrRow["system_comment"]);
            $this->strPrevId = $this->getPrevId();
    }

    /**
     * saves the current object as a new object to the database
     *
     * @param string $strPrevId
     * @return bool
     */
    public function saveObjectToDb($strPrevId) {
        $bitReturn = false;

        if(!validateSystemid($strPrevId))
            $strPrevId = $this->getModuleSystemid($this->arrModule["modul"]);

        class_logger::getInstance()->addLogRow("new folder ".$this->getStrName(), class_logger::$levelInfo);
		if($this->getStrName() != "") 	{
		    $strFolderID = $this->createSystemRecord($strPrevId, $this->getStrName());
			if($strFolderID != "") {
			    $this->setSystemid($strFolderID);
			    $bitReturn = true;
			}
		}
		return $bitReturn;
    }

    /**
     * Updates the current object to the database
     *
     * @return bool
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated folder ".$this->getStrName(), class_logger::$levelInfo);
        $this->setEditDate();
		$strQuery = "UPDATE "._dbprefix_."system
					 SET system_comment='".$this->objDB->dbsafeString($this->getStrName())."',
					     system_prev_id='".dbsafeString($this->strPrevId)."'
					 WHERE system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
		return $this->objDB->_query($strQuery);
    }

    /**
	 * Returns a list of folders under the given systemid
	 *
	 * @param string $strSystemid
	 * @return mixed
	 * @static
	 */
	public static function getFolderList($strSystemid = "") {
		if(!validateSystemid($strSystemid))
			$strSystemid = class_modul_system_module::getModuleByName("pages")->getSystemid();
            
		//Get all folders
		$strQuery = "SELECT system_id FROM "._dbprefix_."system
		              WHERE system_module_nr="._pages_folder_id_."
		                AND system_prev_id='".dbsafeString($strSystemid)."'
		             ORDER BY system_comment ASC";

		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_pages_folder($arrOneId["system_id"]);

		return $arrReturn;
	}


	/**
	 * Changes Position of a folder in the system-tree
	 *
	 * @param string $strFolderID
	 * @param string $strNewPrevID
	 * @return bool
	 * @static
	 */
	public static function moveFolder($strFolderID, $strNewPrevID) {

        if(!validateSystemid($strNewPrevID))
            $strNewPrevID = class_modul_system_module::getModuleByName("pages")->getSystemid();

		$strQuery = "UPDATE "._dbprefix_."system
		              SET  system_prev_id='".dbsafeString($strNewPrevID)."'
		              WHERE system_id='".dbsafeString($strFolderID)."'
		                AND system_module_nr="._pages_folder_id_;
		return class_carrier::getInstance()->getObjDB()->_query($strQuery);
	}


	/**
	 * Changes Position of a site in the system-tree
	 *
	 * @param string $strSiteID
	 * @param string $strNewPrevID
	 * @return bool
	 * @static
	 */
	public static function moveSite($strSiteID, $strNewPrevID) {

        if(!validateSystemid($strNewPrevID))
            $strNewPrevID = class_modul_system_module::getModuleByName("pages")->getSystemid();


		$strQuery = "UPDATE "._dbprefix_."system
		              SET system_prev_id='".dbsafeString($strNewPrevID)."'
		              WHERE system_id='".dbsafeString($strSiteID)."'
		              AND system_module_nr="._pages_modul_id_;
		return class_carrier::getInstance()->getObjDB()->_query($strQuery);
	}


	/**
	 * Returns all Pages listed in a given folder
	 *
	 * @param string $strFolderid
	 * @return string
	 * @static
	 */
	public static function getPagesInFolder($strFolderid = "") {
		if(!validateSystemid($strFolderid))
			$strFolderid = class_modul_system_module::getModuleByName("pages")->getSystemid();
            
		$strQuery = "SELECT system_id
						FROM "._dbprefix_."page as page,
							 "._dbprefix_."system as system
						WHERE system.system_prev_id='".dbsafeString($strFolderid)."'
							AND system.system_module_nr="._pages_modul_id_."
							AND system.system_id = page.page_id
							ORDER BY page_name";

		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_pages_page($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Looks up all folders with the given name
	 *
	 * @param string $strName
	 * @return array
	 */
	private function getFoldersByName($strName) {
		//Get all folders
		$strQuery = "SELECT system_id FROM "._dbprefix_."system
		              WHERE system_module_nr="._pages_folder_id_."
		                AND system_comment ='".dbsafeString($strName)."'
		             ORDER BY system_comment ASC";

		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_pages_folder($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Deletes a folder from the systems,
	 * currently just, if the folder is empty
	 *
	 * @param string $strFolderid
	 * @return bool
	 * @static
	 */
	public static function deleteFolder($strFolderid) {
	    class_logger::getInstance()->addLogRow("deleted folder ".$strFolderid, class_logger::$levelInfo);
	    $objRoot = new class_modul_system_common($strFolderid);
	    if(count(class_modul_pages_folder::getFolderList($strFolderid)) == 0 && count(class_modul_pages_folder::getPagesInFolder($strFolderid)) == 0)
	        return $objRoot->deleteSystemRecord($strFolderid);
	    else
	        return false;
	}

// --- GETTERS / SETTERS --------------------------------------------------------------------------------
    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName, $bitSecure = false) {
        //check, if theres already a folder with the same name at the same level
        if($bitSecure)
            $strName = $this->checkFolderName($strName);
        $this->strName = $strName;
    }

    /**
     * Checks, if a foldername already exits ob the current level.
     * Tries to find a valid foldername
     *
     * @param string $strName
     * @param int $intCounter
     * @return string
     */
    private function checkFolderName($strName, $intCounter = 0) {

        if($intCounter != 0)
            $strNameNew = $strName."_".$intCounter;
        else
            $strNameNew = $strName;

        $arrFolders = $this->getFoldersByName($strNameNew);
        if(count($arrFolders) != 0) {
            foreach ($arrFolders as $intKey => $objOneFolder) {
                //not the same folder as the current?
                if(($objOneFolder->getSystemid() != $this->getSystemid()) || $this->getSystemid() == "") {
                    //used on a different level?
                    if($objOneFolder->getPrevId() != $this->strPrevId) {
                        unset($arrFolders[$intKey]);
                    }
                }
                else
                    unset($arrFolders[$intKey]);
            }

            if(count($arrFolders) != 0)
                $strNameNew = $this->checkFolderName($strName, ++$intCounter);
        }
        return $strNameNew;
    }


    public function setStrPrevId($strPrevid) {
        //only allowed, if not the same id (loop)
        if($strPrevid != $this->getSystemid())
            $this->strPrevId = $strPrevid;
    }

}
?>