<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_search_portal_xml.php 3597 2011-02-11 14:09:51Z sidler $						    *
********************************************************************************************************/

/**
 * Portal-class of the search.
 * Serves xml-requests, e.g. generates search results
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_module_search_admin extends class_admin implements interface_admin {

    private static $INT_MAX_NR_OF_RESULTS = 30;

    /**
     * Constructor
     *
     */
    public function __construct() {
        $this->setArrModuleEntry("modul", "search");
        $this->setArrModuleEntry("moduleId", _search_module_id_);
        parent::__construct();
    }




	/**
	 * Searches for a passed string
	 *
	 * @return string
     * @permissions view
     * @xml
	 */
	protected function actionSearchXml() {
	    $strReturn = "";

	    $strSearchterm = "";
	    if($this->getParam("query") != "") {
			$strSearchterm = htmlToString(urldecode($this->getParam("query")), true);
		}

		$arrResult = array();
	    $objSearchCommons = new class_module_search_commons();
	    if($strSearchterm != "") {
	        $arrResult = $objSearchCommons->doAdminSearch($strSearchterm);
	    }

	    $strReturn .= $this->createSearchXML($strSearchterm, $arrResult);

        return $strReturn;
	}


    /**
     * @param $strSearchterm
     * @param class_search_result[] $arrResults
     *
     * @return string
     */
    private function createSearchXML($strSearchterm, $arrResults) {
        $strReturn = "";

        $strReturn .=
        "<search>\n"
	    ."  <searchterm>".xmlSafeString($strSearchterm)."</searchterm>\n"
	    ."  <nrofresults>".count($arrResults)."</nrofresults>\n";



        //And now all results
        $intI = 0;
        $strReturn .= "    <resultset>\n";
        foreach($arrResults as $objOneResult) {

            if(++$intI > self::$INT_MAX_NR_OF_RESULTS)
                break;

            //create a correct link
            if($objOneResult->getObjObject() == null)
                continue;

            $strIcon = "";
            if($objOneResult->getObjObject() instanceof interface_admin_listable) {
                $strIcon = $objOneResult->getObjObject()->getStrIcon();
                if(is_array($strIcon))
                    $strIcon = $strIcon[0];
            }

            $strLink = $objOneResult->getStrPagelink();
            if($strLink == "")
                $strLink = getLinkAdminHref($objOneResult->getObjObject()->getArrModule("modul"), "edit", "&systemid=".$objOneResult->getStrSystemid());

            $strReturn .=
             "        <item>\n"
		    ."            <systemid>".$objOneResult->getStrSystemid()."</systemid>\n"
		    ."            <icon>".xmlSafeString($strIcon)."</icon>\n"
		    ."            <score>".$objOneResult->getIntHits()."</score>\n"
		    ."            <description>".xmlSafeString(uniStrTrim($objOneResult->getObjObject()->getStrDisplayName(), 200))."</description>\n"
		    ."            <link>".xmlSafeString($strLink)."</link>\n"
		    ."        </item>\n";
        }

        $strReturn .= "    </resultset>\n";
	    $strReturn .= "</search>";
        return $strReturn;
	}
}
