<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                            *
********************************************************************************************************/


/**
 * @package modul_dashboard
 *
 */
class class_modul_dashboard_admin extends class_admin implements interface_admin {

    protected $arrColumnsOnDashboard = array("column1", "column2", "column3");

    private $strStartMonthKey = "DASHBOARD_CALENDAR_START_MONTH";
    private $strStartYearKey = "DASHBOARD_CALENDAR_START_YEAR";

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 				= "modul_dashboard";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _dashboard_modul_id_;
		$arrModul["modul"]				= "dashboard";

		//Base class
		parent::__construct($arrModul);

	}


	public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("moduleRights"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "addWidgetToDashboard", "", $this->getText("addWidget"), "", "", true, "adminnavi"));
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "calendar", "", $this->getText("module_calendar"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


	/**
	 * Generates the dashboard itself.
	 * Loads all widgets placed on the dashboard
	 *
	 * @return string
	 */
	protected function actionList() {
	    $strReturn = "";
	    //check needed permissions
	    if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strReturn .= $this->objToolkit->jsDialog(1);
	        //load the widgets for each column. currently supporting 3 columns on the dashboard.
	        $objDashboardmodel = new class_modul_dashboard_widget();
	        $arrColumns = array();
	        //build each row
	        foreach($this->arrColumnsOnDashboard as $strColumnName) {
	            $strColumnContent = $this->objToolkit->getDashboardColumnHeader($strColumnName);
    	        $strWidgetContent = "";
	            foreach($objDashboardmodel->getWidgetsForColumn($strColumnName, class_modul_system_aspect::getCurrentAspectId()) as $objOneSystemmodel) {
    	            $strWidgetContent .= $this->layoutAdminWidget($objOneSystemmodel);
    	        }

    	        $strColumnContent .= $strWidgetContent;
    	        $strColumnContent .= $this->objToolkit->getDashboardColumnFooter();
    	        $arrColumns[] = $strColumnContent;
	        }
	        $strReturn .= $this->objToolkit->getMainDashboard($arrColumns);

	    }
	    else
	        $strReturn = $this->getText("fehler_recht");

	    return $strReturn;
	}

	/**
	 * Creates the layout of a dashboard-entry. loads the widget to fetch the contents of the concrete widget.
	 *
	 * @param class_modul_dashboard_widget $objDashboardWidget
	 * @return string
	 */
	protected function layoutAdminWidget($objDashboardWidget) {
	    $strWidgetContent = "";
	    $objConcreteWidget = $objDashboardWidget->getWidgetmodelForCurrentEntry()->getConcreteAdminwidget();

        $strGeneratedContent = "<script type=\"text/javascript\">
                            KAJONA.admin.loader.loadAjaxBase(function() {
                                  KAJONA.admin.ajax.genericAjaxCall(\"dashboard\", \"getWidgetContent\", \"%%widget_id%%\", {
                                    success : function(o) {
                                        var intStart = o.responseText.indexOf(\"[CDATA[\")+7;
                                        document.getElementById(\"p_widget_%%widget_id%%\").innerHTML=o.responseText.substr(
                                          intStart, o.responseText.indexOf(\"]]\")-intStart
                                        );
                                        if(o.responseText.indexOf(\"[CDATA[\") < 0) {
                                            var intStart = o.responseText.indexOf(\"<error>\")+7;
                                            document.getElementById(\"p_widget_%%widget_id%%\").innerHTML=o.responseText.substr(
                                              intStart, o.responseText.indexOf(\"</error>\")-intStart
                                            );
                                        }
                                    },
                                    failure : function(o) {
                                        KAJONA.admin.statusDisplay.messageError(\"<b>Request failed!</b><br />\" + o.responseText);
                                    }
                                  })
                            });
                          </script>";

        $strWidgetId = $objConcreteWidget->getSystemid();
        $strWidgetName = $objConcreteWidget->getWidgetName();

        if($this->objRights->rightDelete($objDashboardWidget->getSystemid())) {
            $strWidgetContent .= $this->objToolkit->jsDialog(1);
        }

        $strWidgetContent .= $this->objToolkit->getDashboardWidgetEncloser(
                                $objDashboardWidget->getSystemid(), $this->objToolkit->getAdminwidget(
                                        $strWidgetId,
                                        $strWidgetName,
                                        $strGeneratedContent,
                                        ($this->objRights->rightEdit($objDashboardWidget->getSystemid()) ? getLinkAdmin("dashboard", "editWidget", "&systemid=".$objDashboardWidget->getSystemid(), "", $this->getText("editWidget"), "icon_pencil.gif") : ""),
                                        ($this->objRights->rightDelete($objDashboardWidget->getSystemid()) ?
                                        		$this->objToolkit->listDeleteButton($objDashboardWidget->getWidgetmodelForCurrentEntry()->getConcreteAdminwidget()->getWidgetName(), $this->getText("widgetDeleteQuestion"), getLinkAdminHref($this->arrModule["modul"], "deleteWidget", "&systemid=".$objDashboardWidget->getSystemid()))
                                                 : ""),
                                        $objDashboardWidget->getWidgetmodelForCurrentEntry()->getConcreteAdminwidget()->getLayoutSection()
                                )
                             );

        return $strWidgetContent;
	}

    /**
     * Creates a calendar-based view of the current month.
     * Single objects may register themself to be rendered within the calendar.
     * The calendar-view consists of a view single elements:
     * +---------------------------+
     * | control-elements (pager)  |
     * +---------------------------+
     * | wrapper                   |
     * +---------------------------+
     * | the column headers        |
     * +---------------------------+
     * | a row for each week (4x)  |
     * +---------------------------+
     * | wrapper                   |
     * +---------------------------+
     * | legend                    |
     * +---------------------------+
     *
     * @return string
     * @since 3.4
     */
    protected function actionCalendar() {
        $strReturn = "";

        $arrRelevantModules = array();

        //save dates to session
        if($this->getParam("month") != "")
            $this->objSession->setSession($this->strStartMonthKey, $this->getParam("month"));
        if($this->getParam("year") != "")
            $this->objSession->setSession($this->strStartYearKey, $this->getParam("year"));
        


        //fetch modules relevant for processing
        $arrModules = class_modul_system_module::getAllModules();
        foreach($arrModules as $objSingleModule) {
            if($objSingleModule->getStatus() == 1 && $objSingleModule->getAdminInstanceOfConcreteModule() instanceof interface_calendarsource_admin)
                $arrRelevantModules[] = $objSingleModule->getAdminInstanceOfConcreteModule();
        }
        

        //the header row
        $arrWeekdays = explode(",", $this->getText("calendar_weekday"));
        foreach($arrWeekdays as $intKey => $strValue)
            $arrWeekdays[$intKey] = trim(uniStrReplace("\"", "", $strValue));
        
        $strContent = $this->objToolkit->getCalendarHeaderRow($arrWeekdays);

        //render the single rows. calculate the first day of the row
        $objDate = new class_date();
        $objDate->setIntDay(1);

        if($this->objSession->getSession($this->strStartMonthKey) != "")
            $objDate->setIntMonth($this->objSession->getSession($this->strStartMonthKey));

        if($this->objSession->getSession($this->strStartYearKey) != "")
            $objDate->setIntYear($this->objSession->getSession($this->strStartYearKey));

        $intCurMonth = $objDate->getIntMonth();

        //pager-setup
        $objEndDate = clone $objDate;
        while($objEndDate->getIntMonth() == $intCurMonth)
            $objEndDate->setNextDay();
        $objEndDate->setPreviousDay();

        $strCenter = dateToString($objDate, false)." - ".  dateToString($objEndDate, false);

        $objEndDate->setNextDay();
        $objPrevDate = clone $objDate;
        $objPrevDate->setPreviousDay();

        $strPrev = getLinkAdmin($this->arrModule["modul"], "calendar", "&month=".$objPrevDate->getIntMonth()."&year=".$objPrevDate->getIntYear(), $this->getText("calendar_prev"));
        $strNext = getLinkAdmin($this->arrModule["modul"], "calendar", "&month=".$objEndDate->getIntMonth()."&year=".$objEndDate->getIntYear(), $this->getText("calendar_next"));

        //start by monday
        while($objDate->getIntDayOfWeek() != 1)
            $objDate->setPreviousDay();

        $strEntries = "";
        $intRowEntryCount = 0;
        $arrLegendEntries = array();
        while($objDate->getIntMonth() <= $intCurMonth || $intRowEntryCount % 7 != 0 ) {

            $intRowEntryCount++;

            $strDate = $objDate->getIntDay();

            $arrEvents = array();
            if($objDate->getIntMonth() == $intCurMonth) {
                //Query modules for dates
                $objStartDate = clone $objDate;
                $objStartDate->setIntHour(0);$objStartDate->setIntMin(0);$objStartDate->setIntSec(0);
                $objEndDate = clone $objDate;
                $objEndDate->setIntHour(23);$objEndDate->setIntMin(59);$objEndDate->setIntSec(59);
                foreach($arrRelevantModules as $objOneModule) {
                    $arrEvents = array_merge($objOneModule->getArrCalendarEntries($objStartDate, $objEndDate), $arrEvents);
                    $arrLegendEntries = array_merge($arrLegendEntries, $objOneModule->getArrLegendEntries());
                }
            }

            while(count($arrEvents) <= 3) {
                $objDummy = new class_calendarentry();
                $objDummy->setStrClass("spacer");
                $objDummy->setStrName("&nbsp;");
                $arrEvents[] = $objDummy;
            }

            $strEvents = "";
            foreach($arrEvents as $objOneEvent) {
                $strEvents .= $this->objToolkit->getCalendarEvent($objOneEvent->getStrName(), $objOneEvent->getStrClass());
            }

            $bitBlocked = false;
            if($objDate->getIntDayOfWeek() == 0 || $objDate->getIntDayOfWeek() == 6 )
                $bitBlocked = true;

            if($objDate->getIntMonth() != $intCurMonth)
                $strEntries .= $this->objToolkit->getCalendarEntry($strEvents, $strDate, "calendarEntryOutOfRange");
            else if($bitBlocked)
                $strEntries .= $this->objToolkit->getCalendarEntry($strEvents, $strDate, "calendarEntryBlocked");
            else
                $strEntries .= $this->objToolkit->getCalendarEntry($strEvents, $strDate);

            if($intRowEntryCount % 7 == 0) {
                $strContent .= $this->objToolkit->getCalendarRow($strEntries);
                $strEntries = "";
            }

            $objDate->setNextDay();
        }

        if($strEntries != "") {
            $strContent .= $this->objToolkit->getCalendarRow($strEntries);
        }

        $strReturn .= $this->objToolkit->getCalendarPager($strPrev, $strCenter, $strNext);
        $strReturn .= $this->objToolkit->getCalendarWrapper($strContent);
        $strReturn .= $this->objToolkit->getCalendarLegend($arrLegendEntries);


        return $strReturn;
    }

	/**
	 * Generates the forms to add a widget to the dashboard
	 *
	 * @return string, "" in case of success
	 */
	protected function actionAddWidgetToDashboard() {
	    $strReturn = "";
	    //check permissions
	    if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {

	        //step 1: select a widget, plz
	        if($this->getParam("step") == "") {
	            $objSystemWidget = new class_modul_system_adminwidget();
	            $arrWidgetsAvailable = $objSystemWidget->getListOfWidgetsAvailable();

	            $arrDD = array();
	            foreach ($arrWidgetsAvailable as $strOneWidget) {
	                $objWidget = new $strOneWidget();
	            	$arrDD[$strOneWidget] = $objWidget->getWidgetName();

	            }

	            $arrColumnsAvailable = array();
	            foreach ($this->arrColumnsOnDashboard as $strOneColumn)
	                $arrColumnsAvailable[$strOneColumn] = $this->getText($strOneColumn);


	            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("dashboard", "addWidgetToDashboard"));
	            $strReturn .= $this->objToolkit->formInputDropdown("widget", $arrDD, $this->getText("widget") );
	            $strReturn .= $this->objToolkit->formInputDropdown("column", $arrColumnsAvailable, $this->getText("column") );

	            $strReturn .= $this->objToolkit->formInputHidden("step", "2");
	            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("addWidgetNextStep"));
	            $strReturn .= $this->objToolkit->formClose();

	            $strReturn .= $this->objToolkit->setBrowserFocus("widget");
	        }
	        //step 2: loading the widget and allow it to show a view fields
	        else if($this->getParam("step") == "2") {
	            $strWidgetClass = $this->getParam("widget");
	            $objWidget = new $strWidgetClass();

	            //ask the widget to generate its form-parts and wrap our elements around
	            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("dashboard", "addWidgetToDashboard"));
	            $strReturn .= $objWidget->getEditForm();
	            $strReturn .= $this->objToolkit->formInputHidden("step", "3");
	            $strReturn .= $this->objToolkit->formInputHidden("widget", $strWidgetClass);
	            $strReturn .= $this->objToolkit->formInputHidden("column", $this->getParam("column"));
	            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("addWidgetSave"));
	            $strReturn .= $this->objToolkit->formClose();
	        }
	        //step 3: save all to the database
	        else if($this->getParam("step") == "3") {
	            //instantiate the concrete widget
	            $strWidgetClass = $this->getParam("widget");
	            $objWidget = new $strWidgetClass();

	            //let it process its fields
	            $objWidget->loadFieldsFromArray($this->getAllParams());

	            //instantiate a model-widget
	            $objSystemWidget = new class_modul_system_adminwidget();
	            $objSystemWidget->setStrClass($strWidgetClass);
	            $objSystemWidget->setStrContent($objWidget->getFieldsAsString());

	            //and save the widget itself
	            if($objSystemWidget->updateObjectToDb()) {
                    $strWidgetId = $objSystemWidget->getSystemid();
                    //and save the dashboard-entry
                    $objDashboard = new class_modul_dashboard_widget();
                    $objDashboard->setStrColumn($this->getParam("column"));
                    $objDashboard->setStrUser($this->objSession->getUserID());
                    $objDashboard->setStrWidgetId($strWidgetId);
                    $objDashboard->setStrAspect(class_modul_system_aspect::getCurrentAspectId());
                    if($objDashboard->updateObjectToDb($this->getModuleSystemid($this->arrModule["modul"])) ) {
                        $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
                    }
                    else
                        return $this->getText("errorSavingWidget");
                }
                else
                    return $this->getText("errorSavingWidget");
	        }

	    }
	    else
	        $strReturn = $this->getText("fehler_recht");

	    return $strReturn;
	}

	/**
	 * Deletes a widget from the dashboard
	 *
	 * @return string "" in case of success
	 */
	protected function actionDeleteWidget() {
	    $strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"]))) {
		    $objDashboardwidget = new class_modul_dashboard_widget($this->getSystemid());
		    if(!$objDashboardwidget->deleteObjectFromDb())
		        throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Creates the form to edit a widget (NOT the dashboard entry!)
	 *
	 * @return string "" in case of success
	 */
	protected function actionEditWidget() {
	    $strReturn = "";
		//Rights
		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {

			if($this->getParam("saveWidget") == "") {
			    $objDashboardwidget = new class_modul_dashboard_widget($this->getSystemid());
				$objWidget = $objDashboardwidget->getWidgetmodelForCurrentEntry()->getConcreteAdminwidget();

	            //ask the widget to generate its form-parts and wrap our elements around
	            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("dashboard", "editWidget"));
	            $strReturn .= $objWidget->getEditForm();
	            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
	            $strReturn .= $this->objToolkit->formInputHidden("saveWidget", "1");
	            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("addWidgetSave"));
	            $strReturn .= $this->objToolkit->formClose();
			}
			elseif($this->getParam("saveWidget") == "1") {
			    //the dashboard entry
			    $objDashboardwidget = new class_modul_dashboard_widget($this->getSystemid());
			    //widgets model
			    $objSystemWidget = $objDashboardwidget->getWidgetmodelForCurrentEntry();
                //the concrete widget
			    $objConcreteWidget = $objSystemWidget->getConcreteAdminwidget();
			    $objConcreteWidget->loadFieldsFromArray($this->getAllParams());

	            $objSystemWidget->setStrContent($objConcreteWidget->getFieldsAsString());
	            if(!$objSystemWidget->updateObjectToDb())
	                throw new class_exception("Error updating widget to db!", class_exception::$level_ERROR);

                $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
			}
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}
}


?>