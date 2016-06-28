<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/

namespace Kajona\Dashboard\Admin;

use Kajona\Dashboard\System\DashboardWidget;
use Kajona\Dashboard\System\EventEntry;
use Kajona\Dashboard\System\EventRepository;
use Kajona\Dashboard\System\TodoJstreeNodeLoader;
use Kajona\Dashboard\System\TodoRepository;
use Kajona\System\Admin\AdminController;
use Kajona\System\Admin\XmlAdminInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\SystemJSTreeBuilder;

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
class DashboardAdminXml extends AdminController implements XmlAdminInterface
{

    private $strStartMonthKey = "DASHBOARD_CALENDAR_START_MONTH";
    private $strStartYearKey = "DASHBOARD_CALENDAR_START_YEAR";

    /**
     * Removes a single widget
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
        if($intNewPos != "") {
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
        if($objWidgetModel->rightView()) {
            $objConcreteWidget = $objWidgetModel->getConcreteAdminwidget();

            if(!$objConcreteWidget->getBitBlockSessionClose()) {
                Carrier::getInstance()->getObjSession()->sessionClose();
            }

            //disable the internal changelog
            SystemChangelog::$bitChangelogEnabled = false;

            ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
            $strReturn = json_encode($objConcreteWidget->generateWidgetOutput());

        }
        else {
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
        $objStartDate = new \Kajona\System\System\Date(strtotime($this->getParam("start")));
        $objEndDate = new \Kajona\System\System\Date(strtotime($this->getParam("end")));

        foreach($arrCategories as $arrCategory) {
            foreach($arrCategory as $strKey => $strValue) {
                if($this->objSession->getSession($strKey) != "disabled") {
                    $arrEvents = array_merge($arrEvents, EventRepository::getEventsByCategoryAndDate($strKey, $objStartDate, $objEndDate));
                }
            }
        }

        $arrData = array();
        foreach($arrEvents as $objEvent) {
            /** @var EventEntry $objEvent */
            $strIcon = AdminskinHelper::getAdminImage($objEvent->getStrIcon());
            $arrRow = array(
                "title"     => strip_tags($objEvent->getStrDisplayName()),
                "tooltip"   => $objEvent->getStrDisplayName(),
                "icon"      => $strIcon,
                "allDay"    => true,
                "url"       => $objEvent->getStrHref(),
                "className" => array($objEvent->getStrCategory(), "calendar-event"),
            );

            if($objEvent->getObjStartDate() instanceof \Kajona\System\System\Date && $objEvent->getObjEndDate() instanceof \Kajona\System\System\Date) {
                $arrRow["start"] = date("Y-m-d", $objEvent->getObjStartDate()->getTimeInOldStyle());
                $arrRow["end"] = date("Y-m-d", $objEvent->getObjEndDate()->getTimeInOldStyle());
            }
            elseif($objEvent->getObjValidDate() instanceof \Kajona\System\System\Date) {
                $arrRow["start"] = date("Y-m-d", $objEvent->getObjValidDate()->getTimeInOldStyle());
            }
            else {
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
        if(empty($strCategory)) {
            $arrTodos = TodoRepository::getAllOpenTodos();
        }
        else {
            $arrCategories = explode(',', $strCategory);
            $arrTodos = array();
            foreach($arrCategories as $strCategory) {
                $arrTodos = array_merge($arrTodos, TodoRepository::getOpenTodos($strCategory, false));
            }
        }

        if(empty($arrTodos)) {
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

        foreach($arrTodos as $objTodo) {
            $strActions = "";
            $arrModule = $objTodo->getArrModuleNavi();
            if(!empty($arrModule) && is_array($arrModule)) {
                foreach($arrModule as $strLink) {
                    $strActions .= $this->objToolkit->listButton($strLink);
                }
            }

            $strIcon = AdminskinHelper::getAdminImage($objTodo->getStrIcon());
            $strCategory = TodoRepository::getCategoryName($objTodo->getStrCategory());
            $strValidDate = $objTodo->getObjValidDate() !== null ? dateToString($objTodo->getObjValidDate(), false) : "-";

            $bitSearchMatch = empty($strSearch) || stripos($objTodo->getStrDisplayName(), $strSearch) !== false;
            $bitDateMatch = empty($strDate) || $strValidDate == $strDate;

            if($bitSearchMatch && $bitDateMatch) {
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
        if(!$bitInitialLoading) {
            $arrSystemIdPath = array("");
        }

        $arrReturn = $objJsTreeLoader->getJson($arrSystemIdPath, $bitInitialLoading, $this->getParam(SystemJSTreeBuilder::STR_PARAM_LOADALLCHILDNOES) === "true");
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
        return $arrReturn;
    }
}
