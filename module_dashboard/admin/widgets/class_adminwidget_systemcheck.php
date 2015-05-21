<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
        $strReturn .= $this->objToolkit->formInputCheckbox("php", $this->getLang("systemcheck_checkboxphp"), $this->getFieldValue("php"));
        $strReturn .= $this->objToolkit->formInputCheckbox("kajona", $this->getLang("systemcheck_checkboxkajona"), $this->getFieldValue("kajona"));
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

        if(!class_module_system_module::getModuleByName("system")->rightView() || !class_carrier::getInstance()->getObjSession()->isSuperAdmin())
            return $this->getLang("commons_error_permissions");

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
            $strReturn .= $this->widgetText($this->getLang("systemcheck_php_safemode").(ini_get("safe_mode") ? $this->getLang("commons_yes") : $this->getLang("commons_no") ));
            $strReturn .= $this->widgetText($this->getLang("systemcheck_php_urlfopen").(ini_get("allow_url_fopen") ? $this->getLang("commons_yes") : $this->getLang("commons_no") ));
            $strReturn .= $this->widgetText(
                $this->getLang("systemcheck_php_regglobal").(ini_get("register_globals") ?
                    "<span class=\"nok\">".$this->getLang("commons_yes")."</span>" :
                    "<span class=\"ok\">".$this->getLang("commons_no")."</span>" )
            );
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("kajona") == "checked") {


            $arrFilesAvailable = array(
                "/installer.php", "/debug.php", "/v3_v4_postupdate.php"
            );

            foreach($arrFilesAvailable as $strOneFile) {
                $strReturn .= $this->widgetText(
                    $strOneFile." ".$this->getLang("systemcheck_kajona_filepresent").(is_file(_realpath_.$strOneFile) ?
                        " <span class=\"nok\">".$this->getLang("commons_yes")."</span>" :
                        " <span class=\"ok\">".$this->getLang("commons_no")."</span>")
                );
            }

            $strReturn .= $this->widgetText(
                $this->getLang("systemcheck_kajona_writeper")." /project/system/config/config.php ".( is_writable(_realpath_."/project/system/config/config.php") ?
                        "<span class=\"nok\">".$this->getLang("commons_yes")."</span>" :
                        "<span class=\"ok\">".$this->getLang("commons_no")."</span>")
            );
            $strReturn .= $this->widgetText(
                $this->getLang("systemcheck_kajona_writeper")." /project/log/ ".( is_writable(_realpath_."/project/log/") ?
                        "<span class=\"ok\">".$this->getLang("commons_yes")."</span>" :
                        "<span class=\"nok\">".$this->getLang("commons_no")."</span>" )
            );
            $strReturn .= $this->widgetText(
                $this->getLang("systemcheck_kajona_writeper")." /project/dbdumps/ ".( is_writable(_realpath_."/project/dbdumps/") ?
                        "<span class=\"ok\">".$this->getLang("commons_yes")."</span>" :
                        "<span class=\"nok\">".$this->getLang("commons_no")."</span>")
            );
            $strReturn .= $this->widgetText(
                $this->getLang("systemcheck_kajona_writeper")." /project/temp ".( is_writable(_realpath_."/project/temp") ?
                    "<span class=\"ok\">".$this->getLang("commons_yes")."</span>" :
                    "<span class=\"nok\">".$this->getLang("commons_no")."</span>")
            );
            $strReturn .= $this->widgetText(
                $this->getLang("systemcheck_kajona_writeper")." "._images_cachepath_." ".( is_writable(_realpath_."/"._images_cachepath_) ?
                        "<span class=\"ok\">".$this->getLang("commons_yes")."</span>" :
                        "<span class=\"nok\">".$this->getLang("commons_no")."</span>")
            );

            foreach(class_classloader::getCoreDirectories() as $strOneCore) {
                $strReturn .= $this->widgetText(
                    $this->getLang("systemcheck_kajona_writeper")." /".$strOneCore." ".( is_writable(_realpath_."/".$strOneCore) ?
                        "<span class=\"ok\">".$this->getLang("commons_yes")."</span>" :
                        "<span class=\"nok\">".$this->getLang("commons_no")."</span>")
                );
            }

        }
        return "<div class=\"adminwidget_systemcheck\">".$strReturn."</div>";
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
        if(class_module_system_module::getModuleByName("system") !== null && class_module_system_aspect::getAspectByName("management") !== null) {
            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->setStrColumn("column1");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:2:{s:3:\"php\";s:7:\"checked\";s:6:\"kajona\";s:7:\"checked\";}");
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
        return $this->getLang("systemcheck_name");
    }

}


