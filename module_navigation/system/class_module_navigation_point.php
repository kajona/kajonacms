<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * Model for a navigation point itself
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 */
class class_module_navigation_point extends class_model implements interface_model, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn navigation_name
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn navigation_page_e
     */
    private $strPageE = "";

    /**
     * @var string
     * @tableColumn navigation_page_i
     */
    private $strPageI = "";

    /**
     * @var string
     * @tableColumn navigation_folder_i
     */
    private $strFolderI = "";

    /**
     * @var string
     * @tableColumn navigation_target
     */
    private $strTarget = "";

    /**
     * @var string
     * @tableColumn navigation_image
     */
    private $strImage = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "navigation");
        $this->setArrModuleEntry("moduleId", _navigation_modul_id_);

		//base class
		parent::__construct($strSystemid);

    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."navigation" => "navigation_id");
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
        return "icon_treeLeaf.gif";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        $strNameInternal = $this->getStrPageI();
        $strNameExternal = $this->getStrPageE();
        $strNameFolder = "";
        if(validateSystemid($this->getStrFolderI())) {
            $objFolder = new class_module_pages_folder($this->getStrFolderI());
            $strNameFolder = $objFolder->getStrName();
        }

        return $strNameInternal.$strNameExternal.$strNameFolder;
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
	 * Loads all navigation points one layer under the given systemid
	 *
	 * @param string $strSystemid
	 * @param bool
	 * @return mixed
	 * @static
	 */
	public static function getNaviLayer($strSystemid, $bitJustActive = false) {
	    $strQuery = "SELECT system_id FROM "._dbprefix_."navigation, "._dbprefix_."system
    			             WHERE system_id = navigation_id
    			             AND system_prev_id = ?
    			             AND system_module_nr = ?
    			             ".($bitJustActive ? " AND system_status = 1 ": "")."
    			             ORDER BY system_sort ASC, system_comment ASC";
	    $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid, _navigation_modul_id_));
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $objNavigationPoint = new class_module_navigation_point($arrOneId["system_id"]);
            $arrReturn[] = $objNavigationPoint;
        }

        return $arrReturn;
	}

    /**
     * Loads the number of navigation points one layer under the given systemid
     *
     * @param string $strSystemid
     * @param bool
     * @return int
     * @static
     */
    public static function getNaviLayerCount($strSystemid, $bitJustActive = false) {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."navigation, "._dbprefix_."system
                             WHERE system_id = navigation_id
                             AND system_prev_id = ?
                             AND system_module_nr = ?
                             ".($bitJustActive ? " AND system_status = 1 ": "")."
                             ORDER BY system_sort ASC, system_comment ASC";

        $arrReturn = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strSystemid, _navigation_modul_id_));
        return $arrReturn["COUNT(*)"];
    }

    /**
     * Generates a navigation layer for the portal.
     * Either based on the "real" navigation as maintained in module navigation
     * or generated out of the linked pages-folders.
     * If theres a link to a folder, the first page/folder within the folder is
     * linked to the current point.
     *
     * @param string $strSystemid
     * @return class_module_navigation_point
     */
    public static function getDynamicNaviLayer($strSystemid) {

        $arrReturn = array();

        //split modes  - regular navigation or generated out of the pages / folders
        $objCommon = new class_module_system_common($strSystemid);

        //current node is a navigation-node
        if($objCommon->getIntModuleNr() == _navigation_modul_id_) {

            //check where the point links to - navigation-point or pages-entry
            $objNavigationPoint = new class_module_navigation_point($strSystemid);
            if($objNavigationPoint->getStrPageI() == "" && validateSystemid($objNavigationPoint->getStrFolderI())) {
                $arrReturn = self::loadPageLevelToNavigationNodes($objNavigationPoint->getStrFolderI());
            }
            else
                $arrReturn = self::getNaviLayer($strSystemid, true);
        }
        //current node belongs to pages
        else if($objCommon->getIntModuleNr() == _pages_folder_id_ || $objCommon->getIntModuleNr() == _pages_modul_id_) {
            //load the page-level below
            $arrReturn = self::loadPageLevelToNavigationNodes($strSystemid);
        }



        return $arrReturn;
    }

	/**
	 * Deletes a navigation / a point and all childs
	 *
	 * @return bool
	 */
	protected function deleteObjectInternal() {

        //Are there any childs?
        $arrChild = class_module_navigation_point::getNaviLayer($this->getSystemid());
        if(count($arrChild) > 0) {
            //Call this method for each child
            foreach($arrChild as $objOneChild) {
                if(!$objOneChild->deleteObject()) {
                    return false;
                }
            }
        }

        return parent::deleteObjectInternal();
	}


	/**
	 * Loads all navigation-points linking on the passed page
	 *
	 * @param string $strPagename
	 * @static
	 * @return mixed
	 */
	public static function loadPagePoint($strPagename) {
	    $objDB = class_carrier::getInstance()->getObjDB();
	    $arrReturn = array();
	    $strQuery = "SELECT system_id FROM "._dbprefix_."navigation, "._dbprefix_."system
    			             WHERE system_id = navigation_id
    			             AND system_module_nr = ?
    			             AND navigation_page_i = ?
    			             AND system_status = 1";
	    $arrIds = $objDB->getPArray($strQuery, array(_navigation_modul_id_, $strPagename));

	    foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_navigation_point($arrOneId["system_id"]);

        return $arrReturn;
	}


    /**
     * Loads the level of pages and/or folders stored under a single systemid.
     * Transforms a page- or a folder-node into a navigation-node.
     * This node is used for portal-actions only, so there's no way to edit the node.
     *
     * @param string $strSourceId
     * @return class_module_navigation_point
     * @since 3.4
     */
    private static function loadPageLevelToNavigationNodes($strSourceId) {

        $arrPages = class_module_pages_folder::getPagesAndFolderList($strSourceId);
        $arrReturn = array();

        //transform the sublevel
        foreach($arrPages as $objOneEntry) {
            //validate status
            if($objOneEntry->getStatus() == 0)
                continue;

            if($objOneEntry instanceof class_module_pages_page) {

                //validate if the page to be links has a template assigned and at least a single element created
                if( $objOneEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS || ($objOneEntry->getStrTemplate() != "" && $objOneEntry->getNumberOfElementsOnPage() > 0)) {

                    $objPoint = new class_module_navigation_point();
                    $objPoint->setStrName($objOneEntry->getStrBrowsername() != "" ? $objOneEntry->getStrBrowsername() : $objOneEntry->getStrName());
                    $objPoint->setIntRecordStatus(1, false);

                    //if in alias mode, then check what type of target is requested
                    if($objOneEntry->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS) {
                        $strAlias = uniStrtolower($objOneEntry->getStrAlias());
                        if(uniStrpos($strAlias, "http") !== false) {
                            $objPoint->setStrPageE($objOneEntry->getStrAlias());
                        }
                        else
                            $objPoint->setStrPageI($objOneEntry->getStrAlias());
                    }
                    else
                        $objPoint->setStrPageI($objOneEntry->getStrName());

                    $objPoint->setSystemid($objOneEntry->getSystemid());

                    $arrReturn[] = $objPoint;
                }
            }
        }

        return $arrReturn;
    }



    /**
     * @return string
     * @fieldMandatory
     * @fieldType text
     */
    public function getStrName() {
        return $this->strName;
    }

    /**
     * @return string
     * @fieldType page
     */
    public function getStrPageI() {
        return uniStrtolower($this->strPageI);
    }

    /**
     * @return string
     * @fieldType file
     */
    public function getStrPageE() {
        return $this->strPageE;
    }

    /**
     * @return string
     * @fieldType dropdown
     */
    public function getStrTarget() {
        return $this->strTarget != "" ? $this->strTarget : "_self";
    }

    /**
     * @return string
     * @fieldType image
     */
    public function getStrImage() {
        return $this->strImage;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }
    public function setStrPageE($strPageE) {
        $this->strPageE = $strPageE;
    }
    public function setStrPageI($strPageI) {
        $this->strPageI = $strPageI;
    }
    public function setStrTarget($strTarget) {
        $this->strTarget = $strTarget;
    }

    public function setStrImage($strImage) {
        $strImage = uniStrReplace(_webpath_, "", $strImage);
        $this->strImage = $strImage;
    }
    public function getStrFolderI() {
        return $this->strFolderI;
    }
    public function setStrFolderI($strFolderI) {
        $this->strFolderI = $strFolderI;
    }



}
