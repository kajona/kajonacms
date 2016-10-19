<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Stats\Admin\Reports;

use Kajona\System\Admin\Reports\AdminStatsreportsInterface;
use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\System\Database;
use Kajona\System\System\GraphFactory;
use Kajona\System\System\Lang;
use Kajona\System\System\Session;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemSetting;

/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class StatsReportTopqueries implements AdminStatsreportsInterface
{

    //class vars
    private $intDateStart;
    private $intDateEnd;

    private $objTexts;
    private $objToolkit;
    private $objDB;

    /**
     * Constructor
     */
    public function __construct(Database $objDB, ToolkitAdmin $objToolkit, Lang $objTexts)
    {

        $this->objTexts = $objTexts;
        $this->objToolkit = $objToolkit;
        $this->objDB = $objDB;
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public static function getExtensionName()
    {
        return "core.stats.admin.statsreport";
    }

    /**
     * @param int $intEndDate
     *
     * @return void
     */
    public function setEndDate($intEndDate)
    {
        $this->intDateEnd = $intEndDate;
    }

    /**
     * @param int $intStartDate
     *
     * @return void
     */
    public function setStartDate($intStartDate)
    {
        $this->intDateStart = $intStartDate;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->objTexts->getLang("topqueries", "stats");
    }

    /**
     * @return bool
     */
    public function isIntervalable()
    {
        return false;
    }

    /**
     * @param int $intInterval
     *
     * @return void
     */
    public function setInterval($intInterval)
    {

    }

    /**
     * @return string
     */
    public function getReport()
    {
        $strReturn = "";
        //Create Data-table
        $arrHeader = array();
        $arrValues = array();
        //Fetch data
        $arrStats = $this->getTopQueries();

        //calc a few values
        $intSum = 0;
        foreach ($arrStats as $intHits) {
            $intSum += $intHits;
        }

        $intI = 0;
        $objUser = Session::getInstance()->getUser();
        $intItemsPerPage = $objUser != null ? $objUser->getIntItemsPerPage() : SystemSetting::getConfigValue("_admin_nr_of_rows_");
        foreach ($arrStats as $strKey => $intHits) {
            //Escape?
            if ($intI >= $intItemsPerPage) {
                break;
            }
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
    public function getTopQueries()
    {
        //Load all records in the passed interval
        $arrBlocked = explode(",", SystemSetting::getConfigValue("_stats_exclusionlist_"));

        $arrParams = array($this->intDateStart, $this->intDateEnd);

        $strExclude = "";
        foreach ($arrBlocked as $strBlocked) {
            if ($strBlocked != "") {
                $strExclude .= " AND stats_referer NOT LIKE ? \n";
                $arrParams[] = "%".str_replace("%", "\%", $strBlocked)."%";
            }
        }

        $strQuery = "SELECT stats_referer
						FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
						  AND stats_date <= ?
						  AND stats_referer != ''
						  AND stats_referer IS NOT NULL
						    ".$strExclude."
						ORDER BY stats_date desc";
        $arrRecords = $this->objDB->getPArray($strQuery, $arrParams);

        $arrHits = array();
        //Suchpatterns: q=, query=
        $arrQuerypatterns = array("q=", "query=");
        foreach ($arrRecords as $arrOneRecord) {
            foreach ($arrQuerypatterns as $strOnePattern) {
                if (StringUtil::indexOf($arrOneRecord["stats_referer"], $strOnePattern) !== false) {
                    $strQueryterm = uniSubstr($arrOneRecord["stats_referer"], (StringUtil::indexOf($arrOneRecord["stats_referer"], $strOnePattern) + uniStrlen($strOnePattern)));
                    $strQueryterm = uniSubstr($strQueryterm, 0, StringUtil::indexOf($strQueryterm, "&"));
                    $strQueryterm = uniStrtolower(trim(urldecode($strQueryterm)));
                    if ($strQueryterm != "") {
                        if (isset($arrHits[$strQueryterm])) {
                            $arrHits[$strQueryterm]++;
                        }
                        else {
                            $arrHits[$strQueryterm] = 1;
                        }
                    }
                    break;
                }
            }
        }
        arsort($arrHits);
        return $arrHits;
    }

    /**
     * @return mixed|string
     */
    public function getReportGraph()
    {
        //collect data
        $arrPages = $this->getTopQueries();

        $arrGraphData = array();
        $arrLabels = array();
        $intCount = 1;
        foreach ($arrPages as $intHits) {
            $arrGraphData[$intCount] = $intHits;
            $arrLabels[] = $intCount;
            if ($intCount++ >= 8) {
                break;
            }
        }


        //generate a bar-chart
        if (count($arrGraphData) > 1) {
            $objGraph = GraphFactory::getGraphInstance();

            $objGraph->setArrXAxisTickLabels($arrLabels);
            $objGraph->addBarChartSet($arrGraphData, $this->objTexts->getLang("top_query_titel", "stats"));

            $objGraph->setStrXAxisTitle($this->objTexts->getLang("top_query_titel", "stats"));
            $objGraph->setStrYAxisTitle($this->objTexts->getLang("top_query_gewicht", "stats"));
            return $objGraph->renderGraph();
        }
        else {
            return "";
        }
    }

}
