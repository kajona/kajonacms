<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_adminwidget_workflows.php 4042 2011-07-25 17:37:44Z sidler $                           *
********************************************************************************************************/


/**
 * @package module_workflows
 * @author sidler@mulchprod.de
 */
class class_adminwidget_workflows extends class_adminwidget implements interface_adminwidget {

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
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
        $strReturn .= $this->widgetText($this->getLang("workflows_intro"));
        $strReturn .= $this->widgetText(class_module_workflows_workflow::getPendingWorkflowsForUserCount(class_carrier::getInstance()->getObjSession()->getUserID()));
        $strReturn .= $this->widgetText(getLinkAdmin("workflows", "myList", "", $this->getLang("workflows_show")));
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
        if(class_module_system_module::getModuleByName("workflows") !== null && class_module_system_aspect::getAspectByName("management") !== null) {
            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("");
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
        return $this->getLang("workflows_name");
    }

}


