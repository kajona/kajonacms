<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * This plugin shows the list of queries performed by the local searchengine
 *
 * @package module_search
 * @author sidler@mulchprod.de
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
    public function __construct(class_db $objDB, class_toolkit_admin $objToolkit, class_lang $objTexts) {
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
	    return  $this->objTexts->getLang("stats_title", "search");
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
            $arrLogs[$intKey][2] = $arrOneLog["hits"];
        }

    	//Create a data-table
    	$arrHeader = array();
        $arrHeader[0] = "#";
        $arrHeader[1] = $this->objTexts->getLang("header_query", "search");
        $arrHeader[2] = $this->objTexts->getLang("header_amount", "search");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);

        $strReturn .= $arrPageViews["pageview"];

		return $strReturn;
	}


    public function getReportGraph() {
	    $arrReturn = array();
        //collect data
        $arrQueries = $this->getTopQueries();

		$arrGraphData = array();
		$arrLabels = array();
        
		$intCount = 1;
		foreach ($arrQueries as  $arrOneQuery) {
		    $arrGraphData[$intCount] = $arrOneQuery["hits"];
            $arrLabels[$intCount] = $arrOneQuery["search_log_query"];

		    if($intCount++ >= 9)
		      break;
		}

        if(count($arrGraphData) > 1) {
    	    //generate a bar-chart
    	    $objGraph = class_graph_factory::getGraphInstance();
    	    $objGraph->setArrXAxisTickLabels($arrLabels);
    	    $objGraph->addBarChartSet($arrGraphData, "");
    	    $objGraph->setStrXAxisTitle($this->objTexts->getLang("header_query", "search"));
    	    $objGraph->setStrYAxisTitle($this->objTexts->getLang("header_amount", "search"));
            $objGraph->setBitRenderLegend(false);
            $objGraph->setIntXAxisAngle(20);
    	    $arrReturn[] = $objGraph->renderGraph();

    		return $arrReturn;
        }
        else
            return "";
	}



    private function getTopQueries($intStart = false, $intEnd = false) {
        $strQuery = "SELECT search_log_query, COUNT(*) as hits
					  FROM "._dbprefix_."search_log
					  WHERE search_log_date >= ?
					    AND search_log_date <= ?
				   GROUP BY search_log_query
				   ORDER BY hits DESC";

        if($intStart !== false && $intEnd !== false)
            $arrReturn = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), $intStart, $intEnd);
        else
            $arrReturn = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), 0, _stats_nrofrecords_-1);

		return $arrReturn;
    }

    private function getTopQueriesCount() {
        $strQuery = "SELECT COUNT(DISTINCT(search_log_query)) as total
					  FROM "._dbprefix_."search_log
					  WHERE search_log_date >= ?
					    AND search_log_date <= ?";

        $arrReturn = $this->objDB->getPRow($strQuery, array($this->intDateStart, $this->intDateEnd));
		return $arrReturn["total"];
    }

	

}
