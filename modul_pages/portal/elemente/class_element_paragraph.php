<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * Portal-Part of the paragraph
 *
 * @package modul_pages
 */
class class_element_paragraph extends class_element_portal implements  interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_absatz";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]			= _dbprefix_."element_paragraph";

		parent::__construct($arrModule, $objElementData);
	}


	/**
	 * Does a little "make-up" to the contents
	 *
	 * @return string
	 */
	public function loadData() {

		$strReturn = "";

        $strTemplate = $this->arrElementData["paragraph_template"];
        //fallback
        if($strTemplate == "")
            $strTemplate = "paragraph.tpl";

        $strTemplateID = $this->objTemplate->readTemplate("/element_paragraph/".$strTemplate, "paragraph");

        $this->arrElementData["paragraph_image_tag"] = "";
        if($this->arrElementData["paragraph_image"] != "")
			$this->arrElementData["paragraph_image_tag"] .= "<img src=\"".$this->arrElementData["paragraph_image"]."\" alt=\"".$this->arrElementData["paragraph_title"]."\" />\n";
            
        $this->arrElementData["paragraph_link_tag"] = "";
        if($this->arrElementData["paragraph_link"] != "") {
		    //internal page?
		    $objPage = class_modul_pages_page::getPageByName($this->arrElementData["paragraph_link"]);
		    if($objPage->getStrName() != "")
			    $this->arrElementData["paragraph_link_tag"] .= "<a href=\"".getLinkPortalHref($this->arrElementData["paragraph_link"], "")."\">".$this->arrElementData["paragraph_link"]."</a>\n";
			else
			    $this->arrElementData["paragraph_link_tag"] .= "<a href=\"".getLinkPortalHref("", $this->arrElementData["paragraph_link"])."\">".$this->arrElementData["paragraph_link"]."</a>\n";
		}

        $strReturn .= $this->fillTemplate($this->arrElementData, $strTemplateID);

        return $strReturn;
	}

}	 //class_element_absatz
?>