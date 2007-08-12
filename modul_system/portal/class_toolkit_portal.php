<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_toolkit_portal.php																			*
* 	Portal-part of the toolkits																			*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

include_once(_systempath_."/class_toolkit.php");

/**
 * Portal-Part of the toolkit.
 * Provides a few helpers
 *
 * @package modul_system
 */
class class_toolkit_portal extends class_toolkit {

	public function __construct($strSystemid = 0) {
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
	 * @return mixed array containing the created data:
	 * 						return => [arrData] = array containing the shortened data
	 * 								  [strForward] = link to the next page
	 * 								  [strBack]	= link to the previous page
	 * 								  [strPages] = Pager ( [0][1] ...)
	 */
	public function pager($intNumber, $intPage = 1, $strForward = "weiter", $strBack = "zurueck", $strAction = "list", $strPage = "", $arrData, $strAdd = "") {
		$arrReturn = 	array("arrData" 	=> array(),
							  "strForward" 	=> "",
							  "strBack" 	=> "",
							  "strPages" 	=> "");
		$arrTemp = array();


		//create array-iterator
		include_once(_systempath_."/class_array_iterator.php");
		$objIterator = new class_array_iterator($arrData);
		$objIterator->setIntElementsPerPage($intNumber);

		//Size of the passed array
		$intLength = $objIterator->getNumberOfElements();

		//Anything to create?
		if($objIterator->getNrOfPages() == 1) {
			$arrReturn["arrData"] = $arrData;
		}
		else {
			$intCounter = 1;
			$strLinkPages = "";
			$strLinkForward = "";
			$strLinkBack = "";

			$arrReturn["arrData"] = $objIterator->getElementsOnPage($intPage);

			//FowardLink
			if($intPage < $objIterator->getNrOfPages())
				$strLinkForward = getLinkPortal($strPage, "", null, $strForward, $strAction, "&pv=".($intPage+1).$strAdd);
			//BackLink
			if($intPage > 1)
				$strLinkBack = getLinkPortal($strPage, "", null, $strBack, $strAction, "&pv=".($intPage-1).$strAdd);
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
    					$strLinkPages .= "  <strong>".getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&pv=".$intI.$strAdd)."</strong>";
    				else
    					$strLinkPages .= "  ".getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&pv=".$intI.$strAdd);
                }
                $intCounter2++;
            }



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
        $arrTemplate["systemid"] = $strSystemid;
        $arrTemplate["content"] = $strContent;
        $strReturn = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
        return $strReturn;
    }

}
?>