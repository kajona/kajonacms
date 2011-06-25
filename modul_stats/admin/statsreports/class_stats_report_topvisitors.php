<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/


/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package modul_stats
 */
class class_stats_report_topvisitors implements interface_admin_statsreports {

	//class vars
	private $intDateStart;
	private $intDateEnd;

	private $objTexts;
	private $objToolkit;
	private $objDB;

	private $arrModule;

	/**
	 * Constructor
	 *
	 */
	public function __construct($objDB, $objToolkit, $objTexts) {
		$this->arrModule["name"] 			= "modul_stats_reports_topvisitors";
		$this->arrModule["author"] 			= "sidler@mulchprod.de";
		$this->arrModule["moduleId"] 		= _stats_modul_id_;
		$this->arrModule["table"] 		    = _dbprefix_."stats_data";
		$this->arrModule["modul"]			= "stats";

		$this->objTexts = $objTexts;
		$this->objToolkit = $objToolkit;
		$this->objDB = $objDB;
	}

	public function setEndDate($intEndDate) {
	    $this->intDateEnd = $intEndDate;
	}

	public function setStartDate($intStartDate) {
	    $this->intDateStart = $intStartDate;
	}

	public function getReportTitle() {
	    return  $this->objTexts->getText("topvisitor", "stats", "admin");
	}

	public function getReportCommand() {
	    return "statsTopVisitors";
	}
	
	public function isIntervalable() {
	    return false;
	}
	
	public function setInterval($intInterval) {
	    
	}

	public function getReport() {
	    $strReturn = "";
        //Create Data-table
        $arrHeader = array();
        $arrValues = array();
        //Fetch data
		$arrStats = $this->getTopVisitors();

		//calc a few values
		$intSum = 0;
		foreach($arrStats as $arrOneStat)
			$intSum += $arrOneStat["anzahl"];

		$intI =0;
		foreach($arrStats as $arrOneStat) {
			//Escape?
			if($intI >= _stats_nrofrecords_)
				break;
            $arrValues[$intI] = array();
			$arrValues[$intI][] = $intI+1;
			if($arrOneStat["host"] != "" and $arrOneStat["host"] != "na")
		        $arrValues[$intI][] = $arrOneStat["host"];
		    else
			    $arrValues[$intI][] = $arrOneStat["visitor"];
			$arrValues[$intI][] = $arrOneStat["anzahl"];
			$arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat["anzahl"] / $intSum*100);
			$intI++;
		}
		//HeaderRow
		$arrHeader[] = "#";
		$arrHeader[] = $this->objTexts->getText("top_visitor_titel", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("commons_hits_header", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("anteil", "stats", "admin");

		$strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

        $strReturn .= $this->objToolkit->getTextRow($this->objTexts->getText("stats_hint_task", "stats", "admin"));

		return $strReturn;
	}

	/**
	 * Returns the list of top-visitors
	 *
	 * @return mixed
	 */
	public function getTopVisitors() {
		$strQuery = "SELECT stats_ip as visitor , stats_browser, stats_hostname as host, count(*) as anzahl
						FROM ".$this->arrModule["table"]."
						WHERE stats_date >= ".(int)$this->intDateStart."
								AND stats_date <= ".(int)$this->intDateEnd."
						GROUP BY stats_ip, stats_browser
						ORDER BY anzahl desc";
		return $this->objDB->getArray($strQuery);
	}

	public function getReportGraph() {
		return "";
	}
}
?>