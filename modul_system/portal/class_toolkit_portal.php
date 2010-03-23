<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/


/**
 * Portal-Part of the toolkit.
 * Provides a few helpers
 *
 * @package modul_system
 */
class class_toolkit_portal extends class_toolkit {


	public function __construct($strSystemid = 0) {
        $arrModul = array();
		$arrModul["name"] 			= "modul_elemente_admin";
		$arrModul["author"] 		= "sidler@mulchprod.de";

		//Und den Eltern-Teil informieren
		parent::__construct($arrModul, $strSystemid);
	}


	/**
	 * Creates a forward / backward pager out of the passed array
	 *
	 * @param int $intNumber Records per page
	 * @param int $intPage current page
	 * @param string $strForward text for the forwardlink
	 * @param string $strBack text for the backwardslink
	 * @param string $strAction action on the targetpage
	 * @param string $strPage title of the targetpage
	 * @param mixed $arrData the array of records
	 * @param string $strAdd additional params
     * @param string $strPvParam the param used to create the pagenumber-entries
	 * @return mixed array containing the created data:
	 * 						return => [arrData] = array containing the shortened data
	 * 								  [strForward] = link to the next page
	 * 								  [strBack]	= link to the previous page
	 * 								  [strPages] = Pager ( [0][1] ...)
	 */
	public function pager($intNumber, $intPage = 1, $strForward = "weiter", $strBack = "zurueck", $strAction = "list", $strPage = "", $arrData = array(), $strAdd = "", $strPvParam = "pv") {

        if($intPage <= 0)
            $intPage = 1;

        if((int)$intNumber <= 0)
            $intNumber = 100;

		$arrReturn = 	array("arrData" 	=> array(),
							  "strForward" 	=> "",
							  "strBack" 	=> "",
							  "strPages" 	=> "");


		//create array-iterator
		$objIterator = new class_array_iterator($arrData);
		$objIterator->setIntElementsPerPage($intNumber);

		//Anything to create?
		if($objIterator->getNrOfPages() == 1) {
			$arrReturn["arrData"] = $arrData;
		}
		else {
			$strLinkPages = "";
			$strLinkForward = "";
			$strLinkBack = "";

			$arrReturn["arrData"] = $objIterator->getElementsOnPage($intPage);

			//FowardLink
			if($intPage < $objIterator->getNrOfPages())
				$strLinkForward = getLinkPortal($strPage, "", null, $strForward, $strAction, "&".$strPvParam."=".($intPage+1).$strAdd);
			//BackLink
			if($intPage > 1)
				$strLinkBack = getLinkPortal($strPage, "", null, $strBack, $strAction, "&".$strPvParam."=".($intPage-1).$strAdd);
			//Page-Links
			$strLinkPages = "";
			if($intNumber == 0)
				$intNumber = 1;


			//just load the current +-6 pages and the first/last +-3
            $intCounter2 = 1;
            for($intI = 1; $intI <= $objIterator->getNrOfPages(); $intI++) {
                $bitDisplay = false;
                if($intCounter2 <= 3) {
                    $bitDisplay = true;
                }
                elseif ($intCounter2 >= ($objIterator->getNrOfPages()-3)) {
                    $bitDisplay = true;
                }
                elseif ($intCounter2 >= ($intPage-6) && $intCounter2 <= ($intPage+6)) {
                    $bitDisplay = true;
                }


                if($bitDisplay) {
                    if($intI == $intPage)
    					$strLinkPages .= "  <strong>".getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&".$strPvParam."=".$intI.$strAdd)."</strong>";
    				else
    					$strLinkPages .= "  ".getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&".$strPvParam."=".$intI.$strAdd);
                }
                $intCounter2++;
            }



			$arrReturn["strForward"] = $strLinkForward;
			$arrReturn["strBack"] = $strLinkBack;
			$arrReturn["strPages"] = $strLinkPages;
		}

		return $arrReturn;
	}



    /**
	 * Creates a forward / backward pager out of the passed array
	 *
	 * @param class_array_section_iterator $objArraySectionIterator
	 * @param string $strForward text for the forwardlink
	 * @param string $strBack text for the backwardslink
	 * @param string $strAction action on the targetpage
	 * @param string $strPage title of the targetpage
	 * @param string $strAdd additional params
     * @param string $strPvParam the param used to create the pagenumber-entries
	 * @return mixed array containing the created data:
	 * 						return => [arrData] = array containing the shortened data
	 * 								  [strForward] = link to the next page
	 * 								  [strBack]	= link to the previous page
	 * 								  [strPages] = Pager ( [0][1] ...)
	 */
	public function simplePager($objArraySectionIterator, $strForward = "weiter", $strBack = "zurueck", $strAction = "list", $strPage = "", $strAdd = "", $strPvParam = "pv") {

		$arrReturn = 	array("arrData" 	=> array(),
							  "strForward" 	=> "",
							  "strBack" 	=> "",
							  "strPages" 	=> "");



        $strLinkPages = "";
        $strLinkForward = "";
        $strLinkBack = "";

        $arrReturn["arrData"] = $objArraySectionIterator->getArrayExtended(true);

        $intPage = $objArraySectionIterator->getPageNumber();

        //FowardLink
        if($intPage < $objArraySectionIterator->getNrOfPages())
            $strLinkForward = getLinkPortal($strPage, "", null, $strForward, $strAction, "&".$strPvParam."=".($intPage+1).$strAdd);
        //BackLink
        if($intPage > 1)
            $strLinkBack = getLinkPortal($strPage, "", null, $strBack, $strAction, "&".$strPvParam."=".($intPage-1).$strAdd);


        //just load the current +-6 pages and the first/last +-3
        $intCounter2 = 1;
        for($intI = 1; $intI <= $objArraySectionIterator->getNrOfPages(); $intI++) {
            $bitDisplay = false;
            if($intCounter2 <= 3) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($objArraySectionIterator->getNrOfPages()-3)) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intPage-6) && $intCounter2 <= ($intPage+6)) {
                $bitDisplay = true;
            }


            if($bitDisplay) {
                if($intI == $intPage)
                    $strLinkPages .= "  <strong>".getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&".$strPvParam."=".$intI.$strAdd)."</strong>";
                else
                    $strLinkPages .= "  ".getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&".$strPvParam."=".$intI.$strAdd);
            }
            $intCounter2++;
        }

        if($objArraySectionIterator->getNrOfPages() > 1) {
            $arrReturn["strForward"] = $strLinkForward;
            $arrReturn["strBack"] = $strLinkBack;
            $arrReturn["strPages"] = $strLinkPages;
        }

		return $arrReturn;
	}

// ******************************************************************************************************
// --- PORTALEDITOR -------------------------------------------------------------------------------------

    /**
     * Creates the portaleditor toolbar at top of the page
     *
     * @param array $arrContent
     * @return string
     */
    public function getPeToolbar($arrContent) {
        $strAdminSkin = class_carrier::getInstance()->getObjSession()->getAdminSkin();
        $strTemplateID = $this->objTemplate->readTemplate("/admin/skins/".$strAdminSkin."/elements.tpl", "pe_toolbar", true);
		$strReturn = $this->objTemplate->fillTemplate($arrContent, $strTemplateID);

		return $strReturn;
    }

    /**
     * Creates the portaleditor action-toolbar layout
     *
     * @param string $strSystemid
     * @param array $arrLinks
     * @param string $strContent
     */
    public function getPeActionToolbar($strSystemid, $arrLinks, $strContent) {
        $strAdminSkin = class_carrier::getInstance()->getObjSession()->getAdminSkin();
        $strTemplateID = $this->objTemplate->readTemplate("/admin/skins/".$strAdminSkin."/elements.tpl", "pe_actionToolbar", true);
        $strTemplateRowID = $this->objTemplate->readTemplate("/admin/skins/".$strAdminSkin."/elements.tpl", "pe_actionToolbar_link", true);

        $arrTemplate = array();
        $arrTemplate["actionlinks"] = "";
        foreach ($arrLinks as $strOneLink) {
            $arrRowTemplate = array();
            $arrRowTemplate["link_complete"] = $strOneLink;
            //use regex to get href and name
            $arrTemp = splitUpLink($strOneLink);
            $arrRowTemplate["name"] = $arrTemp["name"];
            $arrRowTemplate["href"] = $arrTemp["href"];
            $arrTemplate["actionlinks"] .= $this->objTemplate->fillTemplate($arrRowTemplate, $strTemplateRowID);
        }
        //FIXME: added random string to systemid, since e.g. one navigation tree can exist multiple times an a page. Maybe there's a better way to fix this.
        $arrTemplate["systemid"] = $strSystemid."_".generateSystemid();
        $arrTemplate["content"] = $strContent;
        $strReturn = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
        return $strReturn;
    }

    /**
     * Loads the link-content to be used when generating a new-icon-link
     * @return string
     */
    public function getPeNewButton($strPlaceholder, $strElement, $strElementName, $strElementHref) {
        $strAdminSkin = class_carrier::getInstance()->getObjSession()->getAdminSkin();
        $strTemplateID = $this->objTemplate->readTemplate("/admin/skins/".$strAdminSkin."/elements.tpl", "pe_actionNew", true);
        $strReturn = $this->objTemplate->fillTemplate(array("placeholder" => $strPlaceholder, "element" => $strElement, "elementName" => $strElementName, "elementHref" => $strElementHref), $strTemplateID);
        return $strReturn;
    }

    /**
     * Generates the icon / link to create a new element at an empty placeholder using the portaleditor
     *
     * @param string $strElementName
     * @param string $strNewLink
     * @return string
     */
    public function getPeNewButtonWrapper($strPlaceholder, $strPlaceholderName, $strLabel, $strContentElements) {
        $strAdminSkin = class_carrier::getInstance()->getObjSession()->getAdminSkin();
        $strTemplateWrapperID = $this->objTemplate->readTemplate("/admin/skins/".$strAdminSkin."/elements.tpl", "pe_actionNewWrapper", true);
        $strReturn = $this->objTemplate->fillTemplate(array("placeholder" => $strPlaceholder, "placeholderName" => $strPlaceholderName, "label" => $strLabel, "contentElements" => $strContentElements), $strTemplateWrapperID);
        return $strReturn;
    }


}
?>