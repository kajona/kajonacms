<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Rssfeed\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\Date;
use Kajona\System\System\Remoteloader;
use Kajona\System\System\StringUtil;
use Kajona\System\System\XmlParser;


/**
 * Loads the rssfeed specified in the element-settings and prepares the output
 *
 * @package element_rssfeed
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementRssfeedPortal extends ElementPortal implements PortalElementInterface
{
    /**
     * @inheritDoc
     */
    protected function getAnchorTag()
    {
        return "";
    }


    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {
        $strReturn = "";
        $strFeed = "";
        try {
            $objRemoteloader = new Remoteloader();

            if (uniStrtolower(StringUtil::substring($this->arrElementData["char2"], 0, 8)) == "https://") {
                $objRemoteloader->setStrProtocolHeader("https://");
            }

            $this->arrElementData["char2"] = uniStrReplace("&amp;", "&", $this->arrElementData["char2"]);

            $objRemoteloader->setStrHost(uniStrReplace(array("http://", "https://"), "", $this->arrElementData["char2"]));
            $objRemoteloader->setIntPort(0);
            $strFeed = $objRemoteloader->getRemoteContent();
        }
        catch (\Kajona\System\System\Exception $objExeption) {
            $strFeed = "";
        }

        $strContent = "";
        $arrTemplate = array();
        if (uniStrlen($strFeed) == 0) {
            $strContent = $this->getLang("rssfeed_errorloading");
        }
        else {
            $objXmlparser = new XmlParser();
            $objXmlparser->loadString($strFeed);

            $arrFeed = $objXmlparser->xmlToArray();

            if (count($arrFeed) >= 1) {

                //rss feed
                if (isset($arrFeed["rss"])) {

                    $arrTemplate["feed_title"] = $arrFeed["rss"][0]["channel"][0]["title"][0]["value"];
                    $arrTemplate["feed_link"] = $arrFeed["rss"][0]["channel"][0]["link"][0]["value"];
                    $arrTemplate["feed_description"] = $arrFeed["rss"][0]["channel"][0]["description"][0]["value"];
                    $intCounter = 0;

                    if (isset($arrFeed["rss"][0]["channel"][0]["item"]) && is_array($arrFeed["rss"][0]["channel"][0]["item"])) {
                        foreach ($arrFeed["rss"][0]["channel"][0]["item"] as $arrOneItem) {

                            $strDateTime = (isset($arrOneItem["pubDate"][0]["value"]) ? $arrOneItem["pubDate"][0]["value"] : "");
                            $strDate = "";
                            if ($strDateTime != "") {
                                $intDate = strtotime($strDateTime);
                                if ($intDate > 0) {
                                    $objDate = new Date($intDate);
                                    $strDateTime = dateToString($objDate, true);
                                    $strDate = dateToString($objDate, false);
                                }
                            }

                            $arrMessage = array();
                            $arrMessage["post_datetime"] = $strDateTime;
                            $arrMessage["post_date"] = $strDate;
                            $arrMessage["post_title"] = (isset($arrOneItem["title"][0]["value"]) ? $arrOneItem["title"][0]["value"] : "");
                            $arrMessage["post_description"] = (isset($arrOneItem["description"][0]["value"]) ? $arrOneItem["description"][0]["value"] : "");
                            $arrMessage["post_link"] = (isset($arrOneItem["link"][0]["value"]) ? $arrOneItem["link"][0]["value"] : "");

                            $strContent .= $this->objTemplate->fillTemplateFile($arrMessage, "/module_rssfeed/".$this->arrElementData["char1"], "rssfeed_post");

                            if (++$intCounter >= $this->arrElementData["int1"]) {
                                break;
                            }

                        }
                    }
                    else {
                        $strContent = $this->getLang("rssfeed_noentry");
                    }

                }

                //atom feed
                if (isset($arrFeed["feed"]) && isset($arrFeed["feed"][0]["entry"])) {

                    $arrTemplate["feed_title"] = $arrFeed["feed"][0]["title"][0]["value"];
                    $arrTemplate["feed_link"] = $arrFeed["feed"][0]["link"][0]["attributes"]["href"];
                    $arrTemplate["feed_description"] = isset($arrFeed["feed"][0]["subtitle"]) ? $arrFeed["feed"][0]["subtitle"][0]["value"] : "";
                    $intCounter = 0;

                    if (isset($arrFeed["feed"][0]["entry"]) && is_array($arrFeed["feed"][0]["entry"])) {
                        foreach ($arrFeed["feed"][0]["entry"] as $arrOneItem) {

                            $strDateTime = (isset($arrOneItem["updated"][0]["value"]) ? $arrOneItem["updated"][0]["value"] : "");
                            $strDate = "";
                            if ($strDateTime != "") {
                                $intDate = strtotime($strDateTime);
                                if ($intDate > 0) {
                                    $objDate = new Date($intDate);
                                    $strDateTime = dateToString($objDate, true);
                                    $strDate = dateToString($objDate, false);
                                }
                            }

                            $arrMessage = array();
                            $arrMessage["post_datetime"] = $strDateTime;
                            $arrMessage["post_date"] = $strDate;
                            $arrMessage["post_title"] = (isset($arrOneItem["title"][0]["value"]) ? $arrOneItem["title"][0]["value"] : "");
                            $arrMessage["post_description"] = (isset($arrOneItem["summary"][0]["value"]) ? $arrOneItem["summary"][0]["value"] : "");
                            $arrMessage["post_link"] = (isset($arrOneItem["link"][0]["attributes"]["href"]) ? $arrOneItem["link"][0]["attributes"]["href"] : "");
                            
                            //fetch special entries
                            if(isset($arrOneItem["media:thumbnail"]) && isset($arrOneItem["media:thumbnail"][0]["attributes"]["url"])) {
                                $arrMessage["post_image_url"] = $arrOneItem["media:thumbnail"][0]["attributes"]["url"];
                            }

                            if(isset($arrOneItem["author"]) && isset($arrOneItem["author"][0]["name"])) {
                                $arrMessage["post_author"] = $arrOneItem["author"][0]["name"][0]["value"];
                            }

                            $strContent .= $this->objTemplate->fillTemplateFile($arrMessage, "/module_rssfeed/".$this->arrElementData["char1"], "rssfeed_post");

                            if (++$intCounter >= $this->arrElementData["int1"]) {
                                break;
                            }

                        }
                    }
                    else {
                        $strContent = $this->getLang("rssfeed_noentry");
                    }
                }
            }
            else {
                $strContent = $this->getLang("rssfeed_errorparsing");
            }

        }

        $arrTemplate["feed_content"] = $strContent;
        $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/module_rssfeed/".$this->arrElementData["char1"], "rssfeed_feed");

        return $strReturn;
    }

}
