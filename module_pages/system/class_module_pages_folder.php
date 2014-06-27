<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/

/**
 * This class manages all stuff related with folders, used by pages. Folders just exist in the database,
 * not in the filesystem
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 * @targetTable page_folder.folder_id
 *
 * @module pages
 * @moduleId _pages_folder_id_
 */
class class_module_pages_folder extends class_model implements interface_model, interface_versionable, interface_admin_listable, interface_search_resultobject {

    /**
     * @var string
     * @versionable
     * @addSearchIndex
     *
     * @fieldMandatory
     * @fieldType text
     * @fieldLabel ordner_name
     * @tableColumn folder_name
     */
    private $strName = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        //base class
        parent::__construct($strSystemid);

        $this->objSortManager = new class_pages_sortmanager($this);
    }

    /**
     * Return an on-lick link for the passed object.
     * This link is used by the backend-search for the autocomplete-field
     *
     * @see getLinkAdminHref()
     * @return mixed
     */
    public function getSearchAdminLinkForObject() {
        return class_link::getLinkAdminHref("pages", "list", "&systemid=".$this->getSystemid());
    }


    protected function onInsertToDb() {

        //fix the initial sort-id
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."system
                      WHERE system_prev_id = ?
                        AND (system_module_nr = ? OR system_module_nr = ?)";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getPrevId(), _pages_modul_id_, _pages_folder_id_));
        $this->setIntSort($arrRow["COUNT(*)"]);

        return parent::onInsertToDb();
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
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
        return "icon_folderClosed";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * Returns a list of folders under the given systemid
     *
     * @param string $strSystemid
     *
     * @return class_module_pages_folder[]
     * @static
     */
    public static function getFolderList($strSystemid = "") {
        if(!validateSystemid($strSystemid)) {
            $strSystemid = class_module_system_module::getModuleByName("pages")->getSystemid();
        }

        return self::getObjectList($strSystemid);

//
//        //Get all folders
//        $strQuery = "SELECT system_id FROM
//                          " . _dbprefix_ . "system
//		              WHERE system_module_nr=?
//		                AND system_prev_id=?
//		             ORDER BY system_sort ASC";
//
//        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(_pages_folder_id_, $strSystemid));
//        $arrReturn = array();
//        foreach($arrIds as $arrOneId) {
//            $arrReturn[] = new class_module_pages_folder($arrOneId["system_id"]);
//        }
//
//        return $arrReturn;
    }

    /**
     * Returns all Pages listed in a given folder
     *
     * @param string $strFolderid
     *
     * @return class_module_pages_page[]
     * @static
     */
    public static function getPagesInFolder($strFolderid = "") {
        if(!validateSystemid($strFolderid)) {
            $strFolderid = class_module_system_module::getModuleByName("pages")->getSystemid();
        }

        return class_module_pages_page::getObjectList($strFolderid);

//        $strQuery = "SELECT system_id
//						FROM " . _dbprefix_ . "page as page,
//							 " . _dbprefix_ . "system as system
//						WHERE system.system_prev_id=?
//							AND system.system_module_nr=?
//							AND system.system_id = page.page_id
//						ORDER BY system.system_sort ASC ";
//
//        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strFolderid, _pages_modul_id_));
//        $arrReturn = array();
//        foreach($arrIds as $arrOneId) {
//            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneId["system_id"]);
//        }
//
//        return $arrReturn;
    }

    /**
     * Returns the list of pages and folders, so containing both object types, being located
     * under a given systemid.
     *
     * @param string $strFolderid
     * @param bool $bitOnlyActive
     * @param null $intStart
     * @param null $intEnd
     *
     * @return class_module_pages_page[] | class_module_pages_folder[]
     */
    public static function getPagesAndFolderList($strFolderid = "", $bitOnlyActive = false, $intStart = null, $intEnd = null) {
        if(!validateSystemid($strFolderid)) {
            $strFolderid = class_module_system_module::getModuleByName("pages")->getSystemid();
        }

        $strQuery = "SELECT system_id, system_module_nr
						FROM " . _dbprefix_ . "system
						WHERE system_prev_id=?
                         AND (system_module_nr = ? OR system_module_nr = ? )
	                      ".($bitOnlyActive ? " AND system_status = 1 ": "")."
                    ORDER BY system_sort ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strFolderid, _pages_modul_id_, _pages_folder_id_), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrIds as $arrOneRecord) {
            $objRecord = class_objectfactory::getInstance()->getObject($arrOneRecord["system_id"]);
            if($objRecord instanceof class_module_pages_folder || $objRecord instanceof class_module_pages_page)
                $arrReturn[] = $objRecord;

        }

        return $arrReturn;
    }

    /**
     * Returns the list of pages and folders, so containing both object types, being located
     * under a given systemid.
     *
     * @param string $strFolderid
     * @param bool $bitOnlyActive
     *
     * @return int
     */
    public static function getPagesAndFolderListCount($strFolderid = "", $bitOnlyActive = false) {
        if(!validateSystemid($strFolderid)) {
            $strFolderid = class_module_system_module::getModuleByName("pages")->getSystemid();
        }

        $strQuery = "SELECT COUNT(*)
						FROM " . _dbprefix_ . "system
						WHERE system_prev_id=?
                         AND (system_module_nr = ? OR system_module_nr = ? )
	                      ".($bitOnlyActive ? " AND system_status = 1 ": "");

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strFolderid, _pages_modul_id_, _pages_folder_id_));
        return $arrRow["COUNT(*)"];
    }


    public function getVersionActionName($strAction) {
        if($strAction == class_module_system_changelog::$STR_ACTION_EDIT) {
            return $this->getLang("pages_ordner_edit", "pages");
        }
        else if($strAction == class_module_system_changelog::$STR_ACTION_DELETE) {
            return $this->getLang("pages_ordner_delete", "pages");
        }

        return $strAction;
    }

    public function renderVersionValue($strProperty, $strValue) {
        return $strValue;
    }

    public function getVersionPropertyName($strProperty) {
        return $strProperty;
    }

    public function getVersionRecordName() {
        return class_carrier::getInstance()->getObjLang()->getLang("change_object_folder", "pages");
    }

    /**
     * @return string
     *
     */
    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

}
