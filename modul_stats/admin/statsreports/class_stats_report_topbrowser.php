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
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package modul_stats
 */
class class_stats_report_topbrowser implements interface_admin_statsreports {

	//class vars
	private $intDateStart;
	private $intDateEnd;
	private $intInterval = 1;

	private $objTexts;
	private $objToolkit;
	private $objDB;

	private $arrModule;

	private $arrParentCache = array();
	private $arrBrowserCache = array();
	private $arrBrowserGiven = array();
	private $arrBrowserGiven2 = array();

	/**
	 * Constructor
	 *
	 */
	public function __construct($objDB, $objToolkit, $objTexts) {
		$this->arrModule["name"] 			= "modul_stats_reports_topbrowser";
		$this->arrModule["author"] 			= "sidler@mulchprod.de";
		$this->arrModule["moduleId"] 		= _stats_modul_id_;
		$this->arrModule["table"] 		    = _dbprefix_."stats_data";
		$this->arrModule["modul"]			= "stats";

		$this->objTexts = $objTexts;
		$this->objToolkit = $objToolkit;
		$this->objDB = $objDB;


		//parse browser (php_browscap.ini)
		$arrBrowserGiven = parse_ini_file(_systempath_."/php_browscap.ini", true);

		//Update Array once to handle regex
		$arrSearch =  array(".", "+", "^", "$", "!", "{", "}", "(", ")", "]", "[", "*", "?", "#");
		$arrReplace = array("\.", "\+", "\^", "\$", "\!", "\{", "\}", "\(", "\)", "\]", "\[", ".*", ".", "\#");

		foreach($arrBrowserGiven as $strSignatureGiven => $arrBrowserData) 	{
			$strSignature = str_replace($arrSearch, $arrReplace, $strSignatureGiven);
			$arrBrowserGiven2[$strSignature] = $arrBrowserData;
		}
		$this->arrBrowserGiven = $arrBrowserGiven;
		$this->arrBrowserGiven2 = $arrBrowserGiven2;

	}

	public function setEndDate($intEndDate) {
	    $this->intDateEnd = $intEndDate;
	}

	public function setStartDate($intStartDate) {
	    $this->intDateStart = $intStartDate;
	}

	public function getReportTitle() {
	    return  $this->objTexts->getText("topbrowser", "stats", "admin");
	}

	public function getReportCommand() {
	    return "statsTopBrowser";
	}

	public function isIntervalable() {
	    return true;
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
		$arrStats = $this->getTopBrowser();

		//calc a few values
		$intSum = 0;
		foreach($arrStats as $arrOneStat)
			$intSum += $arrOneStat;

		$intI =0;
		foreach($arrStats as $strName => $arrOneStat) {
			//Escape?
			if($intI >= _stats_nrofrecords_)
				break;

            $arrValues[$intI] = array();
			$arrValues[$intI][] = $intI+1;
			$arrValues[$intI][] = $strName;
			$arrValues[$intI][] = $arrOneStat;
			$arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat / $intSum*100);
			$intI++;
		}
		//HeaderRow
		$arrHeader[] = "#";
		$arrHeader[] = $this->objTexts->getText("top_browser_titel", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("top_browser_gewicht", "stats", "admin");
		$arrHeader[] = $this->objTexts->getText("anteil", "stats", "admin");

		$strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

		return $strReturn;
	}

/**
	 * Returns a array of top browsers
	 *
	 * @return mixed
	 */
	public function getTopBrowser() {
		$arrReturn = array();


		//load Data
		$strQuery = "SELECT stats_browser, count(*) as anzahl
						FROM ".$this->arrModule["table"]."
						WHERE stats_date >= ".(int)$this->intDateStart."
							AND stats_date <= ".(int)$this->intDateEnd."
						GROUP BY stats_browser";
		$arrBrowser = $this->objDB->getArray($strQuery);

		$arrBrowserGiven = &$this->arrBrowserGiven;
		$arrBrowserGiven2 = &$this->arrBrowserGiven2;

        //way of doing: search the longest match. in 99% this is the most specific match

        //Search the best matching pattern
        foreach($arrBrowser as $arrRow) {
            $strPrevMatchingSignature = "";
            //Browser already found before?
            $strBrowserSign = $arrRow["stats_browser"];
            if(!isset($this->arrBrowserCache[$arrRow["stats_browser"]])) {
    			//Lookup in browsers
    			foreach($arrBrowserGiven2 as $strSignature => $arrBrowserData) {
                    //Current browser matching the browscap signature?

    				if(preg_match("#".$strSignature."#", $arrRow["stats_browser"])) {

    				    //better match then the one before?
    				    if(uniStrlen($strPrevMatchingSignature) <= uniStrlen($strSignature)) {
    				        //yes, save for next run
    				        $strPrevMatchingSignature = $strSignature;
    				    }
    				}
    			}

    			$arrCurrentBrowser = $arrBrowserGiven2[$strPrevMatchingSignature];
    			//parent browser already looked up?
                $arrParentBrowser = array();
    			//search the parent of the current browser
    			if(isset($arrCurrentBrowser["Parent"])) {
    		        if(!isset($this->arrParentCache[$arrCurrentBrowser["Parent"]])) {
        				if(isset($arrBrowserGiven[$arrCurrentBrowser["Parent"]])) {
        					$arrParentBrowser = $arrBrowserGiven[$arrCurrentBrowser["Parent"]];
        					$this->arrParentCache[$arrCurrentBrowser["Parent"]] = $arrParentBrowser;
        				}
        			}
        			else {
        			    $arrParentBrowser = $this->arrParentCache[$arrCurrentBrowser["Parent"]];
        			}
    			}


    			//create the hit-entry
    			//search the version and the browsername
    			$strVersion = (isset($arrParentBrowser["Version"]) ? $arrParentBrowser["Version"] : (isset($arrCurrentBrowser["Version"]) ? $arrCurrentBrowser["Version"] : ""));
    			$strBrower = (isset($arrParentBrowser["Browser"]) ? $arrParentBrowser["Browser"] : (isset($arrCurrentBrowser["Browser"]) ? $arrCurrentBrowser["Browser"] : ""));

    			$strMatchingBrowser = $strBrower." ".$strVersion;

    			//cache browser
    			$this->arrBrowserCache[$strBrowserSign] = $strMatchingBrowser;
            }
            else
                $strMatchingBrowser = $this->arrBrowserCache[$strBrowserSign];

			if(!isset($arrReturn[$strMatchingBrowser]))
				$arrReturn[$strMatchingBrowser] = $arrRow["anzahl"];
			else
				$arrReturn[$strMatchingBrowser] += $arrRow["anzahl"];


        }

		arsort($arrReturn);
		return $arrReturn;
	}

	public function getReportGraph() {
	    $arrReturn = array();

        include_once(_systempath_."/class_graph_pchart.php");

        //--- PIE-GRAPH ---------------------------------------------------------------------------------
        $arrData = $this->getTopBrowser();

        $intSum = 0;
		foreach($arrData as $arrOneStat)
			$intSum += $arrOneStat;

        $arrKeyValues = array();
        //max 6 entries
        $intCount = 0;
        $floatPercentageSum = 0;
        $arrValues = array();
        $arrLabels = array();
        foreach($arrData as $strName => $arrOneBrowser) {
            if(++$intCount <= 6) {
                $floatPercentage = $arrOneBrowser / $intSum*100;
                $floatPercentageSum += $floatPercentage;
                $arrKeyValues[$strName] = $floatPercentage;
                $arrValues[] = $floatPercentage;
                $arrLabels[] = $strName;
            }
            else {
                break;
            }
        }
        //add "others" part?
        if($floatPercentageSum < 99) {
            $arrKeyValues["others"] = 100-$floatPercentageSum;
            $arrLabels[] = "others";
            $arrValues[] =  100-$floatPercentageSum;
        }
        if(count($arrKeyValues) > 0) {
            $objGraph = new class_graph_pchart();
            $objGraph->createPieChart($arrValues, $arrLabels);
            $strFilename = "/portal/pics/cache/stats_topbrowser.png";
            $objGraph->saveGraph($strFilename);
    		$arrReturn[] = _webpath_.$strFilename;
        }

		//--- XY-Plot -----------------------------------------------------------------------------------
		//calc number of plots
		$arrPlots = array();
		$arrTickLabels = array();
		foreach($arrKeyValues as $strBrowser => $arrData) {
		    if($strBrowser != "others")
    		    $arrPlots[$strBrowser] = array();
		}

		$intGlobalEnd = $this->intDateEnd;
		$intGlobalStart = $this->intDateStart;

		$this->intDateEnd = $this->intDateStart + 60*60*24*$this->intInterval;

		$intCount = 0;
		while($this->intDateStart <= $intGlobalEnd) {
		    $arrBrowserData = $this->getTopBrowser();
		    //init plot array for this period
		    $arrTickLabels[$intCount] = date("d.m.", $this->intDateStart);
		    foreach($arrPlots as $strBrowser => &$arrOnePlot) {
		        $arrOnePlot[$intCount] = 0;
		        if(key_exists($strBrowser, $arrBrowserData)) {
		            $arrOnePlot[$intCount] = (int)$arrBrowserData[$strBrowser];
		        }

		    }
		    //increase start & end-date
		    $this->intDateStart = $this->intDateEnd;
		    $this->intDateEnd = $this->intDateStart + 60*60*24*$this->intInterval;
		    $intCount++;
		}
		//create graph
		if(count($arrTickLabels) > 1 && count($arrPlots) > 0) {
    		$objGraph = new class_graph_pchart();
    		foreach($arrPlots as $arrPlotName => $arrPlotData) {
    		    $objGraph->addLinePlot($arrPlotData, $arrPlotName);
    		}
            $objGraph->setArrXAxisTickLabels($arrTickLabels);
    		$strFilename = "/portal/pics/cache/stats_topbrowser_plot.png";
            $objGraph->saveGraph($strFilename);
    		$arrReturn[] = _webpath_.$strFilename;
		}
		//reset global dates
		$this->intDateEnd = $intGlobalEnd;
		$this->intDateStart = $intGlobalStart;

	    return $arrReturn;
	}

}
?>