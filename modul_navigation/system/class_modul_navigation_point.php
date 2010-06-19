<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
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
		             AND system_module_nr = "._navigation_modul_id_."
		             AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
        $arrRow = $this->objDB->getRow($strQuery);
        if(count($arrRow)> 0) {
            $this->setStrName($arrRow["navigation_name"]);
            $this->setStrImage($arrRow["navigation_image"]);
            $this->setStrPageE($arrRow["navigation_page_e"]);
            $this->setStrPageI($arrRow["navigation_page_i"]);
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
                        SET 	navigation_name='".$this->objDB->dbsafeString($this->getStrName())."',
    							navigation_page_i='".$this->objDB->dbsafeString(uniStrtolower($this->getStrPageI()))."',
    							navigation_page_e='".$this->objDB->dbsafeString($this->getStrPageE())."',
    							navigation_target='".$this->objDB->dbsafeString($this->getStrTarget())."',
    							navigation_image='".$this->objDB->dbsafeString($this->getStrImage())."'
    					WHERE navigation_id='".$this->objDB->dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    protected function onInsertToDb() {

        //set the element as last, shift it up once an down again to get a correct order on systemtables
        $strQuery = "UPDATE "._dbprefix_."system SET system_sort = 999999 WHERE system_id = '".dbsafeString($this->getSystemid())."'";
        $this->objDB->_query($strQuery);


        $this->setPosition($this->getSystemid(), "upwards");
        $this->setPosition($this->getSystemid(), "downwards");
        return true;
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
    			             AND system_prev_id = '".dbsafeString($strSystemid)."'
    			             AND system_module_nr = "._navigation_modul_id_."
    			             ".($bitJustActive ? " AND system_status = 1 ": "")."
    			             ORDER BY system_sort ASC, system_comment ASC";
	    $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_modul_navigation_point($arrOneId["system_id"]);

        return $arrReturn;
	}

	/**
	 * Deletes a navigation / a point and all childs
	 *
	 * @return bool
	 */
	public function deleteNaviPoint() {
	    class_logger::getInstance()->addLogRow("deleted navi(point) ".$this->getSystemid(), class_logger::$levelInfo);

	    $objRoot = new class_modul_system_common();
	    //Check rights for the current point
	    if($objRoot->getObjRights()->rightDelete($this->getSystemid())) {
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

	        $strQuery = "DELETE FROM "._dbprefix_."navigation WHERE navigation_id='".dbsafeString($this->getSystemid())."'";
	        if($this->objDB->_query($strQuery)) {
		        if($this->deleteSystemRecord($this->getSystemid())) {
		            return true;
		        }
	        }

	        else
	           return false;


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
    			             AND system_module_nr = "._navigation_modul_id_."
    			             AND navigation_page_i = '".dbsafeString($strPagename)."'
    			             AND system_status = 1";
	    $arrIds = $objDB->getArray($strQuery);

	    foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_modul_navigation_point($arrOneId["system_id"]);

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

}
?>