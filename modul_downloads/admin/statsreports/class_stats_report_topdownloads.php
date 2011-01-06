<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * This plugin show the list of top download, served by the downloads-module
 *
 * @package modul_downloads
 */
class class_stats_report_topdownloads implements interface_admin_statsreports {

	//class vars
	private $intDateStart;
	private $intDateEnd;
	private $intInterval;

	private $objTexts;
	private $objToolkit;
	private $objDB;

	private $arrModule;

	/**
	 * Constructor
	 *
	 */
	public function __construct($objDB, $objToolkit, $objTexts) {
		$this->arrModule["name"] 			= "modul_stats_reports_topdownloads";
		$this->arrModule["author"] 			= "sidler@mulchprod.de";
		$this->arrModule["moduleId"] 		= _downloads_modul_id_;
		$this->arrModule["table"] 		    = _dbprefix_."downloads_log";
		$this->arrModule["modul"]			= "downloads";

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
	    return  $this->objTexts->getText("stats_toptitle", "downloads", "admin");
	}

	public function getReportCommand() {
	    return "statsTopDownloads";
	}

	public function isIntervalable() {
	    return true;
	}

	public function setInterval($intInterval) {
        $this->intInterval = $intInterval;
	}

	public function getReport() {
	    $strReturn = "";

        $arrLogsRaw = $this->getLogbookData();
        $arrLogs = array();
        $intI = 0;
        foreach($arrLogsRaw as $intKey => $arrOneLog) {
            if($intI++ >= _stats_nrofrecords_)
				break;

			$arrLogs[$intKey][0] = $intI;
            $arrLogs[$intKey][1] = $arrOneLog["downloads_log_file"];
            $arrLogs[$intKey][2] = $arrOneLog["amount"];
        }
    	//Create a data-table
    	$arrHeader = array();
        $arrHeader[0] = "#";
        $arrHeader[1] = $this->objTexts->getText("header_file", "downloads", "admin");
        $arrHeader[2] = $this->objTexts->getText("header_amount", "downloads", "admin");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);

		return $strReturn;
	}

	/**
	 * Loads the records of the dl-logbook
	 *
	 * @return mixed
	 */
	private function getLogbookData() {
		$strQuery = "SELECT COUNT(*) as amount, downloads_log_file
					  FROM ".$this->arrModule["table"]."
					  WHERE downloads_log_date >= ".(int)$this->intDateStart."
							AND downloads_log_date <= ".(int)$this->intDateEnd."
					  GROUP BY downloads_log_file
					  ORDER BY amount DESC";

		return $this->objDB->getArraySection($strQuery, 0, _stats_nrofrecords_ -1);
	}

	public function getReportGraph() {
	    $arrReturn = array();
	    //generate a graph showing dls per interval
	    //--- XY-Plot -----------------------------------------------------------------------------------
		//calc number of plots
        $arrPlots = array();
		$intCount = 1;
		$arrDownloads = $this->getLogbookData();
		if(count($arrDownloads) > 0) {
    		foreach ($arrDownloads as $intKey => $arrOneDownload) {
    		    if($intCount++ <= 4)
    		        $arrPlots[$arrOneDownload["downloads_log_file"]] = array();
    		    else
    		        break;

    		}

    		$arrTickLabels = array();

    		$intGlobalEnd = $this->intDateEnd;
    		$intGlobalStart = $this->intDateStart;

    		$this->intDateEnd = $this->intDateStart + 60*60*24*$this->intInterval;

    		$intCount = 0;
    		while($this->intDateStart <= $intGlobalEnd) {
    		    $arrDownloads = $this->getLogbookData();
    		    //init plot array for this period
    		    $arrTickLabels[$intCount] = date("d.m.", $this->intDateStart);
    		    foreach($arrPlots as $strFile => &$arrOnePlot) {
    		        $arrOnePlot[$intCount] = 0;
    		        foreach ($arrDownloads as $intKey => $arrOneDownload) {
    		            if($arrOneDownload["downloads_log_file"] == $strFile) {
    		                $arrOnePlot[$intCount] += $arrOneDownload["amount"];
    		            }
    		        }
    		    }
    		    //increase start & end-date
    		    $this->intDateStart = $this->intDateEnd;
    		    $this->intDateEnd = $this->intDateStart + 60*60*24*$this->intInterval;
    		    $intCount++;
    		}
    		//create graph
    		if($intCount > 1) {
        		$objGraph = new class_graph_pchart();
        		
        		foreach($arrPlots as $arrPlotName => $arrPlotData) {
        		    $objGraph->addLinePlot($arrPlotData, $arrPlotName);
        		}
                $objGraph->setArrXAxisTickLabels($arrTickLabels);
        		$strFilename = "/portal/pics/cache/stats_topdownloads_plot.png";
                $objGraph->saveGraph($strFilename);
        		$arrReturn[] = _webpath_.$strFilename;
    		}
    		//reset global dates
    		$this->intDateEnd = $intGlobalEnd;
    		$this->intDateStart = $intGlobalStart;
    	}
		return $arrReturn;
	}

}
?>