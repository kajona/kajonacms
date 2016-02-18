<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$		                        *
********************************************************************************************************/

namespace Kajona\Pages\Admin\Widgets;

use Kajona\Dashboard\Admin\Widgets\Adminwidget;
use Kajona\Dashboard\System\DashboardWidget;
use Kajona\Pages\System\PagesPage;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\Link;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemCommon;
use Kajona\System\System\SystemModule;


/**
 * A widget rendering the pages last modified
 */
class AdminwidgetLastmodifiedpages extends Adminwidget implements AdminInterface
{

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct()
    {
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
    public function getEditForm()
    {
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
    public function getWidgetOutput()
    {
        $strReturn = "";

        if (!SystemModule::getModuleByName("pages")->rightView()) {
            return $this->getLang("commons_error_permissions");
        }

        $intMax = $this->getFieldValue("nrofrows");
        if ($intMax < 0) {
            $intMax = 1;
        }

        /** @var PagesPage[] $arrRecords */
        $arrRecords = SystemCommon::getLastModifiedRecords($intMax, false, "Kajona\\Pages\\System\\PagesPage");

        foreach ($arrRecords as $objPage) {
            if ($objPage->rightEdit()) {
                $strReturn .= $this->widgetText(Link::getLinkAdmin("pages_content", "list", "&systemid=".$objPage->getSystemid(), $objPage->getStrDisplayName()));
            }
            else {
                $strReturn .= $this->widgetText($objPage->getStrDisplayName());
            }

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
    public function onFistLogin($strUserid)
    {
        if (SystemModule::getModuleByName("pages") !== null && SystemAspect::getAspectByName("content") !== null) {
            $objDashboard = new DashboardWidget();
            $objDashboard->setStrColumn("column1");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:1:{s:8:\"nrofrows\";s:1:\"4\";}");
            return $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, SystemAspect::getAspectByName("content")->getSystemid()));
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
        return $this->getLang("lmpages_name");
    }

}


