<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/

//Interface
include_once(_adminpath_."/interface_admin_statsreport.php");

/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package modul_stats
 */
class class_stats_report_topqueries implements interface_admin_statsreports {

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
		$this->arrModule["name"] 			= "modul_stats_reports_topqueries";
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
	    return  $this->objTexts->getText("topqueries", "stats", "admin");
	}

	public function getReportCommand() {
	    return "statsTopQueries";
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
		$arrStats = $this->getTopQueries();

		//calc a few values
		$intSum = 0;
		foreach($arrStats as $intHits)
			$intSum += $intHits;

		$intI =0;
		foreach($arrStats as $strKey =>  $intHits) {
			//Escape?
			if($intI >= _stats_anzahl_liste_)
				break;
            $arrValues[$intI] = array();
			$arrValues[$intI][] = $intI+1;
			$arrValues[$intI][] = urldecode($strKey);
			$arrValues[$intI][] = $intHits;
			$arrValues[$intI][] = $this->objToolkit->percentBeam($intHits / $intSum*100);
			$intI++;
		}
		//HeaderRow
		$arrHeader[] = "#";
		$arrHeader[] = $this->objTexts->getText("top_query_titel", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("top_query_gewicht", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("anteil", "stats", "admin");

		$strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

		return $strReturn;
	}

	/**
	 * Returns the list of top-queries
	 *
	 * @return mixed
	 */
	public function getTopQueries() {
	    //Load all records in the passed interval
	    $arrBlocked = explode(",", _stats_ausschluss_);

	    $strExclude = "";
			foreach($arrBlocked as $strBlocked)
			    $strExclude .= " AND stats_referer NOT LIKE '%".str_replace("%", "\%", dbsafeString($strBlocked))."%' \n";

		$strQuery = "SELECT stats_referer
						FROM ".$this->arrModule["table"]."
						WHERE stats_date >= ".(int)$this->intDateStart."
						  AND stats_date <= ".(int)$this->intDateEnd."
						  AND stats_referer != ''
						  AND stats_referer IS NOT NULL
						    ".$strExclude."
						ORDER BY stats_date desc";
		$arrRecords =  $this->objDB->getArray($strQuery);

		$arrHits = array();
		//Suchpatterns: q=, query=
		$arrQuerypatterns = array ( "q=", "query=");
		foreach($arrRecords as $arrOneRecord) {
		    foreach($arrQuerypatterns as $strOnePattern) {
		        if(uniStrpos($arrOneRecord["stats_referer"], $strOnePattern) !== false) {
		            $strQueryterm = uniSubstr($arrOneRecord["stats_referer"], (uniStrpos($arrOneRecord["stats_referer"], $strOnePattern)+uniStrlen($strOnePattern)));
    		        $strQueryterm = uniSubstr($strQueryterm, 0, uniStrpos($strQueryterm, "&"));
    		        if($strQueryterm != "") {
    		            if(isset($arrHits[$strQueryterm]))
    		                $arrHits[$strQueryterm]++;
    		            else
    		                $arrHits[$strQueryterm] = 1;
		            }
		            break;
		        }
		    }
		}
		arsort($arrHits);
		return $arrHits;
	}

	public function getReportGraph() {
        //collect data
        $arrPages = $this->getTopQueries();

		$arrGraphData = array();
		$intCount = 1;
		foreach ($arrPages as $strName => $intHits) {
		    $arrGraphData[$intCount] = $intHits;
		    if($intCount++ >= 8)
		      break;
		}


	    //generate a bar-chart
	    if(count($arrGraphData) > 1) {
    	    include_once(_systempath_."/class_graph.php");
    	    $objGraph = new class_graph();
    	    $objGraph->createBarChart($arrGraphData, 715, 200, false);
    	    $objGraph->setXAxisLabelAngle(0);
    	    $objGraph->setStrXAxisTitle($this->objTexts->getText("top_query_titel", "stats", "admin"));
    	    $objGraph->setStrYAxisTitle($this->objTexts->getText("top_query_gewicht", "stats", "admin"));
    	    $strFilename = "/portal/pics/cache/stats_topqueries.png";
    	    $objGraph->saveGraph($strFilename);
    		return _webpath_.$strFilename;
	    }
	    else
	       return "";
	}

}
?>