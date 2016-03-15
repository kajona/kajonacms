<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

namespace Kajona\Workflows\Admin\Widgets;

use Kajona\Dashboard\Admin\Widgets\Adminwidget;
use Kajona\Dashboard\System\DashboardWidget;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\Workflows\System\WorkflowsWorkflow;


/**
 * @package module_workflows
 * @author sidler@mulchprod.de
 */
class AdminwidgetWorkflows extends Adminwidget implements AdminInterface
{

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm()
    {
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
    public function getWidgetOutput()
    {
        $strReturn = "";
        $strReturn .= $this->widgetText($this->getLang("workflows_intro"));
        $strReturn .= $this->widgetText(WorkflowsWorkflow::getPendingWorkflowsForUserCount(array_merge(array(Carrier::getInstance()->getObjSession()->getUserID()), Carrier::getInstance()->getObjSession()->getGroupIdsAsArray())));
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
    public function onFistLogin($strUserid)
    {
        if (SystemModule::getModuleByName("workflows") !== null && SystemAspect::getAspectByName("management") !== null) {
            $objDashboard = new DashboardWidget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("");
            return $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, SystemAspect::getAspectByName("management")->getSystemid()));
        }

        return true;
    }


    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName()
    {
        return $this->getLang("workflows_name");
    }

}


