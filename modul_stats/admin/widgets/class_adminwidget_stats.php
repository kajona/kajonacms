<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_adminwidget_stats.php																			*
* 	widget showing a few infos about the current system													*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                       			*
********************************************************************************************************/

include_once(_adminpath_."/widgets/class_adminwidget.php");
include_once(_adminpath_."/widgets/interface_adminwidget.php");
include_once(_adminpath_."/statsreports/class_stats_report_common.php");

class class_adminwidget_stats extends class_adminwidget implements interface_adminwidget {
    
    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("current", "day", "last", "nrLast"));
    }
    
    /**
     * Allows the widget to add additional fields to the edit-/create form. 
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputCheckbox("current", $this->getText("stats_current"), $this->getFieldValue("current"));
        $strReturn .= $this->objToolkit->formInputCheckbox("day", $this->getText("stats_day"), $this->getFieldValue("day"));
        $strReturn .= $this->objToolkit->formInputCheckbox("last", $this->getText("stats_last"), $this->getFieldValue("last"));
        $strReturn .= $this->objToolkit->formInputText("nrLast", $this->getText("stats_nrLast"), $this->getFieldValue("nrLast"));
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
        $objStatsCommon = new class_stats_report_common(class_carrier::getInstance()->getObjDB(), null, null);
        //check wich infos to produce
        if($this->getFieldValue("current") == "checked") {
            $strReturn .= $this->getText("stats_online").$objStatsCommon->getNumberOfCurrentUsers();
            
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("day") == "checked") {
            //current day:
            $intDay = strtotime(strftime("%Y-%m-%d",time()));
            //pass date to commons-object
            $objStatsCommon->setStartDate($intDay);
            $objStatsCommon->setEndDate($intDay+86400);
            
            $strReturn .= $this->widgetText(timeToString($intDay, false));
            $strReturn .= $this->widgetText($this->getText("stats_hits").$objStatsCommon->getHits());
            $strReturn .= $this->widgetText($this->getText("stats_visitors").$objStatsCommon->getVisitors());
            
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("last") == "checked") {
            
            $strReturn .= $this->widgetText($this->getText("stats_ip")." ".$this->getText("stats_page"));
            
            $intMaxRecords = $this->getFieldValue("nrLast");
            if(!is_numeric($intMaxRecords) || $intMaxRecords > 15)
                $intMaxRecords = 15;
                
            $arrRecordsets = class_carrier::getInstance()->getObjDB()->getArraySection("SELECT * FROM "._dbprefix_."stats_data ORDER BY stats_date DESC ", 0, $intMaxRecords-1);
            
            foreach($arrRecordsets as $arrOneRecord) {
                $strReturn .= $this->widgetText($arrOneRecord["stats_ip"]." ".$arrOneRecord["stats_page"]);
            }
            
        }
        return $strReturn;
    }
    
    
    /**
     * Return a short (!) name of the widget.
     *
     * @return 
     */
    public function getWidgetName() {
        return $this->getText("stats_name");
    }
    
}


?>