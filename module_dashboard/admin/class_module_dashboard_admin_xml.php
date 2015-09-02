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

        //fetch modules relevant for processing
        $arrCategories = class_event_repository::getAllCategories();

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
                $objTargetDate = clone $objDate;

                foreach ($arrCategories as $arrCategory) {
                    foreach ($arrCategory as $strKey => $strValue) {
                        if ($this->objSession->getSession($strKey) != "disabled") {
                            $arrEvents = array_merge($arrEvents, class_event_repository::getEventsByCategoryAndDate($strKey, $objTargetDate));
                        }
                    }
                }
            }

            while(count($arrEvents) <= 3) {
                $objDummy = new class_event_entry();
                $objDummy->setStrCategory("spacer");
                $objDummy->setStrDisplayName("&nbsp;");
                $arrEvents[] = $objDummy;
            }

            $strEvents = "";
            /** @var class_event_entry $objOneEvent */
            foreach($arrEvents as $objOneEvent) {
                $strName = $objOneEvent->getStrDisplayName();
                if ($objOneEvent->getStrCategory() != "spacer") {
                    $strHref = $objOneEvent->getStrHref() != "" ? $objOneEvent->getStrHref() : "#";
                    $strIcon = class_link::getLinkAdminManual('href="' . $strHref . '"', "", "", $objOneEvent->getStrIcon());

                    $strEvents .= $this->objToolkit->getCalendarEvent($strIcon . " " . $strName, $objOneEvent->getStrSystemid(), "", "calendarEvent " . $objOneEvent->getStrCategory());
                } else {
                    $strEvents .= $this->objToolkit->getCalendarEvent($strName, $objOneEvent->getStrSystemid(), "", $objOneEvent->getStrCategory());
                }
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
            $arrTodos = class_todo_repository::getAllOpenTodos();
        } else {
            $arrTodos = class_todo_repository::getOpenTodos($strCategory);
        }

        if (empty($arrTodos)) {
            return $this->objToolkit->warningBox($this->getLang("todo_no_open_tasks"), "alert-info");
        }

        $arrHeaders = array(
            "0 \" style=\"width:20px\"" => "",
            "1" => "Objekt",
            "2 \" style=\"width:300px\"" => "Aktion",
            "3 \" style=\"width:160px\"" => "Fälligkeitsdatum",
            "4 \" style=\"width:20px\"" => "",
        );
        $arrValues = array();

        foreach ($arrTodos as $objTodo) {
            $strActions = "";
            $arrModule = $objTodo->getArrModuleNavi();
            if (!empty($arrModule) && is_array($arrModule)) {
                foreach ($arrModule as $strLink) {
                    $strActions.= $this->objToolkit->listButton($strLink);
                }
            }

            $strIcon = class_adminskin_helper::getAdminImage($objTodo->getStrIcon());
            $strCategory = class_todo_repository::getCategoryName($objTodo->getStrCategory());
            $strValidDate = $objTodo->getObjValidDate() !== null ? dateToString($objTodo->getObjValidDate(), false) : "-";

            $arrValues[] = array($strIcon, $objTodo->getStrDisplayName(), $strCategory, $strValidDate, $strActions);
        }

        return $this->objToolkit->dataTable($arrHeaders, $arrValues);
    }
}
