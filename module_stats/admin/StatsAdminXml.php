<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Stats\Admin;

use Kajona\System\Admin\AdminController;
use Kajona\System\Admin\XmlAdminInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Pluginmanager;


/**
 * Admin class of the stats-module - xml based.
 * Triggers the report-generation
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 * @module stats
 * @moduleId _stats_modul_id_
 */
class StatsAdminXml extends AdminController implements XmlAdminInterface
{

    /**
     * @var \Kajona\System\System\Date
     */
    private $objDateStart;
    /**
     * @var \Kajona\System\System\Date
     */
    private $objDateEnd;
    private $intInterval;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $intDateStart = Carrier::getInstance()->getObjSession()->getSession(StatsAdmin::$STR_SESSION_KEY_DATE_START);
        //Start: first day of current month
        $this->objDateStart = new \Kajona\System\System\Date();
        $this->objDateStart->setTimeInOldStyle($intDateStart);

        //End: Current Day of month
        $intDateEnd = Carrier::getInstance()->getObjSession()->getSession(StatsAdmin::$STR_SESSION_KEY_DATE_END);
        $this->objDateEnd = new \Kajona\System\System\Date();
        $this->objDateEnd->setTimeInOldStyle($intDateEnd);

        $this->intInterval = Carrier::getInstance()->getObjSession()->getSession(StatsAdmin::$STR_SESSION_KEY_INTERVAL);
    }


    /**
     * Triggers the "real" creation of the report and wraps the code inline into a xml-structure
     *
     * @return string
     * @permissions view
     */
    protected function actionGetReport()
    {
        $strPlugin = $this->getParam("plugin");
        $strReturn = "";

        $objPluginManager = new Pluginmanager(StatsAdmin::$STR_PLUGIN_EXTENSION_POINT, "/admin/statsreports");


        $objPlugin = null;
        foreach ($objPluginManager->getPlugins(array(Carrier::getInstance()->getObjDB(), $this->objToolkit, $this->getObjLang())) as $objOneReport) {
            if ($this->getActionForReport($objOneReport) == $strPlugin) {
                $objPlugin = $objOneReport;
                break;
            }
        }


        if ($objPlugin !== null && $objPlugin instanceof AdminStatsreportsInterface) {
            //get date-params as ints
            $intStartDate = mktime(0, 0, 0, $this->objDateStart->getIntMonth(), $this->objDateStart->getIntDay(), $this->objDateStart->getIntYear());
            $intEndDate = mktime(0, 0, 0, $this->objDateEnd->getIntMonth(), $this->objDateEnd->getIntDay(), $this->objDateEnd->getIntYear());
            $objPlugin->setEndDate($intEndDate);
            $objPlugin->setStartDate($intStartDate);
            $objPlugin->setInterval($this->intInterval);

            $arrImage = $objPlugin->getReportGraph();

            if (!is_array($arrImage)) {
                $arrImage = array($arrImage);
            }

            foreach ($arrImage as $strImage) {
                if ($strImage != "") {
                    $strReturn .= $this->objToolkit->getGraphContainer($strImage);
                }
            }


            $strReturn .= $objPlugin->getReport();
            $strReturn = "<content><![CDATA[".$strReturn."]]></content>";
        }

        return $strReturn;
    }

    private function getActionForReport(AdminStatsreportsInterface $objReport)
    {
        return uniStrtolower(uniSubstr(get_class($objReport), uniStrpos("StatsReport", get_class($objReport))+11));
    }

}

