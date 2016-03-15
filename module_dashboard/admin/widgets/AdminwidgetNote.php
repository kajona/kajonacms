<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        		*
********************************************************************************************************/

namespace Kajona\Dashboard\Admin\Widgets;

use Kajona\Dashboard\System\DashboardWidget;
use Kajona\System\System\SystemAspect;

/**
 * @package module_dashboard
 *
 */
class AdminwidgetNote extends Adminwidget implements AdminwidgetInterface {

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
        if(SystemAspect::getAspectByName("content") !== null) {
            $objDashboard = new DashboardWidget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:1:{s:7:\"content\";s:1460:\"Welcome to Kajona V5!<br /><br  />Kajona is developed by volunteers all over the world - show them your support by liking Kajona on facebook or donating a beer.<div id=\"fb-root\"></div><script>(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) {return;} js = d.createElement(s); js.id = id; js.src = \"//connect.facebook.net/en_US/all.js#appId=141503865945925&xfbml=1\"; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk'));</script><div class=\"fb-like\" data-href=\"https://www.facebook.com/pages/Kajona%C2%B3/156841314360532\" data-send=\"false\" data-layout=\"button_count\" data-width=\"60\" data-show-faces=\"false\"></div><form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\"><input type=\"hidden\" name=\"cmd\" value=\"_donations\" /> <input type=\"hidden\" name=\"business\" value=\"donate@kajona.de\" /> <input type=\"hidden\" name=\"item_name\" value=\"Kajona Development\" /> <input type=\"hidden\" name=\"no_shipping\" value=\"0\" /> <input type=\"hidden\" name=\"no_note\" value=\"1\" /> <input type=\"hidden\" name=\"currency_code\" value=\"EUR\" /> <input type=\"hidden\" name=\"tax\" value=\"0\" /> <input type=\"hidden\" name=\"bn\" value=\"PP-DonationsBF\" /> <input type=\"image\" border=\"0\" src=\"https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\" /> <img height=\"1\" width=\"1\" border=\"0\" alt=\"\" src=\"https://www.paypal.com/en_US/i/scr/pixel.gif\" /></form>\";}");
            return $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, SystemAspect::getAspectByName("content")->getSystemid()));
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


