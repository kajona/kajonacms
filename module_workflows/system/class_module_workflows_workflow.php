<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
 *
 * @blockFromAutosave
 */
class class_module_workflows_workflow extends class_model implements interface_model, interface_admin_listable  {


    public static $INT_STATE_NEW = 1;
    public static $INT_STATE_SCHEDULED = 2;
    public static $INT_STATE_EXECUTED = 3;

    private $bitSaved = false;

    /**
     * @var string
     * @tableColumn workflows.workflows_class
     * @tableColumnDatatype char254
     * @tableColumnIndex
     */
    private $strClass = "";

    /**
     * @var string
     * @tableColumn workflows.workflows_systemid
     * @tableColumnDatatype char20
     * @tableColumnIndex
     */
    private $strAffectedSystemid = "";

    /**
     * @var int
     * @tableColumn workflows.workflows_state
     * @tableColumnDatatype int
     * @tableColumnIndex
     */
    private $intState = "1";

    /**
     * @var int
     * @tableColumn workflows.workflows_runs
     * @tableColumnDatatype int
     */
    private $intRuns = "0";

    /**
     * @var string
     * @tableColumn workflows.workflows_responsible
     * @tableColumnDatatype char254
     * @tableColumnIndex
     */
    private $strResponsible = "";

    /**
     * @var int
     * @tableColumn workflows.workflows_int1
     * @tableColumnDatatype int
     */
    private $intInt1 = null;

    /**
     * @var int
     * @tableColumn workflows.workflows_int2
     * @tableColumnDatatype int
     */
    private $intInt2 = null;

    /**
     * @var string
     * @tableColumn workflows.workflows_char1
     * @tableColumnDatatype char254
     * @blockEscaping
     */
    private $strChar1 = "";

    /**
     * @var string
     * @tableColumn workflows.workflows_char2
     * @tableColumnDatatype char254
     * @blockEscaping
     */
    private $strChar2 = "";

    /**
     * @var int
     * @tableColumn workflows.workflows_date1
     * @tableColumnDatatype long
     */
    private $longDate1 = 0;

    /**
     * @var int
     * @tableColumn workflows.workflows_date2
     * @tableColumnDatatype long
     */
    private $longDate2 = 0;

    /**
     * @var string
     * @tableColumn workflows.workflows_text
     * @tableColumnDatatype text
     * @blockEscaping
     */
    private $strText = "";

    /**
     * @var string
     * @tableColumn workflows.workflows_text2
     * @tableColumnDatatype text
     * @blockEscaping
     */
    private $strText2 = "";

    /**
     * @var string
     * @tableColumn workflows.workflows_text3
     * @tableColumnDatatype text
     * @blockEscaping
     */
    private $strText3 = "";


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
        if($this->rightEdit())
            return dateToString($this->getObjTriggerDate());
        else
            return false;
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        if($this->getObjWorkflowHandler() instanceof interface_workflows_handler_extendedinfo)
            return $this->getObjWorkflowHandler()->getInstanceInfo();

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

        $objOrmMapper = new class_orm_objectlist();

        if($bitOnlyWithValidTriggerDate) {
            $objOrmMapper->addWhereRestriction(new class_orm_objectlist_property_restriction("objStartDate", class_orm_comparator_enum::LessThen(), class_date::getCurrentTimestamp()));
        }

        $objOrmMapper->addWhereRestriction(new class_orm_objectlist_property_restriction("intState", class_orm_comparator_enum::Equal(), (int)$intType));
        $objOrmMapper->addOrderBy(new class_orm_objectlist_orderby("system_date_start DESC"));

        return $objOrmMapper->getObjectList("class_module_workflows_workflow");
    }

    /**
     * Loads all workflows for a given systemid
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param string $strAffectedSystemid
     * @param bool $bitOnlyScheduled
     * @param string|array $objClass
     *
     * @return class_module_workflows_workflow[]
     * @throws exception
     */
    public static function getWorkflowsForSystemid($strAffectedSystemid, $bitOnlyScheduled = true, $objClass = null, $bitAsCount = false) {
        if(!validateSystemid($strAffectedSystemid)) {
            return array();
        }


        $objORM = new class_orm_objectlist();
        $objORM->addWhereRestriction(new class_orm_objectlist_restriction(" AND workflows_systemid = ?", $strAffectedSystemid));

        //1. handle param $objClass
        if($objClass != null) {
            if(is_string($objClass)) {
                $objClass = array($objClass);
            }
            $arrClasses = array_map(function($strId) {return "?";}, $objClass);
            $strINClasses = implode(",", $arrClasses);

            $arrParams = array();
            foreach($objClass as $strClass) {
                $arrParams[] = $strClass;
            }

            $objORM->addWhereRestriction(new class_orm_objectlist_restriction(" AND workflows_class IN (".$strINClasses.")  ", $arrParams));
        }

        if($bitOnlyScheduled) {
            $objORM->addWhereRestriction(new class_orm_objectlist_restriction(" AND ( workflows_state = ? OR workflows_state = ? )", array((int)self::$INT_STATE_SCHEDULED, (int)self::$INT_STATE_NEW)));
            $objORM->addWhereRestriction(new class_orm_objectlist_restriction(" AND ( system_date_start > ? OR system_date_start = 0 )", class_date::getCurrentTimestamp()));
        }

        $objORM->addOrderBy(new class_orm_objectlist_orderby("system_date_start DESC"));

        if($bitAsCount) {
            return $objORM->getObjectCount(get_called_class());
        }
        return $objORM->getObjectList(get_called_class());

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
        $objOrmMapper = new class_orm_objectlist();

        if($bitOnlyScheduled) {
            $objOrmMapper->addWhereRestriction(new class_orm_objectlist_restriction("AND ( workflows_state = ? OR workflows_state = ? )", array((int)self::$INT_STATE_SCHEDULED, (int)self::$INT_STATE_NEW)));
            $objOrmMapper->addWhereRestriction(new class_orm_objectlist_restriction("AND ( system_date_start > ? OR system_date_start = 0 )", array(class_date::getCurrentTimestamp())));
        }

        $objOrmMapper->addWhereRestriction(new class_orm_objectlist_property_restriction("strClass", class_orm_comparator_enum::Equal(), $strClass));
        $objOrmMapper->addOrderBy(new class_orm_objectlist_orderby("system_date_start DESC"));

        return $objOrmMapper->getObjectList("class_module_workflows_workflow");
    }
    /**
     * Counts all workflows related with a given class.
     * By default limited to those with a exceeded trigger-date, so valid to be run
     *
     * @param $strClass
     * @param bool $bitOnlyScheduled
     * @return class_module_workflows_workflow[]
     */
    public static function getWorkflowsForClassCount($strClass, $bitOnlyScheduled = true) {
        $objOrmMapper = new class_orm_objectlist();

        if($bitOnlyScheduled) {
            $objOrmMapper->addWhereRestriction(new class_orm_objectlist_restriction("AND ( workflows_state = ? OR workflows_state = ? )", array((int)self::$INT_STATE_SCHEDULED, (int)self::$INT_STATE_NEW)));
            $objOrmMapper->addWhereRestriction(new class_orm_objectlist_restriction("AND ( system_date_start > ? OR system_date_start = 0 )", array(class_date::getCurrentTimestamp())));
        }

        $objOrmMapper->addWhereRestriction(new class_orm_objectlist_property_restriction("strClass", class_orm_comparator_enum::Equal(), $strClass));
        $objOrmMapper->addOrderBy(new class_orm_objectlist_orderby("system_date_start DESC"));

        return $objOrmMapper->getObjectCount("class_module_workflows_workflow");
    }


    /**
     * Counts all workflows for a given set of users
     * By default limited to those with an exceeded trigger-date, so valid to be run
     *
     * @param array $arrUserids
     * @return int
     */
    public static function getPendingWorkflowsForUserCount($arrUserids, array $arrClasses = null) {
        $objOrmMapper = new class_orm_objectlist();
        $objOrmMapper->addWhereRestriction(new class_orm_objectlist_property_restriction("intState", class_orm_comparator_enum::Equal(), (int)self::$INT_STATE_SCHEDULED));
        $objOrmMapper->addWhereRestriction(self::getUserWhereStatement($arrUserids));

        if (!empty($arrClasses)) {
            $objOrmMapper->addWhereRestriction(new class_orm_objectlist_property_in_restriction("strClass", $arrClasses));
        }

        return $objOrmMapper->getObjectCount("class_module_workflows_workflow");
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
    public static function getPendingWorkflowsForUser($arrUserids, $intStart = false, $intEnd = false, array $arrClasses = null) {
        $objOrmMapper = new class_orm_objectlist();
        $objOrmMapper->addWhereRestriction(new class_orm_objectlist_property_restriction("intState", class_orm_comparator_enum::Equal(), (int)self::$INT_STATE_SCHEDULED));
        $objOrmMapper->addWhereRestriction(self::getUserWhereStatement($arrUserids));

        if (!empty($arrClasses)) {
            $objOrmMapper->addWhereRestriction(new class_orm_objectlist_property_in_restriction("strClass", $arrClasses));
        }

        $objOrmMapper->addOrderBy(new class_orm_objectlist_orderby("system_date_start DESC"));
        $objOrmMapper->addOrderBy(new class_orm_objectlist_orderby("system_sort DESC"));

        return $objOrmMapper->getObjectList("class_module_workflows_workflow", "", $intStart, $intEnd);
    }


    /**
     * Retrieves the list of workflows available
     *
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return class_module_workflows_workflow[]
     */
    public static function getAllworkflows($intStart = null, $intEnd = null) {
        $objOrmMapper = new class_orm_objectlist();
        $objOrmMapper->addOrderBy(new class_orm_objectlist_orderby("workflows_state ASC"));
        $objOrmMapper->addOrderBy(new class_orm_objectlist_orderby("system_date_start DESC"));
        return $objOrmMapper->getObjectList("class_module_workflows_workflow", "", $intStart, $intEnd);
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
     * @return class_orm_objectlist_restriction
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

        return new class_orm_objectlist_restriction($strWhere, $arrParams);
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

    public function setStrText2($strText2) {
        $this->strText2 = $strText2;
    }

    public function getStrText2() {
        return $this->strText2;
    }

    /**
     * @return string
     */
    public function getStrText3() {
        return $this->strText3;
    }

    /**
     * @param string $strText3
     */
    public function setStrText3($strText3) {
        $this->strText3 = $strText3;
    }


}
