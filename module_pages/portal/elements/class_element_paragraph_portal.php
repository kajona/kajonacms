<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * Portal-Part of the paragraph
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 */
class class_element_paragraph_portal extends class_element_portal implements  interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_paragraph";
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
		    $objPage = class_module_pages_page::getPageByName($this->arrElementData["paragraph_link"]);
		    if($objPage->getStrName() != "")
			    $this->arrElementData["paragraph_link"] = getLinkPortalHref($this->arrElementData["paragraph_link"]);
			else
			    $this->arrElementData["paragraph_link"] = getLinkPortalHref("", $this->arrElementData["paragraph_link"]);
		}

		if($this->arrElementData["paragraph_title"] != "") {
			$strTemplateTitleID = $this->objTemplate->readTemplate("/element_paragraph/".$strTemplate, "paragraph_title_tag");
			$this->arrElementData["paragraph_title_tag"] = $this->fillTemplate($this->arrElementData, $strTemplateTitleID);
		}

        $strReturn .= $this->fillTemplate($this->arrElementData, $strTemplateID);

        return $strReturn;
	}

}
?>