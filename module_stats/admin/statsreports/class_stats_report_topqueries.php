<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/


/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class class_stats_report_topqueries implements interface_admin_statsreports {

    //class vars
    private $intDateStart;
    private $intDateEnd;

    private $objTexts;
    private $objToolkit;
    private $objDB;

    /**
     * Constructor
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
        return $this->objTexts->getLang("topqueries", "stats");
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

        $intI = 0;
        foreach($arrStats as $strKey => $intHits) {
            //Escape?
            if($intI >= _stats_nrofrecords_)
                break;
            $arrValues[$intI] = array();
            $arrValues[$intI][] = $intI + 1;
            $arrValues[$intI][] = $strKey;
            $arrValues[$intI][] = $intHits;
            $arrValues[$intI][] = $this->objToolkit->percentBeam($intHits / $intSum * 100);
            $intI++;
        }
        //HeaderRow
        $arrHeader[] = "#";
        $arrHeader[] = $this->objTexts->getLang("top_query_titel", "stats");
        $arrHeader[] = $this->objTexts->getLang("top_query_gewicht", "stats");
        $arrHeader[] = $this->objTexts->getLang("anteil", "stats");

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
        $arrBlocked = explode(",", _stats_exclusionlist_);

        $arrParams = array($this->intDateStart, $this->intDateEnd);

        $strExclude = "";
        foreach($arrBlocked as $strBlocked) {
            if($strBlocked != "") {
                $strExclude .= " AND stats_referer NOT LIKE ? \n";
                $arrParams[] = "%".str_replace("%", "\%", $strBlocked)."%";
            }
        }

        $strQuery = "SELECT stats_referer
						FROM "._dbprefix_."stats_data
						WHERE stats_date >= ?
						  AND stats_date <= ?
						  AND stats_referer != ''
						  AND stats_referer IS NOT NULL
						    ".$strExclude."
						ORDER BY stats_date desc";
        $arrRecords = $this->objDB->getPArray($strQuery, $arrParams);

        $arrHits = array();
        //Suchpatterns: q=, query=
        $arrQuerypatterns = array("q=", "query=");
        foreach($arrRecords as $arrOneRecord) {
            foreach($arrQuerypatterns as $strOnePattern) {
                if(uniStrpos($arrOneRecord["stats_referer"], $strOnePattern) !== false) {
                    $strQueryterm = uniSubstr($arrOneRecord["stats_referer"], (uniStrpos($arrOneRecord["stats_referer"], $strOnePattern) + uniStrlen($strOnePattern)));
                    $strQueryterm = uniSubstr($strQueryterm, 0, uniStrpos($strQueryterm, "&"));
                    $strQueryterm = uniStrtolower(trim(urldecode($strQueryterm)));
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
        $arrLabels = array();
        $intCount = 1;
        foreach($arrPages as $intHits) {
            $arrGraphData[$intCount] = $intHits;
            $arrLabels[] = $intCount;
            if($intCount++ >= 8)
                break;
        }


        //generate a bar-chart
        if(count($arrGraphData) > 1) {
            $objGraph = class_graph_factory::getGraphInstance();

            $objGraph->setArrXAxisTickLabels($arrLabels);
            $objGraph->addBarChartSet($arrGraphData, $this->objTexts->getLang("top_query_titel", "stats"));

            $objGraph->setStrXAxisTitle($this->objTexts->getLang("top_query_titel", "stats"));
            $objGraph->setStrYAxisTitle($this->objTexts->getLang("top_query_gewicht", "stats"));
            return $objGraph->renderGraph();
        }
        else
            return "";
    }

}
