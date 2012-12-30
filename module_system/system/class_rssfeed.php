<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/


/**
 * A simple class to generate a xml-based rss-feed.
 * At the moment, the class may be used to generate feeds, only.
 * Therefore, all relevant items and meta-information has to be set before
 * rendering the feed.
 *
 * A rendered feed may be sent to the browser by the usual xml-response schema.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_rssfeed {

    private $strTitle = "Kajona News Feed";
    private $strLink = _webpath_;
    private $strDesc = "";
    private $strGenerator = "Kajona, www.kajona.de";

    private $strEntries ="";

    /**
     * Adds a single element to the feed. Elements will appear in order of adding, so in FIFO-mode
     *
     * @param $strTitle
     * @param $strLink
     * @param $strGuid
     * @param $strDesc
     * @param $intTime
     */
    public function addElement($strTitle, $strLink, $strGuid, $strDesc, $intTime) {
        $this->strEntries .=
            "        <item>\n"
                ."            <title>".xmlSafeString($strTitle)."</title>\n"
                ."            <link>".$strLink."</link>\n"
                ."            <guid isPermaLink=\"false\">".$strGuid."</guid>\n"
                ."            <description>".xmlSafeString($strDesc)."</description>\n"
                ."            <pubDate>".strftime("%a, %d %b %Y %H:%M:%S GMT", $intTime)."</pubDate>\n"
                ."        </item>\n";
    }

    /**
     * Renders the complete rss-feed.
     *
     * @return string
     */
    public function generateFeed() {
        $strReturn =
            "<rss version=\"2.0\">\n"
                ."    <channel>\n";

        $strReturn .=
            "        <title>".xmlSafeString($this->strTitle)."</title>\n"
            ."        <link>".xmlSafeString($this->strLink)."</link>\n"
            ."        <description>".xmlSafeString($this->strDesc)."</description>\n"
            ."        <generator>".xmlSafeString($this->strGenerator)."</generator>\n";

        $strReturn .= $this->strEntries;

        $strReturn .=
            "    </channel>\n"
                ."</rss>";
        return $strReturn;
    }


    public function setStrDesc($strDesc) {
        $this->strDesc = $strDesc;
    }

    public function getStrDesc() {
        return $this->strDesc;
    }

    public function setStrGenerator($strGenerator) {
        $this->strGenerator = $strGenerator;
    }

    public function getStrGenerator() {
        return $this->strGenerator;
    }

    public function setStrLink($strLink) {
        $this->strLink = $strLink;
    }

    public function getStrLink() {
        return $this->strLink;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    public function getStrTitle() {
        return $this->strTitle;
    }



}

