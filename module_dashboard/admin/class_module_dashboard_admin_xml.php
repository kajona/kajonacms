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
    protected function actionGetCalendarEvents() {

        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);

        $arrEvents = array();
        $arrCategories = class_event_repository::getAllCategories();
        $objStartDate = new \Kajona\System\System\Date(strtotime($this->getParam("start")));
        $objEndDate = new \Kajona\System\System\Date(strtotime($this->getParam("end")));

        foreach ($arrCategories as $arrCategory) {
            foreach ($arrCategory as $strKey => $strValue) {
                if ($this->objSession->getSession($strKey) != "disabled") {
                    $arrEvents = array_merge($arrEvents, class_event_repository::getEventsByCategoryAndDate($strKey, $objStartDate, $objEndDate));
                }
            }
        }

        $arrData = array();
        foreach ($arrEvents as $objEvent) {
            /** @var class_event_entry $objEvent */
            $strIcon = class_adminskin_helper::getAdminImage($objEvent->getStrIcon());
            $arrRow = array(
                "title" => strip_tags($objEvent->getStrDisplayName()),
                "tooltip" => $objEvent->getStrDisplayName(),
                "icon" => $strIcon,
                "allDay" => true,
                "url" => $objEvent->getStrHref(),
                "className" => array($objEvent->getStrCategory(), "calendar-event"),
            );

            if ($objEvent->getObjStartDate() instanceof \Kajona\System\System\Date && $objEvent->getObjEndDate() instanceof \Kajona\System\System\Date) {
                $arrRow["start"] = date("Y-m-d", $objEvent->getObjStartDate()->getTimeInOldStyle());
                $arrRow["end"] = date("Y-m-d", $objEvent->getObjEndDate()->getTimeInOldStyle());
            } elseif ($objEvent->getObjValidDate() instanceof \Kajona\System\System\Date) {
                $arrRow["start"] = date("Y-m-d", $objEvent->getObjValidDate()->getTimeInOldStyle());
            } else {
                continue;
            }

            array_push($arrData, $arrRow);
        }

        return json_encode($arrData);
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
            $arrTodos = class_todo_repository::getOpenTodos($strCategory, false);
        }

        if (empty($arrTodos)) {
            return $this->objToolkit->warningBox($this->getLang("todo_no_open_tasks"), "alert-info");
        }

        $strSearch = $this->getParam("search");
        $strDate = $this->getParam("date");

        $arrHeaders = array(
            "0 \" style=\"width:20px\"" => "",
            "1" => $this->getLang("todo_task_col_object"),
            "2 \" style=\"width:300px\"" => $this->getLang("todo_task_col_category"),
            "3 \" style=\"width:160px\"" => $this->getLang("todo_task_col_date"),
            "4 \" style=\"width:80px\"" => "",
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

            $bitSearchMatch = empty($strSearch) || stripos($objTodo->getStrDisplayName(), $strSearch) !== false;
            $bitDateMatch = empty($strDate) || $strValidDate == $strDate;

            if ($bitSearchMatch && $bitDateMatch) {
                $arrValues[] = array(
                    $strIcon,
                    $objTodo->getStrDisplayName(),
                    $strCategory,
                    $strValidDate,
                    "4\" style=\"text-align:right" => $strActions
                );
            }
        }

        return $this->objToolkit->dataTable($arrHeaders, $arrValues);
    }
}
