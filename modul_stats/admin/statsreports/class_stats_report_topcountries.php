<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/

//Interface
include_once(_adminpath_."/interface_admin_statsreport.php");

/**
 * This plugin creates a list of countries the visitors come from
 *
 * @package modul_stats
 */
class class_stats_report_topcountries implements interface_admin_statsreports {

	//class vars
	private $intDateStart;
	private $intDateEnd;
	private $intInterval;

	private $objTexts;
	private $objToolkit;
	private $objDB;

	private $arrModule;

	private $arrHits = null;

	/**
	 * Constructor
	 *
	 */
	public function __construct($objDB, $objToolkit, $objTexts) {
		$this->arrModule["name"] 			= "modul_stats_reports_topcountries";
		$this->arrModule["author"] 			= "sidler@mulchprod.de";
		$this->arrModule["moduleId"] 		= _stats_modul_id_;
		$this->arrModule["table"] 		    = _dbprefix_."stats_data";
		$this->arrModule["table2"] 		    = _dbprefix_."stats_ip2country";
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
	    return  $this->objTexts->getText("topcountries", "stats", "admin");
	}

	public function getReportCommand() {
	    return "statsTopCountries";
	}

	public function isIntervalable() {
	    return false;
	}

	public function setInterval($intInterval) {
	    $this->intInterval = $intInterval;
	}

	public function getReport() {
	    $strReturn = "";

        //Create Data-table
        $arrHeader = array();
        $arrValues = array();
        //Fetch data
		$arrStats = $this->getTopCountries();

		//calc a few values
		$intSum = 0;
		foreach($arrStats as $arrOneStat)
			$intSum += $arrOneStat;

		$intI =0;
		foreach($arrStats as $strCountry => $arrOneStat) {
			//Escape?
			if($intI >= _stats_nrofrecords_)
				break;

            $arrValues[$intI] = array();
			$arrValues[$intI][] = $intI+1;
			$arrValues[$intI][] = $strCountry;
			$arrValues[$intI][] = $arrOneStat;
			$arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat / $intSum*100);
			$intI++;
		}
		//HeaderRow
		$arrHeader[] = "#";
		$arrHeader[] = $this->objTexts->getText("top_country_titel", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("top_country_gewicht", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("anteil", "stats", "admin");

		$strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

		return $strReturn;
	}



    /**
     * Loads a list of systems accessed the page
     *
     * @return mixed
     */
	public function getTopCountries() {
		$arrReturn = array();

		if($this->arrHits != null)
		    return $this->arrHits;

		$strQuery = "SELECT stats_ip, count(*) as anzahl
						FROM ".$this->arrModule["table"]."
						WHERE stats_date >= ".(int)$this->intDateStart."
						  AND stats_date <= ".(int)$this->intDateEnd."
						GROUP BY stats_ip";

		$arrTemp = $this->objDB->getArray($strQuery);

		$intCounter = 0;
		foreach ($arrTemp as $arrOneRecord) {

		    $strQuery = "SELECT ip2c_name as country_name
						   FROM ".$this->arrModule["table2"]."
						  WHERE ip2c_ip = '".$arrOneRecord["stats_ip"]."'";

		    $arrRow = $this->objDB->getRow($strQuery);

            if(!isset($arrRow["country_name"]))
                $arrRow["country_name"] = "n.a.";

		    if(isset($arrReturn[$arrRow["country_name"]]))
		        $arrReturn[$arrRow["country_name"]] += $arrOneRecord["anzahl"];
		    else
		        $arrReturn[$arrRow["country_name"]] = $arrOneRecord["anzahl"];

		    //flush query cache every 2000 hits
		    if($intCounter++ >= 2000)    
		        $this->objDB->flushQueryCache();

		}

		arsort($arrReturn);
		$this->arrHits = $arrReturn;
		return $arrReturn;
	}

	public function getReportGraph() {
	    $arrReturn = array();

        include_once(_systempath_."/class_graph.php");
        $arrData = $this->getTopCountries();

        $intSum = 0;
		foreach($arrData as $arrOneStat)
			$intSum += $arrOneStat;

        $arrKeyValues = array();
        //max 6 entries
        $intCount = 0;
        $floatPercentageSum = 0;
        foreach($arrData as $strName => $intOneSystem) {
            if(++$intCount <= 6) {
                $floatPercentage = $intOneSystem / $intSum*100;
                $floatPercentageSum += $floatPercentage;
                $arrKeyValues[$strName] = $floatPercentage;
            }
            else {
                break;
            }
        }
        //add "others" part?
        if($floatPercentageSum < 99) {
            $arrKeyValues["others"] = 100-$floatPercentageSum;
        }
        $objGraph = new class_graph();
        $objGraph->create3DPieChart($arrKeyValues, 715, 200);
        $strFilename = "/portal/pics/cache/stats_topcountries.png";
        $objGraph->saveGraph($strFilename);
		$arrReturn[] = _webpath_.$strFilename;


		return $arrReturn;
	}
}
?>