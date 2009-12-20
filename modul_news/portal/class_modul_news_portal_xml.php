<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

/**
 * Portal-class of the news.
 * Serves xml-requests, e.g. generates news-feeds
 *
 * @package modul_news
 */
class class_modul_news_portal_xml extends class_portal implements interface_xml_portal {
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_news";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["table"] 			= _dbprefix_."news";
		$arrModule["table2"]			= _dbprefix_."news_category";
		$arrModule["table3"]			= _dbprefix_."news_member";
		$arrModule["moduleId"] 			= _news_modul_id_;
		$arrModule["modul"]				= "news";

		parent::__construct($arrModule, array());

	}


	/**
	 * Actionblock. Controls the further behaviour.
	 *
	 * @param unknown_type $strAction
	 * @return unknown
	 */
	public function action($strAction) {
        $strReturn = "";
        if($strAction == "newsFeed")
            $strReturn .= $this->createNewsfeed();

        return $strReturn;
	}


	/**
	 * This method loads all data to needed for a newsfeed
	 *
	 * @return srtring
	 */
	private function createNewsfeed() {
	    $strReturn = "";

        //if no sysid was given, try to load from feedname
        $objNewsfeed = null;
		if($this->getParam("feedTitle") != "") {
		    $objNewsfeed = class_modul_news_feed::getFeedByUrlName($this->getParam("feedTitle"));
		}


        if($objNewsfeed != null) {

            //and load all news belonging to the selected cats
            if($objNewsfeed->getStrCat() != "0")
                $arrNews = class_modul_news_feed::getNewsList($objNewsfeed->getStrCat(), $objNewsfeed->getIntAmount());
            else
                $arrNews = class_modul_news_feed::getNewsList("", $objNewsfeed->getIntAmount());

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
	 * @param mixed $arrNews
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
            if($this->objRights->rightView($objOneNews->getSystemid())) {

                $objOneNews->setStrIntro(xmlSafeString($objOneNews->getStrIntro()));

                $strReturn .=
                 "        <item>\n"
			    ."            <title>".xmlSafeString($objOneNews->getStrTitle())."</title>\n"
			    ."            <link>".getLinkPortalHref($strPage, "", "newsDetail", "", $objOneNews->getSystemid(), "", $objOneNews->getStrTitle())."</link>\n"
			    ."            <guid isPermaLink=\"false\">".$objOneNews->getSystemid()."</guid>\n"
			    ."            <description>".xmlSafeString($objOneNews->getStrIntro())."</description>\n"
			    ."            <pubDate>".strftime("%a, %d %b %Y 00:00:00 GMT", $objOneNews->getIntDateStart())."</pubDate>\n"
		        ."        </item>\n";

            }
        }


	    $strReturn .=
        "    </channel>\n"
        ."</rss>";
        return $strReturn;
	}
}
?>