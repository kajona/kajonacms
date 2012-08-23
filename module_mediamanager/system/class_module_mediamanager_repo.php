<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Model for a mediamanagers repo itself
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 *
 * @targetTable mediamanager_repo.repo_id
 */
class class_module_mediamanager_repo extends class_model implements interface_model, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn mediamanager_repo.repo_path
     */
    private $strPath = "";

    /**
     * @var string
     * @tableColumn mediamanager_repo.repo_title
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn mediamanager_repo.repo_upload_filter
     */
    private $strUploadFilter = "";

    /**
     * @var string
     * @tableColumn mediamanager_repo.repo_view_filter
     */
    private $strViewFilter = "";

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
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_folderOpen.png";
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
        return $this->getStrPath();
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrTitle();
    }


    /**
     * loads all available repos
     *
     * @param $intStart
     * @param $intEnd
     *
     * @return class_module_mediamanager_repo[]
     * @static
     */
	public static function getAllRepos($intStart = null, $intEnd = null) {
		$strQuery = "SELECT system_id
		               FROM "._dbprefix_."mediamanager_repo,
						    "._dbprefix_."system
				      WHERE repo_id = system_id
		    	   ORDER BY repo_title ASC";
		$objDB = class_carrier::getInstance()->getObjDB();
        $arrIds = $objDB->getPArray($strQuery, array(), $intStart, $intEnd);
        $arrReturn = array();
        foreach ($arrIds as $arrOneRecord) {
        	$arrReturn[] = new class_module_mediamanager_repo($arrOneRecord["system_id"]);
        }
        return $arrReturn;
	}

    /**
     * counts all available repos
     *
     *
     * @return int
     * @static
     */
    public static function getAllReposCount() {
        $strQuery = "SELECT COUNT(*)
		               FROM "._dbprefix_."mediamanager_repo,
						    "._dbprefix_."system
				      WHERE repo_id = system_id";
        $objDB = class_carrier::getInstance()->getObjDB();
        $arrRow = $objDB->getPRow($strQuery, array());
        return $arrRow["COUNT(*)"];
    }

    /**
     * Syncs the complete repo with the filesystem. Adds new files and removes delete files to and
     * from the db.
     *
     * @return array [insert, delete]
     */
    public function syncRepo() {
        return class_module_mediamanager_file::syncRecursive($this->getSystemid(), $this->getStrPath());
    }


    /**
     * @return string
     * @fieldMandatory
     * @fieldValidator folder
     */
    public function getStrPath() {
        return $this->strPath;
    }

    /**
     * @return string
     * @fieldMandatory
     */
    public function getStrTitle() {
        return $this->strTitle;
    }


    public function setStrPath($strPath) {
        $this->strPath = $strPath;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    /**
     * @param string $strUploadFilter
     */
    public function setStrUploadFilter($strUploadFilter) {
        $this->strUploadFilter = $strUploadFilter;
    }

    /**
     * @return string
     */
    public function getStrUploadFilter() {
        return $this->strUploadFilter;
    }

    /**
     * @param string $strViewFilter
     */
    public function setStrViewFilter($strViewFilter) {
        $this->strViewFilter = $strViewFilter;
    }

    /**
     * @return string
     */
    public function getStrViewFilter() {
        return $this->strViewFilter;
    }


}

