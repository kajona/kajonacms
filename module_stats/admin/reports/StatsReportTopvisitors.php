<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

namespace Kajona\Stats\Admin\Reports;

use Kajona\System\Admin\Reports\AdminStatsreportsInterface;
use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\System\Database;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\Session;
use Kajona\System\System\UserUser;

/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class StatsReportTopvisitors implements AdminStatsreportsInterface
{

    //class vars
    private $intDateStart;
    private $intDateEnd;

    /**
     * @var Lang
     */
    private $objLang;
    private $objToolkit;
    private $objDB;

    /**
     * Constructor
     */
    public function __construct(Database $objDB, ToolkitAdmin $objToolkit, Lang $objTexts)
    {
        $this->objLang = $objTexts;
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
        return $this->objLang->getLang("topvisitor", "stats");
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
        $arrStats = $this->getTopVisitors();

        //calc a few values
        $intSum = 0;
        foreach ($arrStats as $arrOneStat) {
            $intSum += $arrOneStat["anzahl"];
        }

        $intI = 0;
        $objUser = new UserUser(Session::getInstance()->getUserID());
        foreach ($arrStats as $arrOneStat) {
            //Escape?
            if ($intI >= $objUser->getIntItemsPerPage()) {
                break;
            }

            $arrValues[$intI] = array();
            $arrValues[$intI][] = $intI + 1;
            if ($arrOneStat["stats_hostname"] != "" and $arrOneStat["stats_hostname"] != "na") {
                $arrValues[$intI][] = $arrOneStat["stats_hostname"];
            }
            else {
                $arrValues[$intI][] = $arrOneStat["stats_ip"];
            }
            $arrValues[$intI][] = $arrOneStat["anzahl"];
            $arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat["anzahl"] / $intSum * 100);

            $strUtraceLinkMap = "href=\"http://www.utrace.de/ip-adresse/".$arrOneStat["stats_ip"]."\" target=\"_blank\"";
            $strUtraceLinkText = "href=\"http://www.utrace.de/whois/".$arrOneStat["stats_ip"]."\" target=\"_blank\"";
            if ($arrOneStat["stats_ip"] != "127.0.0.1" && $arrOneStat["stats_ip"] != "::1") {
                $arrValues[$intI][] = Link::getLinkAdminManual($strUtraceLinkMap, "", $this->objLang->getLang("login_utrace_showmap", "user"), "icon_earth")
                    ." ".Link::getLinkAdminManual($strUtraceLinkText, "", $this->objLang->getLang("login_utrace_showtext", "user"), "icon_text");
            }
            else {
                $arrValues[$intI][] = getImageAdmin("icon_earthDisabled", $this->objLang->getLang("login_utrace_noinfo", "user"))." "
                    .getImageAdmin("icon_textDisabled", $this->objLang->getLang("login_utrace_noinfo", "user"));
            }

            $intI++;
        }
        //HeaderRow
        $arrHeader[] = "#";
        $arrHeader[] = $this->objLang->getLang("top_visitor_titel", "stats");
        $arrHeader[] = $this->objLang->getLang("commons_hits_header", "stats");
        $arrHeader[] = $this->objLang->getLang("anteil", "stats");
        $arrHeader[] = $this->objLang->getLang("login_utrace", "user");

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

        $strReturn .= $this->objToolkit->getTextRow($this->objLang->getLang("stats_hint_task", "stats"));

        return $strReturn;
    }

    /**
     * Returns the list of top-visitors
     *
     * @return mixed
     */
    public function getTopVisitors()
    {
        $strQuery = "  SELECT stats_ip , stats_browser, stats_hostname , COUNT(*) as anzahl
					  	 FROM "._dbprefix_."stats_data
						WHERE stats_date > ?
						  AND stats_date <= ?
						GROUP BY stats_ip, stats_browser, stats_hostname
						ORDER BY anzahl desc";
        return $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd));
    }

    /**
     * @return string
     */
    public function getReportGraph()
    {
        return "";
    }
}
