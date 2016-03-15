<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

namespace Kajona\News\Portal;

use Kajona\News\System\NewsFeed;
use Kajona\News\System\NewsNews;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\XmlPortalInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Rssfeed;

/**
 * Portal-class of the news.
 * Serves xml-requests, e.g. generates news-feeds
 *
 * @package module_news
 * @author sidler@mulchprod.de
 *
 * @module news
 * @moduleId _news_module_id_
 */
class NewsPortalXml extends PortalController implements XmlPortalInterface {

    /**
     * This method loads all data to needed for a newsfeed
     *
     * @return string
     */
    protected function actionNewsFeed() {
        $strReturn = "";

        //if no sysid was given, try to load from feedname
        $objNewsfeed = null;
        if($this->getParam("feedTitle") != "") {
            $objNewsfeed = NewsFeed::getFeedByUrlName($this->getParam("feedTitle"));
        }

        if($objNewsfeed != null) {

            //and load all news belonging to the selected category
            if($objNewsfeed->getStrCat() != "0") {
                $arrNews = NewsFeed::getNewsList($objNewsfeed->getStrCat(), $objNewsfeed->getIntAmount());
            }
            else {
                $arrNews = NewsFeed::getNewsList("", $objNewsfeed->getIntAmount());
            }

            $strReturn .= $this->createNewsfeedXML($objNewsfeed->getStrTitle(), $objNewsfeed->getStrLink(), $objNewsfeed->getStrDesc(), $objNewsfeed->getStrPage(), $arrNews);

            //and count the request
            $objNewsfeed->incrementNewsCounter();
        }
        else {
            $strReturn .= $this->createNewsfeedXML("", "", "", "", array());
        }


        return $strReturn;
    }

    /**
     * Responsible for creating the xml-feed
     *
     * @param string $strTitle
     * @param string $strLink
     * @param string $strDesc
     * @param string $strPage
     * @param NewsNews[] $arrNews
     *
     * @return string
     */
    private function createNewsfeedXML($strTitle, $strLink, $strDesc, $strPage, $arrNews) {

        $objFeed = new Rssfeed();
        $objFeed->setStrTitle($strTitle);
        $objFeed->setStrLink($strLink);
        $objFeed->setStrDesc($strDesc);

        foreach($arrNews as $objOneNews) {
            if($objOneNews->rightView()) {
                $objDate = $objOneNews->getObjStartDate();
                if($objDate == null)
                    $objDate = new \Kajona\System\System\Date();

                $objFeed->addElement(
                    $objOneNews->getStrTitle(),
                    Link::getLinkPortalHref($strPage, "", "newsDetail", "", $objOneNews->getSystemid(), "", $objOneNews->getStrTitle()),
                    $objOneNews->getSystemid(),
                    $objOneNews->getStrIntro(),
                    mktime($objDate->getIntHour(), $objDate->getIntMin(), $objDate->getIntSec(), $objDate->getIntMonth(), $objDate->getIntDay(), $objDate->getIntYear())
                );
            }
        }

        return $objFeed->generateFeed();
    }
}
