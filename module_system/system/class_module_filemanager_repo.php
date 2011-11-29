<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/


/**
 * Model for a filemanager repo
 *
 * @package modul_filemanager
 * @author sidler@mulchprod.de
 */
class class_module_filemanager_repo extends class_model implements interface_model  {

    private $strPath = "";
    private $strName = "";
    private $strUploadFilter = "";
    private $strViewFilter = "";
    private $strForeignId = "";

     /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_filemanager";
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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."filemanager" => "filemanager_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "filemnager repo ".$this->getStrName();
    }


    /**
     * initalizes the current object with proper values
     *
     */
    public function  initObject() {
        $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
                                   "._dbprefix_."system
						WHERE system_id = filemanager_id
						AND system_id = ? ";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(isset($arrRow["filemanager_name"])) {
            $this->setStrName($arrRow["filemanager_name"]);
            $this->setStrPath($arrRow["filemanager_path"]);
            $this->setStrUploadFilter($arrRow["filemanager_upload_filter"]);
            $this->setStrViewFilter($arrRow["filemanager_view_filter"]);
            $this->setStrForeignId($arrRow["filemanager_foreign_id"]);
        }
    }

    protected function updateStateToDb() {

        $strQuery = "UPDATE "._dbprefix_."filemanager
                     SET filemanager_name = ?,
                         filemanager_path = ?,
                         filemanager_upload_filter = ?,
                         filemanager_view_filter = ?,
                         filemanager_foreign_id = ?
                     WHERE filemanager_id = ?";
        return $this->objDB->_pQuery($strQuery, array(
            $this->getStrName(), $this->getStrPath(), $this->getStrUploadFilter(), $this->getStrViewFilter(), $this->getStrForeignId(), $this->getSystemid()
        ));

    }


    /**
     * Loads all repos currently available
     *
     * @param boolean $bitLoadForeign Indicates weather foreign repos should be hidden or not
     * @return mixed Array of objects
     * @static
     */
    public static function getAllRepos($bitLoadForeign = false) {
        $arrReturn = array();
        $objDB = class_carrier::getInstance()->getObjDB();


        $strQuery = "SELECT system_id FROM "._dbprefix_."filemanager AS filem, "._dbprefix_."system AS system
						WHERE system_id = filemanager_id
                    ".(!$bitLoadForeign || $bitLoadForeign == "false" ? " AND (filemanager_foreign_id IS NULL OR filemanager_foreign_id = '')" : "")."";
        $arrIds = $objDB->getPArray($strQuery, array());

        foreach ($arrIds as $arrOneID)
            $arrReturn[] = new class_module_filemanager_repo($arrOneID["system_id"]);

        return $arrReturn;
    }

    /**
     * Searches for an repo identified by a foreign id
     * @param string $strForeignId
     * @return class_module_filemanager_repo
     * @static
     */
    public static function getRepoForForeignId($strForeignId) {
        $objDB = class_carrier::getInstance()->getObjDB();

        $strQuery = "SELECT filemanager_id FROM "._dbprefix_."filemanager
                        WHERE filemanager_foreign_id = ? ";
        $arrId = $objDB->getPRow($strQuery, array($strForeignId));

        if(isset($arrId["filemanager_id"]))
            return new class_module_filemanager_repo($arrId["filemanager_id"]);

        return null;
    }

    /**
     * Deletes the record with the given systemid
     *
     * @param string $strSystemid
     * @static
     * @return bool
     */
    public function deleteRepo() {
        class_logger::getInstance()->addLogRow("deleted repo ".$this->getSystemid(), class_logger::$levelInfo);


		$this->objDB->transactionBegin();
		$bitCommit = true;
        //Delete from the system-table

        //And the repo itself
        $strQuery = "DELETE FROM "._dbprefix_."filemanager WHERE filemanager_id=?";
        if(!$this->objDB->_pQuery($strQuery, array($this->getSystemid())))
            $bitCommit = false;

        if(!$this->deleteSystemRecord($this->getSystemid()))
            $bitCommit = false;
		//end tx
		if($bitCommit) {
			$this->objDB->transactionCommit();
			return true;
		}
		else {
			$this->objDB->transactionRollback();
			echo "Rollback";
			return false;
		}
    }

    /**
     * Checks if the current repo is used as a foreign-repo by another module
     * @return bool
     */
    public function isForeignRepo() {
        if($this->getStrForeignId() != "" && $this->getStrForeignId() != null)
            return true;
        else
            return false;
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
    public function getStrForeignId() {
        return $this->strForeignId;
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
    public function setStrForeignId($strForeignId) {
        $this->strForeignId = $strForeignId;
    }

}


?>