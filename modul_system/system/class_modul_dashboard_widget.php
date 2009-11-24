<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Class to represent a single adminwidget
 *
 * @package modul_dashboard
 */
class class_modul_dashboard_widget extends class_model implements interface_model {

    private $strColumn = "";
    private $strUser = "";
    private $strWidgetId = "";


	/**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_dashboard";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["table"]              = _dbprefix_."dashboard";
		$arrModul["modul"]              = "dashboard";

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
        return array(_dbprefix_."dashboard" => "dashboard_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "dashboard widget ".$this->getSystemid();
    }

    public function initObject() {
        $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
        						   "._dbprefix_."system
        				WHERE system_id = dashboard_id
        				  AND system_id = '".dbsafeString($this->getSystemid())."'";

        $arrRow = $this->objDB->getRow($strQuery);
        if(count($arrRow) > 0) {
            $this->setStrUser($arrRow["dashboard_user"]);
            $this->setStrColumn($arrRow["dashboard_column"]);
            $this->setStrWidgetId($arrRow["dashboard_widgetid"]);
        }

    }

    /**
     * Updates the current widget to the db
     */
    protected function updateStateToDb() {
        
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                   SET dashboard_user = '".dbsafeString($this->getStrUser())."',
                       dashboard_column = '".dbsafeString($this->getStrColumn())."',
                       dashboard_widgetid = '".dbsafeString($this->getStrWidgetId())."'
                 WHERE dashboard_id = '".dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }

    /**
     * Deletes the current object and the assigned widget from the db
     *
     * @return bool
     */
    public function deleteObjectFromDb() {
        if($this->getWidgetmodelForCurrentEntry()->deleteObjectFromDb()) {
            class_logger::getInstance()->addLogRow("deleted dashboardentry ".$this->getSystemid(), class_logger::$levelInfo);
    	    $objRoot = new class_modul_system_common();
    	    $strQuery = "DELETE FROM ".$this->arrModule["table"]."
                                 WHERE dashboard_id = '".dbsafeString($this->getSystemid())."'";
            if($this->objDB->_query($strQuery)) {
                if($objRoot->deleteSystemRecord($this->getSystemid()))
                    return true;
            }
        }

        return false;
    }


    /**
     * Looks up the widgets placed in a given column and
     * returns a list of instances
     *
     * @param string $strColumn
     * @return array of class_modul_system_adminwidget
     */
    public function getWidgetsForColumn($strColumn) {
        $strQuery = "SELECT system_id
        			  FROM ".$this->arrModule["table"].",
        			  	   "._dbprefix_."system
        			 WHERE dashboard_user = '".dbsafeString($this->objSession->getUserID())."'
        			   AND dashboard_column = '".dbsafeString($strColumn)."'
        			   AND dashboard_id = system_id
        	     ORDER BY system_sort ASC ";
        $arrRows = $this->objDB->getArray($strQuery);
        $arrReturn = array();
        if(count($arrRows) > 0) {
            foreach ($arrRows as $arrOneRow) {
            	$arrReturn[] = new class_modul_dashboard_widget($arrOneRow["system_id"]);
            }

        }
        return $arrReturn;
    }


    /**
     * Returns the correpsponding instance of class_modul_system_adminwidget.
     * User class_modul_system_adminwidget::getConcreteAdminwidget() to obtain
     * an instance of the real widget
     *
     * @return class_modul_system_adminwidget
     */
    public function getWidgetmodelForCurrentEntry() {
        return new class_modul_system_adminwidget($this->getStrWidgetId());
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

        //instantiate a model-widget
        $objSystemWidget1 = new class_modul_system_adminwidget();
        $objSystemWidget1->setStrClass("class_adminwidget_systeminfo");
        $objSystemWidget1->setStrContent("a:3:{s:3:\"php\";s:7:\"checked\";s:6:\"server\";s:7:\"checked\";s:6:\"kajona\";s:7:\"checked\";}");

        $objSystemWidget2 = new class_modul_system_adminwidget();
        $objSystemWidget2->setStrClass("class_adminwidget_note");
        $objSystemWidget2->setStrContent("a:1:{s:7:\"content\";s:22:\"Welcome to Kajona V3.2\";}");

        $objSystemWidget3 = new class_modul_system_adminwidget();
        $objSystemWidget3->setStrClass("class_adminwidget_systemlog");
        $objSystemWidget3->setStrContent("a:1:{s:8:\"nrofrows\";s:1:\"5\";}");

        $objSystemWidget4 = new class_modul_system_adminwidget();
        $objSystemWidget4->setStrClass("class_adminwidget_systemcheck");
        $objSystemWidget4->setStrContent("a:2:{s:3:\"php\";s:7:\"checked\";s:6:\"kajona\";s:7:\"checked\";}");

        //and save the widget itself
        if($objSystemWidget1->updateObjectToDb()) {
            $strWidgetId = $objSystemWidget1->getSystemid();
            //and save the dashboard-entry
            $objDashboard = new class_modul_dashboard_widget();
            $objDashboard->setStrColumn("column1");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrWidgetId($strWidgetId);
            if(!$objDashboard->updateObjectToDb())
                $bitReturn = false;
        }

        //and save the widget itself
        if($objSystemWidget2->updateObjectToDb()) {
            $strWidgetId = $objSystemWidget2->getSystemid();
            //and save the dashboard-entry
            $objDashboard = new class_modul_dashboard_widget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrWidgetId($strWidgetId);
            if(!$objDashboard->updateObjectToDb())
                $bitReturn = false;
        }

        //and save the widget itself
        if($objSystemWidget3->updateObjectToDb()) {
            $strWidgetId = $objSystemWidget3->getSystemid();
            //and save the dashboard-entry
            $objDashboard = new class_modul_dashboard_widget();
            $objDashboard->setStrColumn("column3");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrWidgetId($strWidgetId);
            if(!$objDashboard->updateObjectToDb())
                $bitReturn = false;
        }

        //and save the widget itself
        if($objSystemWidget4->updateObjectToDb()) {
            $strWidgetId = $objSystemWidget4->getSystemid();
            //and save the dashboard-entry
            $objDashboard = new class_modul_dashboard_widget();
            $objDashboard->setStrColumn("column3");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrWidgetId($strWidgetId);
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

}


?>