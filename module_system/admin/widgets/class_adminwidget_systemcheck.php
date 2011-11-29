<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/


/**
 * @package module_dashboard
 *
 */
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
        $strReturn = "<style type=\"text/css\">
            .adminwidget_systemcheck .ok {
                color: green;
            }
            .adminwidget_systemcheck .nok {
                color: red;
                font-weight: bold;
            }
        </style>";

        //check wich infos to produce
        if($this->getFieldValue("php") == "checked") {
            $strReturn .= $this->widgetText($this->getText("systemcheck_php_safemode").(ini_get("safe_mode") ? $this->getText("commons_yes") : $this->getText("commons_no") ));
            $strReturn .= $this->widgetText($this->getText("systemcheck_php_urlfopen").(ini_get("allow_url_fopen") ? $this->getText("commons_yes") : $this->getText("commons_no") ));
            $strReturn .= $this->widgetText($this->getText("systemcheck_php_regglobal").(ini_get("register_globals") ?
                         "<span class=\"nok\">".$this->getText("commons_yes")."</span>" :
                         "<span class=\"ok\">".$this->getText("commons_no")."</span>" ));
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("kajona") == "checked") {
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_installer").(is_dir(_realpath_."/installer") ?
                        "<span class=\"nok\">".$this->getText("commons_yes")."</span>" :
                        "<span class=\"ok\">".$this->getText("commons_no")."</span>"));
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_debug").(is_dir(_realpath_."/debug") ?
                        "<span class=\"nok\">".$this->getText("commons_yes")."</span>" :
                        "<span class=\"ok\">".$this->getText("commons_no")."</span>"));
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_configper").( is_writable(_systempath_."/config/config.php") ?
                        "<span class=\"nok\">".$this->getText("commons_yes")."</span>" :
                        "<span class=\"ok\">".$this->getText("commons_no")."</span>"));
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_debugper").( is_writable(_systempath_."/debug/") ?
                        "<span class=\"ok\">".$this->getText("commons_yes")."</span>" :
                        "<span class=\"nok\">".$this->getText("commons_no")."</span>" ));
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_dbdumpsper").( is_writable(_systempath_."/dbdumps/") ?
                        "<span class=\"ok\">".$this->getText("commons_yes")."</span>" :
                        "<span class=\"nok\">".$this->getText("commons_no")."</span>"));
            $strReturn .= $this->widgetText($this->getText("systemcheck_kajona_piccacheper").( is_writable(_realpath_."/"._images_cachepath_) ?
                        "<span class=\"ok\">".$this->getText("commons_yes")."</span>" :
                        "<span class=\"nok\">".$this->getText("commons_no")."</span>"));

        }
        return "<div class=\"adminwidget_systemcheck\">".$strReturn."</div>";
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