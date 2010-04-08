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
		$arrModule["name"] 			= "element_paragraph";
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


        //choose template section
        $strTemplateSection = "paragraph";
        if ($this->arrElementData["paragraph_image"] != "" && $this->arrElementData["paragraph_link"] != "") {
            $strTemplateSection = "paragraph_image_link";
        } else if ($this->arrElementData["paragraph_image"] != "" && $this->arrElementData["paragraph_link"] == "") {
            $strTemplateSection = "paragraph_image";
        } else if ($this->arrElementData["paragraph_image"] == "" && $this->arrElementData["paragraph_link"] != "") {
            $strTemplateSection = "paragraph_link";
        }

        $strTemplateID = $this->objTemplate->readTemplate("/element_paragraph/".$strTemplate, $strTemplateSection);

        if($this->arrElementData["paragraph_image"] != "") {
            //remove the webpath (was added for paragraphs saved pre 3.3.0)
            $this->arrElementData["paragraph_image"] = str_replace("_webpath_", "", $this->arrElementData["paragraph_image"]);
            $this->arrElementData["paragraph_image"] = urlencode($this->arrElementData["paragraph_image"]);
        }

        if($this->arrElementData["paragraph_link"] != "") {
		    //internal page?
		    $objPage = class_modul_pages_page::getPageByName($this->arrElementData["paragraph_link"]);
		    if($objPage->getStrName() != "")
			    $this->arrElementData["paragraph_link"] = getLinkPortalHref($this->arrElementData["paragraph_link"]);
			else
			    $this->arrElementData["paragraph_link"] = getLinkPortalHref("", $this->arrElementData["paragraph_link"]);
		}

        $strReturn .= $this->fillTemplate($this->arrElementData, $strTemplateID);

        return $strReturn;
	}

}
?>