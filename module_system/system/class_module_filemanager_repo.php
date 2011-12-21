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
 * @package module_filemanager
 * @author sidler@mulchprod.de
 */
class class_module_filemanager_repo extends class_model implements interface_model, interface_admin_listable  {

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

        $this->setArrModuleEntry("modul", "filemanager");
        $this->setArrModuleEntry("moduleId", _filemanager_modul_id_);

		//base class
		parent::__construct($strSystemid);

    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."filemanager" => "filemanager_id");
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_folderOpen.gif";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * initializes the current object with proper values
     *
     */
    protected function  initObjectInternal() {
        $strQuery = "SELECT * FROM "._dbprefix_."filemanager,
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
     * @param bool|int $intStart
     * @param bool|int $intEnd
     * @return mixed Array of objects
     * @static
     */
    public static function getAllRepos($bitLoadForeign = false, $intStart = false, $intEnd = false) {
        $arrReturn = array();
        $objDB = class_carrier::getInstance()->getObjDB();


        $strQuery = "SELECT system_id FROM "._dbprefix_."filemanager AS filem, "._dbprefix_."system AS system
						WHERE system_id = filemanager_id
                    ".(!$bitLoadForeign || $bitLoadForeign == "false" ? " AND (filemanager_foreign_id IS NULL OR filemanager_foreign_id = '')" : "")." ";

        if($intStart !== false && $intEnd !== false)
            $arrIds = $objDB->getPArraySection($strQuery, array(), $intStart, $intEnd);
        else
            $arrIds = $objDB->getPArray($strQuery, array());

        foreach ($arrIds as $arrOneID)
            $arrReturn[] = new class_module_filemanager_repo($arrOneID["system_id"]);

        return $arrReturn;
    }

    /**
     * Returns the number of repos available
     * @static
     * @param bool $bitLoadForeign
     * @return mixed
     */
    public static function getAllReposCount($bitLoadForeign = false) {
        $objDB = class_carrier::getInstance()->getObjDB();

        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."filemanager AS filem, "._dbprefix_."system AS system
                        WHERE system_id = filemanager_id
                    ".(!$bitLoadForeign || $bitLoadForeign == "false" ? " AND (filemanager_foreign_id IS NULL OR filemanager_foreign_id = '')" : "")."";

        $arrRow = $objDB->getPRow($strQuery, array());
        return $arrRow["COUNT(*)"];

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
     * @return bool
     */
    protected function deleteObjectInternal() {

        //And the repo itself
        $strQuery = "DELETE FROM "._dbprefix_."filemanager WHERE filemanager_id=?";
        if($this->objDB->_pQuery($strQuery, array($this->getSystemid())))
            return true;

        return false;
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


