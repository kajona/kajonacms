<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
 */
class class_module_pages_folder extends class_model implements interface_model, interface_versionable, interface_admin_listable  {

    private $strActionEdit = "editFolder";
    private $strActionDelete = "deleteFolder";

    private $strName = "";
    private $strLanguage = "";

    private $strOldName = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "pages");
        $this->setArrModuleEntry("moduleId", _pages_folder_id_);


        //init the object with the language currently selected - admin or portal
		if(defined("_admin_") && _admin_ === true)
		    $this->setStrLanguage($this->getStrAdminLanguageToWorkOn());
		else
		    $this->setStrLanguage($this->getStrPortalLanguage());

		//base class
		parent::__construct($strSystemid);
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
        return "icon_folderClosed.gif";
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
     * Initalises the current object, if a systemid was given
     *
     */
    protected function initObjectInternal() {
        //load content language-dependant
        $strQuery = "SELECT *
                    FROM "._dbprefix_."page_folderproperties
                    WHERE folderproperties_id = ?
                      AND folderproperties_language = ?";
        $arrPropRow = $this->objDB->getPRow($strQuery, array($this->getSystemid(), $this->getStrLanguage() ));
        if(count($arrPropRow) == 0) {
            $arrPropRow["folderproperties_name"] = "";
            $arrPropRow["folderproperties_language"] = "";
        }
        else {

            $this->setStrName($arrPropRow["folderproperties_name"]);
            $this->strOldName = $arrPropRow["folderproperties_name"];
            $this->setStrLanguage($arrPropRow["folderproperties_language"]);
        }
    }

    /**
     * Updates the current object to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        //create change-logs
        $objChanges = new class_module_system_changelog();
        $objChanges->createLogEntry($this, $this->strActionEdit);

        class_logger::getInstance()->addLogRow("updated folder ".$this->getStrName(), class_logger::$levelInfo);

        //and the properties record
		//properties for this language already existing?
		$strCountQuery = "SELECT COUNT(*) FROM "._dbprefix_."page_folderproperties
		                 WHERE folderproperties_id=?
		                   AND folderproperties_language=?";
		$arrCountRow = $this->objDB->getPRow($strCountQuery, array($this->getSystemid(), $this->getStrLanguage() ));

        $strQuery = "";
        $arrParams = array();
		if((int)$arrCountRow["COUNT(*)"] >= 1) {
		    //Already existing, updating properties
    		$strQuery = "UPDATE  "._dbprefix_."page_folderproperties
    					    SET folderproperties_name=?
    				      WHERE folderproperties_id=?
    			      	    AND folderproperties_language=?";

            $arrParams = array($this->getStrName(), $this->getSystemid(), $this->getStrLanguage());
		}
		else {
		    //Not existing, create one
		    $strQuery = "INSERT INTO "._dbprefix_."page_folderproperties
						(folderproperties_id, folderproperties_name, folderproperties_language) VALUES
						(?,?,?)";

            $arrParams = array($this->getSystemid(), $this->getStrName(), $this->getStrLanguage());
		}

        return $this->objDB->_pQuery($strQuery, $arrParams) ;

    }

    /**
	 * Returns a list of folders under the given systemid
	 *
	 * @param string $strSystemid
	 * @return class_module_pages_folder[]
	 * @static
	 */
	public static function getFolderList($strSystemid = "") {
		if(!validateSystemid($strSystemid))
			$strSystemid = class_module_system_module::getModuleByName("pages")->getSystemid();

		//Get all folders
		$strQuery = "SELECT system_id FROM "._dbprefix_."system
		              WHERE system_module_nr=?
		                AND system_prev_id=?
		             ORDER BY system_sort ASC";

		$arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(_pages_folder_id_, $strSystemid));
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_module_pages_folder($arrOneId["system_id"]);

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
            $strNewPrevID = class_module_system_module::getModuleByName("pages")->getSystemid();

		$strQuery = "UPDATE "._dbprefix_."system
		              SET  system_prev_id=?
		              WHERE system_id=?
		                AND system_module_nr=?";
		return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strNewPrevID, $strFolderID, _pages_folder_id_));
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
            $strNewPrevID = class_module_system_module::getModuleByName("pages")->getSystemid();


		$strQuery = "UPDATE "._dbprefix_."system
		              SET system_prev_id=?
		              WHERE system_id=?
		              AND system_module_nr=?";
		return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strNewPrevID, $strSiteID, _pages_modul_id_));
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
			$strFolderid = class_module_system_module::getModuleByName("pages")->getSystemid();

		$strQuery = "SELECT system_id
						FROM "._dbprefix_."page as page,
							 "._dbprefix_."system as system
						WHERE system.system_prev_id=?
							AND system.system_module_nr=?
							AND system.system_id = page.page_id
						ORDER BY system.system_sort ASC ";

		$arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array( $strFolderid, _pages_modul_id_ ) );
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_module_pages_page($arrOneId["system_id"]);

		return $arrReturn;
	}

    /**
     * Returns the list of pages and folders, so containing both object types, being located
     * under a given systemid.
     *
     * @param string $strFolderid
     * @return class_module_pages_page[] | class_module_pages_folder[]
     */
    public static function getPagesAndFolderList($strFolderid = "") {
        if(!validateSystemid($strFolderid))
			$strFolderid = class_module_system_module::getModuleByName("pages")->getSystemid();

		$strQuery = "SELECT system_id, system_module_nr
						FROM "._dbprefix_."system
						WHERE system_prev_id=?
							AND (system_module_nr = ? OR system_module_nr = ? )
							ORDER BY system_sort ASC";

		$arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strFolderid, _pages_modul_id_, _pages_folder_id_));
		$arrReturn = array();
		foreach($arrIds as $arrOneRecord) {
            if($arrOneRecord["system_module_nr"] == _pages_modul_id_)
                $arrReturn[] = new class_module_pages_page($arrOneRecord["system_id"]);
            else if($arrOneRecord["system_module_nr"] == _pages_folder_id_)
                $arrReturn[] = new class_module_pages_folder($arrOneRecord["system_id"]);
        }

		return $arrReturn;
    }



	/**
	 * Deletes a folder from the systems,
	 * All pages and folders under the current record are deleted, too.
	 *
	 * @return bool
	 */
	protected function deleteObjectInternal() {

        $arrSubElements = class_module_pages_folder::getPagesAndFolderList($this->getSystemid());
        foreach($arrSubElements as $objOneElement) {
            $objOneElement->deleteObject();
        }

        $objChanges = new class_module_system_changelog();
        $objChanges->createLogEntry($this, $this->strActionDelete);

        //delete the folder-properties
        $strQuery = "DELETE FROM "._dbprefix_."page_folderproperties WHERE folderproperties_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
	}




    public function getActionName($strAction) {
        if($strAction == $this->strActionEdit)
            return $this->getLang("pages_ordner_edit", "pages");
        else if($strAction == $this->strActionDelete)
            return $this->getLang("pages_ordner_delete", "pages");

        return $strAction;
    }

    public function getChangedFields($strAction) {
        if($strAction == $this->strActionEdit) {
            return array(
                array("property" => "foldername",  "oldvalue" => $this->strOldName, "newvalue" => $this->getStrName())
            );
        }
        else if($strAction == $this->strActionDelete) {
            return array(
                array("property" => "foldername",  "oldvalue" => $this->strOldName)
            );
        }
    }

    public function renderValue($strProperty, $strValue) {
        return $strValue;
    }

    public function getClassname() {
        return __CLASS__;
    }

    public function getModuleName() {
        return $this->arrModule["modul"];
    }

    public function getPropertyName($strProperty) {
        return $strProperty;
    }

    public function getRecordName() {
        return class_carrier::getInstance()->getObjLang()->getLang("change_object_folder", "pages");
    }

    /**
     * @return string
     * @fieldMandatory
     */
    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function getStrLanguage() {
        return $this->strLanguage;
    }

    public function setStrLanguage($strLanguage) {
        $this->strLanguage = $strLanguage;
    }


}
