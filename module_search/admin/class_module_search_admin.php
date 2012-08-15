<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						    *
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

        if($this->getParam("asJson") != "")
	        $strReturn .= $this->createSearchJson($strSearchterm, $arrResult);
        else
	        $strReturn .= $this->createSearchXML($strSearchterm, $arrResult);

        return $strReturn;
	}

    /**
     * @param $strSearchterm
     * @param class_search_result[] $arrResults
     *
     * @return string
     */
    private function createSearchJson($strSearchterm, $arrResults) {

        $arrItems = array();
        $intI = 0;
        foreach($arrResults as $objOneResult) {
            $arrItem = array();
            //create a correct link
            if($objOneResult->getObjObject() == null || !$objOneResult->getObjObject()->rightView())
                continue;

            if(++$intI > self::$INT_MAX_NR_OF_RESULTS)
                break;

            $strIcon = "";
            if($objOneResult->getObjObject() instanceof interface_admin_listable) {
                $strIcon = $objOneResult->getObjObject()->getStrIcon();
                if(is_array($strIcon))
                    $strIcon = $strIcon[0];
            }

            $strLink = $objOneResult->getStrPagelink();
            if($strLink == "")
                $strLink = getLinkAdminHref($objOneResult->getObjObject()->getArrModule("modul"), "edit", "&systemid=".$objOneResult->getStrSystemid()."&source=search");

            $arrItem["module"] = class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $objOneResult->getObjObject()->getArrModule("modul"));
            $arrItem["systemid"] = $objOneResult->getStrSystemid();
            $arrItem["icon"] = _skinwebpath_."/pics/".$strIcon;
            $arrItem["score"] = $objOneResult->getStrSystemid();
            $arrItem["description"] = uniStrTrim($objOneResult->getObjObject()->getStrDisplayName(), 200);
            $arrItem["link"] = html_entity_decode($strLink);

            $arrItems[] = $arrItem;
        }


        $objResult = $arrItems;
        class_response_object::getInstance()->setStResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($objResult);
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

            //create a correct link
            if($objOneResult->getObjObject() == null || !$objOneResult->getObjObject()->rightView())
                continue;

            if(++$intI > self::$INT_MAX_NR_OF_RESULTS)
                break;

            $strIcon = "";
            if($objOneResult->getObjObject() instanceof interface_admin_listable) {
                $strIcon = $objOneResult->getObjObject()->getStrIcon();
                if(is_array($strIcon))
                    $strIcon = $strIcon[0];
            }

            $strLink = $objOneResult->getStrPagelink();
            if($strLink == "")
                $strLink = getLinkAdminHref($objOneResult->getObjObject()->getArrModule("modul"), "edit", "&systemid=".$objOneResult->getStrSystemid()."&source=search");

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
