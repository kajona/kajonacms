<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Model for a single system-setting
 * Setting are not represented by a record in the system-table
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_modul_system_setting extends class_model implements interface_model, interface_versionable  {


    private $strActionChange = "changeSetting";

    //0 = bool, 1 = int, 2 = string, 3 = page
    //use ONLY the following static vars to assign a type to your constant.
    //integer values may change without warnings!
    /**
     * Used for bool values = 0
     *
     * @var int
     */
    public static $int_TYPE_BOOL = 0;

    /**
     * Used for integer values = 1
     *
     * @var int
     */
    public static $int_TYPE_INT = 1;

    /**
     * Used for string values = 2
     *
     * @var int
     */
    public static $int_TYPE_STRING = 2;

    /**
     * Used for pages = 3
     *
     * @var int
     */
    public static $int_TYPE_PAGE = 3;

    private $strName = "";
    private $strValue = "";
    private $intType = 0;
    private $intModule = 0;

    private $strOldValue = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "module_system";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["table"]       		= _dbprefix_."system_config";
		$arrModul["modul"]				= "system";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM ".$this->arrModule["table"]." WHERE system_config_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        $this->setStrName($arrRow["system_config_name"]);
        $this->setStrValue($arrRow["system_config_value"]);
        $this->setIntType($arrRow["system_config_type"]);
        $this->setIntModule($arrRow["system_config_module"]);

        $this->strOldValue = $this->strValue;
    }

    /**
     * Updates the current object to the database.
     * Only the value is updated!!!
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {

        if(!class_modul_system_setting::checkConfigExisting($this->getStrName())) {
            class_logger::getInstance()->addLogRow("new constant ".$this->getStrName() ." with value ".$this->getStrValue(), class_logger::$levelInfo);


            $strQuery = "INSERT INTO "._dbprefix_."system_config
                        (system_config_id, system_config_name, system_config_value, system_config_type, system_config_module) VALUES
                        (?, ?, ?, ?, ?)";
            return $this->objDB->_pQuery($strQuery, array(generateSystemid(), $this->getStrName(), $this->getStrValue(), (int)$this->getIntType(), (int)$this->getIntModule()));
        }
        else {

            class_logger::getInstance()->addLogRow("updated constant ".$this->getStrName() ." to value ".$this->getStrValue(), class_logger::$levelInfo);

            $objChangelog = new class_modul_system_changelog();
//            $objChangelog->createLogEntry("system", "change setting", $this->getSystemid(), $this->getStrName(), $this->strOldValue, $this->strValue);

            $objChangelog->createLogEntry($this, $this->strActionChange);

            $strQuery = "UPDATE "._dbprefix_."system_config
                        SET system_config_value = ?
                      WHERE system_config_name = ?";
            return $this->objDB->_pQuery($strQuery, array($this->getStrValue(), $this->getStrName()));
        }
    }


    /**
     * Renames a constant in the database.
     * @param string $strNewName
     * @return bool
     */
    public function renameConstant($strNewName) {
    	class_logger::getInstance()->addLogRow("renamed constant ".$this->getStrName() ." to ".$strNewName, class_logger::$levelInfo);


        $strQuery = "UPDATE "._dbprefix_."system_config
                    SET system_config_name = ? WHERE system_config_name = ?";

        $bitReturn =  $this->objDB->_pQuery($strQuery, array($strNewName, $this->getStrName()));
        $this->strName = $strNewName;
        return $bitReturn;
    }

    /**
	 * Fetches all Configs from the database
	 *
	 * @return array
	 * @static
	 */
	public static function getAllConfigValues() {
	    $strQuery = "SELECT system_config_id FROM "._dbprefix_."system_config ORDER BY system_config_module ASC";
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_system_setting($arrOneId["system_config_id"]);

		return $arrReturn;
	}

	/**
	 * Fetches a Configs selected by name
	 *
	 * @return class_modul_system_setting
	 * @static
	 */
	public static function getConfigByName($strName) {
	    $strQuery = "SELECT system_config_id FROM "._dbprefix_."system_config WHERE system_config_name = ?";
        $arrId = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
		return new class_modul_system_setting($arrId["system_config_id"]);
	}

	/**
	 * Checks, if a config-value is already existing
	 *
	 * @param string $strName
	 * @return boolean
	 */
	public static function checkConfigExisting($strName) {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system_config WHERE system_config_name = ?";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
		return $arrRow["COUNT(*)"] == 1;
	}

    public function getActionName($strAction) {
        return $strAction;
    }

    public function getChangedFields($strAction) {
        if($strAction == $this->strActionChange) {
            return array(
                array("property" => $this->getStrName(), "oldvalue" => $this->strOldValue, "newvalue" => $this->getStrValue())
            );
        }
    }

    public function renderValue($strProperty, $strValue) {
        return $strValue;
    }

    public function getClassname() {
        return __CLASS__;
    }

    public function getPropertyName($strProperty) {
        return $strProperty;
    }

    public function getRecordName() {
        return class_carrier::getInstance()->getObjText()->getText("change_type_setting", "system", "admin");
    }

    public function getModuleName() {
        return $this->arrModule["modul"];
    }



// --- GETTERS / SETTERS --------------------------------------------------------------------------------
    public function getStrName() {
        return $this->strName;
    }
    public function getStrValue() {
        return $this->strValue;
    }
    public function getIntType() {
        return $this->intType;
    }
    public function getIntModule() {
        return $this->intModule;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }
    public function setStrValue($strValue) {
        $this->strValue= $strValue;
    }
    public function setIntType($intType) {
        $this->intType = $intType;
    }
    public function setIntModule($intModule) {
        $this->intModule = $intModule;
    }

}
?>