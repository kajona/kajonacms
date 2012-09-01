<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_news_portal_xml.php 3597 2011-02-11 14:09:51Z sidler $									*
********************************************************************************************************/

/**
 * Portal-class of the news.
 * Serves xml-requests, e.g. generates news-feeds
 *
 * @package module_news
 * @author sidler@mulchprod.de
 */
class class_module_news_portal_xml extends class_portal implements interface_xml_portal {
	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("moduleId", _news_module_id_);
        $this->setArrModuleEntry("modul", "news");
        parent::__construct();

	}


	
	/**
	 * This method loads all data to needed for a newsfeed
	 *
	 * @return string
	 */
	protected function actionNewsFeed() {
	    $strReturn = "";

        //if no sysid was given, try to load from feedname
        $objNewsfeed = null;
		if($this->getParam("feedTitle") != "")
		    $objNewsfeed = class_module_news_feed::getFeedByUrlName($this->getParam("feedTitle"));

        if($objNewsfeed != null) {

            //and load all news belonging to the selected category
            if($objNewsfeed->getStrCat() != "0")
                $arrNews = class_module_news_feed::getNewsList($objNewsfeed->getStrCat(), $objNewsfeed->getIntAmount());
            else
                $arrNews = class_module_news_feed::getNewsList("", $objNewsfeed->getIntAmount());

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
	 * @param class_module_news_news[] $arrNews
	 * @return string
	 */
	private function createNewsfeedXML($strTitle, $strLink, $strDesc, $strPage, $arrNews) {
        $strReturn = "";

        //tricky: htmldecode everything
        $strTitle = xmlSafeString($strTitle);
        $strDesc = xmlSafeString($strDesc);

        $strReturn .=
        "<rss version=\"2.0\">\n"
	    ."    <channel>\n";

	    //Build the feed-description
	    $strReturn .=
	    "        <title>".$strTitle."</title>\n"
		."        <link>".$strLink."</link>\n"
		."        <description>".$strDesc."</description>\n"
		."        <generator>Kajona, www.kajona.de</generator>\n";

        //And now all news
        foreach($arrNews as $objOneNews) {
            if($objOneNews->rightView()) {

                $objOneNews->setStrIntro(xmlSafeString($objOneNews->getStrIntro()));
                $objDate = new class_date($objOneNews->getIntDateStart());
                $intTime = mktime($objDate->getIntHour(), $objDate->getIntMin(), $objDate->getIntSec(), $objDate->getIntMonth(), $objDate->getIntDay(), $objDate->getIntYear());

                $strReturn .=
                 "        <item>\n"
			    ."            <title>".xmlSafeString($objOneNews->getStrTitle())."</title>\n"
			    ."            <link>".getLinkPortalHref($strPage, "", "newsDetail", "", $objOneNews->getSystemid(), "", $objOneNews->getStrTitle())."</link>\n"
			    ."            <guid isPermaLink=\"false\">".$objOneNews->getSystemid()."</guid>\n"
			    ."            <description>".xmlSafeString($objOneNews->getStrIntro())."</description>\n"
			    ."            <pubDate>".strftime("%a, %d %b %Y %H:%M:%S GMT", $intTime)."</pubDate>\n"
		        ."        </item>\n";

            }
        }


	    $strReturn .=
        "    </channel>\n"
        ."</rss>";
        return $strReturn;
	}
}
