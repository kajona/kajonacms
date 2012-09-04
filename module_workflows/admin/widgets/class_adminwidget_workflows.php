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
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName() {
        return $this->getLang("workflows_name");
    }

}


