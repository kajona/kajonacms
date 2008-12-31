<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

//Base Class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");
include_once(_systempath_."/class_modul_pages_page.php");

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

		$arrModul["name"] 			= "element_languageswitch";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"]			= "";
		$arrModul["modul"]          = "elemente";

		parent::__construct($arrModul, $objElementData);

	}


	/**
	 * Returns the ready switch-htmlcode
	 *
	 * @return string
	 */
	public function loadData() {
		$strReturn = "";

        include_once(_systempath_."/class_modul_languages_language.php");
        $arrObjLanguages = class_modul_languages_language::getAllLanguages(true);
        //Iterate over all languages
        foreach($arrObjLanguages as $objOneLanguage) {
            //Check, if the current page has elements
            include_once(_systempath_."/class_modul_pages_page.php");
            $objPage = class_modul_pages_page::getPageByName($this->getPagename());
            $objPage->setStrLanguage($objOneLanguage->getStrName());
            if((int)$objPage->getNumberOfElementsOnPage(true) > 0) {
                $strReturn .= " ";
                $strQueryString = "?".getServer("QUERY_STRING");
                //Remove old language-param, if given
                $strQueryString = preg_replace('/(\?|&)language=([a-z]+)/', "", $strQueryString);

                //remove systemids and actions
                $strQueryString = preg_replace('/(\?|&)action=([a-z]+)/i', "", $strQueryString);
                $strQueryString = preg_replace('/(\?|&)systemid=([a-z0-9]+)/i', "", $strQueryString);

                //first char a '?' ?
                if(uniStrlen($strQueryString) > 0 && $strQueryString[0] == '?')
                	$strQueryString = uniSubstr($strQueryString, 1);
                //and attach the new language param
                if(uniStrlen($strQueryString) == 0) {
                	$strQueryString = "language=".$objOneLanguage->getStrName();
                }
                else {
                	$strQueryString .= "&amp;language=".$objOneLanguage->getStrName();
                }

               //and the link
               //add html code to modify the lool and feel of the buttons (e.g. <img src=\"language_".$objOneLanguage->getStrName().".gif\" />
                $strReturn .= "<a href=\""._indexpath_."?".$strQueryString."\">"
                              //.$this->getText("lang_".$objOneLanguage->getStrName())
                              .$objOneLanguage->getStrName()
                              ."</a>";
            }
        }


		return $strReturn;
	}

}
?>