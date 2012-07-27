<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * This plugin show the list of download, served by the downloads-module
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_stats_report_downloads implements interface_admin_statsreports {

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
	    return  $this->objTexts->getLang("stats_title", "mediamanager");
	}

	public function getReportCommand() {
	    return "statsDownloads";
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
            if($intI++ >= _stats_nrofrecords_)
				break;

			$arrLogs[$intKey][0] = $intI;
            $arrLogs[$intKey][1] = $arrOneLog["downloads_log_id"];
            $arrLogs[$intKey][2] = timeToString($arrOneLog["downloads_log_date"]);
            $arrLogs[$intKey][3] = $arrOneLog["downloads_log_file"];
            $arrLogs[$intKey][4] = $arrOneLog["downloads_log_user"];
            $arrLogs[$intKey][5] = ($arrOneLog["stats_hostname"] != null ? $arrOneLog["stats_hostname"] : $arrOneLog["downloads_log_ip"]);
        }
    	//Create a data-table
    	$arrHeader = array();
        $arrHeader[0] = "#";
        $arrHeader[1] = $this->objTexts->getLang("header_id", "mediamanager");
        $arrHeader[2] = $this->objTexts->getLang("commons_date", "mediamanager");
        $arrHeader[3] = $this->objTexts->getLang("header_file", "mediamanager");
        $arrHeader[4] = $this->objTexts->getLang("header_user", "mediamanager");
        $arrHeader[5] = $this->objTexts->getLang("header_ip", "mediamanager");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);

		return $strReturn;
	}

	/**
	 * Loads the records of the dl-logbook
	 *
	 * @return mixed
	 */
	private function getLogbookData() {
		$strQuery = "SELECT *
					  FROM "._dbprefix_."mediamanager_dllog
					  WHERE downloads_log_date >= ?
							AND downloads_log_date <= ?
					  ORDER BY downloads_log_date DESC";

		$arrReturn = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), 0, (_stats_nrofrecords_-1));

		foreach ($arrReturn as &$arrOneRow) {
    		//Load hostname, if available. faster, then mergin per LEFT JOIN
    		$arrOneRow["stats_hostname"] = null;
    		$strQuery = "SELECT stats_hostname
    		             FROM "._dbprefix_."stats_data
    		             WHERE stats_ip = ?
    		             GROUP BY stats_hostname";
    		$arrRow = $this->objDB->getPRow($strQuery, array($arrOneRow["downloads_log_ip"]));
    		if(isset($arrRow["stats_hostname"]))
    		    $arrOneRow["stats_hostname"] = $arrRow["stats_hostname"];

		}

		return $arrReturn;
	}

	public function getReportGraph() {
		return "";
	}

}
