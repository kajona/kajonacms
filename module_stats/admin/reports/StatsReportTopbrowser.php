<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Stats\Admin\Reports;

use Kajona\Stats\Admin\AdminStatsreportsInterface;
use Kajona\Stats\System\Browscap;
use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\System\Database;
use Kajona\System\System\GraphFactory;
use Kajona\System\System\Lang;
use Kajona\System\System\Session;
use Kajona\System\System\UserUser;

/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class StatsReportTopbrowser implements AdminStatsreportsInterface
{

    //class vars
    private $intDateStart;
    private $intDateEnd;
    private $intInterval = 1;

    private $objTexts;
    private $objToolkit;
    private $objDB;

    /**
     * Constructor
     */
    public function __construct(Database $objDB, ToolkitAdmin $objToolkit, Lang $objTexts)
    {
        $this->objTexts = $objTexts;
        $this->objToolkit = $objToolkit;
        $this->objDB = $objDB;
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public static function getExtensionName()
    {
        return "core.stats.admin.statsreport";
    }

    /**
     * @param int $intEndDate
     *
     * @return void
     */
    public function setEndDate($intEndDate)
    {
        $this->intDateEnd = $intEndDate;
    }

    /**
     * @param int $intStartDate
     *
     * @return void
     */
    public function setStartDate($intStartDate)
    {
        $this->intDateStart = $intStartDate;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->objTexts->getLang("topbrowser", "stats");
    }

    /**
     * @return bool
     */
    public function isIntervalable()
    {
        return true;
    }

    /**
     * @param int $intInterval
     *
     * @return void
     */
    public function setInterval($intInterval)
    {
        $this->intInterval = $intInterval;
    }

    /**
     * @return string
     */
    public function getReport()
    {
        $strReturn = "";

        //Create Data-table
        $arrHeader = array();
        $arrValues = array();
        //Fetch data
        $arrStats = $this->getTopBrowser();

        //calc a few values
        $intSum = 0;
        foreach ($arrStats as $arrOneStat) {
            $intSum += $arrOneStat;
        }

        $intI = 0;
        $objUser = new UserUser(Session::getInstance()->getUserID());
        foreach ($arrStats as $strName => $arrOneStat) {
            //Escape?
            if ($intI >= $objUser->getIntItemsPerPage()) {
                break;
            }

            $arrValues[$intI] = array();
            $arrValues[$intI][] = $intI + 1;
            $arrValues[$intI][] = $strName;
            $arrValues[$intI][] = $arrOneStat;
            $arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat / $intSum * 100);
            $intI++;
        }
        //HeaderRow
        $arrHeader[] = "#";
        $arrHeader[] = $this->objTexts->getLang("top_browser_titel", "stats");
        $arrHeader[] = $this->objTexts->getLang("top_browser_gewicht", "stats");
        $arrHeader[] = $this->objTexts->getLang("anteil", "stats");


        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);


        return $strReturn;
    }

    /**
     * Returns a array of top browsers
     *
     * @return mixed
     */
    private function getTopBrowser()
    {
        $arrReturn = array();


        //load Data
        $strQuery = "SELECT stats_browser, count(*) as anzahl
						FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
							AND stats_date <= ?
						GROUP BY stats_browser";
        $arrBrowser = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd));

        $objBrowscap = new Browscap();

        //Search the best matching pattern
        foreach ($arrBrowser as $arrRow) {
            $strInfo = $objBrowscap->getBrowserForUseragent($arrRow["stats_browser"]);

            if (!isset($arrReturn[$strInfo])) {
                $arrReturn[$strInfo] = $arrRow["anzahl"];
            }
            else {
                $arrReturn[$strInfo] += $arrRow["anzahl"];
            }
        }

        arsort($arrReturn);
        return $arrReturn;
    }

    /**
     * @return array
     */
    public function getReportGraph()
    {
        $arrReturn = array();

        //--- PIE-GRAPH ---------------------------------------------------------------------------------
        $arrData = $this->getTopBrowser();

        $intSum = 0;
        foreach ($arrData as $arrOneStat) {
            $intSum += $arrOneStat;
        }

        $arrKeyValues = array();
        //max 6 entries
        $intCount = 0;
        $floatPercentageSum = 0;
        $arrValues = array();
        $arrLabels = array();
        foreach ($arrData as $strName => $arrOneBrowser) {
            if (++$intCount <= 6) {
                $floatPercentage = $arrOneBrowser / $intSum * 100;
                $floatPercentageSum += $floatPercentage;
                $arrKeyValues[$strName] = $floatPercentage;
                $arrValues[] = $floatPercentage;
                $arrLabels[] = $strName;
            }
            else {
                break;
            }
        }
        //add "others" part?
        if ($floatPercentageSum < 99) {
            $arrKeyValues["others"] = 100 - $floatPercentageSum;
            $arrLabels[] = "others";
            $arrValues[] = 100 - $floatPercentageSum;
        }
        if (count($arrKeyValues) > 0) {
            $objGraph = GraphFactory::getGraphInstance();
            $objGraph->createPieChart($arrValues, $arrLabels);
            $arrReturn[] = $objGraph->renderGraph();
        }

        //--- XY-Plot -----------------------------------------------------------------------------------
        //calc number of plots
        $arrPlots = array();
        $arrTickLabels = array();
        foreach ($arrKeyValues as $strBrowser => $arrData) {
            if ($strBrowser != "others") {
                $arrPlots[$strBrowser] = array();
            }
        }

        $intGlobalEnd = $this->intDateEnd;
        $intGlobalStart = $this->intDateStart;

        $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;

        $intCount = 0;
        while ($this->intDateStart <= $intGlobalEnd) {
            $arrBrowserData = $this->getTopBrowser();
            //init plot array for this period
            $arrTickLabels[$intCount] = date("d.m.", $this->intDateStart);
            foreach ($arrPlots as $strBrowser => &$arrOnePlot) {
                $arrOnePlot[$intCount] = 0;
                if (key_exists($strBrowser, $arrBrowserData)) {
                    $arrOnePlot[$intCount] = (int)$arrBrowserData[$strBrowser];
                }

            }
            //increase start & end-date
            $this->intDateStart = $this->intDateEnd;
            $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;
            $intCount++;
        }
        //create graph
        if (count($arrTickLabels) > 1 && count($arrPlots) > 0) {
            $objGraph = GraphFactory::getGraphInstance();
            $objGraph->setArrXAxisTickLabels($arrTickLabels);
            foreach ($arrPlots as $arrPlotName => $arrPlotData) {
                $objGraph->addLinePlot($arrPlotData, $arrPlotName);
            }
            $arrReturn[] = $objGraph->renderGraph();
        }
        //reset global dates
        $this->intDateEnd = $intGlobalEnd;
        $this->intDateStart = $intGlobalStart;

        return $arrReturn;
    }


}

