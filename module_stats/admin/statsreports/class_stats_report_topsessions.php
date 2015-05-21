<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/


/**
 * This plugin creates a view showing infos about the sessions
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class class_stats_report_topsessions implements interface_admin_statsreports {

    //class vars
    private $intDateStart;
    private $intDateEnd;

    private $objTexts;
    private $objToolkit;

    /**
     * @var class_db
     */
    private $objDB;


    /**
     * Constructor
     */
    public function __construct(class_db $objDB, class_toolkit_admin $objToolkit, class_lang $objTexts) {
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
        return $this->objTexts->getLang("topsessions", "stats");
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
        $arrSessions = $this->getTopSessions();

        $intI = 0;
        $objUser = new class_module_user_user(class_session::getInstance()->getUserID());
        foreach($arrSessions as $arrOneSession) {
            //Escape?
            if($intI >= $objUser->getIntItemsPerPage())
                break;

            $arrValues[$intI] = array();
            $arrValues[$intI][] = $intI + 1;
            $arrValues[$intI][] = $arrOneSession["stats_session"];
            $arrValues[$intI][] = $arrOneSession["stats_ip"];
            $arrValues[$intI][] = $arrOneSession["dauer"];
            $arrValues[$intI][] = $arrOneSession["anzahl"];
            $arrValues[$intI][] = $arrOneSession["detail"];
            $intI++;
        }

        //HeaderRow, but this time a little more complex: we want to provide the possibility to sort the table
        $arrHeader[] = "#";
        $arrHeader[] = $this->objTexts->getLang("top_session_titel", "stats");
        $arrHeader[] = $this->objTexts->getLang("top_session_detail_ip", "stats");
        $arrHeader[] = $this->objTexts->getLang("top_session_dauer", "stats");
        $arrHeader[] = $this->objTexts->getLang("top_session_anzseiten", "stats");
        $arrHeader[] = "";

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrValues);

        return $strReturn;
    }

    /**
     * Returns the pages and their hits
     *
     * @return mixed
     */
    public function getTopSessions() {
        $objUser = new class_module_user_user(class_session::getInstance()->getUserID());
        $objUser = new class_module_user_user(class_session::getInstance()->getUserID());

        $strQuery = "SELECT stats_session,
                            stats_ip,
                            stats_hostname,
                            MIN(stats_date) AS startdate,
                            MAX(stats_date) AS  enddate,
                            COUNT(*) AS anzahl,
                            MAX(stats_date)-MIN(stats_date) AS dauer


                     FROM "._dbprefix_."stats_data
                     WHERE stats_date > ?
					   AND stats_date <= ?
                     GROUP BY  stats_session, stats_ip, stats_hostname
                      ORDER BY enddate DESC";

        $arrSessions = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), 0, $objUser->getIntItemsPerPage() - 1);

        $intI = 0;
        foreach($arrSessions as $intKey => $arrOneSession) {
            if($intI++ >= $objUser->getIntItemsPerPage())
                break;

            //Load the details for all sessions
            $strDetails = "";
            $strSessionID = $arrOneSession["stats_session"];
            $strDetails .= $this->objTexts->getLang("top_session_detail_start", "stats").timeToString($arrOneSession["startdate"])."<br />";
            $strDetails .= $this->objTexts->getLang("top_session_detail_end", "stats").timeToString($arrOneSession["enddate"])."<br />";
            $strDetails .= $this->objTexts->getLang("top_session_detail_time", "stats").($arrOneSession["dauer"])."<br />";
            $strDetails .= $this->objTexts->getLang("top_session_detail_ip", "stats").$arrOneSession["stats_ip"]."<br />";
            $strDetails .= $this->objTexts->getLang("top_session_detail_hostname", "stats").$arrOneSession["stats_hostname"]."<br />";
            //and fetch all pages
            $strQuery = "SELECT stats_page
                           FROM "._dbprefix_."stats_data
                          WHERE stats_session= ?
                          ORDER BY stats_date ASC";

            $arrPages = $this->objDB->getPArray($strQuery, array($strSessionID));

            $strDetails .= $this->objTexts->getLang("top_session_detail_verlauf", "stats");
            foreach($arrPages as $arrOnePage)
                $strDetails .= $arrOnePage["stats_page"]." - ";

            $strDetails = uniSubstr($strDetails, 0, -2);
            $arrFolder = $this->objToolkit->getLayoutFolder($strDetails, $this->objTexts->getLang("top_session_detail", "stats"));
            $arrSessions[$intKey]["detail"] = $arrFolder[1].$arrFolder[0];
        }

        return $arrSessions;
    }

    /**
     * @return string
     */
    public function getReportGraph() {
        return "";
    }

}
