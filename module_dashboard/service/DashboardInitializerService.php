<?php
/*"******************************************************************************************************
*   (c) 2016 ARTEMEON                                                                              *
********************************************************************************************************/

namespace Kajona\Dashboard\Service;


use Kajona\Dashboard\Admin\Widgets\AdminwidgetNote;
use Kajona\Dashboard\Admin\Widgets\AdminwidgetRssfeed;
use Kajona\Dashboard\Admin\Widgets\AdminwidgetSystemcheck;
use Kajona\Dashboard\Admin\Widgets\AdminwidgetSysteminfo;
use Kajona\Dashboard\Admin\Widgets\AdminwidgetSystemlog;
use Kajona\Dashboard\Admin\Widgets\AdminwidgetTodo;
use Kajona\Dashboard\Admin\Widgets\AdminwidgetUpdatecheck;
use Kajona\Dashboard\System\DashboardWidget;
use Kajona\Pages\Admin\Widgets\AdminwidgetLastmodifiedpages;
use Kajona\Stats\Admin\Widgets\AdminwidgetStats;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;

class DashboardInitializerService
{

    /**
     * @param $strUserid
     *
     * @return bool
     */
    public function createInitialDashboard($strUserid)
    {

        if (SystemAspect::getAspectByName("content") !== null) {
            $strContentAspectId = SystemAspect::getAspectByName("content")->getSystemid();

            if (SystemModule::getModuleByName("pages") !== null) {
                $objDashboard = new DashboardWidget();
                $objDashboard->setStrColumn("column1");
                $objDashboard->setStrUser($strUserid);
                $objDashboard->setStrClass(AdminwidgetLastmodifiedpages::class);
                $objDashboard->setStrContent("a:1:{s:8:\"nrofrows\";s:1:\"4\";}");
                $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, $strContentAspectId));
            }

            $objDashboard = new DashboardWidget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(AdminwidgetNote::class);
            $objDashboard->setStrContent("a:1:{s:7:\"content\";s:1460:\"Welcome to Kajona V5!<br /><br  />Kajona is developed by volunteers all over the world - show them your support by liking Kajona on facebook or donating a beer.<div id=\"fb-root\"></div><script>(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) {return;} js = d.createElement(s); js.id = id; js.src = \"//connect.facebook.net/en_US/all.js#appId=141503865945925&xfbml=1\"; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk'));</script><div class=\"fb-like\" data-href=\"https://www.facebook.com/pages/Kajona%C2%B3/156841314360532\" data-send=\"false\" data-layout=\"button_count\" data-width=\"60\" data-show-faces=\"false\"></div><form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\"><input type=\"hidden\" name=\"cmd\" value=\"_donations\" /> <input type=\"hidden\" name=\"business\" value=\"donate@kajona.de\" /> <input type=\"hidden\" name=\"item_name\" value=\"Kajona Development\" /> <input type=\"hidden\" name=\"no_shipping\" value=\"0\" /> <input type=\"hidden\" name=\"no_note\" value=\"1\" /> <input type=\"hidden\" name=\"currency_code\" value=\"EUR\" /> <input type=\"hidden\" name=\"tax\" value=\"0\" /> <input type=\"hidden\" name=\"bn\" value=\"PP-DonationsBF\" /> <input type=\"image\" border=\"0\" src=\"https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\" /> <img height=\"1\" width=\"1\" border=\"0\" alt=\"\" src=\"https://www.paypal.com/en_US/i/scr/pixel.gif\" /></form>\";}");
            $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, $strContentAspectId));

            $objDashboard = new DashboardWidget();
            $objDashboard->setStrColumn("column2");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(AdminwidgetUpdatecheck::class);
            $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, $strContentAspectId));

            $objDashboard = new DashboardWidget();
            $objDashboard->setStrColumn("column3");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(AdminwidgetTodo::class);
            $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, $strContentAspectId));

            $objDashboard = new DashboardWidget();
            $objDashboard->setStrColumn("column3");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(AdminwidgetRssfeed::class);
            $objDashboard->setStrContent("a:2:{s:7:\"feedurl\";s:39:\"http://www.kajona.de/kajona_news_en.rss\";s:5:\"posts\";s:1:\"4\";}");
            $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, $strContentAspectId));

        }



        if (SystemAspect::getAspectByName("management") !== null) {
            $strManagementAspectId = SystemAspect::getAspectByName("management")->getSystemid();

            if (SystemModule::getModuleByName("system") !== null) {
                $objDashboard = new DashboardWidget();
                $objDashboard->setStrColumn("column1");
                $objDashboard->setStrUser($strUserid);
                $objDashboard->setStrClass(AdminwidgetSystemcheck::class);
                $objDashboard->setStrContent("a:2:{s:3:\"php\";s:7:\"checked\";s:6:\"kajona\";s:7:\"checked\";}");
                $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, $strManagementAspectId));

                $objDashboard = new DashboardWidget();
                $objDashboard->setStrColumn("column1");
                $objDashboard->setStrUser($strUserid);
                $objDashboard->setStrClass(AdminwidgetSysteminfo::class);
                $objDashboard->setStrContent("a:3:{s:3:\"php\";s:7:\"checked\";s:6:\"server\";s:7:\"checked\";s:6:\"kajona\";s:7:\"checked\";}");
                $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, $strManagementAspectId));

                $objDashboard = new DashboardWidget();
                $objDashboard->setStrColumn("column3");
                $objDashboard->setStrUser($strUserid);
                $objDashboard->setStrClass(AdminwidgetSystemlog::class);
                $objDashboard->setStrContent("a:1:{s:8:\"nrofrows\";s:1:\"1\";}");
                $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, $strManagementAspectId));
            }

            if (SystemModule::getModuleByName("stats") !== null) {
                $objDashboard = new DashboardWidget();
                $objDashboard->setStrColumn("column2");
                $objDashboard->setStrUser($strUserid);
                $objDashboard->setStrClass(AdminwidgetStats::class);
                $objDashboard->setStrContent("a:5:{s:7:\"current\";s:7:\"checked\";s:3:\"day\";s:7:\"checked\";s:4:\"last\";s:7:\"checked\";s:6:\"nrLast\";s:1:\"4\";s:5:\"chart\";s:7:\"checked\";}");
                $objDashboard->updateObjectToDb(DashboardWidget::getWidgetsRootNodeForUser($strUserid, $strManagementAspectId));
            }
        }


        return true;
    }
}
