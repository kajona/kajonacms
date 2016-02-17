<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/
use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\System\Database;
use Kajona\System\System\Lang;

/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class class_stats_report_topreferers implements interface_admin_statsreports {

    //class vars
    private $intDateStart;
    private $intDateEnd;

    private $objTexts;
    private $objToolkit;
    private $objDB;


    /**
     * Constructor
     */
    public function __construct(Database $objDB, ToolkitAdmin $objToolkit, Lang $objTexts) {
        $this->objTexts = $objTexts;
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
        return $this->objTexts->getLang("topreferer", "stats");
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

        //Create Data-table
        $arrHeader = array();
        $arrValues = array();
        //Fetch data
        $arrStats = $this->getTopReferer();

        //calc a few values
        $intSum = 0;
        foreach($arrStats as $arrOneStat)
            $intSum += $arrOneStat["anzahl"];

        $intI = 0;
        $objUser = new class_module_user_user(class_session::getInstance()->getUserID());
        foreach($arrStats as $arrOneStat) {
            //Escape?
            if($intI >= $objUser->getIntItemsPerPage())
                break;

            if($arrOneStat["refurl"] == "")
                $arrOneStat["refurl"] = $this->objTexts->getLang("referer_direkt", "stats");
            else
                $arrOneStat["refurl"] = class_link::getLinkPortal("", $arrOneStat["refurl"], "_blank", uniStrTrim($arrOneStat["refurl"], 45));

            $arrValues[$intI] = array();
            $arrValues[$intI][] = $intI + 1;
            $arrValues[$intI][] = $arrOneStat["refurl"];
            $arrValues[$intI][] = $arrOneStat["anzahl"];
            $arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat["anzahl"] / $intSum * 100);
            $intI++;
        }
        //HeaderRow
        $arrHeader[] = "#";
        $arrHeader[] = $this->objTexts->getLang("top_referer_titel", "stats");
        $arrHeader[] = $this->objTexts->getLang("top_referer_gewicht", "stats");
        $arrHeader[] = $this->objTexts->getLang("anteil", "stats");

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);


        return $strReturn;
    }

    /**
     * returns a list of top-referer
     *
     * @return mixed
     */
    public function getTopReferer() {
        //Build excluded domains
        $arrBlocked = explode(",", class_module_system_setting::getConfigValue("_stats_exclusionlist_"));

        $arrParams = array("%".str_replace("%", "\%", _webpath_)."%", $this->intDateStart, $this->intDateEnd);

        $strExclude = "";
        foreach($arrBlocked as $strBlocked) {
            if($strBlocked != "") {
                $strExclude .= " AND stats_referer NOT LIKE ? \n";
                $arrParams[] = "%".str_replace("%", "\%", $strBlocked)."%";
            }
        }

        $objUser = new class_module_user_user(class_session::getInstance()->getUserID());
        $strQuery = "SELECT stats_referer as refurl, COUNT(*) as anzahl
						FROM "._dbprefix_."stats_data
						WHERE stats_referer NOT LIKE ?
							AND stats_date > ?
							AND stats_date <= ?
							".$strExclude."
						GROUP BY stats_referer
						ORDER BY anzahl desc";

        return $this->objDB->getPArray($strQuery, $arrParams, 0, $objUser->getIntItemsPerPage() - 1);
    }

    /**
     * @return string
     */
    public function getReportGraph() {
        return "";
    }

}
