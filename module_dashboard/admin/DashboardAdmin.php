<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                            *
********************************************************************************************************/

namespace Kajona\Dashboard\Admin;

use Kajona\Dashboard\Admin\Widgets\Adminwidget;
use Kajona\Dashboard\Admin\Widgets\AdminwidgetInterface;
use Kajona\Dashboard\System\DashboardWidget;
use Kajona\Dashboard\System\EventEntry;
use Kajona\Dashboard\System\EventRepository;
use Kajona\Dashboard\System\TodoJstreeNodeLoader;
use Kajona\Dashboard\System\TodoRepository;
use Kajona\System\Admin\AdminController;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\Admin\Formentries\FormentryText;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Date;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\Link;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Session;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\SystemJSTreeBuilder;
use Kajona\System\System\SystemJSTreeConfig;

/**
 * The dashboard admin class
 *
 * @package module_dashboard
 * @author sidler@mulchprod.de
 *
 * @module dashboard
 * @moduleId _dashboard_module_id_
 */
class DashboardAdmin extends AdminController implements AdminInterface
{

    protected $arrColumnsOnDashboard = array("column1", "column2", "column3");

    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("modul_titel"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "calendar", "", $this->getLang("action_calendar"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "todo", "", $this->getLang("action_todo"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", Link::getLinkAdmin($this->getArrModule("modul"), "addWidgetToDashboard", "", $this->getLang("action_add_widget_to_dashboard"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * @return array
     */
    protected function getArrOutputNaviEntries()
    {
        $arrReturn = parent::getArrOutputNaviEntries();
        if (isset($arrReturn[count($arrReturn) - 2])) {
            unset($arrReturn[count($arrReturn) - 2]);
        }
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
    protected function actionList()
    {
        $strReturn = "";
        //load the widgets for each column. currently supporting 3 columns on the dashboard.
        $objDashboardmodel = new DashboardWidget();
        $arrColumns = array();
        //build each row
        foreach ($this->arrColumnsOnDashboard as $strColumnName) {
            $strColumnContent = $this->objToolkit->getDashboardColumnHeader($strColumnName);
            $strWidgetContent = "";
            foreach ($objDashboardmodel->getWidgetsForColumn($strColumnName, SystemAspect::getCurrentAspectId()) as $objOneSystemmodel) {
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
     * @param DashboardWidget $objDashboardWidget
     *
     * @return string
     */
    protected function layoutAdminWidget($objDashboardWidget)
    {
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
                ($objDashboardWidget->rightEdit() ? Link::getLinkAdminDialog("dashboard", "editWidget", "&systemid=".$objDashboardWidget->getSystemid(), "", $this->getLang("editWidget"), "icon_edit", $objDashboardWidget->getConcreteAdminwidget()->getWidgetName()) : ""),
                ($objDashboardWidget->rightDelete() ? $this->objToolkit->listDeleteButton(
                    $objDashboardWidget->getConcreteAdminwidget()->getWidgetName(),
                    $this->getLang("widgetDeleteQuestion"),
                    "javascript:require(\'dashboard\').removeWidget(\'".$objDashboardWidget->getSystemid()."\');"
                ) : ""),
                $objDashboardWidget->getConcreteAdminwidget()->getLayoutSection()
            )
        );

        return $strWidgetContent;
    }

    /**
     * Create a calendar based on the jquery fullcalendar. Loads all events from the XML action actionGetCalendarEvents
     *
     * @return string
     * @since 3.4
     * @autoTestable
     * @permissions view
     */
    protected function actionCalendar()
    {
        $strReturn = "";

        $strContainerId = "calendar-".generateSystemid();
        $strEventCallback = Link::getLinkAdminXml("dashboard", "getCalendarEvents");
        $strLang = Session::getInstance()->getAdminLanguage();

        $strReturn .= "<div id='".$strContainerId."' class='calendar'></div>";
        $strReturn .= "<script type=\"text/javascript\">";
        $strReturn .= <<<JS
        require(["jquery", "moment", "fullcalendar", "dashboard", "tooltip", "workingIndicator", "loader", "fullcalendar_lang_{$strLang}"], function($, moment, fullcalendar, dashboard, tooltip, workingIndicator, loader){
            loader.loadFile(['/core/module_dashboard/scripts/fullcalendar/fullcalendar.min.css']);
            var loadCalendar = function(){
                $('#{$strContainerId}').fullCalendar({
                    header: {
                        left: 'prev,next',
                        center: 'title',
                        right: ''
                    },
                    editable: false,
                    theme: false,
                    lang: '{$strLang}',
                    eventLimit: true,
                    events: '{$strEventCallback}',
                    eventRender: function(event, el){
                        tooltip.addTooltip(el, event.tooltip);
                        if (event.icon) {
                            el.find("span.fc-title").prepend(event.icon);
                        }
                    },
                    loading: function(isLoading){
                        if (isLoading) {
                            workingIndicator.start();
                        } else {
                            workingIndicator.stop();
                        }
                    }
                });
                $('.fc-button-group').removeClass().addClass('btn-group');
                $('.fc-button').removeClass().addClass('btn btn-default');
            };

            loadCalendar();
        });
JS;
        $strReturn .= "</script>";

        return $strReturn;
    }

    /**
     * @permissions view
     */
    protected function actionTodo()
    {

        $objConfig = new SystemJSTreeConfig();
        $objConfig->setBitDndEnabled(false);
        $objConfig->setStrNodeEndpoint(Link::getLinkAdminXml("dashboard", "treeEndpoint"));
        $objConfig->setArrNodesToExpand(array(""));

        $strCategory = $this->getParam("listfilter_category");

        $strContent = $this->getListTodoFilter();
        $strContent .= "<div id='todo-table'></div>";
        $strContent .= "<script type=\"text/javascript\">";
        $strContent .= <<<JS
            require(["dashboard"], function(dashboard){
                dashboard.todo.loadCategory('{$strCategory}', '');
            });
JS;

        $strContent .= "</script>";

        return $this->objToolkit->getTreeview($objConfig, $strContent);
    }


    protected function getListTodoFilter()
    {
        // create the form
        $objFormgenerator = new AdminFormgenerator("listfilter", null);
        $objFormgenerator->setStrOnSubmit("require('dashboard').todo.formSearch();return false");

        $objFormgenerator->addField(new FormentryText("listfilter", "search"))
            ->setStrLabel($this->getLang("filter_search"));

        //render filter
        $strReturn = $objFormgenerator->renderForm(Link::getLinkAdminHref("dashboard", "todo"), AdminFormgenerator::BIT_BUTTON_SUBMIT);

        return $strReturn;
    }


    /**
     * Generates the forms to add a widget to the dashboard
     *
     * @return string, "" in case of success
     * @autoTestable
     * @permissions edit
     */
    protected function actionAddWidgetToDashboard()
    {
        $strReturn = "";
        //step 1: select a widget, plz
        if ($this->getParam("step") == "") {
            $arrWidgetsAvailable = DashboardWidget::getListOfWidgetsAvailable();

            $arrDD = array();
            foreach ($arrWidgetsAvailable as $strOneWidget) {
                /** @var $objWidget AdminwidgetInterface|Adminwidget */
                $objWidget = new $strOneWidget();
                $arrDD[$strOneWidget] = $objWidget->getWidgetName();

            }

            $arrColumnsAvailable = array();
            foreach ($this->arrColumnsOnDashboard as $strOneColumn) {
                $arrColumnsAvailable[$strOneColumn] = $this->getLang($strOneColumn);
            }


            $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref("dashboard", "addWidgetToDashboard"));
            $strReturn .= $this->objToolkit->formInputDropdown("widget", $arrDD, $this->getLang("widget"));
            $strReturn .= $this->objToolkit->formInputDropdown("column", $arrColumnsAvailable, $this->getLang("column"));
            $strReturn .= $this->objToolkit->formInputHidden("step", "2");
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("addWidgetNextStep"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("widget");
        } //step 2: loading the widget and allow it to show a view fields
        elseif ($this->getParam("step") == "2") {
            $strWidgetClass = $this->getParam("widget");
            /** @var Adminwidget|AdminwidgetInterface $objWidget */
            $objWidget = new $strWidgetClass();

            if ($objWidget->getEditForm() == "") {
                $this->adminReload(Link::getLinkAdminHref("dashboard", "addWidgetToDashboard", "&step=3&widget=".$strWidgetClass."&column=".$this->getParam("column")));
            } else {
                //ask the widget to generate its form-parts and wrap our elements around
                $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref("dashboard", "addWidgetToDashboard"));
                $strReturn .= $objWidget->getEditForm();
                $strReturn .= $this->objToolkit->formInputHidden("step", "3");
                $strReturn .= $this->objToolkit->formInputHidden("widget", $strWidgetClass);
                $strReturn .= $this->objToolkit->formInputHidden("column", $this->getParam("column"));
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                $strReturn .= $this->objToolkit->formClose();
            }
        } //step 3: save all to the database
        elseif ($this->getParam("step") == "3") {
            //instantiate the concrete widget
            $strWidgetClass = $this->getParam("widget");
            /** @var Adminwidget|AdminwidgetInterface $objWidget */
            $objWidget = new $strWidgetClass();

            //let it process its fields
            $objWidget->loadFieldsFromArray($this->getAllParams());

            //and save the dashboard-entry
            $objDashboard = new DashboardWidget();
            $objDashboard->setStrClass($strWidgetClass);
            $objDashboard->setStrContent($objWidget->getFieldsAsString());
            $objDashboard->setStrColumn($this->getParam("column"));
            $objDashboard->setStrUser($this->objSession->getUserID());
            $objDashboard->setStrAspect(SystemAspect::getCurrentAspectId());
            if ($objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($this->objSession->getUserID(), SystemAspect::getCurrentAspectId()))) {
                $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul")));
            } else {
                return $this->getLang("errorSavingWidget");
            }
        }


        return $strReturn;
    }

    /**
     * Creates the form to edit a widget (NOT the dashboard entry!)
     *
     * @throws Exception
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionEditWidget()
    {
        $strReturn = "";
        $this->setArrModuleEntry("template", "/folderview.tpl");
        if ($this->getParam("saveWidget") == "") {
            $objDashboardwidget = new DashboardWidget($this->getSystemid());
            $objWidget = $objDashboardwidget->getConcreteAdminwidget();

            //ask the widget to generate its form-parts and wrap our elements around
            $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref("dashboard", "editWidget"));
            $strReturn .= $objWidget->getEditForm();
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
            $strReturn .= $this->objToolkit->formInputHidden("saveWidget", "1");
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
            $strReturn .= $this->objToolkit->formClose();
        } elseif ($this->getParam("saveWidget") == "1") {
            //the dashboard entry
            $objDashboardwidget = new DashboardWidget($this->getSystemid());
            //the concrete widget
            $objConcreteWidget = $objDashboardwidget->getConcreteAdminwidget();
            $objConcreteWidget->loadFieldsFromArray($this->getAllParams());

            $objDashboardwidget->setStrContent($objConcreteWidget->getFieldsAsString());
            if (!$objDashboardwidget->updateObjectToDb()) {
                throw new Exception("Error updating widget to db!", Exception::$level_ERROR);
            }

            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "", "&peClose=1&blockAction=1"));
        }

        return $strReturn;
    }

    /**
     * Removes a single widget, called by the xml-handler
     *
     * @permissions delete
     * @return string
     */
    protected function actionDeleteWidget()
    {
        $objWidget = new DashboardWidget($this->getSystemid());
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
    protected function actionSetDashboardPosition()
    {
        $strReturn = "";

        $objWidget = new DashboardWidget($this->getSystemid());
        $intNewPos = $this->getParam("listPos");
        $objWidget->setStrColumn($this->getParam("listId"));
        $objWidget->updateObjectToDb();
        Carrier::getInstance()->getObjDB()->flushQueryCache();

        $objWidget = new DashboardWidget($this->getSystemid());
        if ($intNewPos != "") {
            $objWidget->setAbsolutePosition($intNewPos);
        }


        $strReturn .= "<message>".$objWidget->getStrDisplayName()." - ".$this->getLang("setDashboardPosition")."</message>";

        return $strReturn;
    }

    /**
     * Renderes the content of a single widget.
     *
     * @return string
     * @permissions view
     */
    protected function actionGetWidgetContent()
    {

        //load the aspect and close the session afterwards
        SystemAspect::getCurrentAspect();

        $objWidgetModel = new DashboardWidget($this->getSystemid());
        if ($objWidgetModel->rightView()) {
            $objConcreteWidget = $objWidgetModel->getConcreteAdminwidget();

            if (!$objConcreteWidget->getBitBlockSessionClose()) {
                Carrier::getInstance()->getObjSession()->sessionClose();
            }

            //disable the internal changelog
            SystemChangelog::$bitChangelogEnabled = false;

            ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
            $strReturn = json_encode($objConcreteWidget->generateWidgetOutput());

        } else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
            $strReturn = "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * @return string
     * @permissions view
     */
    protected function actionGetCalendarEvents()
    {

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);

        $arrEvents = array();
        $arrCategories = EventRepository::getAllCategories();
        $objStartDate = new Date(strtotime($this->getParam("start")));
        $objEndDate = new Date(strtotime($this->getParam("end")));

        foreach ($arrCategories as $arrCategory) {
            foreach ($arrCategory as $strKey => $strValue) {
                if ($this->objSession->getSession($strKey) != "disabled") {
                    $arrEvents = array_merge($arrEvents, EventRepository::getEventsByCategoryAndDate($strKey, $objStartDate, $objEndDate));
                }
            }
        }

        $arrData = array();
        foreach ($arrEvents as $objEvent) {
            /** @var EventEntry $objEvent */
            $strIcon = AdminskinHelper::getAdminImage($objEvent->getStrIcon());
            $arrRow = array(
                "title"     => strip_tags($objEvent->getStrDisplayName()),
                "tooltip"   => $objEvent->getStrDisplayName(),
                "icon"      => $strIcon,
                "allDay"    => true,
                "url"       => htmlspecialchars_decode($objEvent->getStrHref()),
                "className" => array($objEvent->getStrCategory(), "calendar-event"),
            );

            if ($objEvent->getObjStartDate() instanceof Date && $objEvent->getObjEndDate() instanceof Date) {
                $arrRow["start"] = date("Y-m-d", $objEvent->getObjStartDate()->getTimeInOldStyle());
                $arrRow["end"] = date("Y-m-d", $objEvent->getObjEndDate()->getTimeInOldStyle());
            } elseif ($objEvent->getObjValidDate() instanceof Date) {
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
    protected function actionTodoCategory()
    {
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_HTML);

        $strCategory = $this->getParam("category");
        if (empty($strCategory)) {
            $arrTodos = TodoRepository::getAllOpenTodos();
        } else {
            $arrCategories = explode(',', $strCategory);
            $arrTodos = array();
            foreach ($arrCategories as $strCategory) {
                $arrTodos = array_merge($arrTodos, TodoRepository::getOpenTodos($strCategory, false));
            }
        }

        if (empty($arrTodos)) {
            return $this->objToolkit->warningBox($this->getLang("todo_no_open_tasks"), "alert-info");
        }

        $strSearch = $this->getParam("search");
        $strDate = $this->getParam("date");

        $arrHeaders = array(
            "0 " => "",
            "1"  => $this->getLang("todo_task_col_object"),
            "2 " => $this->getLang("todo_task_col_category"),
            "3 " => $this->getLang("todo_task_col_date"),
            "4 " => "",
        );
        $arrValues = array();

        foreach ($arrTodos as $objTodo) {
            $strActions = "";
            $arrModule = $objTodo->getArrModuleNavi();
            if (!empty($arrModule) && is_array($arrModule)) {
                foreach ($arrModule as $strLink) {
                    $strActions .= $this->objToolkit->listButton($strLink);
                }
            }

            $strIcon = AdminskinHelper::getAdminImage($objTodo->getStrIcon());
            $strCategory = TodoRepository::getCategoryName($objTodo->getStrCategory());
            $strValidDate = $objTodo->getObjValidDate() !== null ? dateToString($objTodo->getObjValidDate(), false) : "-";

            $bitSearchMatch = empty($strSearch) || stripos($objTodo->getStrDisplayName(), $strSearch) !== false;
            $bitDateMatch = empty($strDate) || $strValidDate == $strDate;

            if ($bitSearchMatch && $bitDateMatch) {
                $arrValues[] = array(
                    $strIcon,
                    $objTodo->getStrDisplayName(),
                    $strCategory,
                    $strValidDate,
                    "4 align-right actions" => $strActions
                );
            }
        }

        return $this->objToolkit->dataTable($arrHeaders, $arrValues, "admintable");
    }

    /**
     * @return string
     * @permissions view
     */
    protected function actionTreeEndpoint()
    {
        $objJsTreeLoader = new SystemJSTreeBuilder(
            new TodoJstreeNodeLoader()
        );


        $arrSystemIdPath = $this->getParam(SystemJSTreeBuilder::STR_PARAM_INITIALTOGGLING);
        $bitInitialLoading = is_array($arrSystemIdPath);
        if (!$bitInitialLoading) {
            $arrSystemIdPath = array("");
        }

        $arrReturn = $objJsTreeLoader->getJson($arrSystemIdPath, $bitInitialLoading, $this->getParam(SystemJSTreeBuilder::STR_PARAM_LOADALLCHILDNOES) === "true");
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
        return $arrReturn;
    }
}


