<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
    public function __construct(class_db $objDB, class_toolkit_admin $objToolkit, class_lang $objTexts) {
        $this->objTexts = $objTexts;
        $this->objToolkit = $objToolkit;
        $this->objDB = $objDB;
    }

    public function registerPlugin($objPluginamanger)
    {
        $objPluginamanger->registerPlugin($this);
    }

    public function setEndDate($intEndDate) {
        $this->intDateEnd = $intEndDate;
    }

    public function setStartDate($intStartDate) {
        $this->intDateStart = $intStartDate;
    }

    public function getTitle() {
        return $this->objTexts->getLang("topreferer", "stats");
    }

    public function getPluginCommand() {
        return "statsTopReferer";
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
        $arrStats = $this->getTopReferer();

        //calc a few values
        $intSum = 0;
        foreach($arrStats as $arrOneStat)
            $intSum += $arrOneStat["anzahl"];

        $intI = 0;
        foreach($arrStats as $arrOneStat) {
            //Escape?
            if($intI >= _stats_nrofrecords_)
                break;

            if($arrOneStat["refurl"] == "")
                $arrOneStat["refurl"] = $this->objTexts->getLang("referer_direkt", "stats");
            else
                $arrOneStat["refurl"] = getLinkPortal("", $arrOneStat["refurl"], "_blank", uniStrTrim($arrOneStat["refurl"], 45));

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
        $arrHeader[] = $this->objTexts->getLang("anteil", "stats", "admin");

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
        $arrBlocked = explode(",", _stats_exclusionlist_);

        $arrParams = array("%".str_replace("%", "\%", _webpath_)."%", $this->intDateStart, $this->intDateEnd);

        $strExclude = "";
        foreach($arrBlocked as $strBlocked) {
            if($strBlocked != "") {
                $strExclude .= " AND stats_referer NOT LIKE ? \n";
                $arrParams[] = "%".str_replace("%", "\%", $strBlocked)."%";
            }
        }

        $strQuery = "SELECT stats_referer as refurl, COUNT(*) as anzahl
						FROM "._dbprefix_."stats_data
						WHERE stats_referer NOT LIKE ?
							AND stats_date > ?
							AND stats_date <= ?
							".$strExclude."
						GROUP BY stats_referer
						ORDER BY anzahl desc";

        return $this->objDB->getPArray($strQuery, $arrParams, 0, _stats_nrofrecords_ - 1);
    }

    public function getReportGraph() {
        return "";
    }

}
