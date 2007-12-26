<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_stats_report_topqueries.php																	*
* 	Plugin to create a list of queries performed by the local search                                    *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

//Interface
include_once(_adminpath_."/interface_admin_statsreport.php");
include_once(_systempath_."/class_modul_search_log.php");

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

        $arrLogsRaw = $this->getLogbookData();
        $arrLogs = array();
        $intI = 0;
        foreach($arrLogsRaw as $intKey => $arrOneLog) {
            if($intI++ >= _stats_anzahl_liste_)
				break;

			$arrLogs[$intKey][0] = $intI;
            $arrLogs[$intKey][1] = timeToString($arrOneLog["search_log_date"]);
            $arrLogs[$intKey][2] = $arrOneLog["search_log_query"];
        }
    	//Create a data-table
    	$arrHeader = array();
        $arrHeader[0] = "#";
        $arrHeader[1] = $this->objTexts->getText("header_date", "search", "admin");
        $arrHeader[2] = $this->objTexts->getText("header_query", "search", "admin");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);

		return $strReturn;
	}

	/**
	 * Loads the records of the search-logbook
	 *
	 * @return mixed
	 */
	private function getLogbookData() {
		$strQuery = "SELECT search_log_date, search_log_query
					  FROM ".$this->arrModule["table"]."
					  WHERE search_log_date >= ".(int)$this->intDateStart."
					    AND search_log_date <= ".(int)$this->intDateEnd."
				   GROUP BY search_log_date
				   ORDER BY search_log_date DESC";

		$arrReturn = $this->objDB->getArraySection($strQuery, 0, _stats_anzahl_liste_-1);

		return $arrReturn;
	}

	public function getReportGraph() {
		return "";
	}

}
?>