<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * This plugin shows the list of queries performed by the local searchengine
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_stats_report_searchqueries implements interface_admin_statsreports {

    //class vars
    private $intDateStart;
    private $intDateEnd;

    private $objLang;
    private $objToolkit;

    /**
     * instance of class db
     *
     * @var class_db
     */
    private $objDB;

    /**
     * Constructor
     */
    public function __construct(class_db $objDB, class_toolkit_admin $objToolkit, class_lang $objLang) {
        $this->objLang = $objLang;
        $this->objToolkit = $objToolkit;
        $this->objDB = $objDB;
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public static function getExtensionName() {
        return "core.stats.admin.statsreport";
    }

    /**
     * @param int $intEndDate
     * @return void
     */
    public function setEndDate($intEndDate) {
        $this->intDateEnd = $intEndDate;
    }

    /**
     * @param int $intStartDate
     * @return void
     */
    public function setStartDate($intStartDate) {
        $this->intDateStart = $intStartDate;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->objLang->getLang("stats_title", "search");
    }

    /**
     * @return bool
     */
    public function isIntervalable() {
        return false;
    }

    /**
     * @param int $intInterval
     * @return void
     */
    public function setInterval($intInterval) {

    }

    /**
     * @return string
     */
    public function getReport() {
        $strReturn = "";

        //showing a list using the pageview
        $objArraySectionIterator = new class_array_section_iterator($this->getTopQueriesCount());
        $objArraySectionIterator->setPageNumber((int)(getGet("pv") != "" ? getGet("pv") : 1));
        $objArraySectionIterator->setArraySection($this->getTopQueries($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $intI = 0;
        $arrLogs = array();
        $objUser = new class_module_user_user(class_session::getInstance()->getUserID());
        foreach($objArraySectionIterator as $intKey => $arrOneLog) {
            if($intI++ >= $objUser->getIntItemsPerPage())
                break;

            $arrLogs[$intKey][0] = $intI;
            $arrLogs[$intKey][1] = $arrOneLog["search_log_query"];
            $arrLogs[$intKey][2] = $arrOneLog["hits"];
        }

        //Create a data-table
        $arrHeader = array();
        $arrHeader[0] = "#";
        $arrHeader[1] = $this->objLang->getLang("header_query", "search");
        $arrHeader[2] = $this->objLang->getLang("header_amount", "search");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);

        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, "stats", uniStrReplace("class_stats_report_", "", get_class($this)));

        return $strReturn;
    }


    /**
     * @return array|string
     */
    public function getReportGraph() {
        $arrReturn = array();
        //collect data
        $arrQueries = $this->getTopQueries();

        $arrGraphData = array();
        $arrLabels = array();

        $intCount = 1;
        foreach($arrQueries as $arrOneQuery) {
            $arrGraphData[] = $arrOneQuery["hits"];
            $arrLabels[] = $arrOneQuery["search_log_query"];

            if($intCount++ >= 9)
                break;
        }

        if(count($arrGraphData) > 1) {
            //generate a bar-chart
            $objGraph = class_graph_factory::getGraphInstance();
            $objGraph->setArrXAxisTickLabels($arrLabels);
            $objGraph->addBarChartSet($arrGraphData, "");
            $objGraph->setStrXAxisTitle($this->objLang->getLang("header_query", "search"));
            $objGraph->setStrYAxisTitle($this->objLang->getLang("header_amount", "search"));
            $objGraph->setBitRenderLegend(false);
            $objGraph->setIntXAxisAngle(20);
            $arrReturn[] = $objGraph->renderGraph();

            return $arrReturn;
        }
        else
            return "";
    }


    /**
     * @param bool $intStart
     * @param bool $intEnd
     *
     * @return array
     */
    private function getTopQueries($intStart = false, $intEnd = false) {
        $objUser = new class_module_user_user(class_session::getInstance()->getUserID());
        $strQuery = "SELECT search_log_query, COUNT(*) as hits
					  FROM "._dbprefix_."search_log
					  WHERE search_log_date > ?
					    AND search_log_date <= ?
				   GROUP BY search_log_query
				   ORDER BY hits DESC";

        if($intStart !== false && $intEnd !== false)
            $arrReturn = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), $intStart, $intEnd);
        else
            $arrReturn = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), 0, $objUser->getIntItemsPerPage() - 1);

        return $arrReturn;
    }

    /**
     * @return mixed
     */
    private function getTopQueriesCount() {
        $strQuery = "SELECT COUNT(DISTINCT(search_log_query)) as total
					  FROM "._dbprefix_."search_log
					  WHERE search_log_date > ?
					    AND search_log_date <= ?";

        $arrReturn = $this->objDB->getPRow($strQuery, array($this->intDateStart, $this->intDateEnd));
        return $arrReturn["total"];
    }

}
