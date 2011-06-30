<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * Model for a navigation point itself
 *
 * @package modul_navigation
 */
class class_modul_navigation_point extends class_model implements interface_model  {

    private $strName = "";
    private $strPageE = "";
    private $strPageI = "";
    private $strFolderI = "";
    private $strTarget = "";
    private $strImage = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_navigation";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _navigation_modul_id_;
		$arrModul["table"]       		= _dbprefix_."navigation";
		$arrModul["modul"]				= "navigation";

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
        return array(_dbprefix_."navigation" => "navigation_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "navigation point ".$this->getStrName();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
         $strQuery = "SELECT * FROM ".$this->arrModule["table"].", "._dbprefix_."system
		             WHERE system_id = navigation_id
		             AND system_module_nr = ?
		             AND system_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array(_navigation_modul_id_, $this->getSystemid()));
        if(count($arrRow)> 0) {
            $this->setStrName($arrRow["navigation_name"]);
            $this->setStrImage($arrRow["navigation_image"]);
            $this->setStrPageE($arrRow["navigation_page_e"]);
            $this->setStrPageI($arrRow["navigation_page_i"]);
            $this->setStrFolderI($arrRow["navigation_folder_i"]);
            $this->setStrTarget($arrRow["navigation_target"]);
            $this->setStrImage($arrRow["navigation_image"]);
        }
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        $strQuery = "UPDATE  ".$this->arrModule["table"]."
                        SET 	navigation_name= ?,
    							navigation_page_i= ?,
    							navigation_folder_i= ?,
    							navigation_page_e= ?,
    							navigation_target= ?,
    							navigation_image= ?
    					WHERE navigation_id= ?";
        return $this->objDB->_pQuery($strQuery, array(
            $this->getStrName(),
    		uniStrtolower($this->getStrPageI()),
    		$this->getStrFolderI(),
    		$this->getStrPageE(),
    		$this->getStrTarget(),
    		$this->getStrImage(),
    		$this->getSystemid()
        ));
    }


    /**
	 * Loads all navigations points one layer under the given systemid
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
            $objNavigationPoint = new class_modul_navigation_point($arrOneId["system_id"]);
            //check where the layer links to
            //TODO removal, no longer required
//            if($objNavigationPoint->getStrPageI() == "" && validateSystemid($objNavigationPoint->getStrFolderI())) {
//                $objFirstLevelPage = self::getNextValidPage($objNavigationPoint->getStrFolderI());
//                if($objFirstLevelPage != null)
//                    $objNavigationPoint->setStrPageI($objFirstLevelPage->getStrName());
//            }

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
     * @return class_modul_navigation_point
     */
    public static function getDynamicNaviLayer($strSystemid) {

        $arrReturn = array();

        //split modes  - regular navigation or generated out of the pages / folders
        $objCommon = new class_modul_system_common($strSystemid);

        //current node is a navigation-node
        if($objCommon->getIntModuleNr() == _navigation_modul_id_) {
            
            //check where the point links to - navigation-point or pages-entry
            $objNavigationPoint = new class_modul_navigation_point($strSystemid);
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
	public function deleteNaviPoint() {
	    class_logger::getInstance()->addLogRow("deleted navi(point) ".$this->getSystemid(), class_logger::$levelInfo);

       //Are there any childs?
       $arrChild = class_modul_navigation_point::getNaviLayer($this->getSystemid());
        if(count($arrChild) > 0) {
            //Call this method for each child
            foreach($arrChild as $objOneChild) {
                if(!$objOneChild->deleteNaviPoint()) {
                    return false;
                }
            }
        }

        //Now delete the current point
        //start in the navigation-table
        $strQuery = "DELETE FROM "._dbprefix_."navigation WHERE navigation_id=?";
        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
            if($this->deleteSystemRecord($this->getSystemid())) {
                return true;
            }
        }

        else
           return false;

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
            $arrReturn[] = new class_modul_navigation_point($arrOneId["system_id"]);

        return $arrReturn;
	}


    /**
     * Loads the level of pages and/or folders stored under a single systemid.
     * Transforms a page- or a folder-node into a navigation-node.
     * This node is used for portal-actions only, so there's no way to edit the node.
     *
     * @param string $strSourceId
     * @return class_modul_navigation_point
     * @since 3.4
     */
    private static function loadPageLevelToNavigationNodes($strSourceId) {

        $arrPages = class_modul_pages_folder::getPagesAndFolderList($strSourceId);
        $arrReturn = array();
        
        //transform the sublevel
        foreach($arrPages as $objOneEntry) {
            //validate status
            if($objOneEntry->getStatus() == 0)
                continue;

            if($objOneEntry instanceof class_modul_pages_page) {
                
                //validate if the page to be links has a template assigned and at least a single element created
                if( $objOneEntry->getIntType() == class_modul_pages_page::$INT_TYPE_ALIAS || ($objOneEntry->getStrTemplate() != "" && $objOneEntry->getNumberOfElementsOnPage() > 0)) {
                
                    $objPoint = new class_modul_navigation_point();
                    $objPoint->setStrName($objOneEntry->getStrBrowsername() != "" ? $objOneEntry->getStrBrowsername() : $objOneEntry->getStrName());
                    $objPoint->setIntRecordStatus(1);
                    
                    //if in alias mode, then check what type of target is requested
                    if($objOneEntry->getIntType() == class_modul_pages_page::$INT_TYPE_ALIAS) {
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


// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrName() {
        return $this->strName;
    }
    public function getStrPageE() {
        return $this->strPageE;
    }
    public function getStrPageI() {
        return $this->strPageI;
    }
    public function getStrTarget() {
        return $this->strTarget != "" ? $this->strTarget : "_self";
    }
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
?>