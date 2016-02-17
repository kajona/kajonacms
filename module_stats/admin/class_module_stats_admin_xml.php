<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * Admin class of the stats-module - xml based.
 * Triggers the report-generation
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 * @module stats
 * @moduleId _stats_modul_id_
 */
class class_module_stats_admin_xml extends class_admin_controller implements interface_xml_admin {

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
    public function __construct() {
        parent::__construct();

        $intDateStart = class_carrier::getInstance()->getObjSession()->getSession(class_module_stats_admin::$STR_SESSION_KEY_DATE_START);
        //Start: first day of current month
        $this->objDateStart = new \Kajona\System\System\Date();
        $this->objDateStart->setTimeInOldStyle($intDateStart);

        //End: Current Day of month
        $intDateEnd = class_carrier::getInstance()->getObjSession()->getSession(class_module_stats_admin::$STR_SESSION_KEY_DATE_END);
        $this->objDateEnd = new \Kajona\System\System\Date();
        $this->objDateEnd->setTimeInOldStyle($intDateEnd);

        $this->intInterval = class_carrier::getInstance()->getObjSession()->getSession(class_module_stats_admin::$STR_SESSION_KEY_INTERVAL);
    }


    /**
     * Triggers the "real" creation of the report and wraps the code inline into a xml-structure
     *
     * @return string
     * @permissions view
     */
    protected function actionGetReport() {
        $strPlugin = $this->getParam("plugin");
        $strReturn = "";

        $objPluginManager = new class_pluginmanager(class_module_stats_admin::$STR_PLUGIN_EXTENSION_POINT, "/admin/statsreports");


        $objPlugin = null;
        foreach($objPluginManager->getPlugins(array(class_carrier::getInstance()->getObjDB(), $this->objToolkit, $this->getObjLang())) as $objOneReport) {
            if(uniStrReplace("class_stats_report_", "", get_class($objOneReport)) == $strPlugin) {
                $objPlugin = $objOneReport;
                break;
            }
        }


        if($objPlugin !== null && $objPlugin instanceof interface_admin_statsreports) {
            //get date-params as ints
            $intStartDate = mktime(0, 0, 0, $this->objDateStart->getIntMonth(), $this->objDateStart->getIntDay(), $this->objDateStart->getIntYear());
            $intEndDate = mktime(0, 0, 0, $this->objDateEnd->getIntMonth(), $this->objDateEnd->getIntDay(), $this->objDateEnd->getIntYear());
            $objPlugin->setEndDate($intEndDate);
            $objPlugin->setStartDate($intStartDate);
            $objPlugin->setInterval($this->intInterval);

            $arrImage = $objPlugin->getReportGraph();

            if(!is_array($arrImage)) {
                $arrImage = array($arrImage);
            }

            foreach($arrImage as $strImage) {
                if($strImage != "") {
                    $strReturn .= $this->objToolkit->getGraphContainer($strImage);
                }
            }


            $strReturn .= $objPlugin->getReport();
            $strReturn = "<content><![CDATA[".$strReturn."]]></content>";
        }

        return $strReturn;
    }

}

