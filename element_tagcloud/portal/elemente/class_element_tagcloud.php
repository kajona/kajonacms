<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_tagcloud.php																		    *
* 	Portal-class of the tagcloud element															    *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_tagcloud.php 1884 2007-12-26 15:04:48Z sidler $                                  *
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
class class_element_tagcloud extends class_element_portal implements interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_tagcloud";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_universal";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Looks up the last modified-date of the current page
     *
     * @return string the prepared html-output
     */
	public function loadData() {
		$strReturn = "";
		
		//load the list of new-cats availableobjOneCat
		include_once(_systempath_."/class_modul_news_category.php");
		include_once(_systempath_."/class_modul_news_news.php");
		
		$arrCats = class_modul_news_category::getCategories();
		//fetch the number of news per cat
		$arrCatNumber = array();
		foreach ($arrCats as $objOneCat) {
			$intCount = count(class_modul_news_news::loadListNewsPortal(0, $objOneCat->getSystemid()));
			if($intCount > 0)
			    $arrCatNumber[$objOneCat->getSystemid()] = $intCount;
		}
		
		//sort the array by value to get a correct order
		asort($arrCatNumber);
		//rebuild the array to get a corrent cat-name <-> font-size relation
		$arrCatsFinal = array();
		$intNrCounter = -1;
		$intSizeStart = 10;
		foreach ($arrCatNumber as $objOneCatSysid => $intNrOfChilds) {
			$objOneCat = new class_modul_news_category($objOneCatSysid);
			if($intNrCounter == -1) {
				//first run -> use the start size
			    $intNrCounter = $intNrOfChilds;
			}
			else if($intNrCounter < $intNrOfChilds) {
				//more news in current cat than in cat before -> increase size
				$intSizeStart++;    
			}
			$arrCatsFinal[$objOneCat->getStrTitle()] = array($intSizeStart, $objOneCat);
		}
		
		//and resort the array again...
		ksort($arrCatsFinal);

        //load the template
        $strTemplateCloudID = $this->objTemplate->readTemplate("/element_tagcloud/".$this->arrElementData["char1"], "tagcloud");
        $strTemplateTagID = $this->objTemplate->readTemplate("/element_tagcloud/".$this->arrElementData["char1"], "tagname");
        
        //and print that funky shit...
        $strTags = "";
        foreach($arrCatsFinal as $arrCatDetails) {
        	$arrTemplate = array();
        	$arrTemplate["catname"] = $arrCatDetails[1]->getStrTitle();
        	$arrTemplate["fontsize"] = $arrCatDetails[0];
        	$strTags .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateTagID);
        }
        
        $strReturn = $this->objTemplate->fillTemplate(array("tags" => $strTags), $strTemplateCloudID);

		return $strReturn;
	}

}
?>