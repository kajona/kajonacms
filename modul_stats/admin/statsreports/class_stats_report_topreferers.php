<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_stats_report_topreferers.php																	*
* 	Plugin to create topreferers                                                                        *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

//Interface
include_once(_adminpath_."/interface_admin_statsreport.php");

/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package modul_stats
 */
class class_stats_report_topreferers implements interface_admin_statsreports {

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
		$this->arrModule["name"] 			= "modul_stats_reports_topreferers";
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
	    return  $this->objTexts->getText("topreferer", "stats", "admin");
	}

	public function getReportCommand() {
	    return "statsTopReferer";
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
		$arrStats = $this->getTopReferer();

		//calc a few values
		$intSum = 0;
		foreach($arrStats as $arrOneStat)
			$intSum += $arrOneStat["anzahl"];

		$intI =0;
		foreach($arrStats as $arrOneStat) {
			//Escape?
			if($intI >= _stats_anzahl_liste_)
				break;

			if($arrOneStat["referer"] == "")
				$arrOneStat["referer"] =  $this->objTexts->getText("referer_direkt", "stats", "admin");
			else
				$arrOneStat["referer"] = getLinkPortal("", $arrOneStat["referer"], "_blank", uniStrTrim($arrOneStat["referer"], 45));

            $arrValues[$intI] = array();
			$arrValues[$intI][] = $intI+1;
			$arrValues[$intI][] = $arrOneStat["referer"];
			$arrValues[$intI][] = $arrOneStat["anzahl"];
			$arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat["anzahl"] / $intSum*100);
			$intI++;
		}
		//HeaderRow
		$arrHeader[] = "#";
		$arrHeader[] = $this->objTexts->getText("top_referer_titel", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("top_referer_gewicht", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("anteil", "stats", "admin");

		$strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);


		return $strReturn;
	}

	/**
	 * returns a list of top-referer
	 *
	 * @return mixed
	 */
	public function getTopReferer() {
	    //Build excluded domains
	    $arrBlocked = explode(",", _stats_ausschluss_);

	    $strExclude = "";
			foreach($arrBlocked as $strBlocked)
			    if($strBlocked != "")
			        $strExclude .= " AND stats_referer NOT LIKE '%".str_replace("%", "\%", $strBlocked)."%' \n";

		$strQuery = "SELECT stats_referer as referer, count(*) as anzahl
						FROM ".$this->arrModule["table"]."
						WHERE stats_referer NOT LIKE '%".str_replace("%", "\%", _webpath_)."%'
							AND stats_date >= ".(int)$this->intDateStart."
							AND stats_date <= ".(int)$this->intDateEnd."
							".$strExclude."
						GROUP BY referer
						ORDER BY anzahl desc";

		return $this->objDB->getArraySection($strQuery, 0, _stats_anzahl_liste_ -1);
	}

	public function getReportGraph() {
		return "";
	}

}
?>