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
use Kajona\System\System\UserUser;

/**
 * This plugin creates a list of countries the visitors come from
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class StatsReportTopcountries implements AdminStatsreportsInterface
{

    //class vars
    private $intDateStart;
    private $intDateEnd;
    private $intInterval;

    private $objTexts;
    private $objToolkit;
    private $objDB;

    private $arrHits = null;

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
        return $this->objTexts->getLang("topcountries", "stats");
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
        $this->intInterval = $intInterval;
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
        $arrStats = $this->getTopCountries();

        //calc a few values
        $intSum = 0;
        foreach ($arrStats as $arrOneStat) {
            $intSum += $arrOneStat;
        }

        $intI = 0;
        $objUser = new UserUser(Session::getInstance()->getUserID());
        foreach ($arrStats as $strCountry => $arrOneStat) {
            //Escape?
            if ($intI >= $objUser->getIntItemsPerPage()) {
                break;
            }

            $arrValues[$intI] = array();
            $arrValues[$intI][] = $intI + 1;
            $arrValues[$intI][] = $strCountry;
            $arrValues[$intI][] = $arrOneStat;
            $arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat / $intSum * 100);
            $intI++;
        }
        //HeaderRow
        $arrHeader[] = "#";
        $arrHeader[] = $this->objTexts->getLang("top_country_titel", "stats");
        $arrHeader[] = $this->objTexts->getLang("commons_hits_header", "stats");
        $arrHeader[] = $this->objTexts->getLang("anteil", "stats");

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

        $strReturn .= $this->objToolkit->getTextRow($this->objTexts->getLang("stats_hint_task", "stats"));

        return $strReturn;
    }


    /**
     * Loads a list of systems accessed the page
     *
     * @return mixed
     */
    public function getTopCountries()
    {
        $arrReturn = array();

        if ($this->arrHits != null) {
            return $this->arrHits;
        }

        $strQuery = "SELECT stats_ip, count(*) as anzahl
						FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
						  AND stats_date <= ?
						GROUP BY stats_ip";

        $arrTemp = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd));

        $intCounter = 0;
        foreach ($arrTemp as $arrOneRecord) {

            $strQuery = "SELECT ip2c_name as country_name
						   FROM "._dbprefix_."stats_ip2country
						  WHERE ip2c_ip = ?";

            $arrRow = $this->objDB->getPRow($strQuery, array($arrOneRecord["stats_ip"]));

            if (!isset($arrRow["country_name"])) {
                $arrRow["country_name"] = "n.a.";
            }

            if (isset($arrReturn[$arrRow["country_name"]])) {
                $arrReturn[$arrRow["country_name"]] += $arrOneRecord["anzahl"];
            }
            else {
                $arrReturn[$arrRow["country_name"]] = $arrOneRecord["anzahl"];
            }

            //flush query cache every 2000 hits
            if ($intCounter++ >= 2000) {
                $this->objDB->flushQueryCache();
            }

        }

        arsort($arrReturn);
        $this->arrHits = $arrReturn;
        return $arrReturn;
    }

    /**
     * @return array
     */
    public function getReportGraph()
    {
        $arrReturn = array();

        $arrData = $this->getTopCountries();

        $intSum = 0;
        foreach ($arrData as $arrOneStat) {
            $intSum += $arrOneStat;
        }

        $arrKeyValues = array();
        //max 6 entries
        $intCount = 0;
        $floatPercentageSum = 0;
        $arrValues = array();
        $arrLabels = array();
        foreach ($arrData as $strName => $intOneSystem) {
            if (++$intCount <= 6) {
                $floatPercentage = $intOneSystem / $intSum * 100;
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
        if ($floatPercentageSum < 99) {
            $arrKeyValues["others"] = 100 - $floatPercentageSum;
            $arrLabels[] = "others";
            $arrValues[] = 100 - $floatPercentageSum;
        }
        $objGraph = GraphFactory::getGraphInstance();
        $objGraph->createPieChart($arrValues, $arrLabels);

        $arrReturn[] = $objGraph->renderGraph();


        return $arrReturn;
    }
}

