<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Class to represent a single adminwidget
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 */
class class_module_dashboard_widget extends class_model implements interface_model, interface_recorddeleted_listener {

    /**
     * @var string
     * @tableColumn dashboard_column
     */
    private $strColumn = "";

    /**
     * @var string
     * @tableColumn dashboard_user
     */
    private $strUser = "";

    /**
     * @var string
     * @tableColumn dashboard_aspect
     */
    private $strAspect = "";

    /**
     * @var string
     * @tableColumn dashboard_class
     */
    private $strClass = "";

    /**
     * @var string
     * @tableColumn dashboard_content
     * @blockEscaping
     */
    private $strContent = "";


	/**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "dashboard");
        $this->setArrModuleEntry("moduleId", _dashboard_modul_id_);

		parent::__construct($strSystemid);

    }


    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."dashboard" => "dashboard_id");
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return "dashboard widget ".$this->getSystemid();
    }


    /**
     * Looks up all widgets available in the filesystem.
     * ATTENTION: returns the class-name representation of a file, NOT the filename itself.
     *
     * @return array
     */
    public function getListOfWidgetsAvailable() {
        $arrReturn = array();

        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/admin/widgets/", array(".php"));

        foreach($arrFiles as $strOneFile) {
            if($strOneFile != "interface_adminwidget.php" && $strOneFile != "class_adminwidget.php") {
                $arrReturn[] = uniStrReplace(".php", "", $strOneFile);
            }
        }

        return $arrReturn;
    }


    /**
     * Creates the concrete widget represented by this model-element
     *
     * @return interface_adminwidget|class_adminwidget
     */
    public function getConcreteAdminwidget() {
        $objWidget = new $this->strClass();
        //Pass the field-values
        $objWidget->setFieldsAsString($this->getStrContent());
        $objWidget->setSystemid($this->getSystemid());
        return $objWidget;
    }



    /**
     * Implementing callback to react on user-delete events
     *
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param $strSystemid
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid) {
        if(validateSystemid($strSystemid)) {
            $strQuery = "SELECT dashboard_id FROM "._dbprefix_."dashboard WHERE dashboard_user = ?";
            $arrRows = $this->objDB->getPArray($strQuery, array($strSystemid));
            foreach($arrRows as $arrOneRow) {
                $objWidget = new class_module_dashboard_widget($arrOneRow["dashboard_id"]);
                $objWidget->deleteObject();
            }
        }

        return true;
    }

    /**
     * Looks up the widgets placed in a given column and
     * returns a list of instances
     *
     * @param string $strColumn
     * @param string $strAspectFilter
     * @return array of class_module_dashboard_widget
     */
    public function getWidgetsForColumn($strColumn, $strAspectFilter = "") {

        $arrParams = array();
        $arrParams[] = $this->objSession->getUserID();
        $arrParams[] = $strColumn;
        if($strAspectFilter != "") {
            $arrParams[] = "%".$strAspectFilter."%";
            $strAspectFilter = " AND (dashboard_aspect = '' OR dashboard_aspect IS NULL OR dashboard_aspect LIKE ? )";
        }

        $strQuery = "SELECT system_id
        			  FROM "._dbprefix_."dashboard,
        			  	   "._dbprefix_."system
        			 WHERE dashboard_user = ?
        			   AND dashboard_column = ?
        			   AND dashboard_id = system_id
                       ".$strAspectFilter."
        	     ORDER BY system_sort ASC ";

        $arrRows = $this->objDB->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        if(count($arrRows) > 0) {
            foreach ($arrRows as $arrOneRow) {
            	$arrReturn[] = new class_module_dashboard_widget($arrOneRow["system_id"]);
            }

        }
        return $arrReturn;
    }


    /**
     * Looks up the widgets placed in a given column and
     * returns a list of instances
     *
     * @return class_module_dashboard_widget[]
     */
    public static function getAllWidgets() {

        $arrParams = array();

        $strQuery = "SELECT system_id
        			  FROM "._dbprefix_."dashboard,
        			  	   "._dbprefix_."system
        			 WHERE dashboard_id = system_id
        	     ORDER BY system_sort ASC ";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        if(count($arrRows) > 0) {
            foreach ($arrRows as $arrOneRow) {
                $arrReturn[] = new class_module_dashboard_widget($arrOneRow["system_id"]);
            }

        }
        return $arrReturn;
    }


    /**
     * Returns the corresponding instance of class_module_system_adminwidget.
     * User class_module_system_adminwidget::getConcreteAdminwidget() to obtain
     * an instance of the real widget
     *
     * @return class_module_system_adminwidget
     */
    public function getWidgetmodelForCurrentEntry() {
        return new class_module_system_adminwidget($this->getStrWidgetId());
    }


    /**
     * Creates an initial set of widgets to be displayed to new users.
     * NOTE: Low-level variant!
     *
     * @param string $strUserid
     * @return bool
     */
    public function createInitialWidgetsForUser($strUserid) {
        $bitReturn = true;


        $arrWidgets = array();
        $arrWidgets[] = array("class_adminwidget_systeminfo", "a:3:{s:3:\"php\";s:7:\"checked\";s:6:\"server\";s:7:\"checked\";s:6:\"kajona\";s:7:\"checked\";}", "column1");
        $arrWidgets[] = array("class_adminwidget_note", "a:1:{s:7:\"content\";s:22:\"Welcome to Kajona V3.4\";}", "column2");
        $arrWidgets[] = array("class_adminwidget_systemlog", "a:1:{s:8:\"nrofrows\";s:1:\"5\";}", "column3");
        $arrWidgets[] = array("class_adminwidget_systemcheck", "a:2:{s:3:\"php\";s:7:\"checked\";s:6:\"kajona\";s:7:\"checked\";}", "column3");

        if(class_exists("class_adminwidget_lastmodifiedpages"))
            $arrWidgets[] = array("class_adminwidget_lastmodifiedpages", "a:1:{s:8:\"nrofrows\";s:1:\"4\";}", "column2");


        foreach($arrWidgets as $arrOneWidget) {

            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->setStrColumn($arrOneWidget[2]);
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass($arrOneWidget[0]);
            $objDashboard->setStrContent($arrOneWidget[1]);
            $objDashboard->setStrAspect(class_module_system_aspect::getCurrentAspectId());
            if(!$objDashboard->updateObjectToDb())
                $bitReturn = false;
        }


        return $bitReturn;
    }


    //--- GETTERS / SETTERS ---------------------------------------------------------------------------------

    public function setStrColumn($strColumn) {
        $this->strColumn = $strColumn;
    }
    public function setStrUser($strUser) {
        $this->strUser = $strUser;
    }
    public function setStrWidgetId($strWidgetId) {
        $this->strWidgetId = $strWidgetId;
    }

    public function getStrColumn() {
        return $this->strColumn;
    }
    public function getStrUser() {
        return $this->strUser;
    }
    public function getStrWidgetId() {
        return $this->strWidgetId;
    }

    public function getStrAspect() {
        return $this->strAspect;
    }

    public function setStrAspect($strAspect) {
        $this->strAspect = $strAspect;
    }

    /**
     * @param string $strClass
     */
    public function setStrClass($strClass) {
        $this->strClass = $strClass;
    }

    /**
     * @return string
     */
    public function getStrClass() {
        return $this->strClass;
    }

    /**
     * @param string $strContent
     */
    public function setStrContent($strContent) {
        $this->strContent = $strContent;
    }

    /**
     * @return string
     */
    public function getStrContent() {
        return $this->strContent;
    }


}


