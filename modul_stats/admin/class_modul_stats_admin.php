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
	

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_stats";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _stats_modul_id_;
		$arrModule["table"] 		    = _dbprefix_."stats_data";
		$arrModule["modul"]				= "stats";

		//base class
		parent::__construct($arrModule);

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
        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], $this->getParam("action")));
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


} //class_modul_stats_admin
?>