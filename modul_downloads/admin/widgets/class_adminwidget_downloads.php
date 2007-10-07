<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_adminwidget_downloads.php																		*
* 	widget showing a few entries from the downloads log													*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_adminwidget_downloads.php 1735 2007-10-05 19:45:00Z sidler $	                       	 	*
********************************************************************************************************/

include_once(_adminpath_."/widgets/class_adminwidget.php");
include_once(_adminpath_."/widgets/interface_adminwidget.php");
include_once(_systempath_."/class_modul_downloads_logbook.php");

class class_adminwidget_downloads extends class_adminwidget implements interface_adminwidget {
    
    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("nrOfEntries"));
    }
    
    /**
     * Allows the widget to add additional fields to the edit-/create form. 
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputText("nrOfEntries", $this->getText("downloads_nrOfEntries"), $this->getFieldValue("nrOfEntries"));
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
        
        $objLogbook = new class_modul_downloads_logbook();
        //total downloads:
        $strReturn .= $this->widgetText($this->getText("downloads_total").$objLogbook->getLogbookDataCount());
        $strReturn .= $this->widgetSeparator();
        //fetch the log-entries
        $intMaxNumber = $this->getFieldValue("nrOfEntries");
        if(!is_numeric($intMaxNumber) || $intMaxNumber > 15)
            $intMaxNumber = 15;
            
        $arrLogs = $objLogbook->getLogbookSection(0, $intMaxNumber-1);    
        
        //print headers
        $strReturn .= $this->widgetText($this->getText("downloads_head_date")."&nbsp;&nbsp;".$this->getText("downloads_head_file"));
        
        foreach ($arrLogs as $arrOneLogRow) {
            $strReturn .= $this->widgetText(timeToString($arrOneLogRow["downloads_log_date"]));
            $strReturn .= $this->widgetText("&nbsp;&nbsp;".$arrOneLogRow["downloads_log_file"]);    
        }
        
        return $strReturn;
    }
    
    
    /**
     * Return a short (!) name of the widget.
     *
     * @return 
     */
    public function getWidgetName() {
        return $this->getText("downloads_name");
    }
    
}


?>
 
