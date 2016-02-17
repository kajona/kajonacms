<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Admin-Part of the stats, generating all reports an handles requests to workers
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 *
 * @module stats
 * @moduleId _stats_modul_id_
 */
class class_module_stats_admin extends class_admin_controller implements interface_admin {


    public static $STR_SESSION_KEY_DATE_START = "STR_SESSION_KEY_DATE_START";
    public static $STR_SESSION_KEY_DATE_END = "STR_SESSION_KEY_DATE_END";
    public static $STR_SESSION_KEY_INTERVAL = "STR_SESSION_KEY_INTERVAL";

    public static $STR_PLUGIN_EXTENSION_POINT = "core.stats.admin.statsreport";

    /**
     * @var \Kajona\System\System\Date
     */
    private $objDateStart;
    /**
     * @var \Kajona\System\System\Date
     */
    private $objDateEnd;
    private $intInterval = 2;

    /**
     * @var class_pluginmanager
     */
    private $objPluginManager;

    static $arrReports = null;

    /**
     * Constructor

     */
    public function __construct() {
        parent::__construct();


        $intDateStart = class_carrier::getInstance()->getObjSession()->getSession(self::$STR_SESSION_KEY_DATE_START);
        if($intDateStart == "") {
            $intDateStart = strtotime(strftime("%Y-%m", time()) . "-01");
        }

        //Start: first day of current month
        $this->objDateStart = new \Kajona\System\System\Date();
        $this->objDateStart->setTimeInOldStyle($intDateStart);

        //End: Current Day of month
        $intDateEnd = class_carrier::getInstance()->getObjSession()->getSession(self::$STR_SESSION_KEY_DATE_END);
        if($intDateEnd == "") {
            $intDateEnd = time() + 3600 * 24;
        }

        $this->objDateEnd = new \Kajona\System\System\Date();
        $this->objDateEnd->setTimeInOldStyle($intDateEnd);


        $this->intInterval = class_carrier::getInstance()->getObjSession()->getSession(self::$STR_SESSION_KEY_INTERVAL);
        if($this->intInterval == "") {
            $this->intInterval = 2;
        }

        class_carrier::getInstance()->getObjSession()->setSession(self::$STR_SESSION_KEY_DATE_START, $intDateStart);
        class_carrier::getInstance()->getObjSession()->setSession(self::$STR_SESSION_KEY_DATE_END, $intDateEnd);
        class_carrier::getInstance()->getObjSession()->setSession(self::$STR_SESSION_KEY_INTERVAL, $this->intInterval);


        //stats may take time -> increase the time available
        if(@ini_get("max_execution_time") < 500 && @ini_get("max_execution_time") > 0)
            @ini_set("max_execution_time", "500");

        //stats may consume a lot of memory, increase max mem limit
        if(class_carrier::getInstance()->getObjConfig()->getPhpIni("memory_limit") < 30) {
            @ini_set("memory_limit", "60M");
        }

        $this->objPluginManager = new class_pluginmanager(self::$STR_PLUGIN_EXTENSION_POINT, "/admin/statsreports");

        $this->setAction("list");
    }


    public function getOutputModuleNavi() {
        $arrReturn = array();
        //Load all plugins available and create the navigation
        if($this->objPluginManager != null) {

            /** @var interface_admin_statsreports[] $arrReports */
            $arrReports = $this->getArrReports();

            foreach($arrReports as $objPlugin) {
                $arrReturn[] = array("view", getLinkAdmin($this->getArrModule("modul"), $this->getActionForReport($objPlugin), "", $objPlugin->getTitle(), "", "", true, "adminnavi"));
            }
        }

        return $arrReturn;
    }


    /**
     * @return string|void
     * @permissions view
     * @autoTestable
     */
    protected function actionList() {
        //In every case, we should generate the date-selector
        $this->processDates();

        $strAction = $this->getParam("action");
        if($strAction == "") {
            $strAction = "common";
            $this->setParam("action", $strAction);
        }

        //And now we have to load the requested plugin
        return $this->loadRequestedPlugin($strAction);
    }

    /**
     * Creates a pathnavigation through all folders till the current page / folder
     *
     * @return array
     */
    protected function getArrOutputNaviEntries() {
        $arrPathLinks = parent::getArrOutputNaviEntries();

        foreach($this->getArrReports() as $objOneReport) {
            if($this->getActionForReport($objOneReport) == $this->getParam("action")) {
                $arrPathLinks[] = getLinkAdmin($this->getArrModule("modul"), $this->getActionForReport($objOneReport), "", $objOneReport->getTitle());
            }
        }

        return $arrPathLinks;
    }


    /**
     * Loads the given plugin, i.e. the given report.
     * Creates an instance, passes control an returns parsed data
     *
     * @param string $strPlugin
     *
     * @return string
     */
    private function loadRequestedPlugin($strPlugin) {
        $strReturn = "";

        $objPlugin = null;
        foreach($this->getArrReports() as $objOneReport) {
            if($this->getActionForReport($objOneReport) == $strPlugin) {
                $objPlugin = $objOneReport;
                break;
            }
        }

        if($objPlugin) {
            $strReturn .= $this->getInlineLoadingCode($objPlugin);
            //place date-selector before
            $strReturn = $this->createDateSelector($objPlugin) . $strReturn;
        }
        return $strReturn;
    }


    /**
     * Creates a small form to set the date-interval of the current report
     *
     * @param \interface_admin_statsreports|null $objReport
     *
     * @return string
     */
    private function createDateSelector(interface_admin_statsreports $objReport = null) {
        $strReturn = "";

        //And create the selector
        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], $this->getParam("action")));
        $strReturn .= $this->objToolkit->formInputHidden("sort", $this->getParam("sort"));
        $strReturn .= $this->objToolkit->formInputHidden("action", $this->getParam("action"));
        $strReturn .= $this->objToolkit->formInputHidden("filter", "true");
        $strReturn .= $this->objToolkit->formDateSingle("start", $this->getLang("start"), $this->objDateStart);
        $strReturn .= $this->objToolkit->formDateSingle("end", $this->getLang("ende"), $this->objDateEnd);

        //create interval dropdown?
        if($objReport != null) {
            if($objReport instanceof interface_admin_statsreports) {
                if($objReport->isIntervalable()) {
                    $arrOption = array();
                    $arrOption["1"] = $this->getLang("interval_1day");
                    $arrOption["2"] = $this->getLang("interval_2days");
                    $arrOption["7"] = $this->getLang("interval_7days");
                    $arrOption["15"] = $this->getLang("interval_15days");
                    $arrOption["30"] = $this->getLang("interval_30days");
                    $arrOption["60"] = $this->getLang("interval_60days");
                    $strReturn .= $this->objToolkit->formInputDropdown("interval", $arrOption, $this->getLang("interval"), $this->intInterval);
                }
            }
        }
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("filtern"));
        $strReturn .= $this->objToolkit->formClose();
        $strReturn = "<div class=\"dateSelector\">" . $strReturn . "</div>";

        return $strReturn;
    }

    /**
     * Creates int-values of the passed date-values
     */
    private function processDates() {

        if($this->getParam("filter") == "true") {

            $this->objDateStart = new \Kajona\System\System\Date();
            $this->objDateStart->generateDateFromParams("start", $this->getAllParams());

            $this->objDateEnd = new \Kajona\System\System\Date();
            $this->objDateEnd->generateDateFromParams("end", $this->getAllParams());

            class_carrier::getInstance()->getObjSession()->setSession(self::$STR_SESSION_KEY_DATE_START, $this->objDateStart->getTimeInOldStyle());
            class_carrier::getInstance()->getObjSession()->setSession(self::$STR_SESSION_KEY_DATE_END, $this->objDateEnd->getTimeInOldStyle());

            if($this->getParam("interval") != "") {
                $this->intInterval = (int)$this->getParam("interval");
            }
            else {
                $this->intInterval = 2;
            }


            class_carrier::getInstance()->getObjSession()->setSession(self::$STR_SESSION_KEY_INTERVAL, $this->intInterval);
        }
    }


    /**
     * Creates the code required to load the report via an ajax request
     *
     * @param interface_admin_statsreports $objPlugin
     * @param string $strPv
     *
     * @return string
     */
    private function getInlineLoadingCode($objPlugin, $strPv = "") {
        $strReturn = "<script type=\"text/javascript\">
                            $(document).ready(function() {
                                  KAJONA.admin.ajax.genericAjaxCall(\"stats\", \"getReport\", \"&plugin=" . $this->getActionForReport($objPlugin) . "&pv=" . $strPv . "\", function(data, status, jqXHR) {

                                    if(status == 'success')  {
                                        var intStart = data.indexOf(\"[CDATA[\")+7;
                                        document.getElementById(\"report_container\").innerHTML=data.substr(
                                          intStart, data.indexOf(\"]]>\")-intStart
                                        );
                                        if(data.indexOf(\"[CDATA[\") < 0) {
                                            var intStart = data.indexOf(\"<error>\")+7;
                                            document.getElementById(\"report_container\").innerHTML=data.substr(
                                              intStart, data.indexOf(\"</error>\")-intStart
                                            );
                                        }
                                        //trigger embedded js-code
                                        try { KAJONA.admin.tooltip.initTooltip(); KAJONA.util.evalScript(data); } catch (objEx) { console.warn(objEx)};
                                    }
                                    else  {
                                        KAJONA.admin.statusDisplay.messageError(\"<b>Request failed!</b><br />\" + data);
                                    }
                                  })
                            });
                          </script>";

        $strReturn .= "<div id=\"report_container\" ><div class=\"loadingContainer\"></div></div>";
        return $strReturn;
    }

    protected function getOutputActionTitle() {
        foreach($this->getArrReports() as $objOneReport) {
            if($this->getActionForReport($objOneReport) == $this->getParam("action")) {
                return $objOneReport->getTitle();
            }
        }

        return parent::getOutputActionTitle();
    }

    protected function getQuickHelp() {
        $strOldAction = $this->getAction();
        $this->setAction($this->getParam("action"));
        $strReturn = parent::getQuickHelp();
        $this->setAction($strOldAction);
        return $strReturn;
    }

    /**
     * @return interface_admin_statsreports[]
     */
    private function getArrReports() {
        if(self::$arrReports == null)
            self::$arrReports = $this->objPluginManager->getPlugins(array(class_carrier::getInstance()->getObjDB(), $this->objToolkit, $this->getObjLang()));

        uasort(self::$arrReports, function(interface_admin_statsreports $objA, interface_admin_statsreports $objB) {
            return strcmp($objA->getTitle(), $objB->getTitle());
        });

        return self::$arrReports;
    }

    private function getActionForReport(interface_admin_statsreports $objReport) {
        return uniStrReplace("class_stats_report_", "", get_class($objReport));
    }
}
