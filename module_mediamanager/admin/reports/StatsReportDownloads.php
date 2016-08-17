<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Mediamanager\Admin\Reports;

use Kajona\System\Admin\Reports\AdminStatsreportsInterface;
use Kajona\System\Admin\ToolkitAdmin;
use Kajona\System\System\Database;
use Kajona\System\System\Lang;
use Kajona\System\System\Session;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\UserUser;

/**
 * This plugin show the list of download, served by the downloads-module
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class StatsReportDownloads implements AdminStatsreportsInterface
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
        return $this->objTexts->getLang("stats_title", "mediamanager");
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

        $arrLogsRaw = $this->getLogbookData();
        $arrLogs = array();
        $intI = 0;

        $objUser = Session::getInstance()->getUser();
        $intItemsPerPage = $objUser != null ? $objUser->getIntItemsPerPage() : SystemSetting::getConfigValue("_admin_nr_of_rows_");
        foreach ($arrLogsRaw as $intKey => $arrOneLog) {
            if ($intI++ >= $intItemsPerPage) {
                break;
            }

            $arrLogs[$intKey][0] = $intI;
            $arrLogs[$intKey][1] = $arrOneLog["downloads_log_id"];
            $arrLogs[$intKey][2] = timeToString($arrOneLog["downloads_log_date"]);
            $arrLogs[$intKey][3] = $arrOneLog["downloads_log_file"];
            $arrLogs[$intKey][4] = $arrOneLog["downloads_log_user"];
            $arrLogs[$intKey][5] = ($arrOneLog["stats_hostname"] != null ? $arrOneLog["stats_hostname"] : $arrOneLog["downloads_log_ip"]);
        }
        //Create a data-table
        $arrHeader = array();
        $arrHeader[0] = "#";
        $arrHeader[1] = $this->objTexts->getLang("header_id", "mediamanager");
        $arrHeader[2] = $this->objTexts->getLang("commons_date", "mediamanager");
        $arrHeader[3] = $this->objTexts->getLang("header_file", "mediamanager");
        $arrHeader[4] = $this->objTexts->getLang("header_user", "mediamanager");
        $arrHeader[5] = $this->objTexts->getLang("header_ip", "mediamanager");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);

        return $strReturn;
    }

    /**
     * Loads the records of the dl-logbook
     *
     * @return mixed
     */
    private function getLogbookData()
    {
        $strQuery = "SELECT *
					  FROM "._dbprefix_."mediamanager_dllog
					  WHERE downloads_log_date > ?
							AND downloads_log_date <= ?
					  ORDER BY downloads_log_date DESC";

        $objUser = Session::getInstance()->getUser();
        $intItemsPerPage = $objUser != null ? $objUser->getIntItemsPerPage() : SystemSetting::getConfigValue("_admin_nr_of_rows_");

        $arrReturn = $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), 0, ($intItemsPerPage - 1));

        foreach ($arrReturn as &$arrOneRow) {
            //Load hostname, if available. faster, then mergin per LEFT JOIN
            $arrOneRow["stats_hostname"] = null;
            $strQuery = "SELECT stats_hostname
    		             FROM "._dbprefix_."stats_data
    		             WHERE stats_ip = ?
    		             GROUP BY stats_hostname";
            $arrRow = $this->objDB->getPRow($strQuery, array($arrOneRow["downloads_log_ip"]));
            if (isset($arrRow["stats_hostname"])) {
                $arrOneRow["stats_hostname"] = $arrRow["stats_hostname"];
            }

        }

        return $arrReturn;
    }

    /**
     * @return string
     */
    public function getReportGraph()
    {
        return "";
    }

}
