<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                            *
********************************************************************************************************/


/**
 * The dashboard admin class
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 *
 * @module dashboard
 * @moduleId _dashboard_module_id_
 */
class class_module_dashboard_admin extends class_admin_controller implements interface_admin {

    protected $arrColumnsOnDashboard = array("column1", "column2", "column3");

    private $strStartMonthKey = "DASHBOARD_CALENDAR_START_MONTH";
    private $strStartYearKey = "DASHBOARD_CALENDAR_START_YEAR";


    /**
     * @return array
     */
    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("modul_titel"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "calendar", "", $this->getLang("action_calendar"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "todo", "", $this->getLang("action_todo"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", class_link::getLinkAdmin($this->getArrModule("modul"), "addWidgetToDashboard", "", $this->getLang("action_add_widget_to_dashboard"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * @return array
     */
    protected function getArrOutputNaviEntries() {
        $arrReturn = parent::getArrOutputNaviEntries();
        if(isset($arrReturn[count($arrReturn)-2]))
            unset($arrReturn[count($arrReturn)-2]);
        return $arrReturn;
    }


    /**
     * Generates the dashboard itself.
     * Loads all widgets placed on the dashboard
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList() {
        $strReturn = "";
        //load the widgets for each column. currently supporting 3 columns on the dashboard.
        $objDashboardmodel = new class_module_dashboard_widget();
        $arrColumns = array();
        //build each row
        foreach($this->arrColumnsOnDashboard as $strColumnName) {
            $strColumnContent = $this->objToolkit->getDashboardColumnHeader($strColumnName);
            $strWidgetContent = "";
            foreach($objDashboardmodel->getWidgetsForColumn($strColumnName, class_module_system_aspect::getCurrentAspectId()) as $objOneSystemmodel) {
                $strWidgetContent .= $this->layoutAdminWidget($objOneSystemmodel);
            }

            $strColumnContent .= $strWidgetContent;
            $strColumnContent .= $this->objToolkit->getDashboardColumnFooter();
            $arrColumns[] = $strColumnContent;
        }
        $strReturn .= $this->objToolkit->getMainDashboard($arrColumns);

        return $strReturn;
    }

    /**
     * Creates the layout of a dashboard-entry. loads the widget to fetch the contents of the concrete widget.
     *
     * @param class_module_dashboard_widget $objDashboardWidget
     *
     * @return string
     */
    protected function layoutAdminWidget($objDashboardWidget) {
        $strWidgetContent = "";
        $objConcreteWidget = $objDashboardWidget->getConcreteAdminwidget();

        $strWidgetId = $objConcreteWidget->getSystemid();
        $strWidgetName = $objConcreteWidget->getWidgetName();
        $strWidgetNameAdditionalContent = $objConcreteWidget->getWidgetNameAdditionalContent();

        $strWidgetContent .= $this->objToolkit->getDashboardWidgetEncloser(
            $objDashboardWidget->getSystemid(),
            $this->objToolkit->getAdminwidget(
                $strWidgetId,
                $strWidgetName,
                $strWidgetNameAdditionalContent,
                ($objDashboardWidget->rightEdit() ? class_link::getLinkAdminDialog("dashboard", "editWidget", "&systemid=".$objDashboardWidget->getSystemid(), "", $this->getLang("editWidget"), "icon_edit", $objDashboardWidget->getConcreteAdminwidget()->getWidgetName()) : ""),
                ($objDashboardWidget->rightDelete() ? $this->objToolkit->listDeleteButton(
                    $objDashboardWidget->getConcreteAdminwidget()->getWidgetName(),
                    $this->getLang("widgetDeleteQuestion"),
                    "javascript:KAJONA.admin.dashboard.removeWidget(\'".$objDashboardWidget->getSystemid()."\');"
//                    getLinkAdminHref($this->getArrModule("modul"), "deleteWidget", "&systemid=".$objDashboardWidget->getSystemid())
                )  : ""),
                $objDashboardWidget->getConcreteAdminwidget()->getLayoutSection()
            )
        );

        return $strWidgetContent;
    }

    /**
     * Creates a calendar-based view of the current month.
     * Single objects may register themselves to be rendered within the calendar.
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
     * The calendar internally is loaded via ajax since fetching all events
     * may take some time.
     *
     * @return string
     * @since 3.4
     * @autoTestable
     * @permissions view
     */
    protected function actionCalendar() {
        $strReturn = "";

        //save dates to session
        if($this->getParam("month") != "")
            $this->objSession->setSession($this->strStartMonthKey, $this->getParam("month"));
        if($this->getParam("year") != "")
            $this->objSession->setSession($this->strStartYearKey, $this->getParam("year"));

        $strContainerId = generateSystemid();

        $strContent = "<script type=\"text/javascript\">";
        $strContent .= <<<JS
            $(document).ready(function() {
                  KAJONA.admin.ajax.genericAjaxCall("dashboard", "renderCalendar", "{$strContainerId}", function(data, status, jqXHR) {
                    if(status == 'success') {
                        var intStart = data.indexOf("[CDATA[")+7;
                        $("#{$strContainerId}").html(data.substr(
                          intStart, data.indexOf("]]")-intStart
                        ));
                        if(data.indexOf("[CDATA[") < 0) {
                            var intStart = data.indexOf("<error>")+7;
                            $("#{$strContainerId}").html(o.responseText.substr(
                              intStart, data.indexOf("</error>")-intStart
                            ));
                        }
                        KAJONA.util.evalScript(data);
                        KAJONA.admin.tooltip.initTooltip();
                    }
                    else {
                        KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
                    }
                  })
            });
JS;
        $strContent .= "</script>";

        //fetch modules relevant for processing
        $arrLegendEntries = array();
        $arrFilterEntries = array();

        $arrCategories = class_event_repository::getAllCategories();
        foreach ($arrCategories as $arrCategory) {
            foreach ($arrCategory as $strKey => $strCategory) {
                $arrLegendEntries[$strCategory] = $strKey . " calendarEvent";
                $arrFilterEntries[$strKey] = $strCategory;
            }
        }

        if($this->getParam("doCalendarFilter") != "") {
            //update filter-criteria
            foreach(array_keys($arrFilterEntries) as $strOneId) {
                if($this->getParam($strOneId) != "")
                    $this->objSession->sessionUnset($strOneId);
                else
                    $this->objSession->setSession($strOneId, "disabled");
            }
        }

        //render the single rows. calculate the first day of the row
        $objDate = new class_date();
        $objDate->setIntDay(1);

        if($this->objSession->getSession($this->strStartMonthKey) != "")
            $objDate->setIntMonth($this->objSession->getSession($this->strStartMonthKey));

        if($this->objSession->getSession($this->strStartYearKey) != "")
            $objDate->setIntYear($this->objSession->getSession($this->strStartYearKey));


        //pager-setup
        $objEndDate = clone $objDate;
        $objEndDate->setNextMonth();
        $objEndDate->setPreviousDay();

        $strCenter = dateToString($objDate, false)." - ".  dateToString($objEndDate, false);

        $objEndDate->setNextDay();
        $objPrevDate = clone $objDate;
        $objPrevDate->setPreviousDay();

        $strPrev = class_link::getLinkAdmin($this->getArrModule("modul"), "calendar", "&month=".$objPrevDate->getIntMonth()."&year=".$objPrevDate->getIntYear(), $this->getLang("calendar_prev"));
        $strNext = class_link::getLinkAdmin($this->getArrModule("modul"), "calendar", "&month=".$objEndDate->getIntMonth()."&year=".$objEndDate->getIntYear(), $this->getLang("calendar_next"));

        $strReturn .= $this->objToolkit->getCalendarPager($strPrev, $strCenter, $strNext);
        $strReturn .= $strContent;
        $strReturn .= $this->objToolkit->getCalendarContainer($strContainerId);
        //$strReturn .= $this->objToolkit->getCalendarLegend($arrLegendEntries);
        $strReturn .= $this->objToolkit->getCalendarFilter($arrFilterEntries);

        return $strReturn;
    }

    /**
     * @permissions view
     */
    protected function actionTodo() {
        $arrCategories = class_todo_repository::getAllCategories();
        $arrContent = array();

        if (empty($arrCategories)) {
            return $this->objToolkit->warningBox($this->getLang("todo_no_open_tasks"), "alert-info");
        }

        // overall tab
        $arrContent[$this->getLang("todo_overall_tasks")] = class_link::getLinkAdminXml("dashboard", "todoCategory");

        // each category
        foreach ($arrCategories as $strProviderName => $arrTaskCategories) {
            foreach ($arrTaskCategories as $strKey => $strCategoryName) {
                $arrContent[$strCategoryName] = class_link::getLinkAdminXml("dashboard", "todoCategory", "&category=" . $strKey);
            }
        }

        return $this->objToolkit->getTabbedContent($arrContent);
    }

    /**
     * Generates the forms to add a widget to the dashboard
     *
     * @return string, "" in case of success
     * @autoTestable
     * @permissions edit
     */
    protected function actionAddWidgetToDashboard() {
        $strReturn = "";
        //step 1: select a widget, plz
        if($this->getParam("step") == "") {
            $arrWidgetsAvailable = class_module_dashboard_widget::getListOfWidgetsAvailable();

            $arrDD = array();
            foreach ($arrWidgetsAvailable as $strOneWidget) {
                /** @var $objWidget interface_adminwidget|class_adminwidget */
                $objWidget = new $strOneWidget();
                $arrDD[$strOneWidget] = $objWidget->getWidgetName();

            }

            $arrColumnsAvailable = array();
            foreach ($this->arrColumnsOnDashboard as $strOneColumn)
                $arrColumnsAvailable[$strOneColumn] = $this->getLang($strOneColumn);


            $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref("dashboard", "addWidgetToDashboard"));
            $strReturn .= $this->objToolkit->formInputDropdown("widget", $arrDD, $this->getLang("widget"));
            $strReturn .= $this->objToolkit->formInputDropdown("column", $arrColumnsAvailable, $this->getLang("column"));
            $strReturn .= $this->objToolkit->formInputHidden("step", "2");
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("addWidgetNextStep"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("widget");
        }
        //step 2: loading the widget and allow it to show a view fields
        else if($this->getParam("step") == "2") {
            $strWidgetClass = $this->getParam("widget");
            /** @var class_adminwidget|interface_adminwidget $objWidget */
            $objWidget = new $strWidgetClass();

            if($objWidget->getEditForm() == "") {
                $this->adminReload(class_link::getLinkAdminHref("dashboard", "addWidgetToDashboard", "&step=3&widget=".$strWidgetClass."&column=".$this->getParam("column")));
            }
            else {
                //ask the widget to generate its form-parts and wrap our elements around
                $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref("dashboard", "addWidgetToDashboard"));
                $strReturn .= $objWidget->getEditForm();
                $strReturn .= $this->objToolkit->formInputHidden("step", "3");
                $strReturn .= $this->objToolkit->formInputHidden("widget", $strWidgetClass);
                $strReturn .= $this->objToolkit->formInputHidden("column", $this->getParam("column"));
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                $strReturn .= $this->objToolkit->formClose();
            }
        }
        //step 3: save all to the database
        else if($this->getParam("step") == "3") {
            //instantiate the concrete widget
            $strWidgetClass = $this->getParam("widget");
            /** @var class_adminwidget|interface_adminwidget $objWidget */
            $objWidget = new $strWidgetClass();

            //let it process its fields
            $objWidget->loadFieldsFromArray($this->getAllParams());

            //and save the dashboard-entry
            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->setStrClass($strWidgetClass);
            $objDashboard->setStrContent($objWidget->getFieldsAsString());
            $objDashboard->setStrColumn($this->getParam("column"));
            $objDashboard->setStrUser($this->objSession->getUserID());
            $objDashboard->setStrAspect(class_module_system_aspect::getCurrentAspectId());
            if($objDashboard->updateObjectToDb(class_module_dashboard_widget::getWidgetsRootNodeForUser($this->objSession->getUserID(), class_module_system_aspect::getCurrentAspectId())))
                $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul")));
            else
                return $this->getLang("errorSavingWidget");
        }


        return $strReturn;
    }

    /**
     * Deletes a widget from the dashboard
     *
     * @throws class_exception
     * @return string "" in case of success
     * @permissions delete
     */
    protected function actionDeleteWidget() {
        $strReturn = "";
        $objDashboardwidget = new class_module_dashboard_widget($this->getSystemid());
        if(!$objDashboardwidget->deleteObject())
            throw new class_exception("Error deleting widget", class_exception::$level_ERROR);

        $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul")));

        return $strReturn;
    }

    /**
     * Creates the form to edit a widget (NOT the dashboard entry!)
     *
     * @throws class_exception
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionEditWidget() {
        $strReturn = "";
        $this->setArrModuleEntry("template", "/folderview.tpl");
        if($this->getParam("saveWidget") == "") {
            $objDashboardwidget = new class_module_dashboard_widget($this->getSystemid());
            $objWidget = $objDashboardwidget->getConcreteAdminwidget();

            //ask the widget to generate its form-parts and wrap our elements around
            $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref("dashboard", "editWidget"));
            $strReturn .= $objWidget->getEditForm();
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputHidden("saveWidget", "1");
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
            $strReturn .= $this->objToolkit->formClose();
        }
        elseif($this->getParam("saveWidget") == "1") {
            //the dashboard entry
            $objDashboardwidget = new class_module_dashboard_widget($this->getSystemid());
            //the concrete widget
            $objConcreteWidget = $objDashboardwidget->getConcreteAdminwidget();
            $objConcreteWidget->loadFieldsFromArray($this->getAllParams());

            $objDashboardwidget->setStrContent($objConcreteWidget->getFieldsAsString());
            if(!$objDashboardwidget->updateObjectToDb())
                throw new class_exception("Error updating widget to db!", class_exception::$level_ERROR);

            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "", "&peClose=1&blockAction=1"));
        }

        return $strReturn;
    }

}


