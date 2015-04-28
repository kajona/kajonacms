<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$		                        *
********************************************************************************************************/


/**
 * @package module_dashboard
 *
 */
class class_adminwidget_systemlog extends class_adminwidget implements interface_adminwidget {

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("nrofrows"));
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputText("nrofrows", $this->getLang("syslog_nrofrows"), $this->getFieldValue("nrofrows"));
        return $strReturn;
    }

    /**
     * This method is called, when the widget should generate it's content.
     * Return the complete content using the methods provided by the base class.
     * Do NOT use the toolkit right here!
     * @return string
     */
    public function getWidgetOutput() {
        $strReturn = "";

        if(!class_module_system_module::getModuleByName("system")->rightRight3() || !class_carrier::getInstance()->getObjSession()->isSuperAdmin())
            return $this->getLang("commons_error_permissions");

        $objFilesystem = new class_filesystem();
        $arrFiles = $objFilesystem->getFilelist(_projectpath_."/log", array(".log"));

        foreach($arrFiles as $strName) {
            $objFilesystem->openFilePointer(_projectpath_."/log/".$strName, "r");
            $strLogContent = $objFilesystem->readLastLinesFromFile($this->getFieldValue("nrofrows"));
            $objFilesystem->closeFilePointer();

            $strLogContent = str_replace(array("INFO", "ERROR"), array("INFO   ", "ERROR  "), $strLogContent);
            $arrLogEntries = explode("\r", $strLogContent);
            $strReturn .= $this->objToolkit->getPreformatted($arrLogEntries);

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
            $objDashboard->setStrColumn("column3");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:1:{s:8:\"nrofrows\";s:1:\"1\";}");
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
        return $this->getLang("syslog_name");
    }

}


