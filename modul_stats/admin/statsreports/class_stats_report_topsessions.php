<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_stats_report_topsessions.php																	*
* 	Plugin to create topsessions                                                                        *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

//Interface
include_once(_adminpath_."/interface_admin_statsreport.php");

/**
 * This plugin creates a view showing infos about the sessions
 *
 * @package modul_stats
 */
class class_stats_report_topsessions implements interface_admin_statsreports {

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
		$this->arrModule["name"] 			= "modul_stats_reports_topsessions";
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
	    return  $this->objTexts->getText("topsessions", "stats", "admin");
	}

	public function getReportCommand() {
	    return "statsTopSessions";
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
		$arrSessions = $this->getTopSessions();

		$intI =0;
		foreach($arrSessions as $arrOneSession) {
			//Escape?
			if($intI >= _stats_anzahl_liste_)
				break;
            $arrValues[$intI] = array();
			$arrValues[$intI][] = $intI+1;
			$arrValues[$intI][] = $arrOneSession["stats_session"];
			$arrValues[$intI][] = $arrOneSession["dauer"];
			$arrValues[$intI][] = $arrOneSession["anzahl"];
			$arrValues[$intI][] = $arrOneSession["detail"];
			$intI++;
		}

		//HeaderRow, but this time a little more complex: we want to provide the possibility to sort the table
		$arrHeader[] = "#";
		$arrHeader[] = $this->objTexts->getText("top_session_titel", "stats", "admin");
		$arrHeader[] = getLinkAdmin("stats", $this->getReportCommand(), "&sort=time", $this->objTexts->getText("top_session_dauer", "stats", "admin"));
		$arrHeader[] = getLinkAdmin("stats", $this->getReportCommand(), "&sort=pages",$this->objTexts->getText("top_session_anzseiten", "stats", "admin"));
		$arrHeader[] = "";

		$strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

		return $strReturn;
	}

	/**
	 * Returns the pages and their hits
	 *
	 * @return mixed
	 */
	public function getTopSessions() {
	    //Build the order-string
	     $strOrder = "ORDER BY ";
	    if(getGet("sort") == "time" || getPost("sort") == "time")
	       $strOrder .= " dauer DESC";
	    elseif (getGet("sort") == "pages" || getPost("sort") == "pages") {
	       $strOrder .= " anzahl DESC ";
	    }
	    else {
	        $strOrder .= " stats_date DESC ";
	    }

	    $strOrder .= ", stats_date DESC ";


		$strQuery = "SELECT stats_session,
                              MIN(stats_date) as start,
                              MAX(stats_date) as end,
                              MAX(stats_date)-MIN(stats_date) as dauer,
                              stats_session,
                              stats_ip,
                              stats_hostname,
                              COUNT(*) as anzahl
                     FROM ".$this->arrModule["table"]."
                     WHERE stats_session != ''
                       AND stats_date >= ".(int)$this->intDateStart."
					   AND stats_date <= ".(int)$this->intDateEnd."
                     GROUP BY stats_session
                     ".$strOrder."
                     ";

        $arrSessions = $this->objDB->getArraySection($strQuery, 0, _stats_anzahl_liste_ -1);

        $intI = 0;
        foreach($arrSessions as $intKey => $arrOneSession) {
            if($intI++ >= _stats_anzahl_liste_)
				break;

            //Load the details for all sessions
            $strDetails = "";
            $strSessionID = $arrOneSession["stats_session"];
            $strDetails .= $this->objTexts->getText("top_session_detail_start", "stats", "admin"). timeToString($arrOneSession["start"]) ."<br />";
            $strDetails .= $this->objTexts->getText("top_session_detail_end", "stats", "admin"). timeToString($arrOneSession["end"]) ."<br />";
            $strDetails .= $this->objTexts->getText("top_session_detail_time", "stats", "admin"). ($arrOneSession["end"]-$arrOneSession["start"] )."<br />";
            $strDetails .= $this->objTexts->getText("top_session_detail_ip", "stats", "admin"). $arrOneSession["stats_ip"] ."<br />";
            $strDetails .= $this->objTexts->getText("top_session_detail_hostname", "stats", "admin"). $arrOneSession["stats_hostname"] ."<br />";
            //and fetch all pages
            $strQuery = "SELECT stats_page
                           FROM ".$this->arrModule["table"]."
                          WHERE stats_session='".$strSessionID."'
                          ORDER BY stats_date ASC";

            $arrPages = $this->objDB->getArray($strQuery);

            $strDetails .= $this->objTexts->getText("top_session_detail_verlauf", "stats", "admin");
            foreach($arrPages as $arrOnePage)
                $strDetails .= $arrOnePage["stats_page"] ." - ";

            $strDetails = uniSubstr($strDetails, 0, -2);
            $arrFolder = $this->objToolkit->getLayoutFolder($strDetails, $this->objTexts->getText("top_session_detail", "stats", "admin"));
            $arrSessions[$intKey]["detail"] = $arrFolder[1].$arrFolder[0];
        }

		return $arrSessions;
	}

	public function getReportGraph() {
		return "";
	}

}
?>