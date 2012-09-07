<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        		*
********************************************************************************************************/


/**
 * @package module_dashboard
 *
 */
class class_adminwidget_note extends class_adminwidget implements interface_adminwidget {

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("content"));
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputTextArea("content", $this->getLang("note_content"), $this->getFieldValue("content"));
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
        return $this->widgetText(nl2br($this->getFieldValue("content")));
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
        if(class_module_system_aspect::getAspectByName("content") !== null) {
            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:1:{s:7:\"content\";s:20:\"Welcome to Kajona V4\";}");
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
        return $this->getLang("note_name");
    }

}


