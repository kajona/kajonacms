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
class class_adminwidget_systeminfo extends class_adminwidget implements interface_adminwidget {

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("php", "server", "kajona"));
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputCheckbox("php", $this->getLang("sysinfo_checkboxphp"), $this->getFieldValue("php"));
        $strReturn .= $this->objToolkit->formInputCheckbox("server", $this->getLang("sysinfo_checkboxserver"), $this->getFieldValue("server"));
        $strReturn .= $this->objToolkit->formInputCheckbox("kajona", $this->getLang("sysinfo_checkboxkajona"), $this->getFieldValue("kajona"));
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

        if(!class_module_system_module::getModuleByName("system")->rightView() || !class_carrier::getInstance()->getObjSession()->isSuperAdmin())
            return $this->getLang("commons_error_permissions");

        //check wich infos to produce
        if($this->getFieldValue("php") == "checked") {
            $strReturn .= $this->widgetText($this->getLang("sysinfo_php_version").PHP_VERSION);
            $strReturn .= $this->widgetText($this->getLang("sysinfo_php_memlimit").bytesToString(ini_get("memory_limit"), true));
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("server") == "checked") {
            $strReturn .= $this->widgetText($this->getLang("sysinfo_server_system") . php_uname("s") . " " . php_uname("r"));
            if(@disk_total_space(_realpath_)) {
                $strReturn .= $this->widgetText($this->getLang("sysinfo_server_diskspace") . bytesToString(@disk_total_space(_realpath_)));
                $strReturn .= $this->widgetText($this->getLang("sysinfo_server_diskspacef") . bytesToString(@disk_free_space(_realpath_)));
            }
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("kajona") == "checked") {

            $objManager = new class_module_packagemanager_manager();
            $arrPackageMetadata = $objManager->getAvailablePackages();


            $strReturn .= $this->widgetText($this->getLang("sysinfo_kajona_version")." ".class_module_system_module::getModuleByName("system")->getStrVersion());
            $strReturn .= $this->widgetText($this->getLang("sysinfo_kajona_versionAvail")." ".$this->getLatestKernelVersion());
            $strReturn .= $this->widgetText($this->getLang("sysinfo_kajona_nrOfModules")." ".count(class_module_system_module::getAllModules()));
            $strReturn .= $this->widgetText($this->getLang("sysinfo_kajona_nrOfPackages")." ".count($arrPackageMetadata));
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
        if(class_module_system_module::getModuleByName("system") !== null && class_module_system_aspect::getAspectByName("management") !== null) {
            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->setStrColumn("column1");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:3:{s:3:\"php\";s:7:\"checked\";s:6:\"server\";s:7:\"checked\";s:6:\"kajona\";s:7:\"checked\";}");
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
        return $this->getLang("sysinfo_name");
    }

    /**
     * Queries the kajona-updatecheck-server to fetch the latest version
     *
     * @return string
     */
    private function getLatestKernelVersion() {

        $objManager = new class_module_packagemanager_manager();
        $arrPackages = $objManager->scanForUpdates();

        $strVersion = "n.a.";
        if(isset($arrPackages["system"]))
            $strVersion = $arrPackages["system"];

        return $strVersion;
    }

}


