<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Stats\Admin\Statsreports;


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
class StatsReportTopsystems implements AdminStatsreportsInterface
{

    //class vars
    private $intDateStart;
    private $intDateEnd;
    private $intInterval;

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
        return $this->objTexts->getLang("topsystem", "stats");
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
        $arrStats = $this->getTopSystem();

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
        $arrHeader[] = $this->objTexts->getLang("top_system_titel", "stats");
        $arrHeader[] = $this->objTexts->getLang("top_system_gewicht", "stats");
        $arrHeader[] = $this->objTexts->getLang("anteil", "stats");

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);


        return $strReturn;
    }


    /**
     * Loads a list of systems accessed the page
     *
     * @return mixed
     */
    public function getTopSystem()
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
            $strSystem = $objBrowscap->getPlatformForUseragent($arrRow["stats_browser"]);

            if (!isset($arrReturn[$strSystem])) {
                $arrReturn[$strSystem] = $arrRow["anzahl"];
            }
            else {
                $arrReturn[$strSystem] += $arrRow["anzahl"];
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
        $arrData = $this->getTopSystem();

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
        foreach ($arrData as $strName => $intOneSystem) {
            if (++$intCount <= 6) {
                $floatPercentage = $intOneSystem / $intSum * 100;
                $floatPercentageSum += $floatPercentage;
                $arrKeyValues[$strName] = $floatPercentage;
                $arrLabels[] = $strName;
                $arrValues[] = $floatPercentage;
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
        $objGraph = GraphFactory::getGraphInstance();
        $objGraph->createPieChart($arrValues, $arrLabels);
        $arrReturn[] = $objGraph->renderGraph();

        //--- XY-Plot -----------------------------------------------------------------------------------
        //calc number of plots
        $arrPlots = array();
        $arrTickLabels = array();
        foreach ($arrKeyValues as $strSystem => $arrData) {
            if ($strSystem != "others") {
                $arrPlots[$strSystem] = array();
            }
        }

        $intGlobalEnd = $this->intDateEnd;
        $intGlobalStart = $this->intDateStart;

        $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;

        $intCount = 0;
        while ($this->intDateStart <= $intGlobalEnd) {
            $arrSystemData = $this->getTopSystem();
            //init plot array for this period
            $arrTickLabels[$intCount] = date("d.m.", $this->intDateStart);
            foreach ($arrPlots as $strSystem => &$arrOnePlot) {
                $arrOnePlot[$intCount] = 0;
                if (array_key_exists($strSystem, $arrSystemData)) {
                    $arrOnePlot[$intCount] = (int)$arrSystemData[$strSystem];
                }

            }
            //increase start & end-date
            $this->intDateStart = $this->intDateEnd;
            $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;
            $intCount++;
        }
        //create graph


        //fehler fangen: mind. 2 datumswerte
        if (count($arrTickLabels) > 1 && count($arrPlots) > 0) {
            $objGraph = GraphFactory::getGraphInstance();
            $objGraph->setArrXAxisTickLabels($arrTickLabels);

            foreach ($arrPlots as $arrPlotName => $arrPlotData) {
                $objGraph->addLinePlot($arrPlotData, $arrPlotName);
            }
            $objGraph->renderGraph();
            $arrReturn[] = $objGraph->renderGraph();
        }

        //reset global dates
        $this->intDateEnd = $intGlobalEnd;
        $this->intDateStart = $intGlobalStart;

        return $arrReturn;
    }
}
