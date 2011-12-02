<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Model for a navigation tree itself
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 */
class class_module_navigation_tree extends class_model implements interface_model  {

    private $strName = "";
    private $strFolderId = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "module_navigation";
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
        return "navigation tree ".$this->getStrName();
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
        $this->setStrName($arrRow["navigation_name"]);
        $this->setStrFolderId($arrRow["navigation_folder_i"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {

        $strQuery = "UPDATE ".$this->arrModule["table"]."
                     SET navigation_name= ?,
                         navigation_folder_i=?
                     WHERE navigation_id=?";
        return $this->objDB->_pQuery($strQuery, array($this->getStrName(), $this->getStrFolderId(), $this->getSystemid()));
    }


    /**
     * Returns an array of all navigation-trees available
     *
     * @return mixed
     * @static
     */
    public static function getAllNavis() {
        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."navigation, "._dbprefix_."system
		             WHERE system_id = navigation_id
		               AND system_prev_id = ?
		               AND system_module_nr = ?
		          ORDER BY system_comment ASC";
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(class_module_system_module::getModuleIdByNr(_navigation_modul_id_), _navigation_modul_id_));
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_navigation_tree($arrOneId["system_id"]);

        return $arrReturn;
    }


    /**
     * Looks up a navigation by its name
     *
     * @param string $strName
     * @return class_module_navigation_tree
     * @static
     */
    public static function getNavigationByName($strName) {
        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."navigation, "._dbprefix_."system
                     WHERE system_id = navigation_id
                     AND system_prev_id = ?
                     AND navigation_name = ?
                     AND system_module_nr = ?
                     ORDER BY system_sort ASC, system_comment ASC";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array(class_module_system_module::getModuleIdByNr(_navigation_modul_id_), $strName, _navigation_modul_id_));
        if(isset($arrRow["system_id"]))
            return new class_module_navigation_tree($arrRow["system_id"]);
        else
            return null;

    }

    /**
     * Loads al nodes of a navigation, skipping inactive and non-viewable ones.
     * Includes transformed page-nodes!
     *
     * @return array
     */
    public function getCompleteNaviStructure() {
        $arrReturn = array();

        $arrReturn["node"] = null;
        $arrReturn["subnodes"] = $this->loadSingleLevel($this->getSystemid());

        return $arrReturn;
    }

    /**
     * Loads a singe level of nodes, internal recursion helper
     *
     * @param string $strParentNode
     * @return array
     */
    private function loadSingleLevel($strParentNode) {
        $arrReturn = array();

        $arrCurLevel = class_module_navigation_point::getDynamicNaviLayer($strParentNode);

        foreach($arrCurLevel as $objOneNode) {
            if($objOneNode->getStatus() == 1 && $objOneNode->rightView()) {
                $arrTemp = array();
                $arrTemp["node"] = $objOneNode;
                $arrTemp["subnodes"] = $this->loadSingleLevel($objOneNode->getSystemid());

                $arrReturn[] = $arrTemp;
            }
        }

        return $arrReturn;
    }


    /**
	 * Deletes a navigation / a point and all childs
	 *
	 * @return bool
	 */
	public function deleteNavigation() {
	    class_logger::getInstance()->addLogRow("deleted navi(point) ".$this->getSystemid(), class_logger::$levelInfo);

       //Are there any childs?
       $arrChild = class_module_navigation_point::getNaviLayer($this->getSystemid());
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

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function getStrFolderId() {
        return $this->strFolderId;
    }

    public function setStrFolderId($strFolderId) {
        $this->strFolderId = $strFolderId;
    }



}
