<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_adminwidget_systemcheck.php																	*
* 	widget showing a few infos about the current system													*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_adminwidget_systemcheck.php 1791 2007-10-08 06:29:06Z sidler $	                        *
********************************************************************************************************/

include_once(_adminpath_."/widgets/class_adminwidget.php");
include_once(_adminpath_."/widgets/interface_adminwidget.php");

class class_adminwidget_systemcheck extends class_adminwidget implements interface_adminwidget {
    
    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("php", "kajona"));
    }
    
    /**
     * Allows the widget to add additional fields to the edit-/create form. 
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputCheckbox("php", $this->getText("systemcheck_checkboxphp"), $this->getFieldValue("php"));
        $strReturn .= $this->objToolkit->formInputCheckbox("kajona", $this->getText("systemcheck_checkboxkajona"), $this->getFieldValue("kajona"));
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
        //check wich infos to produce
        if($this->getFieldValue("php") == "checked") {
            $strReturn .= $this->widgetText($this->getText("systemcheck_php_safemode").(ini_get("safe_mode") ? $this->getText("systemcheck_yes") : $this->getText("systemcheck_no") ));
            $strReturn .= $this->widgetText($this->getText("systemcheck_php_urlfopen").(ini_get("allow_url_fopen") ? $this->getText("systemcheck_yes") : $this->getText("systemcheck_no") ));
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("kajona") == "checked") {
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_installer").(is_dir(_realpath_."/installer") ? 
                        "<span style=\"color: red\">".$this->getText("systemcheck_yes")."</span>" : 
                        "<span style=\"color: green\">".$this->getText("systemcheck_no")."</span>"));
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_configper").( is_writable(_systempath_."/config.php") ? 
                        "<span style=\"color: red\">".$this->getText("systemcheck_yes")."</span>" : 
                        "<span style=\"color: green\">".$this->getText("systemcheck_no")."</span>"));
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_debugper").( is_writable(_systempath_."/debug/") ? 
                        "<span style=\"color: green\">".$this->getText("systemcheck_yes")."</span>" : 
                        "<span style=\"color: red\">".$this->getText("systemcheck_no")."</span>" ));
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_dbdumpsper").( is_writable(_systempath_."/dbdumps/") ? 
                        "<span style=\"color: green\">".$this->getText("systemcheck_yes")."</span>" : 
                        "<span style=\"color: red\">".$this->getText("systemcheck_no")."</span>"));
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_piccacheper").( is_writable(_realpath_."/"._bildergalerie_cachepfad_) ? 
                        "<span style=\"color: green\">".$this->getText("systemcheck_yes")."</span>" : 
                        "<span style=\"color: red\">".$this->getText("systemcheck_no")."</span>"));
            
        }
        return $strReturn;
    }
    
    
    /**
     * Return a short (!) name of the widget.
     *
     * @return 
     */
    public function getWidgetName() {
        return $this->getText("systemcheck_name");
    }
    
}


?>