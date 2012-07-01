<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_stats_report_common.php 4141 2011-10-17 15:53:15Z sidler $                                *
********************************************************************************************************/

/**
 * This plugin creates a view common numbers, such as "user online" or "total pagehits"
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class class_stats_report_common implements interface_admin_statsreports {

	//class vars
	private $intDateStart;
	private $intDateEnd;
	private $intInterval = 1;

	private $objTexts;
	private $objToolkit;
	private $objDB;

	private $arrModule;

	/**
	 * Constructor
	 *
	 */
    public function __construct(class_db $objDB, class_toolkit_admin $objToolkit, class_lang $objTexts) {

		$this->objTexts = $objTexts;
		$this->objToolkit = $objToolkit;
		$this->objDB = $objDB;

		if (class_carrier::getInstance()->getObjConfig()->getPhpIni("memory_limit") < 30)
			@ini_set("memory_limit", "30M");
	}

	public function setEndDate($intEndDate) {
	    $this->intDateEnd = $intEndDate;
	}

	public function setStartDate($intStartDate) {
	    $this->intDateStart = $intStartDate;
	}

	public function getReportTitle() {
	    return  $this->objTexts->getLang("allgemein", "stats");
	}

	public function getReportCommand() {
	    return "statsCommon";
	}

	public function isIntervalable() {
	    return true;
	}

	public function setInterval($intInterval) {
	    $this->intInterval = $intInterval;
	}

	public function getReport() {
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
	public function getHits() {
		$intReturn = 0;
		$strQuery = "SELECT count(*)
						FROM "._dbprefix_."stats_data
						WHERE stats_date >= ?
						  AND stats_date <= ?";

		$arrRow = $this->objDB->getPRow($strQuery, array($this->intDateStart, $this->intDateEnd));
		$intReturn = $arrRow["count(*)"];

		return $intReturn;
	}

	/**
	 * Returns the number of hits
	 *
	 * @param int $intStart
	 * @param int $intEnd
	 * @return int
	 */
	public function getHitsForOnePeriod($intStart, $intEnd) {
		$strQuery = "SELECT stats_date, COUNT(*) as hits
						FROM "._dbprefix_."stats_data
						WHERE stats_date >= ?
								AND stats_date <= ?
						GROUP BY stats_date
						ORDER BY stats_date ASC";

		$arrTemp = $this->objDB->getPArray($strQuery, array($intStart, $intEnd));
		return $arrTemp;
	}

	/**
	 * Returns the number of visitors
	 *
	 * @return int
	 */
	public function getVisitors() {
		$intReturn = 0;

        $strQuery = "SELECT stats_ip , stats_browser, stats_date
						FROM "._dbprefix_."stats_data
						WHERE stats_date >= ?
								AND stats_date <= ?
						GROUP BY stats_ip, stats_browser
						ORDER BY stats_date ASC";
        
		$arrRows = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd));
		$intReturn = count($arrRows);
		return $intReturn;
	}


    /**
     * Returns the number of visitors
     *
     * @param $intStart
     * @param $intEnd
     *
     * @return int
     */
	private function getVisitorsForOnePeriod($intStart, $intEnd) {
		$strQuery = "SELECT stats_ip , stats_browser, stats_date
						FROM "._dbprefix_."stats_data
						WHERE stats_date >= ?
								AND stats_date <= ?
						GROUP BY stats_ip, stats_browser
						ORDER BY stats_date ASC";
		$arrTemp = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), $intStart, $intEnd);
		return $arrTemp;
	}

	/**
	 * Returns the average number of pages per visit
	 *
	 * @return int
	 */
	private function getPagesPerVisit() {
		$intReturn = 0;
		$intUser = $this->getVisitors();
		$intHits = $this->getHits();

		if($intHits != 0)
			$intReturn = (int)($intHits/$intUser);

		return $intReturn;
	}

	/**
	 * Returns the number of useres currently online browsing the portal
	 *
	 * @return int
	 */
	public function getNumberOfCurrentUsers() {
		$strQuery = "SELECT stats_ip, stats_browser, count(*)
					  FROM "._dbprefix_."stats_data
					  WHERE stats_date >= ?
					  GROUP BY stats_ip, stats_browser";

		$arrRow = $this->objDB->getPArray($strQuery, array(time() - _stats_duration_online_));

		return count($arrRow);
	}

	/**
	 * Returns the average time a user spent on the site
	 *
	 * @return int
	 */
	private function getTimePerVisit() {
        $strQuery = "SELECT MAX(stats_date) as max,
                            MIN(stats_date) as min,
                            MAX(stats_date)-MIN(stats_date) as dauer,
                            stats_session
                     FROM "._dbprefix_."stats_data
                     WHERE stats_session != ''
                       AND stats_date >= ?
					   AND stats_date <= ?
                     GROUP BY stats_session";

        $arrSessions = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd));
        $intTime = 0;
        foreach($arrSessions as $arrOneSession)
            $intTime += $arrOneSession["dauer"];

        if(count($arrSessions) > 0) {
            $intTime = $intTime / count($arrSessions);

            return ceil($intTime);
        }
        else
            return "0";
	}

	public function getReportGraph() {
		//load datasets, reloading after 30 days to limit memory consumption
		$arrHits = array();
		$arrUser = array();
		$arrTickLabels = array();

		$intDaysPerLoad = 10;

        //create tick labels
        $intCount = 0;
        $intStart = $this->intDateStart;
        $intDBStart = $this->intDateStart;
        $intDBEnd = ($intDBStart+$intDaysPerLoad*24*60*60);

        $arrHitsTotal = $this->getHitsForOnePeriod($intDBStart, $intDBEnd);
        $arrUserTotal = $this->getVisitorsForOnePeriod($intDBStart, $intDBEnd);
		while($intStart <= $this->intDateEnd) {

			$arrTickLabels[$intCount] = date("d.m.", $intStart);
			$arrHits[$intCount] = 0;
			$arrUser[$intCount] = 0;

			//reload hits?
			if(($intStart+24*60*60*$this->intInterval) > $intDBEnd ) {
			    //reload arrays
			    $intDBStart = $intStart;
			    $intDBEnd = ($intStart+$intDaysPerLoad*24*60*60);
			    $arrHitsTotal = $this->getHitsForOnePeriod($intDBStart, $intDBEnd);
                $arrUserTotal = $this->getVisitorsForOnePeriod($intDBStart, $intDBEnd);

			    //IMPORTANT: Flush query cache to avoid max mem errors
			    $this->objDB->flushQueryCache();
			}

			foreach($arrHitsTotal as $arrOneKey => $arrOneHit) {
			    if($arrOneHit["stats_date"] >= $intStart && $arrOneHit["stats_date"] < ($intStart+24*60*60*$this->intInterval)) {
			        $arrHits[$intCount] += $arrOneHit["hits"];
			    }
			}

			foreach($arrUserTotal as $arrOneKey => $arrOneUser) {
			    if($arrOneUser["stats_date"] >= $intStart && $arrOneUser["stats_date"] < ($intStart+24*60*60*$this->intInterval)) {
			        $arrUser[$intCount] += 1;
			    }
			}

			//load the next interval
			$intStart += 24*60*60*$this->intInterval;
			$intCount++;
		}


		//create a graph ->line-graph
		if($intCount > 1) {

            $objChart1 = class_graph_factory::getGraphInstance();
            $objChart1->setStrGraphTitle($this->objTexts->getLang("graph_hitsPerDay", "stats"));
            $objChart1->setStrXAxisTitle($this->objTexts->getLang("graph_date", "stats"));
            $objChart1->setStrYAxisTitle($this->objTexts->getLang("graph_hits", "stats"));
            $objChart1->setIntWidth(715);
            $objChart1->setIntHeight(200);
            $objChart1->setArrXAxisTickLabels($arrTickLabels);
            $objChart1->addLinePlot($arrHits, "Hits");
            $objChart1->setBitRenderLegend(false);
            $strImagePath1 = _images_cachepath_."stats_common_1.png";
    		$objChart1->saveGraph($strImagePath1);

            $objChart2 = class_graph_factory::getGraphInstance();
            $objChart2->setStrGraphTitle($this->objTexts->getLang("graph_visitorsPerDay", "stats"));
            $objChart2->setStrXAxisTitle($this->objTexts->getLang("graph_date", "stats"));
            $objChart2->setStrYAxisTitle($this->objTexts->getLang("graph_visitors", "stats"));
            $objChart2->setIntWidth(715);
            $objChart2->setIntHeight(200);
            $objChart2->setArrXAxisTickLabels($arrTickLabels);
            $objChart2->addLinePlot($arrUser, "Visitors/Day");
            $objChart2->setBitRenderLegend(false);
            $strImagePath2 = _images_cachepath_."stats_common_2.png";
    		$objChart2->saveGraph($strImagePath2);

            $this->objDB->flushQueryCache();

            return array((_webpath_.$strImagePath1), (_webpath_.$strImagePath2));

		}
		else
		  return "";
	}

}
