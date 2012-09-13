<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/


/**
 * This plugin creates a view common numbers, such as "user online" oder "total pagehits"
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class class_stats_report_topvisitors implements interface_admin_statsreports {

    //class vars
    private $intDateStart;
    private $intDateEnd;

    /**
     * @var class_lang
     */
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

    public function setEndDate($intEndDate) {
        $this->intDateEnd = $intEndDate;
    }

    public function setStartDate($intStartDate) {
        $this->intDateStart = $intStartDate;
    }

    public function getReportTitle() {
        return $this->objLang->getLang("topvisitor", "stats");
    }

    public function getReportCommand() {
        return "statsTopVisitors";
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
        $arrStats = $this->getTopVisitors();

        //calc a few values
        $intSum = 0;
        foreach($arrStats as $arrOneStat)
            $intSum += $arrOneStat["anzahl"];

        $intI = 0;
        foreach($arrStats as $arrOneStat) {
            //Escape?
            if($intI >= _stats_nrofrecords_)
                break;

            $arrValues[$intI] = array();
            $arrValues[$intI][] = $intI + 1;
            if($arrOneStat["stats_hostname"] != "" and $arrOneStat["stats_hostname"] != "na")
                $arrValues[$intI][] = $arrOneStat["stats_hostname"];
            else {
                $arrValues[$intI][] = $arrOneStat["stats_ip"];
            }
            $arrValues[$intI][] = $arrOneStat["anzahl"];
            $arrValues[$intI][] = $this->objToolkit->percentBeam($arrOneStat["anzahl"] / $intSum * 100);

            $strUtraceLinkMap = "http://www.utrace.de/ip-adresse/".$arrOneStat["stats_ip"];
            $strUtraceLinkText = "http://www.utrace.de/whois/".$arrOneStat["stats_ip"];
            if($arrOneStat["stats_ip"] != "127.0.0.1" && $arrOneStat["stats_ip"] != "::1") {
                $arrValues[$intI][]	= getLinkAdminRaw($strUtraceLinkMap, "", $this->objLang->getLang("login_utrace_showmap", "user"), "icon_earth.png", "_blank")
                    . " " . getLinkAdminRaw($strUtraceLinkText, "", $this->objLang->getLang("login_utrace_showtext", "user"), "icon_text.png", "_blank");
            }
            else 
                $arrValues[$intI][] = getImageAdmin("icon_earthDisabled.png", $this->objLang->getLang("login_utrace_noinfo", "user")) ." "
                    .getImageAdmin("icon_textDisabled.png", $this->objLang->getLang("login_utrace_noinfo", "user"));
            
            $intI++;
        }
        //HeaderRow
        $arrHeader[] = "#";
        $arrHeader[] = $this->objLang->getLang("top_visitor_titel", "stats");
        $arrHeader[] = $this->objLang->getLang("commons_hits_header", "stats");
        $arrHeader[] = $this->objLang->getLang("anteil", "stats", "admin");
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
    public function getTopVisitors() {
        $strQuery = "  SELECT stats_ip , stats_browser, stats_hostname , COUNT(*) as anzahl
					  	 FROM "._dbprefix_."stats_data
						WHERE stats_date >= ?
						  AND stats_date <= ?
						GROUP BY stats_ip, stats_browser, stats_hostname
						ORDER BY anzahl desc";
        return $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd));
    }

    public function getReportGraph() {
        return "";
    }
}
