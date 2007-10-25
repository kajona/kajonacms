<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_filemanager_repo.php                                                                    *
* 	Model for filemanager repos                                                                         *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Model for a filemanager repo
 *
 * @package modul_filemanager
 */
class class_modul_filemanager_repo extends class_model implements interface_model  {

    private $strPath = "";
    private $strName = "";
    private $strUploadFilter = "";
    private $strViewFilter = "";

     /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_filemanager";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _filemanager_modul_id_;
		$arrModul["table"]       		= _dbprefix_."filemanager";
		$arrModul["modul"]				= "filemanager";

		//base class
		parent::__construct($arrModul, $strSystemid, "model");

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }


    /**
     * initalizes the current object with proper values
     *
     */
    public function  initObject() {
        $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
                                   "._dbprefix_."system
						WHERE system_id = filemanager_id
						AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
        $arrRow = $this->objDB->getRow($strQuery);

        $this->setStrName($arrRow["filemanager_name"]);
        $this->setStrPath($arrRow["filemanager_path"]);
        $this->setStrUploadFilter($arrRow["filemanager_upload_filter"]);
        $this->setStrViewFilter($arrRow["filemanager_view_filter"]);
    }

    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated repo ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE "._dbprefix_."filemanager
                     SET filemanager_name = '".$this->objDB->dbsafeString($this->getStrName())."',
                         filemanager_path = '".$this->objDB->dbsafeString($this->getStrPath())."',
                         filemanager_upload_filter = '".$this->objDB->dbsafeString($this->getStrUploadFilter())."',
                         filemanager_view_filter = '".$this->objDB->dbsafeString($this->getStrViewFilter())."'
                     WHERE filemanager_id = '".dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);

    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    public function saveObjectToDb() {
         $strRepoSystemId = $this->createSystemRecord(0, "Repo: ".$this->getStrName());
         $this->setSystemid($strRepoSystemId);
         class_logger::getInstance()->addLogRow("new repo ".$strRepoSystemId, class_logger::$levelInfo);
	     //And the repo-record
	     $strQuery = "INSERT INTO ".$this->arrModule["table"]."
		                (filemanager_id, filemanager_path, filemanager_name, filemanager_upload_filter, filemanager_view_filter) VALUES
		                ('".$this->objDB->dbsafeString($strRepoSystemId)."', '".$this->objDB->dbsafeString($this->getStrPath())."',
		                 '".$this->objDB->dbsafeString($this->getStrName())."', '".$this->objDB->dbsafeString($this->getStrUploadFilter())."',
		                 '".$this->objDB->dbsafeString($this->getStrViewFilter())."')";
		return $this->objDB->_query($strQuery);
    }

    /**
     * Loads all repos currently available
     *
     * @return mixed Array of objects
     * @static
     */
    public static function getAllRepos() {
        $arrReturn = array();
        $objDB = class_carrier::getInstance()->getObjDB();

        $strQuery = "SELECT system_id FROM "._dbprefix_."filemanager AS file, "._dbprefix_."system AS system
						WHERE system_id = filemanager_id";
        $arrIds = $objDB->getArray($strQuery);

        foreach ($arrIds as $arrOneID)
            $arrReturn[] = new class_modul_filemanager_repo($arrOneID["system_id"]);

        return $arrReturn;
    }

    /**
     * Deletes the record with the given systemid
     *
     * @param string $strSystemid
     * @static
     * @return bool
     */
    public static function deleteRepo($strSystemid) {
        class_logger::getInstance()->addLogRow("deleted repo ".$strSystemid, class_logger::$levelInfo);
        //start tx
        $objDB = class_carrier::getInstance()->getObjDB();
        $objRoot = new class_modul_system_common();

		$objDB->transactionBegin();
		$bitCommit = true;
        //Delete from the system-table

        //And the repo itself
        $strQuery = "DELETE FROM "._dbprefix_."filemanager WHERE filemanager_id='".dbsafeString($strSystemid)."'";
        if(!$objDB->_query($strQuery))
            $bitCommit = false;

        if(!$objRoot->deleteSystemRecord($strSystemid))
            $bitCommit = false;
		//end tx
		if($bitCommit) {
			$objDB->transactionCommit();
			return true;
		}
		else {
			$objDB->transactionRollback();
			echo "Rollback";
			return false;
		}
    }

// --- GETTER / SETTER ----------------------------------------------------------------------------------

    public function getStrPath() {
        return $this->strPath;
    }
    public function getStrName() {
        return $this->strName;
    }
    public function getStrUploadFilter() {
        return $this->strUploadFilter;
    }
    public function getStrViewFilter() {
        return $this->strViewFilter;
    }

    public function setStrPath($strPath) {
        $this->strPath = $strPath;
    }
    public function setStrName($strName) {
        $this->strName = $strName;
    }
    public function setStrUploadFilter($strUploadFilter) {
        $this->strUploadFilter = $strUploadFilter;
    }
    public function setStrViewFilter($strViewFilter) {
        $this->strViewFilter = $strViewFilter;
    }
}


?>