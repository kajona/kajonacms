<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

/**
 * Portal-Class of the picture element
 *
 * @package modul_pages
 */
class class_element_image extends class_element_portal implements interface_portal_element  {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_image";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]			= _dbprefix_."element_image";

		parent::__construct($arrModule, $objElementData);
	}


	/**
	 * Returns the ready image-htmlcode
	 *
	 * @return string
	 */
	public function loadData() {
		$strReturn = "";


        $strTemplate = $this->arrElementData["image_template"];
        //fallback
        if($strTemplate == "")
            $strTemplate = "image.tpl";

        if($this->arrElementData["image_link"] != "")
            $strTemplateID = $this->objTemplate->readTemplate("/element_image/".$strTemplate, "imageWithLink");
        else
            $strTemplateID = $this->objTemplate->readTemplate("/element_image/".$strTemplate, "imageWithoutLink");


		
        //scale the image?
        $intMaxWidth = 0;
        $intMaxHeight = 0;

        if($this->arrElementData["image_x"] != "" && $this->arrElementData["image_x"] != 0)
            $intMaxWidth = (int)$this->arrElementData["image_x"];

        if($this->arrElementData["image_y"] != "" && $this->arrElementData["image_y"] != 0)
            $intMaxHeight = (int)$this->arrElementData["image_y"];

        if($intMaxHeight > 0 || $intMaxWidth > 0)
            $this->arrElementData["image_src"] = "/image.php?image=".urlencode($this->arrElementData["image_image"])."&amp;maxWidth=".$intMaxWidth."&amp;maxHeight=".$intMaxHeight;
        else
            $this->arrElementData["image_src"] = $this->arrElementData["image_image"];

		//Link?
		if($this->arrElementData["image_link"] != "") {
		    //internal page?
		    $objPage = class_modul_pages_page::getPageByName($this->arrElementData["image_link"]);
		    if($objPage->getStrName() != "")
			    $this->arrElementData["link_href"] = getLinkPortalHref($this->arrElementData["image_link"], "");
			else
			    $this->arrElementData["link_href"] = getLinkPortalHref("",$this->arrElementData["image_link"]);
		}


        $strReturn .= $this->fillTemplate($this->arrElementData, $strTemplateID);

		return $strReturn;
	}

}
?>