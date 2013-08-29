<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
class class_module_system_setting extends class_model implements interface_model, interface_versionable {


    private static $arrInstanceCache = null;


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

    /**
     * @var string
     * @versionable
     */
    private $strName = "";

    /**
     * @var string
     * @versionable
     */
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
        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);

        //base class
        parent::__construct($strSystemid);

    }

    /**
     * Initalises the current object, if a systemid was given

     */
    protected function initObjectInternal() {
        $strQuery = "SELECT * FROM " . _dbprefix_ . "system_config WHERE system_config_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        $this->setStrName($arrRow["system_config_name"]);
        $this->setStrValue($arrRow["system_config_value"]);
        $this->setIntType($arrRow["system_config_type"]);
        $this->setIntModule($arrRow["system_config_module"]);

        $this->strOldValue = $this->strValue;
    }

    /**
     * Deletes the current object from the system
     *
     * @return bool
     */
    public function deleteObject() {
        return true;
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
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb() {
        return true;
    }


    /**
     * Updates the current object to the database.
     * Only the value is updated!!!
     *
     * @param bool $strPrevId
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {

        if(!class_module_system_setting::checkConfigExisting($this->getStrName())) {
            class_logger::getInstance()->addLogRow("new constant " . $this->getStrName() . " with value " . $this->getStrValue(), class_logger::$levelInfo);


            $strQuery = "INSERT INTO " . _dbprefix_ . "system_config
                        (system_config_id, system_config_name, system_config_value, system_config_type, system_config_module) VALUES
                        (?, ?, ?, ?, ?)";
            return $this->objDB->_pQuery($strQuery, array(generateSystemid(), $this->getStrName(), $this->getStrValue(), (int)$this->getIntType(), (int)$this->getIntModule()));
        }
        else {

            class_logger::getInstance()->addLogRow("updated constant " . $this->getStrName() . " to value " . $this->getStrValue(), class_logger::$levelInfo);

            $objChangelog = new class_module_system_changelog();
            $objChangelog->createLogEntry($this, class_module_system_changelog::$STR_ACTION_EDIT);

            $strQuery = "UPDATE " . _dbprefix_ . "system_config
                        SET system_config_value = ?
                      WHERE system_config_name = ?";
            return $this->objDB->_pQuery($strQuery, array($this->getStrValue(), $this->getStrName()));
        }
    }


    /**
     * Renames a constant in the database.
     *
     * @param string $strNewName
     *
     * @return bool
     */
    public function renameConstant($strNewName) {
        class_logger::getInstance()->addLogRow("renamed constant " . $this->getStrName() . " to " . $strNewName, class_logger::$levelInfo);

        $strQuery = "UPDATE " . _dbprefix_ . "system_config
                    SET system_config_name = ? WHERE system_config_name = ?";

        $bitReturn = $this->objDB->_pQuery($strQuery, array($strNewName, $this->getStrName()));
        $this->strName = $strNewName;
        return $bitReturn;
    }

    /**
     * Fetches all Configs from the database
     *
     * @return class_module_system_setting[]
     * @static
     */
    public static function getAllConfigValues() {
        if(self::$arrInstanceCache == null) {
            $strQuery = "SELECT system_config_id FROM " . _dbprefix_ . "system_config ORDER BY system_config_module ASC";
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
            foreach($arrIds as $arrOneId) {
                self::$arrInstanceCache[$arrOneId["system_config_id"]] = new class_module_system_setting($arrOneId["system_config_id"]);
            }
        }

        return self::$arrInstanceCache;
    }

    /**
     * Fetches a Configs selected by name
     *
     * @param $strName
     *
     * @return class_module_system_setting|null
     * @static
     */
    public static function getConfigByName($strName) {

        $arrConfigs = self::getAllConfigValues();
        foreach($arrConfigs as $objOneConfig)
            if($objOneConfig->getStrName() == $strName)
                return $objOneConfig;

        return null;

    }

    /**
     * Checks, if a config-value is already existing
     *
     * @param string $strName
     *
     * @return boolean
     */
    public static function checkConfigExisting($strName) {
        $strQuery = "SELECT COUNT(*) FROM " . _dbprefix_ . "system_config WHERE system_config_name = ?";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
        return $arrRow["COUNT(*)"] == 1;
    }

    public function getVersionActionName($strAction) {
        return $strAction;
    }

    public function renderVersionValue($strProperty, $strValue) {
        return $strValue;
    }

    public function getVersionPropertyName($strProperty) {
        return $strProperty;
    }

    public function getVersionRecordName() {
        return class_carrier::getInstance()->getObjLang()->getLang("change_type_setting", "system");
    }

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
        $this->strValue = $strValue;
    }

    public function setIntType($intType) {
        $this->intType = $intType;
    }

    public function setIntModule($intModule) {
        $this->intModule = $intModule;
    }

}
