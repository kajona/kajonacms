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
 *
 * @targetTable navigation.navigation_id
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
     * Internal field, used for navigation nodes added by other modules
     * @var string
     */
    private $strLinkAction = "";

    /**
     * Internal field, used for navigation nodes added by other modules
     * @var string
     */
    private $strLinkSystemid = "";

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
        return "icon_treeLeaf.png";
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
    			             ".($bitJustActive ? " AND system_status = 1 ": "")."
    			             ORDER BY system_sort ASC, system_comment ASC";
	    $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid));
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $objNavigationPoint = new class_module_navigation_point($arrOneId["system_id"]);
            $arrReturn[] = $objNavigationPoint;
        }

        return $arrReturn;
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

        /** @var $objNode class_module_navigation_point|class_module_navigation_tree */
        $objNode = class_objectfactory::getInstance()->getObject($strSystemid);

        //current node is a navigation-node
        if($objNode instanceof class_module_navigation_point || $objNode instanceof class_module_navigation_tree) {

            //check where the point links to - navigation-point or pages-entry
            if($objNode instanceof class_module_navigation_tree && validateSystemid($objNode->getStrFolderId())) {
                $arrReturn = self::loadPageLevelToNavigationNodes($objNode->getStrFolderId());
            }
            else
                $arrReturn = self::getNaviLayer($strSystemid, true);
        }
        //current node belongs to pages
        else if($objNode instanceof class_module_pages_page || $objNode instanceof class_module_pages_folder) {
            //load the page-level below
            $arrReturn = self::loadPageLevelToNavigationNodes($strSystemid);
        }



        return $arrReturn;
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
     * @return class_module_navigation_point[]|array
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
                        else {
                            $objPoint->setStrPageI($objOneEntry->getStrAlias());
                        }
                    }
                    else {
                        $objPoint->setStrPageI($objOneEntry->getStrName());
                    }

                    $objPoint->setSystemid($objOneEntry->getSystemid());

                    $arrReturn[] = $objPoint;
                }
            }
        }

        //merge with elements on the page - if given
        /** @var $objInstance class_module_pages_page */
        $objInstance = class_objectfactory::getInstance()->getObject($strSourceId);
        if($objInstance instanceof class_module_pages_page) {

            if($objInstance->getIntType() == class_module_pages_page::$INT_TYPE_ALIAS)
                $arrReturn = array_merge($arrReturn, self::getAdditionalEntriesForPage(class_module_pages_page::getPageByName($objInstance->getStrAlias())));
            else
                $arrReturn = array_merge($arrReturn, self::getAdditionalEntriesForPage($objInstance));

        }

        return $arrReturn;
    }


    /**
     * Triggers all subelements in order to fetch the additional navigation
     * entries.
     *
     * @see class_element_portal::getNavigationEntries()
     * @param class_module_pages_page $objPage
     * @return class_module_navigation_point[]|array
     * @since 4.0
     */
    private static function getAdditionalEntriesForPage(class_module_pages_page $objPage) {
        $arrReturn = array();
        $objLanguage = new class_module_languages_language();
        $arrElements =  class_module_pages_pageelement::getElementsOnPage($objPage->getSystemid(), true, $objLanguage->getStrPortalLanguage());

        $strOldPageName = $objPage->getParam("page");

        foreach($arrElements as $objOneElementOnPage) {
            //Build the class-name for the object
            $strClassname = uniSubstr($objOneElementOnPage->getStrClassPortal(), 0, -4);

            /** @var  class_element_portal $objElement  */
            $objElement = new $strClassname($objOneElementOnPage);
            $objElement->setParam("page", $objPage->getStrName());

            $arrNavigationPoints = $objElement->getNavigationEntries();
            if($arrNavigationPoints !== false) {
                $arrReturn = array_merge($arrReturn, $arrNavigationPoints);
            }

        }

        $objPage->setParam("page", $strOldPageName);

        return $arrReturn;
    }



    /**
     * @return string
     * @fieldMandatory
     * @fieldType text
     * @fieldLabel commons_name
     */
    public function getStrName() {
        return $this->strName;
    }

    /**
     * @return string
     * @fieldType page
     * @fieldLabel navigation_page_i
     */
    public function getStrPageI() {
        return uniStrtolower($this->strPageI);
    }

    /**
     * @return string
     * @fieldType file
     * @fieldLabel navigation_page_e
     */
    public function getStrPageE() {
        return $this->strPageE;
    }

    /**
     * @return string
     * @fieldType dropdown
     * @fieldLabel navigation_target
     */
    public function getStrTarget() {
        return $this->strTarget != "" ? $this->strTarget : "_self";
    }

    /**
     * @return string
     * @fieldType image
     * @fieldLabel commons_image
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

    /**
     * @param string $strLinkAction
     */
    public function setStrLinkAction($strLinkAction) {
        $this->strLinkAction = $strLinkAction;
    }

    /**
     * @return string
     */
    public function getStrLinkAction() {
        return $this->strLinkAction;
    }

    /**
     * @param string $strLinkSystemid
     */
    public function setStrLinkSystemid($strLinkSystemid) {
        $this->strLinkSystemid = $strLinkSystemid;
    }

    /**
     * @return string
     */
    public function getStrLinkSystemid() {
        return $this->strLinkSystemid;
    }


}
