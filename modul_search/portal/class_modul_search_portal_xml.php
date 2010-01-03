<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						    *
********************************************************************************************************/

/**
 * Portal-class of the search.
 * Serves xml-requests, e.g. generates search results
 *
 * @package modul_search
 */
class class_modul_search_portal_xml extends class_portal implements interface_xml_portal {
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct() {
		$arrModule["name"] 				= "modul_search";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _suche_modul_id_;
		$arrModule["modul"]				= "search";

		parent::__construct($arrModule, array());
	}


	/**
	 * Actionblock. Controls the further behaviour.
	 *
	 * @param string $strAction
	 * @return string
	 */
	public function action($strAction) {
        $strReturn = "";
        if($strAction == "doSearch")
            $strReturn .= $this->createSearchResult();

        return $strReturn;
	}


	/**
	 * Searches for a passed string
	 *
	 * @return string
	 */
	private function createSearchResult() {
	    $strReturn = "";

	    $strSearchterm = "";
	    if($this->getParam("searchterm") != "") {
			$strSearchterm = htmlToString(urldecode($this->getParam("searchterm")), true);
		}

		$arrResult = array();
	    $objSearchCommons = new class_modul_search_commons();
	    if($strSearchterm != "") {
	        $arrResult = $objSearchCommons->doSearch($strSearchterm);
	    }

	    $strReturn .= $this->createSearchXML($strSearchterm, $arrResult);

        return $strReturn;
	}


	private function createSearchXML($strSearchterm, $arrResults) {
        $strReturn = "";

        $strReturn .=
        "<search>\n"
	    ."    <searchterm>".xmlSafeString($strSearchterm)."</searchterm>\n"
	    ."    <nrofresults>".count($arrResults)."</nrofresults>\n";



        //And now all results
        $strReturn .="    <resultset>\n";
        foreach($arrResults as $arrOneResult) {
            //create a correct link
            if(!isset($arrOneResult["pagelink"]))
				$arrOneResult["pagelink"] = getLinkPortal($arrOneResult["pagename"], "", "_self", $arrOneResult["pagename"], "", "&highlight=".$strSearchterm."#".$strSearchterm);

            $strReturn .=
             "        <item>\n"
		    ."            <pagename>".$arrOneResult["pagename"]."</pagename>\n"
		    ."            <pagelink>".$arrOneResult["pagelink"]."</pagelink>\n"
		    ."            <description>".xmlSafeString($arrOneResult["description"])."</description>\n"
		    ."        </item>\n";
        }

        $strReturn .="    </resultset>\n";
	    $strReturn .= "</search>";
        return $strReturn;
	}
}
?>