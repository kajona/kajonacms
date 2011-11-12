<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/

/**
 * A single workflow. Holds all values and the reference to the concrete workflow-handler.
 * Provides methods in order to manage the existing workflows.
 *
 * @package modul_workflows
 * @author sidler@mulchprod.de
 */
class class_modul_workflows_workflow extends class_model implements interface_model  {


    public static $INT_STATE_NEW = 1;
    public static $INT_STATE_SCHEDULED = 2;
    public static $INT_STATE_EXECUTED = 3;

    private $bitSaved = false;



    private $strClass = "";
    private $strAffectedSystemid = "";
    private $intState = "1";
    private $intRuns = "0";
    private $objTriggerdate = null;
    private $strResponsible = "";
    private $intInt1 = null;
    private $intInt2 = null;
    private $strChar1 = "";
    private $strChar2 = "";
    private $longDate1 = 0;
    private $longDate2 = 0;
    private $strText = "";

   

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_workflows";
		$arrModul["moduleId"] 			= _workflows_modul_id_;
		$arrModul["modul"]				= "workflows";
		$arrModul["table"]				= _dbprefix_."workflows";

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
        return array(_dbprefix_."workflows" => "workflows_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "workflow ".$this->getSystemid();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
         $strQuery = "SELECT * FROM ".$this->arrModule["table"].", 
                                    "._dbprefix_."system_date,
                                    "._dbprefix_."system
	                   WHERE workflows_id = ?
                         AND system_id= workflows_id
                         AND system_id = system_date_id";

         $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
         $this->setStrClass($arrRow["workflows_class"]);
         $this->setStrAffectedSystemid($arrRow["workflows_systemid"]);
         $this->setIntState($arrRow["workflows_state"]);
         $this->setIntRuns($arrRow["workflows_runs"]);
         $this->setStrResponsible($arrRow["workflows_responsible"]);
         $this->setIntInt1($arrRow["workflows_int1"]);
         $this->setIntInt2($arrRow["workflows_int2"]);
         $this->setStrChar1($arrRow["workflows_char1"]);
         $this->setStrChar2($arrRow["workflows_char2"]);
         $this->setLongDate1($arrRow["workflows_date1"]);
         $this->setLongDate2($arrRow["workflows_date2"]);
         $this->setStrText($arrRow["workflows_text"]);

         if($arrRow["system_date_start"] != "0")
             $this->setObjTriggerdate(new class_date($arrRow["system_date_start"]));
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        class_logger::getInstance()->addLogRow("updated workflow ".$this->getSystemid(), class_logger::$levelInfo);

        $this->bitSaved = true;

        $this->getObjWorkflowHandler()->initialize();

        $this->updateDateRecord($this->getSystemid(), $this->getObjTriggerdate(), null);

        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET workflows_class = ?,
                            workflows_systemid = ?,
                            workflows_state = ?,
                            workflows_runs = ?,
                            workflows_responsible = ?,
                            workflows_int1 = ?,
                            workflows_int2 = ?,
                            workflows_char1 = ?,
                            workflows_char2 = ?,
                            workflows_date1 = ?,
                            workflows_date2 = ?,
                            workflows_text = ?
                      WHERE workflows_id = ?";
        
        return $this->objDB->_pQuery($strQuery, array(
            $this->getStrClass(),
            $this->getStrAffectedSystemid(),
            (int)$this->getIntState(),
            (int)$this->getIntRuns(),
            $this->getStrResponsible(),
            (int)$this->getIntInt1(),
            (int)$this->getIntInt2(),
            $this->getStrChar1(),
            $this->getStrChar2(),
            $this->getLongDate1(),
            $this->getLongDate2(),
            $this->getStrText(),
            $this->getSystemid()
        ));
    }



    /**
     * Creates the matching date-records
     * @return bool
     */
    protected function onInsertToDb() {
        return $this->createDateRecord($this->getSystemid());
    }



    
    /**
     * Deletes a workflow from the database
     * @return bool
     */
	public function deleteWorkflow() {

        $this->getObjWorkflowHandler()->onDelete();

	    class_logger::getInstance()->addLogRow("deleted ".$this->getObjectDescription(), class_logger::$levelInfo);
        $strQuery = "DELETE FROM ".$this->arrModule["table"]." WHERE workflows_id = ? ";

        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
            if($this->deleteSystemRecord($this->getSystemid()))
                return true;
        }
	    return false;
	}


    /**
     * Loads all workflows in a given state.
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param int $intType
     * @param bool $bitOnlyWithValidTriggerDate
     * @return class_modul_workflows_workflow
     */
    public static function getWorkflowsByType($intType, $bitOnlyWithValidTriggerDate = true) {
        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows,
                            "._dbprefix_."system_date
                      WHERE system_id = workflows_id
                        AND system_id = system_date_id
                        AND workflows_state = ?
                     ".($bitOnlyWithValidTriggerDate ? " AND system_date_start < ? " : "")."
                   ORDER BY system_date_start DESC";

        $arrParams = array();
        $arrParams[] = (int)$intType;
        
        if($bitOnlyWithValidTriggerDate)
            $arrParams[] = class_date::getCurrentTimestamp();


        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Loads all workflows for a given systemid
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param int $intType
     * @param bool $bitOnlyScheduled
     * @param string $strClass
     * @return class_modul_workflows_workflow
     */
    public static function getWorkflowsForSystemid($strAffectedSystemid, $bitOnlyScheduled = true, $strClass = null) {
        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows,
                            "._dbprefix_."system_date
                      WHERE system_id = workflows_id
                        AND system_id = system_date_id
                        AND workflows_systemid = ?
                     ".($bitOnlyScheduled ? " AND ( workflows_state = ? OR workflows_state = ? )" : "" )  ."
                     ".($bitOnlyScheduled ? " AND ( system_date_start > ? OR system_date_start = 0 )" : "")."
                     ".($strClass != null ? " AND workflows_class = ? " : "")."
                   ORDER BY system_date_start DESC";

        $arrParams = array();
        $arrParams[] = $strAffectedSystemid;
        
        if($bitOnlyScheduled) {
            $arrParams[] = (int)self::$INT_STATE_SCHEDULED;
            $arrParams[] = (int)self::$INT_STATE_NEW;
            $arrParams[] = class_date::getCurrentTimestamp();
        }

        if($strClass != null)
            $arrParams[] = $strClass;

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Loads all workflows related with a given class.
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param int $intType
     * @param bool $bitOnlyScheduled
     * @return class_modul_workflows_workflow
     */
    public static function getWorkflowsForClass($strClass, $bitOnlyScheduled = true) {
        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows,
                            "._dbprefix_."system_date
                      WHERE system_id = workflows_id
                        AND system_id = system_date_id
                        AND workflows_class = ?
                     ".($bitOnlyScheduled ? " AND ( workflows_state = ? OR workflows_state = ? )" : "" )  ."
                     ".($bitOnlyScheduled ? " AND ( system_date_start > ? OR system_date_start = 0 )" : "")."
                   ORDER BY system_date_start DESC";

        $arrParams = array();
        $arrParams[] = $strClass;

        if($bitOnlyScheduled) {
            $arrParams[] = (int)self::$INT_STATE_SCHEDULED;
            $arrParams[] = (int)self::$INT_STATE_NEW;
            $arrParams[] = class_date::getCurrentTimestamp();
        }

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Counts all workflows for a given set of users
     * By default limited to those with an exceeded trigger-date, so valid to be run
     *
     * @param array $arrUserids
     * @return class_modul_workflows_workflow
     */
    public static function getPendingWorkflowsForUserCount($arrUserids) {

        $arrTemp = self::getUserWhereStatement($arrUserids);
        $arrParams = $arrTemp[1];

        $strQuery = "SELECT COUNT(*) FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows,
                            "._dbprefix_."system_date
                      WHERE system_id = workflows_id
                        AND system_id = system_date_id
                        ".$arrTemp[0]."
                        AND ( workflows_state = ?  )
                        AND ( system_date_start > ? OR system_date_start = 0 )
                   ORDER BY system_date_start DESC";

        $arrParams[] = (int)self::$INT_STATE_SCHEDULED;
        $arrParams[] = class_date::getCurrentTimestamp();


        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams );
        return $arrRow["COUNT(*)"];
    }
    
    /**
     * Loads all workflows for a given user
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param array $arrUserids
     * @param int $intStart
     * @param int $intEnd
     * @return class_modul_workflows_workflow
     */
    public static function getPendingWorkflowsForUser($arrUserids, $intStart = false, $intEnd = false) {

        $arrTemp = self::getUserWhereStatement($arrUserids);
        $arrParams = $arrTemp[1];

        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows,
                            "._dbprefix_."system_date
                      WHERE system_id = workflows_id
                        AND system_id = system_date_id
                        ".$arrTemp[0]."
                        AND ( workflows_state = ?  )
                        /*AND ( system_date_start > ? OR system_date_start = 0 )*/
                   ORDER BY system_date_start DESC";

        $arrParams[] = (int)self::$INT_STATE_SCHEDULED;
        //$arrParams[] = class_date::getCurrentTimestamp();


       if($intStart != false && $intEnd != false)
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, $arrParams, $intStart, $intEnd);
        else
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }




    /**
     * Retrieves the list of workflows available
     *
     * @param int $intStart
     * @param int $intEnd
     * @return class_modul_workflows_workflow
     */
    public static function getAllworkflows($intStart = false, $intEnd = false) {
        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows,
                            "._dbprefix_."system_date
                      WHERE system_id = workflows_id
                        AND system_id = system_date_id
                   ORDER BY workflows_state ASC, system_date_start DESC";
                              

        if($intStart != false && $intEnd != false)
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array(), $intStart, $intEnd);
        else
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Counts the number of workflows available
     * @return int
     */
    public static function getAllworkflowsCount() {

        $strQuery = "SELECT COUNT(*) FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows,
                            "._dbprefix_."system_date
                      WHERE system_id = workflows_id
                        AND system_id = system_date_id";

       $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
       return $arrRow["COUNT(*)"];
    }


    /**
     * Returns the current workflow-handler
     *
     * @return interface_workflows_handler
     */
    public function getObjWorkflowHandler() {
        $strClassname = $this->getStrClass();
        if(class_exists($strClassname)) {

            //load the config-object
            $objConfig = class_modul_workflows_handler::getHandlerByClass($strClassname);

            $objHandler = new $strClassname();
            $objHandler->setObjWorkflow($this);
            if($objConfig != null)
                $objHandler->setConfigValues($objConfig->getStrConfigVal1(), $objConfig->getStrConfigVal2(), $objConfig->getStrConfigVal3());
            return $objHandler;
        }
        else {
            throw new class_exception("workflow handler ".$strClassname." not exisiting", class_exception::$level_ERROR);
        }

        return null;

    }

    /**
     * Transforms the passd list of user-/group ids into an sql-where restriction
     *
     * @param array $arrUsers
     * @return array ($strQuery, $arrParams)
     */
    private static function getUserWhereStatement($arrUsers) {

        $arrReturn = array();
        $arrParams = array();

        if(count($arrUsers) == 0)
            return "";
        
        $strWhere = "";
        foreach($arrUsers as $strOneUser) {
            if($strWhere != "")
                $strWhere .= " OR ";

            $strWhere .= "workflows_responsible LIKE ? ";
            $arrParams[] = "%".dbsafeString($strOneUser)."%";
        }
        
        $strWhere = "AND( ".$strWhere." )";

        $arrReturn = array($strWhere, $arrParams);

        return $arrReturn;

    }

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrClass() {
        return $this->strClass;
    }

    public function setStrClass($strClass) {
        $this->strClass = $strClass;
    }

    public function getStrAffectedSystemid() {
        return $this->strAffectedSystemid;
    }

    public function setStrAffectedSystemid($strAffectedSystemid) {
        $this->strAffectedSystemid = $strAffectedSystemid;
    }

    /**
     *
     * @return class_date
     */
    public function getObjTriggerdate() {
        return $this->objTriggerdate;
    }

    public function setObjTriggerdate($objTriggerdate) {
        $this->objTriggerdate = $objTriggerdate;
    }

    public function getStrResponsible() {
        return $this->strResponsible;
    }

    /**
     * Please note that this may be a comma-separated list of user-/group ids
     * @param string $strResponsible
     */
    public function setStrResponsible($strResponsible) {
        $this->strResponsible = $strResponsible;
    }

    public function getIntInt1() {
        return $this->intInt1;
    }

    public function setIntInt1($intInt1) {
        $this->intInt1 = $intInt1;
    }

    public function getIntInt2() {
        return $this->intInt2;
    }

    public function setIntInt2($intInt2) {
        $this->intInt2 = $intInt2;
    }

    public function getStrChar1() {
        return $this->strChar1;
    }

    public function setStrChar1($strChar1) {
        $this->strChar1 = $strChar1;
    }

    public function getStrChar2() {
        return $this->strChar2;
    }

    public function setStrChar2($strChar2) {
        $this->strChar2 = $strChar2;
    }

    public function getLongDate1() {
        return $this->longDate1;
    }

    public function setLongDate1($longDate1) {
        $this->longDate1 = $longDate1;
    }

    public function getLongDate2() {
        return $this->longDate2;
    }

    public function setLongDate2($longDate2) {
        $this->longDate2 = $longDate2;
    }

    public function getStrText() {
        return $this->strText;
    }

    public function setStrText($strText) {
        $this->strText = $strText;
    }

    public function getIntState() {
        return $this->intState;
    }

    public function setIntState($intState) {
        $this->intState = $intState;
    }

    public function getIntRuns() {
        return $this->intRuns;
    }

    public function setIntRuns($intRuns) {
        $this->intRuns = $intRuns;
    }

    public function getBitSaved() {
        return $this->bitSaved;
    }




}
?>