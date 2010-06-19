<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Model for a navigation tree itself
 *
 * @package modul_navigation
 */
class class_modul_navigation_tree extends class_model implements interface_model  {

    private $strName = "";

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
        return "navigation tree ".$this->getStrName();
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
        $this->setStrName($arrRow["navigation_name"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {

        $strQuery = "UPDATE ".$this->arrModule["table"]."
                     SET navigation_name='".$this->objDB->dbsafeString($this->getStrName())."'
                     WHERE navigation_id='".$this->objDB->dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
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
		             AND system_prev_id = '".dbsafeString(class_modul_system_module::getModuleByName("navigation")->getSystemid())."'
		             AND system_module_nr = "._navigation_modul_id_."
		             ORDER BY system_sort ASC, system_comment ASC";
        $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_modul_navigation_tree($arrOneId["system_id"]);

        return $arrReturn;
    }


    /**
     * Looks up a navigation by its name
     *
     * @param string $strName
     * @return class_modul_navigation_tree
     * @static
     */
    public static function getNavigationByName($strName) {
        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."navigation, "._dbprefix_."system
                     WHERE system_id = navigation_id
                     AND system_prev_id = '".dbsafeString(class_modul_system_module::getModuleByName("navigation")->getSystemid())."'
                     AND navigation_name = '".dbsafeString($strName)."'
                     AND system_module_nr = "._navigation_modul_id_."
                     ORDER BY system_sort ASC, system_comment ASC";
        $arrRow = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
        if(isset($arrRow["system_id"]))
            return new class_modul_navigation_tree($arrRow["system_id"]);
        else
            return null;

    }

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

}
?>