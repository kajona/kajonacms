<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Plugin to module stats, ploting a list of most active query-sources
 *
 * @package module_packageserver
 * @author sidler@mulchprod.de
 */
class class_stats_report_packageserverqueries implements interface_admin_statsreports {

    //class vars
    private $intDateStart;
    private $intDateEnd;
    private $intInterval;

    private $objLang;
    private $objToolkit;
    private $objDB;


    /**
     * Constructor
     */
    public function __construct(class_db $objDB, class_toolkit_admin $objToolkit, class_lang $objTexts) {
        $this->objLang = $objTexts;
        $this->objToolkit = $objToolkit;
        $this->objDB = $objDB;
    }

    public function registerPlugin(class_admininterface_pluginmanager $objPluginamanger) {
        $objPluginamanger->registerPlugin($this);
    }

    public function setEndDate($intEndDate) {
        $this->intDateEnd = $intEndDate;
    }

    public function setStartDate($intStartDate) {
        $this->intDateStart = $intStartDate;
    }

    public function getTitle() {
        return $this->objLang->getLang("packageservertopqueries", "packageserver");
    }

    public function getPluginCommand() {
        return "packageserverTopQueries";
    }

    public function isIntervalable() {
        return true;
    }

    public function setInterval($intInterval) {
        $this->intInterval = $intInterval;
    }

    public function getReport() {
        $strReturn = "";

        $arrData = $this->getTotalUniqueHostsInInterval();

        $arrLogs = array();
        $intI = 0;
        foreach($arrData as $arrOneLog) {
            if($intI++ >= _stats_nrofrecords_) {
                break;
            }

            $arrLogs[$intI][0] = $intI;
            $arrLogs[$intI][1] = $arrOneLog["log_hostname"];
            $arrLogs[$intI][2] = $arrOneLog["anzahl"];
        }
        //Create a data-table
        $arrHeader = array();
        $arrHeader[0] = "#";
        $arrHeader[1] = $this->objLang->getLang("packageservertopqueries_header_host", "packageserver");
        $arrHeader[2] = $this->objLang->getLang("packageservertopqueries_header_requests", "packageserver");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);

        return $strReturn;
    }

    /**
     * Returns the pages and their hits
     *
     * @return mixed
     */
    public function getTotalHitsInInterval() {
        $objStart = new class_date($this->intDateStart);
        $objEnd = new class_date($this->intDateEnd);
        $strQuery = "SELECT COUNT(*)
						FROM "._dbprefix_."packageserver_log
						WHERE log_date > ?
						  AND log_date <= ?";

        $arrRow = $this->objDB->getPRow($strQuery, array($objStart->getLongTimestamp(), $objEnd->getLongTimestamp()));

        return $arrRow["COUNT(*)"];
    }

    public function getTotalUniqueHitsInInterval() {
        return count($this->getTotalUniqueHostsInInterval());
    }

    public function getTotalUniqueHostsInInterval() {
        $objStart = new class_date($this->intDateStart);
        $objEnd = new class_date($this->intDateEnd);
        $strQuery = "SELECT log_hostname, COUNT(*) as anzahl
						FROM "._dbprefix_."packageserver_log
						WHERE log_date > ?
						  AND log_date <= ?
				     GROUP BY log_hostname
				     ORDER BY anzahl DESC";

        $arrRow = $this->objDB->getPArray($strQuery, array($objStart->getLongTimestamp(), $objEnd->getLongTimestamp()));

        return $arrRow;
    }

    public function getReportGraph() {
        $arrReturn = array();

        $arrTickLabels = array();

        $intGlobalEnd = $this->intDateEnd;
        $intGlobalStart = $this->intDateStart;

        $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;

        $intCount = 0;
        $arrTotalHits = array();
        $arrUniqueHits = array();

        while($this->intDateStart <= $intGlobalEnd) {
            $arrTotalHits[$intCount] = $this->getTotalHitsInInterval();
            $arrUniqueHits[$intCount] = $this->getTotalUniqueHitsInInterval();
            $arrTickLabels[$intCount] = date("d.m.", $this->intDateStart);
            //increase start & end-date
            $this->intDateStart = $this->intDateEnd;
            $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;
            $intCount++;
        }
        //create graph
        if($intCount > 1) {
            $objGraph = class_graph_factory::getGraphInstance();
            $objGraph->setArrXAxisTickLabels($arrTickLabels);
            $objGraph->addLinePlot($arrTotalHits, $this->objLang->getLang("packageservertopqueries_total", "packageserver"));
            $objGraph->addLinePlot($arrUniqueHits, $this->objLang->getLang("packageservertopqueries_unique", "packageserver"));
            $objGraph->setIntWidth(815);
            $objGraph->renderGraph();
            $arrReturn[] = $objGraph->renderGraph();
        }
        //reset global dates
        $this->intDateEnd = $intGlobalEnd;
        $this->intDateStart = $intGlobalStart;

        return $arrReturn;

    }

}

