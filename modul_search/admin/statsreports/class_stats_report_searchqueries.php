<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * This plugin shows the list of queries performed by the local searchengine
 *
 * @package modul_search
 */
class class_stats_report_searchqueries implements interface_admin_statsreports {

	//class vars
	private $intDateStart;
	private $intDateEnd;

	private $objTexts;
	private $objToolkit;
	/**
	 * instance of class db
	 *
	 * @var class_db
	 */
	private $objDB;

	private $arrModule;

	/**
	 * Constructor
	 *
	 */
	public function __construct($objDB, $objToolkit, $objTexts) {
		$this->arrModule["name"] 			= "modul_stats_reports_seachqueries";
		$this->arrModule["author"] 			= "sidler@mulchprod.de";
		$this->arrModule["moduleId"] 		= _suche_modul_id_;
		$this->arrModule["table"] 		    = _dbprefix_."search_log";
		$this->arrModule["modul"]			= "search";

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
	    return  $this->objTexts->getText("stats_title", "search", "admin");
	}

	public function getReportCommand() {
	    return "statsSearchqueries";
	}

	public function isIntervalable() {
	    return false;
	}

	public function setInterval($intInterval) {

	}

	public function getReport() {
	    $strReturn = "";

        //showing a list using the pageview
        $objArraySectionIterator = new class_array_section_iterator($this->getTopQueriesCount());
        $objArraySectionIterator->setIntElementsPerPage(_stats_nrofrecords_);
        $objArraySectionIterator->setPageNumber((int)(getGet("pv") != "" ? getGet("pv") : 1));
        $objArraySectionIterator->setArraySection($this->getTopQueries($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $strParams  = "&start_day=".(getPost("start_day") != "" ? getPost("start_day") : getGet("start_day"));
        $strParams .= "&start_month=".(getPost("start_month") != "" ? getPost("start_month") : getGet("start_month"));
        $strParams .= "&start_year=".(getPost("start_year") != "" ? getPost("start_year") : getGet("start_year"));

        $strParams .= "&end_day=".(getPost("end_day") != "" ? getPost("end_day") : getGet("end_day"));
        $strParams .= "&end_month=".(getPost("end_month") != "" ? getPost("end_month") : getGet("end_month"));
        $strParams .= "&end_year=".(getPost("end_year") != "" ? getPost("end_year") : getGet("end_year"));


        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "stats", $this->getReportCommand(), $strParams."&filter=true");
        
        $arrLogsRaw = $arrPageViews["elements"];

        $intI = 0;
        $arrLogs = array();
        foreach($arrLogsRaw as $intKey => $arrOneLog) {
            if($intI++ >= _stats_nrofrecords_)
				break;

			$arrLogs[$intKey][0] = $intI;
            $arrLogs[$intKey][1] = $arrOneLog["search_log_query"];
            $arrLogs[$intKey][2] = $arrOneLog["number"];
        }

    	//Create a data-table
    	$arrHeader = array();
        $arrHeader[0] = "#";
        $arrHeader[1] = $this->objTexts->getText("header_query", "search", "admin");
        $arrHeader[2] = $this->objTexts->getText("header_amount", "search", "admin");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);

        $strReturn .= $arrPageViews["pageview"];

		return $strReturn;
	}


    public function getReportGraph() {
	    $arrReturn = array();
        //collect data
        $arrQueries = $this->getTopQueries();

		$arrGraphData = array();
		$arrPlots = array();
		$arrLabels = array();
        
		$intCount = 1;
		foreach ($arrQueries as  $arrOneQuery) {
		    $arrGraphData[$intCount] = $arrOneQuery["number"];
            $arrLabels[$intCount] = $arrOneQuery["search_log_query"];

		    if($intCount++ >= 9)
		      break;
		}

        if(count($arrGraphData) > 1) {
    	    //generate a bar-chart
    	    $objGraph = new class_graph_pchart();
    	    $objGraph->addBarChartSet($arrGraphData, "");
    	    $objGraph->setStrXAxisTitle($this->objTexts->getText("header_query", "search", "admin"));
    	    $objGraph->setStrYAxisTitle($this->objTexts->getText("header_amount", "search", "admin"));
    	    $objGraph->setArrXAxisTickLabels($arrLabels);
    	    $strFilename = "/portal/pics/cache/stats_toppages.png";
            $objGraph->setBitRenderLegend(false);
            $objGraph->setIntXAxisAngle(20);
    	    $objGraph->saveGraph($strFilename);
    		$arrReturn[] =  _webpath_.$strFilename;


    		return $arrReturn;
        }
        else
            return "";
	}



    private function getTopQueries($intStart = false, $intEnd = false) {
        $strQuery = "SELECT search_log_query, COUNT(*) as number
					  FROM ".$this->arrModule["table"]."
					  WHERE search_log_date >= ".(int)$this->intDateStart."
					    AND search_log_date <= ".(int)$this->intDateEnd."
				   GROUP BY search_log_query
				   ORDER BY number DESC, search_log_date DESC";

        if($intStart !== false && $intEnd !== false)
            $arrReturn = $this->objDB->getArraySection($strQuery, $intStart, $intEnd);
        else
            $arrReturn = $this->objDB->getArraySection($strQuery, 0, _stats_nrofrecords_-1);

		return $arrReturn;
    }

    private function getTopQueriesCount() {
        $strQuery = "SELECT COUNT(DISTINCT(search_log_query)) as total
					  FROM ".$this->arrModule["table"]."
					  WHERE search_log_date >= ".(int)$this->intDateStart."
					    AND search_log_date <= ".(int)$this->intDateEnd."";

        $arrReturn = $this->objDB->getRow($strQuery);
		return $arrReturn["total"];
    }

	

}
?>