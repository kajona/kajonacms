<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/


/**
 * @package modul_workflows
 *
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
        $strReturn .= $this->widgetText($this->getText("workflows_intro"));
        $strReturn .= $this->widgetText(class_modul_workflows_workflow::getPendingWorkflowsForUserCount(class_carrier::getInstance()->getObjSession()->getUserID()));
        $strReturn .= $this->widgetText(getLinkAdmin("workflows", "myList", "", $this->getText("workflows_show")));
        return $strReturn;


    }


    /**
     * Return a short (!) name of the widget.
     *
     * @return
     */
    public function getWidgetName() {
        return $this->getText("workflows_name");
    }

}


?>