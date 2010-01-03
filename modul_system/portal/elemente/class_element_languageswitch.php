<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * Portal-Class of the picture element
 *
 * @package modul_languages
 */
class class_element_languageswitch extends class_element_portal implements interface_portal_element  {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_languageswitch";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]			= _dbprefix_."element_universal";
		$arrModule["modul"]          = "elemente";

		parent::__construct($arrModule, $objElementData);

	}


	/**
	 * Returns the ready switch-htmlcode
	 *
	 * @return string
	 */
	public function loadData() {

        //fallback for old elements not yet using the template
        if(!isset($this->arrElementData["char1"]) || $this->arrElementData["char1"] == "")
            $this->arrElementData["char1"] = "languageswitch.tpl";

		$strReturn = "";

        $arrObjLanguages = class_modul_languages_language::getAllLanguages(true);
        //Iterate over all languages
        $strRows = "";
        foreach($arrObjLanguages as $objOneLanguage) {
            //Check, if the current page has elements
            $objPage = class_modul_pages_page::getPageByName($this->getPagename());
            $objPage->setStrLanguage($objOneLanguage->getStrName());
            if((int)$objPage->getNumberOfElementsOnPage(true) > 0) {

                //and the link
                $arrTemplate = array();
                $arrTemplate["href"] = getLinkPortalHref($objPage->getStrName(), "", "", "", "", $objOneLanguage->getStrName());
                $arrTemplate["langname_short"] = $objOneLanguage->getStrName();
                $arrTemplate["langname_long"] = $this->getText("lang_".$objOneLanguage->getStrName());

                $strTemplateRowID = $this->objTemplate->readTemplate("/element_languageswitch/".$this->arrElementData["char1"], "languageswitch_entry");
                $strTemplateActiveRowID = $this->objTemplate->readTemplate("/element_languageswitch/".$this->arrElementData["char1"], "languageswitch_entry_active");

                if($objOneLanguage->getStrName() == $this->getPortalLanguage())
                    $strRows .= $this->fillTemplate($arrTemplate, $strTemplateActiveRowID);
                else
                    $strRows .= $this->fillTemplate($arrTemplate, $strTemplateRowID);

            }
        }

        $strTemplateWrapperID = $this->objTemplate->readTemplate("/element_languageswitch/".$this->arrElementData["char1"], "languageswitch_wrapper");
        $strReturn = $this->fillTemplate(array("languageswitch_entries" => $strRows), $strTemplateWrapperID);

		return $strReturn;
	}

}
?>