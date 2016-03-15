<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

namespace Kajona\Stats\Admin\Statsreports;


use Kajona\Stats\Admin\AdminStatsreportsInterface;
use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\GraphFactory;
use Kajona\System\System\Lang;
use Kajona\System\System\SystemSetting;

/**
 * This plugin creates a view common numbers, such as "user online" or "total pagehits"
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class StatsReportCommon implements AdminStatsreportsInterface
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

        if (Carrier::getInstance()->getObjConfig()->getPhpIni("memory_limit") < 30) {
            @ini_set("memory_limit", "30M");
        }
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
        return $this->objTexts->getLang("allgemein", "stats");
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
        $arrValues[0] = array();
        $arrValues[0][] = $this->objTexts->getLang("anzahl_hits", "stats");
        $arrValues[0][] = $this->getHits();

        $arrValues[1] = array();
        $arrValues[1][] = $this->objTexts->getLang("anzahl_visitor", "stats");
        $arrValues[1][] = $this->getVisitors();

        $arrValues[2] = array();
        $arrValues[2][] = $this->objTexts->getLang("anzahl_pagespvisit", "stats");
        $arrValues[2][] = $this->getPagesPerVisit();

        $arrValues[3] = array();
        $arrValues[3][] = $this->objTexts->getLang("anzahl_timepvisit", "stats");
        $arrValues[3][] = $this->getTimePerVisit();

        $arrValues[4] = array();
        $arrValues[4][] = "&nbsp;";
        $arrValues[4][] = " ";

        $arrValues[5] = array();
        $arrValues[5][] = $this->objTexts->getLang("anzahl_online", "stats");
        $arrValues[5][] = $this->getNumberOfCurrentUsers();


        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

        return $strReturn;
    }

    /**
     * Returns the number of hits
     *
     * @return int
     */
    public function getHits()
    {
        $strQuery = "SELECT COUNT(*)
						FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
						  AND stats_date <= ?";

        $arrRow = $this->objDB->getPRow($strQuery, array($this->intDateStart, $this->intDateEnd));
        $intReturn = $arrRow["COUNT(*)"];

        return $intReturn;
    }

    /**
     * Returns the number of hits
     *
     * @param int $intStart
     * @param int $intEnd
     *
     * @return array
     */
    private function getHitsForOnePeriod($intStart, $intEnd)
    {
        $strQuery = "SELECT COUNT(*)
						FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
								AND stats_date <= ?";

        $arrTemp = $this->objDB->getPRow($strQuery, array($intStart, $intEnd));
        return $arrTemp["COUNT(*)"];
    }

    /**
     * Returns the number of visitors
     *
     * @return int
     */
    public function getVisitors()
    {

        $strQuery = "SELECT stats_ip , stats_browser
						FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
								AND stats_date <= ?
						GROUP BY stats_ip, stats_browser";

        $arrRows = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd));
        return count($arrRows);
    }


    /**
     * Returns the number of visitors
     *
     * @param int $intStart
     * @param int $intEnd
     *
     * @return array
     */
    private function getVisitorsForOnePeriod($intStart, $intEnd)
    {
        $strQuery = "SELECT stats_ip, stats_browser
						FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
								AND stats_date <= ?
						GROUP BY stats_ip, stats_browser";
        $arrTemp = $this->objDB->getPArray($strQuery, array($intStart, $intEnd));
        return count($arrTemp);
    }

    /**
     * Returns the average number of pages per visit
     *
     * @return int
     */
    private function getPagesPerVisit()
    {
        $intReturn = 0;
        $intUser = $this->getVisitors();
        $intHits = $this->getHits();

        if ($intHits != 0) {
            $intReturn = (int)($intHits / $intUser);
        }

        return $intReturn;
    }

    /**
     * Returns the number of useres currently online browsing the portal
     *
     * @return int
     */
    public function getNumberOfCurrentUsers()
    {
        $strQuery = "SELECT stats_ip, stats_browser, count(*)
					  FROM "._dbprefix_."stats_data
					  WHERE stats_date > ?
					  GROUP BY stats_ip, stats_browser";

        $arrRow = $this->objDB->getPArray($strQuery, array(time() - SystemSetting::getConfigValue("_stats_duration_online_")));

        return count($arrRow);
    }

    /**
     * Returns the average time a user spent on the site
     *
     * @return int
     */
    private function getTimePerVisit()
    {
        $strQuery = "SELECT MAX(stats_date) as max,
                            MIN(stats_date) as min,
                            MAX(stats_date)-MIN(stats_date) as dauer,
                            stats_session
                     FROM "._dbprefix_."stats_data
                     WHERE stats_session != ''
                       AND stats_date > ?
					   AND stats_date <= ?
                     GROUP BY stats_session";

        $arrSessions = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd));
        $intTime = 0;
        foreach ($arrSessions as $arrOneSession) {
            $intTime += $arrOneSession["dauer"];
        }

        if (count($arrSessions) > 0) {
            $intTime = $intTime / count($arrSessions);

            return ceil($intTime);
        }
        else {
            return "0";
        }
    }

    /**
     * @return array|string
     */
    public function getReportGraph()
    {
        //load datasets, reloading after 30 days to limit memory consumption
        $arrHits = array();
        $arrUser = array();
        $arrTickLabels = array();

        //create tick labels
        $intCount = 0;
        $intDBStart = $this->intDateStart;
        $intDBEnd = ($intDBStart + $this->intInterval * 24 * 60 * 60);


        while ($intDBStart <= $this->intDateEnd) {

            $arrTickLabels[$intCount] = date("d.m.", $intDBStart);
            $arrHits[$intCount] = $this->getHitsForOnePeriod($intDBStart, $intDBEnd);
            $arrUser[$intCount] = $this->getVisitorsForOnePeriod($intDBStart, $intDBEnd);

            $intDBStart = $intDBEnd;
            $intDBEnd = ($intDBStart + $this->intInterval * 24 * 60 * 60);

            $this->objDB->flushQueryCache();
            $intCount++;
        }

        //create a graph ->line-graph
        if ($intCount > 1) {


            $objChart1 = GraphFactory::getGraphInstance();
            $objChart1->setStrGraphTitle($this->objTexts->getLang("graph_hitsPerDay", "stats"));
            $objChart1->setStrXAxisTitle($this->objTexts->getLang("graph_date", "stats"));
            $objChart1->setStrYAxisTitle($this->objTexts->getLang("graph_hits", "stats"));
            $objChart1->setIntWidth(715);
            $objChart1->setIntHeight(200);
            $objChart1->setArrXAxisTickLabels($arrTickLabels);
            $objChart1->addLinePlot($arrHits, "Hits");
            $objChart1->setBitRenderLegend(false);

            $objChart2 = GraphFactory::getGraphInstance();
            $objChart2->setStrGraphTitle($this->objTexts->getLang("graph_visitorsPerDay", "stats"));
            $objChart2->setStrXAxisTitle($this->objTexts->getLang("graph_date", "stats"));
            $objChart2->setStrYAxisTitle($this->objTexts->getLang("graph_visitors", "stats"));
            $objChart2->setIntWidth(715);
            $objChart2->setIntHeight(200);
            $objChart2->setArrXAxisTickLabels($arrTickLabels);
            $objChart2->addLinePlot($arrUser, "Visitors/Day");
            $objChart2->setBitRenderLegend(false);

            $this->objDB->flushQueryCache();

            return array($objChart1->renderGraph(), $objChart2->renderGraph());

        }
        else {
            return "";
        }
    }

}
