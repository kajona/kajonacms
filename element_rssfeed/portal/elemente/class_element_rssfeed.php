<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_rssfeed.php																		    *
* 	Portal-class of the rssfeed element														    	    *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_rssfeed.php 1509 2007-04-23 16:35:19Z sidler $                                   *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package modul_pages
 */
class class_element_rssfeed extends class_element_portal implements interface_portal_element {

    private $arrError = array();

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_rssfeed";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_universal";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
	public function loadData() {
		$strReturn = "";
		
		$strFeed = @file_get_contents($this->arrElementData["char2"]);
		
		$strFeedTemplateID = $this->objTemplate->readTemplate("/element_rssfeed/".$this->arrElementData["char1"], "rssfeed_feed");
        $strPostTemplateID = $this->objTemplate->readTemplate("/element_rssfeed/".$this->arrElementData["char1"], "rssfeed_post");
		
		$strContent = "";
		$arrTemplate = array();
		if(uniStrlen($strFeed) == 0) {
		  $strContent = $this->getText("rssfeed_errorloading");
		}
		else {
		    include_once(_systempath_."/class_xml_parser.php");
		    $objXmlparser = new class_xml_parser();
		    $objXmlparser->loadString($strFeed);
		    
		    $arrFeed = $objXmlparser->xmlToArray();
		    
		    if(count($arrFeed) >= 1) {
    		    $arrTemplate["feed_title"] = $arrFeed["rss"][0]["channel"][0]["title"][0]["value"];
    		    $arrTemplate["feed_link"] = $arrFeed["rss"][0]["channel"][0]["link"][0]["value"];
    		    $arrTemplate["feed_description"] = $arrFeed["rss"][0]["channel"][0]["description"][0]["value"];
    		    $intCounter = 0;
    		    foreach ($arrFeed["rss"][0]["channel"][0]["item"] as $arrOneItem) {
    		        
    		        $arrMessage = array();
    		        $arrMessage["post_date"] = (isset($arrOneItem["pubDate"][0]["value"]) ? $arrOneItem["pubDate"][0]["value"] : "");
    		        $arrMessage["post_title"] = (isset($arrOneItem["title"][0]["value"]) ? $arrOneItem["title"][0]["value"] : "");
    		        $arrMessage["post_description"] = (isset($arrOneItem["description"][0]["value"]) ? $arrOneItem["description"][0]["value"] : "");
    		        $arrMessage["post_link"] = (isset($arrOneItem["link"][0]["value"]) ? $arrOneItem["link"][0]["value"] : "");
        
    	   	        $strContent .= $this->objTemplate->fillTemplate($arrMessage, $strPostTemplateID);

    	   	        if(++$intCounter >= $this->arrElementData["int1"])
    	   	           break;
    		    
    		    }
		    }
		    else {
		        $strContent = $this->getText("rssfeed_errorparsing");
		    }
		    
		}
		
		$arrTemplate["feed_content"] = $strContent;
		$strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strFeedTemplateID);
        
		return $strReturn;
	}

	

}
?>