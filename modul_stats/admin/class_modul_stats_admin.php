<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/
//Base class
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_admin.php");
//model
include_once(_systempath_."/class_modul_stats_worker.php");

/**
 * Admin-Part of the stats, generating all reports an handles requests to workers
 *
 * @package modul_stats
 */
class class_modul_stats_admin extends class_admin implements interface_admin {

	//class vars
	private $intDateStart;
	private $intDateEnd;
	private $intInterval;
	
	private $strIp2cServer = "ip2c.kajona.de";

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 				= "modul_stats";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _stats_modul_id_;
		$arrModul["table"] 		     	= _dbprefix_."stats_data";
		$arrModul["modul"]				= "stats";

		//base class
		parent::__construct($arrModul);

		 //Start: first day of current month
		$this->intDateStart = strtotime(strftime("%Y-%m",time())."-01");
		//End: Current Day of month
		$this->intDateEnd = time()+86400;

		//Write start & end date to the params array
		$arrStart = explode(".", date("d.m.Y", $this->intDateStart));
		$arrEnd = explode(".", date("d.m.Y", $this->intDateEnd));
		if($this->getParam("filter") == "") {
			$this->setParam("start_datum_tag", $arrStart[0]);
			$this->setParam("start_datum_monat", $arrStart[1]);
			$this->setParam("start_datum_jahr", $arrStart[2]);
			$this->setParam("ende_datum_tag", $arrEnd[0]);
			$this->setParam("ende_datum_monat", $arrEnd[1]);
			$this->setParam("ende_datum_jahr", $arrEnd[2]);
		}

		//stats may take time -> increase the time available
        @ini_set("max_execution_time", "500");

        //stats may consume a lot of memory, increase max mem limit
        if (class_carrier::getInstance()->getObjConfig()->getPhpIni("memory_limit") < 30)
			@ini_set("memory_limit", "30M");
	}

	/**
	 * Action-block invoking all later actions
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
		$strReturn = "";
		if($strAction == "")
		    $strAction = "statsCommon";

		if($strAction == "worker") {
		    //Run the workers
		    $strReturn .= $this->actionWorker();
		}
		else {
		    //In every case, we should generate the date-selector
            $strReturn .= $this->processDates();

            //And now we have to load the requested plugin
            $strReturn .= $this->loadRequestedPlugin($strAction);
		}
		$this->strOutput = $strReturn;
	}


	public function getOutputContent() {
		return $this->strOutput;
	}

	public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("modul_rechte"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right1",getLinkAdmin("stats", "worker", "",  $this->getText("modul_worker"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        //Load all plugins available and create the navigation
        include_once(_systempath_."/class_filesystem.php");
        $objFilesystem = new class_filesystem();
        $arrPlugins = $objFilesystem->getFilelist(_adminpath_."/statsreports", ".php");

        foreach($arrPlugins as $strOnePlugin) {
            include_once(_adminpath_."/statsreports/".$strOnePlugin);
            $strClassName = str_replace(".php", "", $strOnePlugin);

            $objPlugin = new $strClassName($this->objDB, $this->objToolkit, $this->getObjText());
            if($objPlugin instanceof interface_admin_statsreports && $this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"])))
                $arrReturn[] = array("", getLinkAdmin($this->arrModule["modul"], $objPlugin->getReportCommand(), "", $objPlugin->getReportTitle(), "", "", true, "adminnavi"));
        }

        return $arrReturn;
	}


// --- Allgemein ----------------------------------------------------------------------------------------

    /**
     * Loads the given plugin, i.e. the given report.
     * Creates an instance, passes control an returns parsed data
     *
     * @param string $strPlugin
     * @return string
     */
    private function loadRequestedPlugin($strPlugin) {
        $strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

            include_once(_systempath_."/class_filesystem.php");
            $objFilesystem = new class_filesystem();
            $arrPlugins = $objFilesystem->getFilelist(_adminpath_."/statsreports", ".php");

            foreach($arrPlugins as $strOnePlugin) {
                include_once(_adminpath_."/statsreports/".$strOnePlugin);
                $strClassName = str_replace(".php", "", $strOnePlugin);
                $objPlugin = new $strClassName($this->objDB, $this->objToolkit, $this->getObjText());

                if($objPlugin->getReportCommand() == $strPlugin && $objPlugin instanceof interface_admin_statsreports) {
                    $objPlugin->setEndDate($this->intDateEnd);
                    $objPlugin->setStartDate($this->intDateStart);
                    $objPlugin->setInterval($this->intInterval);

                    $arrImage = $objPlugin->getReportGraph();

                    if(!is_array($arrImage))
                        $arrImage = array($arrImage);
                    foreach($arrImage as $strImage) {
                        if($strImage != "") {
                    	   $strReturn .= $this->objToolkit->getGraphContainer($strImage."?reload=".time());
                        }
                    }


                    $strReturn .= $objPlugin->getReport();
                    //place date-selctor before
                    $strReturn = $this->createDateSelector($objPlugin).$strReturn;
                }
            }
        }
		else
			$strReturn = $this->getText("fehler_recht");

        return $strReturn;
    }



// --- Datumsfunktionen ---------------------------------------------------------------------------------

    /**
     * Creates a small form to set the date-intervall of the current report
     *
     * @return string
     */
	private function createDateSelector($objReport = null) {
		$strReturn = "";

		$intDayStart = $this->getParam("start_datum_tag");
		$intMonthStart = $this->getParam("start_datum_monat");
		$intYearStart = $this->getParam("start_datum_jahr");
		$intDayEnd = $this->getParam("ende_datum_tag");
		$intMonthEnd = $this->getParam("ende_datum_monat");
		$intYearEnd = $this->getParam("ende_datum_jahr");

		//And create the selector
        $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=".$this->arrModule["modul"]."&amp;action=".$this->getParam("action"));
        $strReturn .= $this->objToolkit->formInputHidden("sort", $this->getParam("sort"));
        $strReturn .= $this->objToolkit->formInputHidden("action", $this->getParam("action"));
        $strReturn .= $this->objToolkit->formInputHidden("filter", "true");
        $strReturn .= $this->objToolkit->formDateSimple("start", $intDayStart, $intMonthStart, $intYearStart, $this->getText("start"));
        $strReturn .= $this->objToolkit->formDateSimple("ende", $intDayEnd, $intMonthEnd, $intYearEnd, $this->getText("ende"));

        //create intervall dropdown?
        if($objReport != null) {
            if($objReport instanceof interface_admin_statsreports ) {
                if($objReport->isIntervalable()) {
                    $arrOption = array();
                    $arrOption["1"] = $this->getText("interval_1day");
                    $arrOption["2"] = $this->getText("interval_2days");
                    $arrOption["7"] = $this->getText("interval_7days");
                    $arrOption["15"] = $this->getText("interval_15days");
                    $arrOption["30"] = $this->getText("interval_30days");
                    $arrOption["60"] = $this->getText("interval_60days");
                    $strReturn .= $this->objToolkit->formInputDropdown("interval", $arrOption, $this->getText("interval"), $this->intInterval);
                }
            }
        }
        $strReturn .= $this->objToolkit->formInputSubmit($this->getText("filtern"));
        $strReturn .= $this->objToolkit->formClose();
        $strReturn = "<div class=\"dateSelector\">".$strReturn."</div>";

		return $strReturn;
	}

	/**
	 * Creates int-values of the passed date-values
	 *
	 */
	private function processDates() {
	    $intDayStart = $this->getParam("start_datum_tag");
		$intMonthStart = $this->getParam("start_datum_monat");
		$intYearStart = $this->getParam("start_datum_jahr");
		$intDayEnd = $this->getParam("ende_datum_tag");
		$intMonthEnd = $this->getParam("ende_datum_monat");
		$intYearEnd = $this->getParam("ende_datum_jahr");

		//Set the class-vars
		$this->intDateStart = strtotime($intYearStart."-".$intMonthStart."-".$intDayStart);
		$this->intDateEnd = strtotime($intYearEnd."-".$intMonthEnd."-".$intDayEnd);

		if($this->getParam("interval") != "")
		    $this->intInterval = (int)$this->getParam("interval");
		else
		    $this->intInterval = 2;
	}

// --- Wokers -------------------------------------------------------------------------------------------

    /**
     * This method is responsible for calling common worker / cleanup tasks
     *
     */
    private function actionWorker() {
        $strReturn = "";
        if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
            $intI = 0;
            $strReturn .= $this->objToolkit->getTextRow($this->getText("worker_intro"));

            //check, if theres anything to do
            if($this->getParam("task") != "") {
                $strTask = $this->getParam("task");

                if($strTask == "lookup")
                    $strReturn .= $this->doHostnameLookups();

                if($strTask == "lookupReset")
                    $strReturn .= $this->doHostnameLookupReset();
                    
                if($strTask == "ip2c")
                    $strReturn .= $this->doIp2cLookup();    

                if($strTask == "exportToCsv")
                    $strReturn .= $this->exportDataToCsv();

                if($strTask == "importFromCsv")
                    $strReturn .= $this->importDataFromCsv();

                $strReturn .= "<br />";
            }

            $objWorker = new class_modul_stats_worker("");
            $intIpsOpen = $objWorker->getNumberOfIpsToLookup();

            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_dot.gif"), $this->getText("task_lookup") ." (".$intIpsOpen.")", $this->objToolkit->listButton(getLinkAdmin("stats", "worker", "&task=lookup", $this->getText("task_lookup"), "Run", "icon_accept.gif")), $intI++);
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_dot.gif"), $this->getText("task_lookupReset"), $this->objToolkit->listButton(getLinkAdmin("stats", "worker", "&task=lookupReset", $this->getText("task_lookupReset"), "Run", "icon_accept.gif")), $intI++);
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_dot.gif"), $this->getText("task_ip2c"), $this->objToolkit->listButton(getLinkAdmin("stats", "worker", "&task=ip2c", $this->getText("task_ip2c"), "Run", "icon_accept.gif")), $intI++);
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_dot.gif"), $this->getText("task_exportToCsv"), $this->objToolkit->listButton(getLinkAdmin("stats", "worker", "&task=exportToCsv", $this->getText("task_exportToCsv"), "Run", "icon_accept.gif")), $intI++);
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_dot.gif"), $this->getText("task_importFromCsv"), $this->objToolkit->listButton(getLinkAdmin("stats", "worker", "&task=importFromCsv", $this->getText("task_importFromCsv"), "Run", "icon_accept.gif")), $intI++);


            $strReturn .= $this->objToolkit->listFooter();

        }
		else
			$strReturn = $this->getText("fehler_recht");

        return $strReturn;
    }

    /**
     * Form to control the import of csv-data into the stats-tables
     *
     * @return string
     */
    private function importDataFromCsv() {
       $strReturn = "";
       if(!$this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"])))
            return $this->getText("fehler_recht");

       //present form or start working?
       if($this->getParam("startImport") == "") {
           //create the form and a row as explanation
           $strReturn .= $this->objToolkit->getTextRow($this->getText("task_importFromCsvIntro"));
           $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=".$this->arrModule["modul"]."&amp;action=worker&amp;task=importFromCsv&amp;startImport=1");
           //show dropdown to select csv-file
           include_once(_systempath_."/class_filesystem.php");
           $objFilesystem = new class_filesystem();
           $arrFiles = $objFilesystem->getFilelist("/system/dbdumps/", array(".csv"));
           $arrOptions = array();
           foreach($arrFiles as $strOneFile)
               $arrOptions[$strOneFile] = $strOneFile;
           $strReturn .= $this->objToolkit->formInputDropdown("import_filename", $arrOptions, $this->getText("import_filename"));

           $strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit_import"));
           $strReturn .= $this->objToolkit->formClose();
       }
       else {
           //call the worker and init
    	   include_once(_systempath_."/class_modul_stats_worker.php");
    	   $objWorker = new class_modul_stats_worker();

    	   if($objWorker->importFromCSV($this->getParam("import_filename")))
    	       $strReturn .= $this->getText("import_success");
           else
    	       $strReturn .= $this->getText("import_failure");

       }

       return $strReturn;
    }

    /**
     * GUI for the worker-tasl exportDataToCsv.
     * Creates a form to set up all needed params
     *
     * @return string
     */
    private function exportDataToCsv() {
       $strReturn = "";
       if(!$this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"])))
            return $this->getText("fehler_recht");

       //present form or start working?
       if($this->getParam("startExport") == "") {
           //create the form and a row as explanation
           $strReturn .= $this->objToolkit->getTextRow($this->getText("task_csvExportIntro"));
           $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=".$this->arrModule["modul"]."&amp;action=worker&amp;task=exportToCsv&amp;startExport=1");
           $strReturn .= $this->objToolkit->formDateSimple("export_start", "", "", "", $this->getText("export_start"));
           $strReturn .= $this->objToolkit->formDateSimple("export_end", "", "", "", $this->getText("export_end"));
           $strReturn .= $this->objToolkit->formInputText("export_filename", $this->getText("export_filename"));
           $strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit_export"));
           $strReturn .= $this->objToolkit->formClose();
       }
       else {
           //start export
           $intDayStart = $this->getParam("export_start_datum_tag");
    	   $intMonthStart = $this->getParam("export_start_datum_monat");
    	   $intYearStart = $this->getParam("export_start_datum_jahr");
    	   $intDayEnd = $this->getParam("export_end_datum_tag");
    	   $intMonthEnd = $this->getParam("export_end_datum_monat");
    	   $intYearEnd = $this->getParam("export_end_datum_jahr");

    	   $intDateStart = strtotime($intYearStart."-".$intMonthStart."-".$intDayStart);
    	   $intDateEnd = strtotime($intYearEnd."-".$intMonthEnd."-".$intDayEnd);

    	   //call the worker and init
    	   include_once(_systempath_."/class_modul_stats_worker.php");
    	   $objWorker = new class_modul_stats_worker();

    	   if($objWorker->exportDataToCsv($this->getParam("export_filename"), $intDateStart, $intDateEnd))
    	       $strReturn .= $this->getText("export_success");
           else
    	       $strReturn .= $this->getText("export_failure");

       }

       return $strReturn;
    }
    
    private function doIp2cLookup() {
    	 $strReturn = "";
        if(!$this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"])))
            return $this->getText("fehler_recht");

        $objWorker = new class_modul_stats_worker("");
        
    	//determin the number of ips to lookup
        $arrIpToLookup = $objWorker->getArrayOfIp2cLookups();

        if(count($arrIpToLookup) == 0) {
            return $this->objToolkit->getTextRow($this->getText("worker_lookup_end"));
        }
        
        //check, if we did anything before
        if($this->getParam("totalCount") == "")
            $this->setParam("totalCount", count($arrIpToLookup));

        $strReturn .= $this->objToolkit->getTextRow($this->getText("intro_worker_lookupip2c"). $this->getParam("totalCount"));

        //Lookup 10 Ips an load the page again
        for($intI = 0; $intI < 10; $intI++) {
            if(isset($arrIpToLookup[$intI])) {
                $strIP = $arrIpToLookup[$intI]["stats_ip"];
                
                try {
                    include_once(_systempath_."/class_remoteloader.php");
		            $objRemoteloader = new class_remoteloader();
		            $objRemoteloader->setStrHost($this->strIp2cServer);
		            $objRemoteloader->setStrQueryParams("/ip2c.php?ip=".urlencode($strIP)."&domain=".urlencode(_webpath_)."&checksum=".md5(urldecode(_webpath_).$strIP));
		            $strCountry = $objRemoteloader->getRemoteContent();
		        }
		        catch (class_exception $objExeption) {
		            $strCountry = "n.a.";
		        }
                
                $objWorker->saveIp2CountryRecord($strIP, $strCountry);

            }
        }

        //and Create a small progress-info
        $intTotal = $this->getParam("totalCount");
        $floatOnePercent = 100 / $intTotal;
        //and multiply it with the alredy looked up ips
        $intLookupsDone = ((int)$intTotal - count($arrIpToLookup)) * $floatOnePercent;
        $intLookupsDone = round($intLookupsDone, 2);
        if($intLookupsDone < 0)
            $intLookupsDone = 0;

        $strReturn .= $this->objToolkit->getTextRow($this->getText("progress_worker_lookup"));
        $strReturn .= $this->objToolkit->percentBeam($intLookupsDone, "500");
        header("Refresh: 0; "._indexpath_."?admin=1&module=stats&action=worker&task=ip2c&totalCount=".$this->getParam("totalCount")."");


        return $strReturn;
    }

    private function doHostnameLookups() {
        $strReturn = "";
        if(!$this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"])))
            return $this->getText("fehler_recht");


        $objWorker = new class_modul_stats_worker("");

        //Load all IPs to lookup
        $arrIpToLookup = $objWorker->hostnameLookupIpsToLookup();

        if(count($arrIpToLookup) == 0) {
            return $this->objToolkit->getTextRow($this->getText("worker_lookup_end"));
        }

        //check, if we did anything before
        if($this->getParam("totalCount") == "")
            $this->setParam("totalCount", count($arrIpToLookup));

        $strReturn .= $this->objToolkit->getTextRow($this->getText("intro_worker_lookup"). $this->getParam("totalCount"));

        //Lookup 10 Ips an load the page again
        for($intI = 0; $intI < 10; $intI++) {
            if(isset($arrIpToLookup[$intI])) {
                $strIP = $arrIpToLookup[$intI]["stats_ip"];
                $strHostname = gethostbyaddr($strIP);
                if($strHostname != $strIP) {
                    //Hit. So save it to databse
                    $objWorker->hostnameLookupSaveHostname($strHostname, $strIP);
                }
                else {
                    //Mark the record as already touched
                    $objWorker->hostnameLookupSaveHostname("na", $strIP);
                }

            }
        }

        //and Create a small progress-info
        $intTotal = $this->getParam("totalCount");
        $floatOnePercent = 100 / $intTotal;
        //and multiply it with the alredy looked up ips
        $intLookupsDone = ((int)$intTotal - count($arrIpToLookup)) * $floatOnePercent;
        $intLookupsDone = round($intLookupsDone, 2);
        if($intLookupsDone < 0)
            $intLookupsDone = 0;

        $strReturn .= $this->objToolkit->getTextRow($this->getText("progress_worker_lookup"));
        $strReturn .= $this->objToolkit->percentBeam($intLookupsDone, "500");
        header("Refresh: 0; "._indexpath_."?admin=1&module=stats&action=worker&task=lookup&totalCount=".$this->getParam("totalCount")."");


        return $strReturn;
    }

    private function doHostnameLookupReset() {
        $strReturn = "";
        $objWorker = new class_modul_stats_worker("");
        $objWorker->hostnameLookupResetHostnames();

        $strReturn .= $this->objToolkit->getTextRow($this->getText("worker_lookupReset_end"));

        return $strReturn;
    }


} //class_modul_stats_admin
?>