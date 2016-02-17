<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                       			*
********************************************************************************************************/


/**
 * @package module_stats
 *
 */
class class_adminwidget_stats extends class_adminwidget implements interface_adminwidget {
    
    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("current", "day", "last", "nrLast", "chart"));
    }
    
    /**
     * Allows the widget to add additional fields to the edit-/create form. 
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputCheckbox("current", $this->getLang("stats_current"), $this->getFieldValue("current"));
        $strReturn .= $this->objToolkit->formInputCheckbox("chart", $this->getLang("stats_chart"), $this->getFieldValue("chart"));
        $strReturn .= $this->objToolkit->formInputCheckbox("day", $this->getLang("stats_day"), $this->getFieldValue("day"));
        $strReturn .= $this->objToolkit->formInputCheckbox("last", $this->getLang("stats_last"), $this->getFieldValue("last"));
        $strReturn .= $this->objToolkit->formInputText("nrLast", $this->getLang("stats_nrLast"), $this->getFieldValue("nrLast"));
        return $strReturn;
    }
    
    /**
     * This method is called, when the widget should generate it's content.
     * Return the complete content using the methods provided by the base class.
     * Do NOT use the toolkit right here! 
     *
     * @return string
     */
    public function getWidgetOutput() {
        $strReturn = "";

        if(!class_module_system_module::getModuleByName("stats")->rightView())
            return $this->getLang("commons_error_permissions");


        $objStatsCommon = new class_stats_report_common(class_carrier::getInstance()->getObjDB(), class_carrier::getInstance()->getObjToolkit("admin"), class_carrier::getInstance()->getObjLang());
        //check wich infos to produce
        if($this->getFieldValue("current") == "checked") {
            $strReturn .= $this->getLang("stats_online").$objStatsCommon->getNumberOfCurrentUsers();
            
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("chart") == "checked") {
            //load the last view days
            $objDate = new \Kajona\System\System\Date();
            $objDate->setIntHour(0); 
            $objDate->setIntMin(0);
            $objDate->setIntSec(0);
            
            $arrHits = array();
            $arrLabels = array();
            for($intI = 0; $intI<7; $intI++) {
                $objEndDate = clone $objDate;
                $objEndDate->setNextDay();
                $objStatsCommon->setStartDate($objDate->getTimeInOldStyle());
                $objStatsCommon->setEndDate($objEndDate->getTimeInOldStyle());
                
                $arrHits[] = $objStatsCommon->getHits();
                $arrLabels[] = $objDate->getIntDay();
                
                $objDate->setPreviousDay();
            }
            
            $arrHits = array_reverse($arrHits);
            $arrLabels = array_reverse($arrLabels);
            
            
            $strReturn .= $this->widgetText($this->getLang("stats_hits"));
            
            $objChart = class_graph_factory::getGraphInstance();
            $objChart->setArrXAxisTickLabels($arrLabels);
            $objChart->addLinePlot($arrHits, "");
            $objChart->setBitRenderLegend(false);
            $objChart->setIntHeight(220);
            $objChart->setIntWidth(300);
            $objChart->setStrXAxisTitle("");
            $objChart->setStrYAxisTitle("");
            $strReturn .= $objChart->renderGraph();
        }
        if($this->getFieldValue("day") == "checked") {
            //current day:
            //pass date to commons-object
            $objDate = new \Kajona\System\System\Date();
            $objDate->setIntHour(0);
            $objDate->setIntMin(0);
            $objDate->setIntSec(0);
            $strReturn .= $this->widgetText(dateToString($objDate, false));
            
            $objStatsCommon->setStartDate($objDate->getTimeInOldStyle());
            $objDate->setNextDay();
            $objStatsCommon->setEndDate($objDate->getTimeInOldStyle());
            
            $strReturn .= $this->widgetText($this->getLang("stats_hits")." ".$objStatsCommon->getHits());
            $strReturn .= $this->widgetText($this->getLang("stats_visitors")." ".$objStatsCommon->getVisitors());
            
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("last") == "checked") {
            
            $strReturn .= $this->widgetText($this->getLang("stats_ip")." ".$this->getLang("stats_page"));
            
            $intMaxRecords = $this->getFieldValue("nrLast");
            if(!is_numeric($intMaxRecords) || $intMaxRecords > 15)
                $intMaxRecords = 15;
                
            $arrRecordsets = class_carrier::getInstance()->getObjDB()->getPArray("SELECT * FROM "._dbprefix_."stats_data ORDER BY stats_date DESC ", array(), 0, $intMaxRecords-1);
            
            foreach($arrRecordsets as $arrOneRecord) {
                $strReturn .= $this->widgetText($arrOneRecord["stats_ip"]." ".$arrOneRecord["stats_page"]);
            }
            
        }
        return $strReturn;
    }

    /**
     * This callback is triggered on a users' first login into the system.
     * You may use this method to install a widget as a default widget to
     * a users dashboard.
     *
     * @param $strUserid
     *
     * @return bool
     */
    public function onFistLogin($strUserid) {
        if(class_module_system_module::getModuleByName("stats") !== null && class_module_system_aspect::getAspectByName("management") !== null) {
            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:5:{s:7:\"current\";s:7:\"checked\";s:3:\"day\";s:7:\"checked\";s:4:\"last\";s:7:\"checked\";s:6:\"nrLast\";s:1:\"4\";s:5:\"chart\";s:7:\"checked\";}");
            return $objDashboard->updateObjectToDb(class_module_dashboard_widget::getWidgetsRootNodeForUser($strUserid, class_module_system_aspect::getAspectByName("management")->getSystemid()));
        }

        return true;
    }

    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName() {
        return $this->getLang("stats_name");
    }
    
}


