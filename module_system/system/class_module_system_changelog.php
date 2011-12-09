<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * The changelog is a global wrapper to the gui-based logging.
 * Changes should reflect user-changes and not internal system-logs.
 * For logging to the logfile, see class_logger.
 * But: entries added to the changelog are copied to the systemlog leveled as information, too.
 * Changes are stored as a flat list in the database only and have no representation within the
 * system-table. This means there are no common system-id relations.
 * Have a look at the memento pattern by Gamma et al. to get a glance at the conecptional behaviour.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @see class_logger
 */
class class_module_system_changelog extends class_model implements interface_model  {

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);

		parent::__construct($strSystemid);

    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array();
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return "changelog";
    }


    /**
     * Initalises the current object, if a systemid was given
     */
    protected function initObjectInternal() {
    }

    /**
     * Generates a new entry in the modification log storing all relevant information.
     * Creates an entry in the systemlog leveled as information, too.
     * By default entries with same old- and new-values are dropped.
     * The passed object has to implement interface_versionable.
     *
     *
     * @param interface_versionable $objSourceModel
     * @param string $strAction
     * @param bool $bitForceEntry if set to true, an entry will be created even if the values didn't change
     * @return bool
     */
    public function createLogEntry(interface_versionable $objSourceModel, $strAction, $bitForceEntry = false) {
        $bitReturn = true;

        if(!$objSourceModel instanceof interface_versionable) {
            throw new class_exception("object passed to create changelog not implementing interface_versionable", class_logger::$levelWarning);
            return true;
        }

        if(!defined("_system_changehistory_enabled_") || _system_changehistory_enabled_ == "false")
            return true;

        //changes require at least kajona 3.3.1.10
        $arrModul = $this->getModuleData("system", false);
        if(version_compare($arrModul["module_version"], "3.3.1.10") < 0)
            return;

        $arrChanges = $objSourceModel->getChangedFields($strAction);
        if(is_array($arrChanges) && in_array(_dbprefix_."changelog", $this->objDB->getTables())) {
            foreach($arrChanges as $arrChangeSet) {

                $strOldvalue = "";
                if(isset($arrChangeSet["oldvalue"]))
                    $strOldvalue = $arrChangeSet["oldvalue"];

                $strNewvalue = "";
                if(isset($arrChangeSet["newvalue"]))
                    $strNewvalue = $arrChangeSet["newvalue"];

                $strProperty= $arrChangeSet["property"];

                if($strOldvalue instanceof class_date)
                    $strOldvalue = $strOldvalue->getLongTimestamp();

                if($strNewvalue instanceof class_date)
                    $strNewvalue= $strNewvalue->getLongTimestamp();

                if(!$bitForceEntry && ($strOldvalue == $strNewvalue) )
                    continue;

                class_logger::getInstance()->addLogRow("change in class ".$objSourceModel->getClassname()."@".$strAction." systemid: ".$objSourceModel->getSystemid()." property: ".$strProperty." old value: ".uniStrTrim($strOldvalue, 60)." new value: ".uniStrTrim($strNewvalue, 60), class_logger::$levelInfo);

                $strQuery = "INSERT INTO "._dbprefix_."changelog
                     (change_id,
                      change_date,
                      change_systemid,
                      change_system_previd,
                      change_user,
                      change_class,
                      change_action,
                      change_property,
                      change_oldvalue,
                      change_newvalue) VALUES
                     (?,?,?,?,?,?,?,?,?,?)";

                $bitReturn = $bitReturn && $this->objDB->_pQuery($strQuery, array(
                    generateSystemid(),
                    class_date::getCurrentTimestamp(),
                    $objSourceModel->getSystemid(),
                    $objSourceModel->getPrevid(),
                    $this->objSession->getUserID(),
                    $objSourceModel->getClassname(),
                    $strAction,
                    $strProperty,
                    $strOldvalue,
                    $strNewvalue
                ));
            }
        }

        return $bitReturn;
    }

    /**
     * Creates the list of logentries, either without a systemid-based filter
     * or limited to the given systemid.
     *
     * @param string $strSystemidFilter
     * @param null|int $intStart
     * @param null|int $intEnd
     * @return class_changelog_container
     */
    public static function getLogEntries($strSystemidFilter = "", $intStart = null, $intEnd = null) {
        $strQuery = "SELECT *
                       FROM "._dbprefix_."changelog
                      ".($strSystemidFilter != "" ? " WHERE change_systemid = ? ": "")."
                   ORDER BY change_date DESC";

        $arrParams = array();
        if($strSystemidFilter != "")
            $arrParams[] = $strSystemidFilter;

        if($intStart != null && $intEnd != null)
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, $arrParams, $intStart, $intEnd);
        else
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrRows as $arrRow)
            $arrReturn[] = new class_changelog_container($arrRow["change_date"], $arrRow["change_systemid"], $arrRow["change_user"],
                           $arrRow["change_class"], $arrRow["change_action"], $arrRow["change_property"], $arrRow["change_oldvalue"], $arrRow["change_newvalue"]);

        return $arrReturn;
    }

    /**
     * Counts the number of logentries available
     *
     * @param string $strSystemidFilter
     * @return int
     */
    public static function getLogEntriesCount($strSystemidFilter = "") {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."changelog
                      ".($strSystemidFilter != "" ? " WHERE change_systemid = ? ": "")."
                   ORDER BY change_date DESC";

        $arrParams = array();
        if($strSystemidFilter != "")
            $arrParams[] = $strSystemidFilter;

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }


     /**
     * Creates the list of logentries, based on a flexible but specific filter-list
     *
     * @param type $strSystemidFilter
     * @param type $strActionFilter
     * @param type $strPropertyFilter
     * @param type $strOldvalueFilter
     * @param type $strNewvalueFilter
     * @return class_changelog_container
     */
    public static function getSpecificEntries($strSystemidFilter = null, $strActionFilter = null, $strPropertyFilter = null, $strOldvalueFilter = null, $strNewvalueFilter = null) {

        $arrWhere = array();
        if($strSystemidFilter !== null)
            $arrWhere[] = " change_systemid = ? ";
        if($strActionFilter !== null)
            $arrWhere[] = " change_action = ? ";
        if($strPropertyFilter !== null)
            $arrWhere[] = " change_property = ? ";
        if($strOldvalueFilter !== null)
            $arrWhere[] = " change_oldvalue = ? ";
        if($strNewvalueFilter !== null)
            $arrWhere[] = " change_newvalue = ? ";

        $strQuery = "SELECT *
                       FROM "._dbprefix_."changelog
                      ".(count($arrWhere) > 0 ? " WHERE ".implode("AND", $arrWhere) : "")."
                   ORDER BY change_date DESC";

        $arrParams = array();
        if($strSystemidFilter !== null)
            $arrParams[] = $strSystemidFilter;

        if($strActionFilter !== null)
            $arrParams[] = $strActionFilter;

        if($strPropertyFilter !== null)
            $arrParams[] = $strPropertyFilter;

        if($strOldvalueFilter !== null)
            $arrParams[] = $strOldvalueFilter;

        if($strNewvalueFilter !== null)
            $arrParams[] = $strNewvalueFilter;

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrRows as $arrRow)
            $arrReturn[] = new class_changelog_container($arrRow["change_date"], $arrRow["change_systemid"], $arrRow["change_user"],
                           $arrRow["change_class"], $arrRow["change_action"], $arrRow["change_property"], $arrRow["change_oldvalue"], $arrRow["change_newvalue"]);

        return $arrReturn;
    }

    /**
     * Deletes the current object from the system
     * Overwrite!
     * @return bool
     */
    public function deleteObject() {
        return true;
    }

    /**
     * Deletes the current object from the system.
     * Overwrite this method in order to remove the current object from the system.
     * The system-record itself is being delete automatically.
     *
     * @return bool
     */
    protected function deleteObjectInternal() {
        return false;
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


}


/**
 * Simple data-container for logentries.
 * Has no regular use.
 */
final class class_changelog_container {
    private $objDate;
    private $strSystemid;
    private $strUserId;
    private $strClass;
    private $strAction;
    private $strProperty;
    private $strOldValue;
    private $strNewValue;

    function __construct($intDate, $strSystemid, $strUserId, $strClass, $strAction, $strProperty, $strOldValue, $strNewValue) {
        $this->objDate = new class_date($intDate);
        $this->strSystemid = $strSystemid;
        $this->strUserId = $strUserId;
        $this->strClass = $strClass;
        $this->strAction = $strAction;
        $this->strProperty = $strProperty;
        $this->strOldValue = $strOldValue;
        $this->strNewValue = $strNewValue;
    }

    /**
     *
     * @return interface_versionable
     */
    public function getObjTarget() {
        if(class_exists($this->strClass))
            return new $this->strClass($this->strSystemid);
        else
            return null;
    }

    /**
     *
     * @return class_date
     */
    public function getObjDate() {
        return $this->objDate;
    }

    public function getStrSystemid() {
        return $this->strSystemid;
    }

    public function getStrUserId() {
        return $this->strUserId;
    }

    public function getStrUsername() {
        $objUser = new class_module_user_user($this->getStrUserId());
        return $objUser->getStrUsername();
    }

    public function getStrClass() {
        return $this->strClass;
    }

    public function getStrAction() {
        return $this->strAction;
    }

    public function getStrOldValue() {
        return $this->strOldValue;
    }

    public function getStrNewValue() {
        return $this->strNewValue;
    }

    public function getStrProperty() {
        return $this->strProperty;
    }


}
