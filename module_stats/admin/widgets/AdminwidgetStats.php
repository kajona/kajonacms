<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                       			*
********************************************************************************************************/

namespace Kajona\Stats\Admin\Widgets;

use Kajona\Dashboard\Admin\Widgets\Adminwidget;
use Kajona\Dashboard\Admin\Widgets\AdminwidgetInterface;
use Kajona\Dashboard\System\DashboardWidget;
use Kajona\Stats\Admin\Statsreports\StatsReportCommon;
use Kajona\System\System\Carrier;
use Kajona\System\System\GraphFactory;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;


/**
 * @package module_stats
 *
 */
class AdminwidgetStats extends Adminwidget implements AdminwidgetInterface
{

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct()
    {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("current", "day", "last", "nrLast", "chart"));
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm()
    {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputCheckbox("current", $this->getLang("stats_current"), $this->getFieldValue("current"));
        $strReturn .= $this->objToolkit->formInputCheckbox("chart", $this->getLang("stats_chart"), $this->getFieldValue("chart"));
        $strReturn .= $this->objToolkit->formInputCheckbox("day", $this->getLang("stats_day"), $this->getFieldValue("day"));
        $strReturn .= $this->objToolkit->formInputCheckbox("last", $this->getLang("stats_last"), $this->getFieldValue("last"));
        $strReturn .= $this->objToolkit->formInputText("nrLast", $this->getLang("stats_nrLast"), $this->getFieldValue("nrLast"));
        return $strReturn;
    }

    /**
     * This method is called, when the widget should generate it's content.
     * Return the complete content using the methods provided by the base class.
     * Do NOT use the toolkit right here!
     *
     * @return string
     */
    public function getWidgetOutput()
    {
        $strReturn = "";

        if (!SystemModule::getModuleByName("stats")->rightView()) {
            return $this->getLang("commons_error_permissions");
        }


        $objStatsCommon = new StatsReportCommon(Carrier::getInstance()->getObjDB(), Carrier::getInstance()->getObjToolkit("admin"), Carrier::getInstance()->getObjLang());
        //check wich infos to produce
        if ($this->getFieldValue("current") == "checked") {
            $strReturn .= $this->getLang("stats_online").$objStatsCommon->getNumberOfCurrentUsers();

            $strReturn .= $this->widgetSeparator();
        }
        if ($this->getFieldValue("chart") == "checked") {
            //load the last view days
            $objDate = new \Kajona\System\System\Date();
            $objDate->setIntHour(0);
            $objDate->setIntMin(0);
            $objDate->setIntSec(0);

            $arrHits = array();
            $arrLabels = array();
            for ($intI = 0; $intI < 7; $intI++) {
                $objEndDate = clone $objDate;
                $objEndDate->setNextDay();
                $objStatsCommon->setStartDate($objDate->getTimeInOldStyle());
                $objStatsCommon->setEndDate($objEndDate->getTimeInOldStyle());

                $arrHits[] = $objStatsCommon->getHits();
                $arrLabels[] = $objDate->getIntDay();

                $objDate->setPreviousDay();
            }

            $arrHits = array_reverse($arrHits);
            $arrLabels = array_reverse($arrLabels);


            $strReturn .= $this->widgetText($this->getLang("stats_hits"));

            $objChart = GraphFactory::getGraphInstance();
            $objChart->setArrXAxisTickLabels($arrLabels);
            $objChart->addLinePlot($arrHits, "");
            $objChart->setBitRenderLegend(false);
            $objChart->setIntHeight(220);
            $objChart->setIntWidth(300);
            $objChart->setStrXAxisTitle("");
            $objChart->setStrYAxisTitle("");
            $strReturn .= $objChart->renderGraph();
        }
        if ($this->getFieldValue("day") == "checked") {
            //current day:
            //pass date to commons-object
            $objDate = new \Kajona\System\System\Date();
            $objDate->setIntHour(0);
            $objDate->setIntMin(0);
            $objDate->setIntSec(0);
            $strReturn .= $this->widgetText(dateToString($objDate, false));

            $objStatsCommon->setStartDate($objDate->getTimeInOldStyle());
            $objDate->setNextDay();
            $objStatsCommon->setEndDate($objDate->getTimeInOldStyle());

            $strReturn .= $this->widgetText($this->getLang("stats_hits")." ".$objStatsCommon->getHits());
            $strReturn .= $this->widgetText($this->getLang("stats_visitors")." ".$objStatsCommon->getVisitors());

            $strReturn .= $this->widgetSeparator();
        }
        if ($this->getFieldValue("last") == "checked") {

            $strReturn .= $this->widgetText($this->getLang("stats_ip")." ".$this->getLang("stats_page"));

            $intMaxRecords = $this->getFieldValue("nrLast");
            if (!is_numeric($intMaxRecords) || $intMaxRecords > 15) {
                $intMaxRecords = 15;
            }

            $arrRecordsets = Carrier::getInstance()->getObjDB()->getPArray("SELECT * FROM "._dbprefix_."stats_data ORDER BY stats_date DESC ", array(), 0, $intMaxRecords - 1);

            foreach ($arrRecordsets as $arrOneRecord) {
                $strReturn .= $this->widgetText($arrOneRecord["stats_ip"]." ".$arrOneRecord["stats_page"]);
            }

        }
        return $strReturn;
    }

    /**
     * This callback is triggered on a users' first login into the system.
     * You may use this method to install a widget as a default widget to
     * a users dashboard.
     *
     * @param $strUserid
     *
     * @return bool
     */
    public function onFistLogin($strUserid)
    {
        if (SystemModule::getModuleByName("stats") !== null && SystemAspect::getAspectByName("management") !== null) {
            $objDashboard = new DashboardWidget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:5:{s:7:\"current\";s:7:\"checked\";s:3:\"day\";s:7:\"checked\";s:4:\"last\";s:7:\"checked\";s:6:\"nrLast\";s:1:\"4\";s:5:\"chart\";s:7:\"checked\";}");
            return $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, SystemAspect::getAspectByName("management")->getSystemid()));
        }

        return true;
    }

    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName()
    {
        return $this->getLang("stats_name");
    }

}


