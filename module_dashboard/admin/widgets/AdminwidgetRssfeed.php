<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                            *
********************************************************************************************************/

namespace Kajona\Dashboard\Admin\Widgets;

use Kajona\Dashboard\System\DashboardWidget;
use Kajona\System\System\Exception;
use Kajona\System\System\Remoteloader;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\XmlParser;

/**
 * @package module_dashboard
 *
 */
class AdminwidgetRssfeed extends Adminwidget implements AdminwidgetInterface {

    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("feedurl", "posts"));
    }

    /**
     * Allows the widget to add additional fields to the edit-/create form.
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputText("feedurl", $this->getLang("rssfeed_feedurl"), $this->getFieldValue("feedurl"));
        $strReturn .= $this->objToolkit->formInputText("posts", $this->getLang("rssfeed_posts"), $this->getFieldValue("posts"));
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

        //request the xml...

        try {

            $arrUrl = parse_url($this->getFieldValue("feedurl"));
            $objRemoteloader = new Remoteloader();

            $intPort = isset($arrUrl["port"]) ? $arrUrl["port"] : "";
            if($intPort == "") {
                if($arrUrl["scheme"] == "https" ? 443 : 80);
            }

            $objRemoteloader->setStrHost($arrUrl["host"]);
            $objRemoteloader->setStrQueryParams($arrUrl["path"].(isset($arrUrl["query"]) ? $arrUrl["query"] : ""));
            $objRemoteloader->setIntPort($intPort);
            $objRemoteloader->setStrProtocolHeader($arrUrl["scheme"]."://");
            $strContent = $objRemoteloader->getRemoteContent();
        }
        catch (Exception $objExeption) {
            $strContent = "";
        }

        if($strContent != "") {
            $objXmlparser = new XmlParser();
            $objXmlparser->loadString($strContent);

            $arrFeed = $objXmlparser->xmlToArray();

            if(count($arrFeed) >= 1) {

                //rss feed
                if(isset($arrFeed["rss"])) {
                    $intCounter = 0;
                    foreach ($arrFeed["rss"][0]["channel"][0]["item"] as $arrOneItem) {

                        $strTitle = (isset($arrOneItem["title"][0]["value"]) ? $arrOneItem["title"][0]["value"] : "");
                        $strLink = (isset($arrOneItem["link"][0]["value"]) ? $arrOneItem["link"][0]["value"] : "");

                        $strReturn .= $this->widgetText("<a href=\"".$strLink."\" target=\"_blank\">".$strTitle."</a>");
                        $strReturn .= $this->widgetSeparator();

                        if(++$intCounter >= $this->getFieldValue("posts"))
                           break;

                    }
                }

                //atom feed
                if(isset($arrFeed["feed"]) && isset($arrFeed["feed"][0]["entry"])) {
                    $intCounter = 0;
                    foreach ($arrFeed["feed"][0]["entry"] as $arrOneItem) {

                        $strTitle = (isset($arrOneItem["title"][0]["value"]) ? $arrOneItem["title"][0]["value"] : "");
                        $strLink = (isset($arrOneItem["link"][0]["attributes"]["href"]) ? $arrOneItem["link"][0]["attributes"]["href"] : "");

                        $strReturn .= $this->widgetText("<a href=\"".$strLink."\" target=\"_blank\">".$strTitle."</a>");
                        $strReturn .= $this->widgetSeparator();

                        if(++$intCounter >= $this->getFieldValue("posts"))
                           break;

                    }
                }
            }
            else {
                $strContent = $this->getLang("rssfeed_errorparsing");
            }

        }
        else
            $strReturn .= $this->getLang("rssfeed_errorloading");



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
        if(SystemAspect::getAspectByName("content") !== null) {
            $objDashboard = new DashboardWidget();
            $objDashboard->setStrColumn("column3");
            $objDashboard->setStrUser($strUserid);
            $objDashboard->setStrClass(__CLASS__);
            $objDashboard->setStrContent("a:2:{s:7:\"feedurl\";s:39:\"http://www.kajona.de/kajona_news_en.rss\";s:5:\"posts\";s:1:\"4\";}");
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
        return $this->getLang("rssfeed_name");
    }

}


