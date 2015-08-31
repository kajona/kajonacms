<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/


/**
 * admin-class of the dashboard-module
 * Serves xml-requests, mostly general requests e.g. changing a widgets position
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 *
 * @module dashboard
 * @moduleId _dashboard_module_id_
 *
 */
class class_module_dashboard_admin_xml extends class_admin_controller implements interface_xml_admin {

    private $strStartMonthKey = "DASHBOARD_CALENDAR_START_MONTH";
    private $strStartYearKey = "DASHBOARD_CALENDAR_START_YEAR";

    /**
     * Removes a single widget
     *
     * @permissions delete
     * @return string
     */
    protected function actionDeleteWidget() {
        $objWidget = new class_module_dashboard_widget($this->getSystemid());
        $strName = $objWidget->getStrDisplayName();
        $objWidget->deleteObject();
        return "<message>".$this->getLang("deleteWidgetSuccess", array($strName))."</message>";
    }


    /**
     * saves the new position of a widget on the dashboard.
     * updates the sorting AND the assigned column
     *
     * @return string
     * @permissions edit
     */
    protected function actionSetDashboardPosition() {
        $strReturn = "";

        $objWidget = new class_module_dashboard_widget($this->getSystemid());
        $intNewPos = $this->getParam("listPos");
        $objWidget->setStrColumn($this->getParam("listId"));
        $objWidget->updateObjectToDb();
        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objWidget = new class_module_dashboard_widget($this->getSystemid());
        if($intNewPos != "")
            $objWidget->setAbsolutePosition($intNewPos);


        $strReturn .= "<message>".$objWidget->getStrDisplayName()." - ".$this->getLang("setDashboardPosition")."</message>";

        return $strReturn;
    }

    /**
     * Renderes the content of a single widget.
     *
     * @return string
     * @permissions view
     */
    protected function actionGetWidgetContent() {

        //load the aspect and close the session afterwards
        class_module_system_aspect::getCurrentAspect();

        $objWidgetModel = new class_module_dashboard_widget($this->getSystemid());
        if($objWidgetModel->rightView()) {
            $objConcreteWidget = $objWidgetModel->getConcreteAdminwidget();

            if(!$objConcreteWidget->getBitBlockSessionClose())
                class_carrier::getInstance()->getObjSession()->sessionClose();

            //disable the internal changelog
            class_module_system_changelog::$bitChangelogEnabled = false;

            class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);
            $strReturn = json_encode($objConcreteWidget->generateWidgetOutput());

        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn = "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * @return string
     * @permissions view
     */
    protected function actionRenderCalendar() {
        $strReturn = "";
        $strContent = "";

        $arrJsHighlights = array();

        $strReturn .= "<content><![CDATA[";

        /** @var interface_calendarsource_admin[] $arrRelevantModules  */
        $arrRelevantModules = array();

        //fetch modules relevant for processing
        $arrModules = class_module_system_module::getAllModules();
        foreach($arrModules as $objSingleModule) {
            if($objSingleModule->getIntRecordStatus() == 1 && $objSingleModule->getAdminInstanceOfConcreteModule() instanceof interface_calendarsource_admin)
                $arrRelevantModules[] = $objSingleModule->getAdminInstanceOfConcreteModule();
        }

        //the header row
        $arrWeekdays = explode(",", $this->getLang("calendar_weekday"));
        foreach($arrWeekdays as $intKey => $strValue)
            $arrWeekdays[$intKey] = trim(uniStrReplace("\"", "", $strValue));

        $strContent .= $this->objToolkit->getCalendarHeaderRow($arrWeekdays);

        //render the single rows. calculate the first day of the row
        $objDate = new class_date();
        $objDate->setIntDay(1);

        //set to interval stored in session
        if($this->objSession->getSession($this->strStartMonthKey) != "")
            $objDate->setIntMonth($this->objSession->getSession($this->strStartMonthKey));

        if($this->objSession->getSession($this->strStartYearKey) != "")
            $objDate->setIntYear($this->objSession->getSession($this->strStartYearKey));

        $intCurMonth = $objDate->getIntMonth();
        $intCurYear = $objDate->getIntYear();
        $objToday = new class_date();

        //start by monday
        while($objDate->getIntDayOfWeek() != 1)
            $objDate->setPreviousDay();

        $strEntries = "";
        $intRowEntryCount = 0;
        while(
            ($objDate->getIntMonth() <= $intCurMonth && $objDate->getIntYear() <= $intCurYear) ||
            ($objDate->getIntMonth() == 12 && $objDate->getIntYear() < $intCurYear) ||
            $intRowEntryCount % 7 != 0
        ) {
            $intRowEntryCount++;

            $strDate = $objDate->getIntDay();

            $arrEvents = array();
            if($objDate->getIntMonth() == $intCurMonth) {
                //Query modules for dates
                $objStartDate = clone $objDate;
                $objStartDate->setIntHour(0)->setIntMin(0)->setIntSec(0);
                $objEndDate = clone $objDate;
                $objEndDate->setIntHour(23)->setIntMin(59)->setIntSec(59);
                foreach($arrRelevantModules as $objOneModule) {
                    $arrEvents = array_merge($objOneModule->getArrCalendarEntries($objStartDate, $objEndDate), $arrEvents);
                }
            }

            while(count($arrEvents) <= 3) {
                $objDummy = new class_calendarentry();
                $objDummy->setStrClass("spacer");
                $objDummy->setStrName("&nbsp;");
                $arrEvents[] = $objDummy;
            }

            $strEvents = "";
            /** @var class_calendarentry $objOneEvent */
            foreach($arrEvents as $objOneEvent) {

                $strName = $objOneEvent->getStrName();
                $strSecondLine = $objOneEvent->getStrSecondLine();

                if($strSecondLine != "")
                    $strSecondLine = "<br />".$strSecondLine;

                //register mouse-over highlight relations
                if($objOneEvent->getStrHighlightId() != "" && $objOneEvent->getStrSystemid() != "") {
                    if(!isset($arrJsHighlights[$objOneEvent->getStrHighlightId()]))
                        $arrJsHighlights[$objOneEvent->getStrHighlightId()] = array();

                    $arrJsHighlights[$objOneEvent->getStrHighlightId()][] = $objOneEvent->getStrSystemid();
                }

                $strEvents .= $this->objToolkit->getCalendarEvent($strName.$strSecondLine, $objOneEvent->getStrSystemid(), $objOneEvent->getStrHighlightId(), $objOneEvent->getStrClass());
            }

            $bitBlocked = false;
            if($objDate->getIntDayOfWeek() == 0 || $objDate->getIntDayOfWeek() == 6)
                $bitBlocked = true;

            $strToday = "";
            if($objToday->getIntYear() == $objDate->getIntYear() && $objToday->getIntMonth() == $objDate->getIntMonth() && $objToday->getIntDay() == $objDate->getIntDay())
                $strToday = " calendarDateToday";


            if($objDate->getIntMonth() != $intCurMonth)
                $strEntries .= $this->objToolkit->getCalendarEntry($strEvents, $strDate, "calendarEntryOutOfRange".$strToday);
            else if($bitBlocked)
                $strEntries .= $this->objToolkit->getCalendarEntry($strEvents, $strDate, "calendarEntryBlocked".$strToday);
            else
                $strEntries .= $this->objToolkit->getCalendarEntry($strEvents, $strDate, "calendarEntry".$strToday);

            if($intRowEntryCount % 7 == 0) {
                $strContent .= $this->objToolkit->getCalendarRow($strEntries);
                $strEntries = "";
            }

            $objDate->setNextDay();
        }

        if($strEntries != "") {
            $strContent .= $this->objToolkit->getCalendarRow($strEntries);
        }

        $strReturn .= $this->objToolkit->getCalendarWrapper($strContent);

        //build js-arrays
        $strJs = "<script type=\"text/javascript\">";
        foreach($arrJsHighlights as $strCommonId => $arrEntries) {
            $strJs .= " var kj_cal_".$strCommonId." = new Array();";
            foreach($arrEntries as $strOneIdentifier) {
                $strJs .= "kj_cal_".$strCommonId.".push('".$strOneIdentifier."');";
            }
        }
        $strJs .= "</script>";

        $strReturn .= $strJs;

        $strReturn .= "]]></content>";
        return $strReturn;
    }

    /**
     * @return string
     * @permissions view
     */
    protected function actionTodoCategory() {
        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_HTML);

        $strCategory = $this->getParam("category");
        if (empty($strCategory)) {
            $arrTodos = class_todo_entry::getAllOpenTodos();
        } else {
            $arrTodos = class_todo_entry::getOpenTodos($strCategory);
        }

        if (empty($arrTodos)) {
            return $this->objToolkit->warningBox($this->getLang("todo_no_open_tasks"), "alert-info");
        }

        $strReturn = "";
        $strReturn .= $this->objToolkit->listHeader();
        $intI = 0;
        foreach ($arrTodos as $objTodo) {
            $strActions = "";
            $arrModule = $objTodo->getArrModuleNavi();
            if (!empty($arrModule) && is_array($arrModule)) {
                foreach ($arrModule as $strLink) {
                    $strActions.= $this->objToolkit->listButton($strLink);
                }
            }
            $strReturn .= $this->objToolkit->simpleAdminList($objTodo, $strActions, $intI++);
        }
        $strReturn .= $this->objToolkit->listFooter();

        return $strReturn;
    }
}
