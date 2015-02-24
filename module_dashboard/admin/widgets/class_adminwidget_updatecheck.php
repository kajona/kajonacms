<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/


/**
 * A widget to check the availability of possible updates
 *
 * @package module_dashboard
 */
class class_adminwidget_updatecheck extends class_adminwidget implements interface_adminwidget {

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        return "";
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

        if(!class_module_system_module::getModuleByName("packagemanager")->rightEdit())
            return $this->getLang("commons_error_permissions");

        $objManager = new class_module_packagemanager_manager();
        $arrRemotePackages = $objManager->scanForUpdates();

        $strSystemVersion = "n.a.";
        if(isset($arrRemotePackages["system"]))
            $strSystemVersion = $arrRemotePackages["system"];

        $arrUpdates = array();
        $arrLocalPackages = $objManager->getAvailablePackages();
        foreach($arrLocalPackages as $objOneMetadata) {
            if(isset($arrRemotePackages[$objOneMetadata->getStrTitle()])) {
                if($arrRemotePackages[$objOneMetadata->getStrTitle()] != null && version_compare($arrRemotePackages[$objOneMetadata->getStrTitle()], $objOneMetadata->getStrVersion(), ">"))
                    $arrUpdates[$objOneMetadata->getStrTitle()] = $arrRemotePackages[$objOneMetadata->getStrTitle()];
            }
        }


        $strReturn .= $this->widgetText($this->getLang("sysinfo_kajona_version")." ".class_module_system_module::getModuleByName("system")->getStrVersion());
        $strReturn .= $this->widgetText($this->getLang("sysinfo_kajona_versionAvail")." ".$strSystemVersion);
        $strReturn .= $this->widgetSeparator();
        if(count($arrUpdates) > 0)
            $strReturn .= $this->widgetText($this->getLang("updatecheck_versionAvail"));
        foreach($arrUpdates as $strPackage => $intVersion) {
            $strReturn .= $this->widgetText(class_link::getLinkAdmin("packagemanager", "list", "&packagelist_filter=".$strPackage."&doFilter=1", $strPackage." (".$intVersion.")"));
        }


        return $strReturn;
    }

    /**
     * This callback is triggered on a users' first login into the system.
     * You may use this method to install a widget as a default widget to
     * a users dashboard.
     *
     * @param string $strUserid
     *
     * @return bool
     */
    public function onFistLogin($strUserid) {
        if(class_module_system_module::getModuleByName("system") !== null && class_module_system_aspect::getAspectByName("content") !== null) {
            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            return $objDashboard->updateObjectToDb(class_module_dashboard_widget::getWidgetsRootNodeForUser($strUserid, class_module_system_aspect::getAspectByName("content")->getSystemid()));
        }

        return true;
    }

    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName() {
        return $this->getLang("updatecheck_name");
    }


}


