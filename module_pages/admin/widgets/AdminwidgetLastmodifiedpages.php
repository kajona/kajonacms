<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$		                        *
********************************************************************************************************/

namespace Kajona\Pages\Admin\Widgets;

use class_adminwidget;
use class_link;
use class_module_dashboard_widget;
use class_module_system_aspect;
use class_module_system_common;
use class_module_system_module;
use interface_adminwidget;
use Kajona\Pages\System\PagesPage;


/**
 * A widget rendering the pages last modified
 */
class AdminwidgetLastmodifiedpages extends class_adminwidget implements interface_adminwidget {

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("nrofrows"));
        $this->setBitBlockSessionClose(true);
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
     *
     * @return string
     */
    public function getWidgetOutput() {
        $strReturn = "";

        if(!class_module_system_module::getModuleByName("pages")->rightView())
            return $this->getLang("commons_error_permissions");

        $intMax = $this->getFieldValue("nrofrows");
        if($intMax < 0)
            $intMax = 1;

        /** @var PagesPage[] $arrRecords */
        $arrRecords = class_module_system_common::getLastModifiedRecords($intMax, false, "class_module_pages_page");

        foreach($arrRecords as $objPage) {
            if($objPage->rightEdit())
                $strReturn .= $this->widgetText(class_link::getLinkAdmin("pages_content", "list", "&systemid=".$objPage->getSystemid(), $objPage->getStrDisplayName()));
            else
                $strReturn .= $this->widgetText($objPage->getStrDisplayName());

            $strReturn .= $this->widgetText("&nbsp; &nbsp; ".timeToString($objPage->getIntLmTime())."");
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
        if(class_module_system_module::getModuleByName("pages") !== null && class_module_system_aspect::getAspectByName("content") !== null) {
            $objDashboard = new class_module_dashboard_widget();
            $objDashboard->setStrColumn("column1");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:1:{s:8:\"nrofrows\";s:1:\"4\";}");
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
        return $this->getLang("lmpages_name");
    }

}


