<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/

/**
 * A single workflow. Holds all values and the reference to the concrete workflow-handler.
 * Provides methods in order to manage the existing workflows.
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 *
 * @targetTable workflows.workflows_id
 *
 * @module workflows
 * @moduleId _workflows_module_id_
 */
class class_module_workflows_workflow extends class_model implements interface_model, interface_admin_listable  {


    public static $INT_STATE_NEW = 1;
    public static $INT_STATE_SCHEDULED = 2;
    public static $INT_STATE_EXECUTED = 3;

    private $bitSaved = false;

    /**
     * @var string
     * @tableColumn workflows.workflows_class
     */
    private $strClass = "";

    /**
     * @var string
     * @tableColumn workflows.workflows_systemid
     */
    private $strAffectedSystemid = "";

    /**
     * @var int
     * @tableColumn workflows.workflows_state
     */
    private $intState = "1";

    /**
     * @var int
     * @tableColumn workflows.workflows_runs
     */
    private $intRuns = "0";

    /**
     * @var string
     * @tableColumn workflows.workflows_responsible
     */
    private $strResponsible = "";

    /**
     * @var int
     * @tableColumn workflows.workflows_int1
     */
    private $intInt1 = null;

    /**
     * @var int
     * @tableColumn workflows.workflows_int2
     */
    private $intInt2 = null;

    /**
     * @var string
     * @tableColumn workflows.workflows_char1
     * @blockEscaping
     */
    private $strChar1 = "";

    /**
     * @var string
     * @tableColumn workflows.workflows_char2
     * @blockEscaping
     */
    private $strChar2 = "";

    /**
     * @var int
     * @tableColumn workflows.workflows_date1
     */
    private $longDate1 = 0;

    /**
     * @var int
     * @tableColumn workflows.workflows_date2
     */
    private $longDate2 = 0;

    /**
     * @var string
     * @tableColumn workflows.workflows_text
     * @blockEscaping
     */
    private $strText = "";

    /**
     * @var string
     * @tableColumn workflows.workflows_text2
     * @blockEscaping
     */
    private $strText2 = "";


    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_workflow";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return dateToString($this->getObjTriggerDate());
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getObjWorkflowHandler()->getStrName();
    }


    /**
     * Creates the matching date-records
     * @return bool
     */
    protected function onInsertToDb() {
        //the creation of a date-record is forced for workflows
        return $this->createDateRecord($this->getSystemid());
    }




    /**
     * Loads all workflows in a given state.
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param int $intType
     * @param bool $bitOnlyWithValidTriggerDate
     * @return class_module_workflows_workflow[]
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
            $arrReturn[] = new class_module_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Loads all workflows for a given systemid
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param $strAffectedSystemid
     * @param bool $bitOnlyScheduled
     * @param string $strClass
     *
     * @return class_module_workflows_workflow[]
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
            $arrReturn[] = new class_module_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Loads all workflows related with a given class.
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param $strClass
     * @param bool $bitOnlyScheduled
     * @return class_module_workflows_workflow[]
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
            $arrReturn[] = new class_module_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Counts all workflows for a given set of users
     * By default limited to those with an exceeded trigger-date, so valid to be run
     *
     * @param array $arrUserids
     * @return int
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
                        AND  workflows_state = ?
                        AND ( system_date_start > ? OR system_date_start = 0 ) ";

        $arrParams[] = (int)self::$INT_STATE_SCHEDULED;
        $arrParams[] = class_date::getCurrentTimestamp();


        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }

    /**
     * Loads all workflows for a given user
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param array $arrUserids
     * @param bool|int $intStart
     * @param bool|int $intEnd
     * @return class_module_workflows_workflow[]
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
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Retrieves the list of workflows available
     *
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return class_module_workflows_workflow[]
     */
    public static function getAllworkflows($intStart = false, $intEnd = false) {
        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows,
                            "._dbprefix_."system_date
                      WHERE system_id = workflows_id
                        AND system_id = system_date_id
                   ORDER BY workflows_state ASC, system_date_start DESC";


        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_workflows_workflow($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Returns the current workflow-handler
     *
     * @throws class_exception
     * @return interface_workflows_handler
     */
    public function getObjWorkflowHandler() {
        $strClassname = $this->getStrClass();
        if(class_exists($strClassname)) {

            //load the config-object
            $objConfig = class_module_workflows_handler::getHandlerByClass($strClassname);

            /** @var $objHandler interface_workflows_handler */
            $objHandler = new $strClassname();
            $objHandler->setObjWorkflow($this);
            if($objConfig != null)
                $objHandler->setConfigValues($objConfig->getStrConfigVal1(), $objConfig->getStrConfigVal2(), $objConfig->getStrConfigVal3());
            return $objHandler;
        }
        else {
            throw new class_exception("workflow handler ".$strClassname." not exisiting", class_exception::$level_ERROR);
        }

    }

    /**
     * Transforms the passed list of user-/group ids into an sql-where restriction
     *
     * @param array $arrUsers
     * @return array ($strQuery, $arrParams)
     */
    private static function getUserWhereStatement($arrUsers) {

        if(!is_array($arrUsers))
            $arrUsers = array($arrUsers);

        $arrParams = array();
        if(count($arrUsers) == 0)
            return "";

        $strWhere = "";
        foreach($arrUsers as $strOneUser) {
            if($strWhere != "")
                $strWhere .= " OR ";

            $strWhere .= "workflows_responsible LIKE ? ";
            $arrParams[] = "%".$strOneUser."%";
        }

        if($strWhere != "")
            $strWhere = "AND ( ".$strWhere." )";
        $arrReturn = array($strWhere, $arrParams);
        return $arrReturn;
    }


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
        return $this->getObjStartDate();
    }

    public function setObjTriggerdate($objTriggerdate) {
        $this->setObjStartDate($objTriggerdate);
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

    function setStrText2($strText2) {
        $this->strText2 = $strText2;
    }

    public function getStrText2() {
        return $this->strText2;
    }




}
