<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_stats_report_topsessions.php 4141 2011-10-17 15:53:15Z sidler $                           *
********************************************************************************************************/


/**
 * This plugin creates a view showing infos about the sessions
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class class_stats_report_topsessions implements interface_admin_statsreports {

	//class vars
	private $intDateStart;
	private $intDateEnd;

	private $objTexts;
	private $objToolkit;
    
    /**
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
	    return  $this->objTexts->getLang("topsessions", "stats", "admin");
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
			if($intI >= _stats_nrofrecords_)
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
		$arrHeader[] = $this->objTexts->getLang("top_session_titel", "stats", "admin");
		$arrHeader[] = getLinkAdmin("stats", $this->getReportCommand(), "&sort=time", $this->objTexts->getLang("top_session_dauer", "stats", "admin"));
		$arrHeader[] = getLinkAdmin("stats", $this->getReportCommand(), "&sort=pages",$this->objTexts->getLang("top_session_anzseiten", "stats", "admin"));
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
                     FROM "._dbprefix_."stats_data
                     WHERE stats_session != ''
                       AND stats_date >= ?
					   AND stats_date <= ?
                     GROUP BY stats_session
                     ".$strOrder."
                     ";

        $arrSessions = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), 0, _stats_nrofrecords_ -1);

        $intI = 0;
        foreach($arrSessions as $intKey => $arrOneSession) {
            if($intI++ >= _stats_nrofrecords_)
				break;

            //Load the details for all sessions
            $strDetails = "";
            $strSessionID = $arrOneSession["stats_session"];
            $strDetails .= $this->objTexts->getLang("top_session_detail_start", "stats", "admin"). timeToString($arrOneSession["start"]) ."<br />";
            $strDetails .= $this->objTexts->getLang("top_session_detail_end", "stats", "admin"). timeToString($arrOneSession["end"]) ."<br />";
            $strDetails .= $this->objTexts->getLang("top_session_detail_time", "stats", "admin"). ($arrOneSession["end"]-$arrOneSession["start"] )."<br />";
            $strDetails .= $this->objTexts->getLang("top_session_detail_ip", "stats", "admin"). $arrOneSession["stats_ip"] ."<br />";
            $strDetails .= $this->objTexts->getLang("top_session_detail_hostname", "stats", "admin"). $arrOneSession["stats_hostname"] ."<br />";
            //and fetch all pages
            $strQuery = "SELECT stats_page
                           FROM ".$this->arrModule["table"]."
                          WHERE stats_session= ?
                          ORDER BY stats_date ASC";

            $arrPages = $this->objDB->getPArray($strQuery, array($strSessionID));

            $strDetails .= $this->objTexts->getLang("top_session_detail_verlauf", "stats", "admin");
            foreach($arrPages as $arrOnePage)
                $strDetails .= $arrOnePage["stats_page"] ." - ";

            $strDetails = uniSubstr($strDetails, 0, -2);
            $arrFolder = $this->objToolkit->getLayoutFolder($strDetails, $this->objTexts->getLang("top_session_detail", "stats", "admin"));
            $arrSessions[$intKey]["detail"] = $arrFolder[1].$arrFolder[0];
        }

		return $arrSessions;
	}

	public function getReportGraph() {
		return "";
	}

}
