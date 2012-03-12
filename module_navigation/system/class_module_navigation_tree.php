<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
class class_module_navigation_tree extends class_model implements interface_model, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn navigation_name
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn navigation_folder_i
     */
    private $strFolderId = "";

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
        return "icon_treeRoot.gif";
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
     * Returns an array of all navigation-trees available
     *
     * @param bool|int $intStart
     * @param bool|int $intEnd
     * @return mixed
     * @static
     */
    public static function getAllNavis($intStart = false, $intEnd = false) {
        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."navigation, "._dbprefix_."system
		             WHERE system_id = navigation_id
		               AND system_prev_id = ?
		               AND system_module_nr = ?
		          ORDER BY system_comment ASC";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(class_module_system_module::getModuleIdByNr(_navigation_modul_id_), _navigation_modul_id_), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_navigation_tree($arrOneId["system_id"]);

        return $arrReturn;
    }


    /**
     * Returns the number of navigation available
     *
     * @return int
     * @static
     */
    public static function getAllNavisCount() {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."navigation, "._dbprefix_."system
                     WHERE system_id = navigation_id
                       AND system_prev_id = ?
                       AND system_module_nr = ?
                  ORDER BY system_comment ASC";

        $arrReturn = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array(class_module_system_module::getModuleIdByNr(_navigation_modul_id_), _navigation_modul_id_));
        return $arrReturn["COUNT(*)"];
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
     * @return string
     * @fieldMandatory
     */
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
